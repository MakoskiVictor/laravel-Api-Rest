<?php

namespace App\Helpers;

class ResponseHelper {
    public static function successResponse($message = 'Succes', $status = 200) {
        return response()->json([
            'message' => $message,
            'status' => $status
        ], $status);
    }

    public static function successResponseWithData($data = [], $message = 'Succes', $status = 200) {
        return response()->json([
            'message' => $message,
            'status' => $status,
            'data' => $data
        ], $status);
    }

    public static function errorResponse($message = 'Error', $status = 400) {
        return response()->json([
            'message' => $message,
            'status' => $status
        ], $status);
    }

    public static function notFoundResponse($message = 'Not found') {
        return self::errorResponse($message, 404);
    }

    public static function serverErrorResponse($message = 'Server error') {
        return self::errorResponse($message, 500);
    }
}