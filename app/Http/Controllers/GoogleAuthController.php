<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Services\Google;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GoogleAuthController extends Controller
{
    /**
     * Handle Google OAuth flow and account linking.
     */
    public function index(Request $request, Google $google)
    {
        if (!$request->code) {
            /** @phpstan-ignore-next-line */
            return redirect($google->createAuthUrl());
        }

        // WORKSUITESAAS - Handle multi-tenant callback
        if ($request->state) {
            /** @phpstan-ignore-next-line */
            $google->authenticate($request->code);
            $account = $google->service('Oauth2')->userinfo->get();

            return redirect($request->state . '?google_id=' . $account->id .
                '&userName=' . $account->name .
                '&access_token=' . json_encode($google->getAccessToken()) .
                '&code=' . $request->code);
        }

        // Standard Worksuite handling
        if (isWorksuite()) {
            /** @phpstan-ignore-next-line */
            $google->authenticate($request->code);
            $account = $google->service('Oauth2')->userinfo->get();
        }

        $googleAccount = companyOrGlobalSetting();

        // Show appropriate success message
        if (
            empty($googleAccount->user_id) &&
            empty($googleAccount->google_id) &&
            empty($googleAccount->name) &&
            empty($googleAccount->token)
        ) {
            Session::flash('message', __('messages.googleCalendar.verifiedSuccess'));
        } else {
            Session::flash('message', __('messages.googleCalendar.updatedSuccess'));
        }

        // Update verification status
        $googleAccount->google_calendar_verification_status = 'verified';

        if (isWorksuite()) {
            $googleAccount->google_id = $account->id;
            $googleAccount->name = $account->name;
            /** @phpstan-ignore-next-line */
            $googleAccount->token = $google->getAccessToken();
        } else {
            // WORKSUITESAAS
            $googleAccount->google_id = $request->google_id;
            $googleAccount->name = $request->userName;
            /** @phpstan-ignore-next-line */
            $googleAccount->token = $request->access_token;
        }

        $googleAccount->update();

        cache()->forget('global_setting');

        return redirect()->route('google-calendar-settings.index');
    }

    /**
     * Remove Google account connection.
     */
    public function destroy()
    {
        $googleAccount = companyOrGlobalSetting();

        $googleAccount->update([
            'google_calendar_verification_status' => 'non_verified',
            'google_id' => '',
            'name' => '',
            'token' => '',
        ]);

        session()->forget(['company_setting', 'company']);
        cache()->forget('global_setting');

        return Reply::success(__('messages.googleCalendar.removedSuccess'));
    }
}
