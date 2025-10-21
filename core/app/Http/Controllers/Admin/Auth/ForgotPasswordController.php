<?php

namespace App\Http\Controllers\Admin\Auth;

/**
 * Forgot Password Controller - Handles admin password reset flow
 * 4-step process: Request → Email Code → Verify Code → Reset Password
 */

use App\Models\Admin;
use App\Models\AdminPasswordReset;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Constants\Status;

class ForgotPasswordController extends Controller
{
    /**
     * Display password reset request form
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        $pageTitle = 'Account Recovery';
        return view('admin.auth.passwords.email', compact('pageTitle'));
    }

    /**
     * Send 6-digit reset code to admin's email
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetCodeEmail(Request $request)
    {
        $this->command->info("   🔑 Processing password reset request for {$request->email}...");

        // Validate input
        $request->validate([
            'email' => 'required|email',
        ]);

        // Verify CAPTCHA
        if (!verifyCaptcha()) {
            $this->command->warn("      ⚠ Invalid CAPTCHA provided");
            return back()->withNotify([['error', 'Invalid captcha provided']]);
        }

        // Check if admin exists
        $admin = Admin::where('email', $request->email)->first();
        if (!$admin) {
            $this->command->warn("      ⚠ No admin found for {$request->email}");
            return back()->withNotify([['error', 'No admin account found with this email']]);
        }

        // Generate and store 6-digit code
        $code = verificationCode(6);
        $adminPasswordReset = new AdminPasswordReset();
        $adminPasswordReset->email = $admin->email;
        $adminPasswordReset->token = $code;
        $adminPasswordReset->created_at = Carbon::now();
        $adminPasswordReset->save();

        // Gather device info
        $adminIpInfo = getIpInfo();
        $adminBrowser = osBrowser();

        // Send email notification
        notify($admin, 'PASS_RESET_CODE', [
            'code' => $code,
            'operating_system' => $adminBrowser['os_platform'],
            'browser' => $adminBrowser['browser'],
            'ip' => $adminIpInfo['ip'],
            'time' => $adminIpInfo['time']
        ], ['email'], false);

        $this->command->info("      ✓ Reset code sent to {$admin->email}");

        // Store email in session
        session()->put('pass_res_mail', $admin->email);

        return to_route('admin.password.code.verify');
    }

    /**
     * Display code verification form
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function codeVerify()
    {
        $pageTitle = 'Verify Code';
        $email = session()->get('pass_res_mail');

        if (!$email) {
            $this->command->warn("      ⚠ Session expired for password reset");
            return to_route('admin.password.reset')->withNotify([['error', 'Oops! session expired']]);
        }

        return view('admin.auth.passwords.code_verify', compact('pageTitle', 'email'));
    }

    /**
     * Verify the submitted reset code
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyCode(Request $request)
    {
        $request->validate(['code' => 'required']);
        $email = session()->get('pass_res_mail');
        $this->command->info("      🔍 Verifying reset code for {$email}...");

        $adminPasswordReset = AdminPasswordReset::where('email', $email)
            ->where('status', Status::ENABLE)
            ->orderBy('id', 'desc')
            ->first();

        $code = str_replace(' ', '', $request->code);
        if (!$adminPasswordReset || $adminPasswordReset->token !== $code) {
            $this->command->warn("      ⚠ Invalid or mismatched verification code");
            return to_route('admin.login')->withNotify([['error', 'Verification code does not match']]);
        }

        $this->command->info("      ✓ Code verified successfully");
        return to_route('admin.password.reset.form', $code)
            ->withNotify([['success', 'You can change your password']]);
    }
}