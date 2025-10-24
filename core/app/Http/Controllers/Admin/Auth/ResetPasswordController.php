<?php

namespace App\Http\Controllers\Admin\Auth;

/**
 * Reset Password Controller - Finalizes admin password reset process
 * Validates token, updates password, and sends confirmation email
 */

use App\Constants\Status;
use App\Models\Admin;
use App\Models\AdminPasswordReset;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /**
     * Display password reset form for valid token
     * @param Request $request
     * @param string $token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showResetForm(Request $request, $token)
    {
        $this->command->info("   🔑 Loading password reset form for token: {$token}...");

        $pageTitle = "Account Recovery";
        $resetToken = AdminPasswordReset::where('token', $token)
            ->where('status', Status::ENABLE)
            ->first();

        if (!$resetToken) {
            $this->command->warn("      ⚠ Token {$token} is invalid or expired");
            return to_route('admin.password.reset')
                ->withNotify([['error', 'Verification code mismatch']]);
        }

        $email = $resetToken->email;
        $this->command->info("      ✓ Valid token for email: {$email}");
        return view('admin.auth.passwords.reset', compact('pageTitle', 'email', 'token'));
    }

    /**
     * Process password reset with validated inputs
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        $this->command->info("   🔄 Resetting password for {$request->email}...");

        // Validate input
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|confirmed|min:4',
        ], [
            'password.confirmed' => 'Password confirmation does not match',
            'password.min' => 'Password must be at least 4 characters',
        ]);

        // Verify reset token
        $reset = AdminPasswordReset::where('token', $request->token)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$reset || $reset->status == Status::DISABLE) {
            $this->command->warn("      ⚠ Invalid or disabled token: {$request->token}");
            return to_route('admin.login')
                ->withNotify([['error', 'Invalid code']]);
        }

        // Verify admin account
        $admin = Admin::where('email', $reset->email)->first();
        if (!$admin) {
            $this->command->warn("      ⚠ No admin found for email: {$reset->email}");
            return to_route('admin.login')
                ->withNotify([['error', 'Invalid email']]);
        }

        // Update password and disable token
        $admin->password = Hash::make($request->password);
        $admin->save();

        $reset->status = Status::DISABLE;
        $reset->save();

        // Send confirmation email
        $ipInfo = getIpInfo();
        $browser = osBrowser();
        notify($admin, 'PASS_RESET_DONE', [
            'operating_system' => $browser['os_platform'],
            'browser' => $browser['browser'],
            'ip' => $ipInfo['ip'],
            'time' => $ipInfo['time']
        ], ['email'], false);

        $this->command->info("      ✓ Password reset completed for {$admin->email}");

        return to_route('admin.login')
            ->withNotify([['success', 'Password changed']]);
    }
}