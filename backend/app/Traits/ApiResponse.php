<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse(
        string $message = 'Success',
        $data = null,
        int $statusCode = 200
    ) {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    protected function errorResponse(
        string $message = 'Error',
        $errors = null,
        int $statusCode = 400
    ) {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}