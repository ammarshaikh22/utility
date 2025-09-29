<?php

namespace App\Helper;

class Common
{
    /**
     * Format a date with color coding based on whether it is today or in the past.
     *
     * @param \Carbon\Carbon|null $date The date to format
     * @param bool $past Whether to highlight past dates in red (default: true)
     * @return string The formatted date with HTML color styling
     */
    public static function dateColor($date, $past = true): string
    {
        if (is_null($date)) {
            return '--';
        }

        $formattedDate = $date->translatedFormat(company()->date_format);
        $todayText = __('app.today');

        if ($date->setTimezone(company()->timezone)->isToday()) {
            return '<span class="text-success">' . $todayText . '</span>';
        }

        if ($date->endOfDay()->isPast() && $past) {
            return '<span class="text-danger">' . $formattedDate . '</span>';
        }

        return '<span>' . $formattedDate . '</span>';
    }

    /**
     * Return HTML markup for an active status indicator.
     *
     * @return string HTML string with a green circle and 'Active' text
     */
    public static function active(): string
    {
        return '<i class="fa fa-circle mr-1 text-light-green f-10"></i>' . __('app.active');
    }

    /**
     * Return HTML markup for an inactive status indicator.
     *
     * @return string HTML string with a red circle and 'Inactive' text
     */
    public static function inactive(): string
    {
        return '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.inactive');
    }

    /**
     * Encrypt or decrypt a string using AES-256-CBC.
     *
     * @param string $string The string to encrypt or decrypt
     * @param string $action The action to perform ('encrypt' or 'decrypt')
     * @return string The encrypted or decrypted string
     * @throws \Exception If an invalid action is provided
     */
    public static function encryptDecrypt($string, $action = 'encrypt')
    {
        // DO NOT CHANGE IT. CHANGING IT WILL AFFECT THE APPLICATION
        $secret_key = 'worksuite'; // User define private key
        $secret_iv = 'froiden'; // User define secret key

        $encryptMethod = 'AES-256-CBC';
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo

        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encryptMethod, $key, 0, $iv);

            return base64_encode($output);
        }

        if ($action == 'decrypt') {
            return openssl_decrypt(base64_decode($string), $encryptMethod, $key, 0, $iv);
        }

        throw new \Exception('No action provided for Common::encryptDecrypt');
    }

    /**
     * Sanitize a string to ensure it is safe for use in queries.
     *
     * @param mixed $string The input to sanitize
     * @return string The sanitized string
     * @throws \Exception If the input is invalid (not a string or exceeds 255 characters)
     */
    public static function safeString($string)
    {
        if (empty($string) || $string == null) {
            return '';
        }

        if (!is_string($string) || strlen($string) > 255) {
            abort(400, 'Invalid search term.');
        }
        return $string;
    }
}