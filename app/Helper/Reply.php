<?php

namespace App\Helper;

class Reply
{
    /**
     * Return a success response with a translated message.
     *
     * @param string $message The message key to translate
     * @return array The success response array
     */
    public static function success($message)
    {
        return [
            'status' => 'success',
            'message' => self::getTranslated($message)
        ];
    }

    /**
     * Return a success response with additional data and a translated message.
     *
     * @param string $message The message key to translate
     * @param array $data Additional data to include in the response
     * @return array The success response array with merged data
     */
    public static function successWithData($message, $data)
    {
        $response = self::success($message);

        return array_merge($response, $data);
    }

    /**
     * Return an error response with a translated message and optional error details.
     *
     * @param string $message The message key to translate
     * @param null|string $error_name The name of the error (optional)
     * @param array $errorData Additional error data (optional)
     * @return array The error response array
     */
    public static function error($message, $error_name = null, $errorData = [])
    {
        return [
            'status' => 'fail',
            'error_name' => $error_name,
            'data' => $errorData,
            'message' => self::getTranslated($message)
        ];
    }

    /**
     * Return validation errors from a validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator The validator instance
     * @return array The error response with validation errors
     */
    public static function formErrors($validator)
    {
        return [
            'status' => 'fail',
            'errors' => $validator->getMessageBag()->toArray()
        ];
    }

    /**
     * Return a redirect response for AJAX requests with an optional translated message.
     *
     * @param string $url The URL to redirect to
     * @param null|string $message The optional message key to translate
     * @return array The redirect response array
     */
    public static function redirect($url, $message = null)
    {
        if ($message != null) {
            return [
                'status' => 'success',
                'message' => self::getTranslated($message),
                'action' => 'redirect',
                'url' => $url
            ];
        }

        return [
            'status' => 'success',
            'action' => 'redirect',
            'url' => $url
        ];
    }

    /**
     * Translate a message using Laravel's translation system, returning the original if not found.
     *
     * @param string $message The message key to translate
     * @return string The translated message or original message if translation not found
     */
    private static function getTranslated($message)
    {
        $trans = trans($message);

        if ($trans == $message) {
            return $message;
        }

        return $trans;
    }

    /**
     * Return data without wrapping it in a status/message structure.
     *
     * @param mixed $data The data to return
     * @return mixed The raw data
     */
    public static function dataOnly($data)
    {
        return $data;
    }
}