<?php

namespace App\Providers;

use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use App\Traits\HasMaskImage;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Session\SessionServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;

/**
 * This class is used to set the SMTP configuration, push notifications, session, driver,
 * and translation settings. This is done via a service provider to ensure functionality
 * during supervisor processes, as database configuration in controllers may not work.
 */
class CustomConfigProvider extends ServiceProvider
{
    use HasMaskImage;

    const ALL_ENVIRONMENT = ['demo', 'development', 'production'];

    /**
     * Register application services and configurations.
     * This method fetches settings from multiple database tables in a single query,
     * configures mail, push notifications, session driver, translation, and Stripe settings,
     * and registers core Laravel service providers (Mail, Queue, Session).
     *
     * @return void
     */
    public function register()
    {
        try {
            // Fetch all settings in a single query
            $setting = DB::table('smtp_settings')
                ->join('global_settings', function ($join) {
                    $join->on('global_settings.id', '=', DB::raw('global_settings.id'));
                })
                ->leftJoin('push_notification_settings', function ($join) {
                    $join->on('push_notification_settings.id', '=', DB::raw('push_notification_settings.id'));
                })
                ->leftJoin('translate_settings', function ($join) {
                    $join->on('translate_settings.id', '=', DB::raw('translate_settings.id'));
                })
                ->leftJoin('global_payment_gateway_credentials', function ($join) {
                    $join->on('global_payment_gateway_credentials.id', '=', DB::raw('global_payment_gateway_credentials.id'));
                })
                ->select(
                    'smtp_settings.*',
                    'global_settings.global_app_name',
                    'global_settings.session_driver',
                    'global_settings.timezone',
                    'global_settings.light_logo',
                    'push_notification_settings.onesignal_app_id',
                    'push_notification_settings.onesignal_rest_api_key',
                    'translate_settings.google_key',
                    'global_payment_gateway_credentials.stripe_mode',
                    'global_payment_gateway_credentials.test_stripe_client_id',
                    'global_payment_gateway_credentials.test_stripe_secret',
                    'global_payment_gateway_credentials.test_stripe_webhook_secret',
                    'global_payment_gateway_credentials.live_stripe_client_id',
                    'global_payment_gateway_credentials.live_stripe_secret',
                    'global_payment_gateway_credentials.live_stripe_webhook_secret'
                )
                ->first();

            if ($setting) {
                $this->setMailConfig($setting);
                $this->setPushNotification($setting);
                $this->setSessionDriver($setting);
                $this->translateSettingConfig($setting);
                $this->setStripConfigs($setting);
            }
        } catch (\Exception $e) {
            // info($e->getMessage());
            // Handle exceptions appropriately, e.g., log the error
        }

        $app = App::getInstance();
        $app->register(MailServiceProvider::class);
        $app->register(QueueServiceProvider::class);
        $app->register(SessionServiceProvider::class);
    }

    /**
     * Configure mail settings based on database settings.
     * This method sets up the mail driver, SMTP configuration, application name,
     * and logo, with fallback values where necessary. It also handles email verification
     * and queue connection settings.
     *
     * @param object $setting The database settings object containing mail configuration.
     * @return void
     */
    public function setMailConfig($setting)
    {
        if (!in_array(app()->environment(), self::ALL_ENVIRONMENT)) {
            $driver = ($setting->mail_driver != 'mail') ? $setting->mail_driver : 'sendmail';

            // Decrypt the password to be used
            $password = Crypt::decryptString($setting->mail_password);

            Config::set('mail.default', $driver);
            Config::set('mail.mailers.smtp.host', $setting->mail_host);
            Config::set('mail.mailers.smtp.port', $setting->mail_port);
            Config::set('mail.mailers.smtp.username', $setting->mail_username);
            Config::set('mail.mailers.smtp.password', $password);
            Config::set('mail.mailers.smtp.encryption', $setting->mail_encryption);

            Config::set('mail.verified', (bool)$setting->email_verified);
            Config::set('queue.default', $setting->mail_connection);
        }

        Config::set('mail.from.name', $setting->mail_from_name);
        Config::set('mail.from.address', $setting->mail_from_email);

        Config::set('app.name', $setting->global_app_name);
        Config::set('app.global_app_name', $setting->global_app_name);
        Config::set('app.logo', is_null($setting->light_logo) ? asset('img/worksuite-logo.png') : $this->generateMaskedImageAppUrl('app-logo/' . $setting->light_logo));
    }

    /**
     * Configure push notification settings for OneSignal.
     * This method sets the OneSignal app ID and REST API key in the configuration
     * if they are available in the settings.
     *
     * @param object $setting The database settings object containing push notification configuration.
     * @return void
     */
    public function setPushNotification($setting)
    {
        // Set push notification settings if available
        if ($setting->onesignal_app_id && $setting->onesignal_rest_api_key) {
            Config::set('services.onesignal.app_id', $setting->onesignal_app_id);
            Config::set('services.onesignal.rest_api_key', $setting->onesignal_rest_api_key);
            Config::set('onesignal.app_id', $setting->onesignal_app_id);
            Config::set('onesignal.rest_api_key', $setting->onesignal_rest_api_key);
        }
    }

    /**
     * Configure the session driver and application timezone.
     * This method sets the session driver (defaulting to 'file' if not specified)
     * and the cron timezone based on the provided settings.
     *
     * @param object $setting The database settings object containing session and timezone configuration.
     * @return void
     */
    public function setSessionDriver($setting)
    {
        Config::set('session.driver', $setting->session_driver != '' ? $setting->session_driver : 'file');
        Config::set('app.cron_timezone', $setting->timezone);
    }

    /**
     * Configure translation settings for Google Translate.
     * This method sets the Google Translate API key in the configuration.
     *
     * @param object $setting The database settings object containing translation configuration.
     * @return void
     */
    public function translateSettingConfig($setting)
    {
        Config::set('laravel_google_translate.google_translate_api_key', $setting->google_key);
    }

    /**
     * Configure Stripe payment gateway settings.
     * This method sets up Stripe credentials (client ID, secret, and webhook secret)
     * based on the mode (test or live) and falls back to environment variables if necessary.
     * It also sets the Stripe API key.
     *
     * @param object $setting The database settings object containing Stripe configuration.
     * @return void
     */
    public function setStripConfigs($setting)
    {
        if ($setting->stripe_mode === 'test') {
            $stripeClientId = $setting->test_stripe_client_id;
            $stripeSecret = $setting->test_stripe_secret;
            $stripeWebhookSecret = $setting->test_stripe_webhook_secret;
        } else {
            $stripeClientId = $setting->live_stripe_client_id;
            $stripeSecret = $setting->live_stripe_secret;
            $stripeWebhookSecret = $setting->live_stripe_webhook_secret;
        }

        $key = ($stripeClientId) ?: env('STRIPE_KEY');
        $apiSecret = ($stripeSecret) ?: env('STRIPE_SECRET');
        $webhookKey = ($stripeWebhookSecret) ?: env('STRIPE_WEBHOOK_SECRET');

        Config::set('cashier.key', $key);
        Config::set('cashier.secret', $apiSecret);
        Config::set('cashier.webhook.secret', $webhookKey);

        Stripe::setApiKey(config('cashier.secret'));
    }

    /**
     * Bootstrap any application services.
     * This method is currently empty but can be used for additional setup if needed.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}