<?php
namespace App\Core;

/**
 * Standardized JSON response handler
 * Ensures consistent API response format across endpoints
 */
class Response {

    /**
     * Send success response
     */
    public static function success($data = null, $message = null, $code = 200) {
        self::sendResponse([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $code);
    }

    /**
     * Send error response
     */
    public static function error($message = null, $error = 'error', $code = 400) {
        self::sendResponse([
            'success' => false,
            'error' => $error,
            'message' => $message
        ], $code);
    }

    /**
     * Send validation error response
     */
    public static function validationError($errors, $code = 422) {
        self::sendResponse([
            'success' => false,
            'error' => 'validation_error',
            'message' => 'Validation failed',
            'errors' => $errors
        ], $code);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized($message = 'Unauthorized') {
        self::sendResponse([
            'success' => false,
            'error' => 'unauthorized',
            'message' => $message
        ], 401);
    }

    /**
     * Forbidden response
     */
    public static function forbidden($message = 'Forbidden') {
        self::sendResponse([
            'success' => false,
            'error' => 'forbidden',
            'message' => $message
        ], 403);
    }

    /**
     * Not found response
     */
    public static function notFound($message = 'Not found') {
        self::sendResponse([
            'success' => false,
            'error' => 'not_found',
            'message' => $message
        ], 404);
    }

    /**
     * Send HTTP response and exit
     */
    private static function sendResponse($response, $code = 200) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
