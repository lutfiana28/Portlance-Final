<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AdminAccountController extends Controller
{
    use ApiResponse;

    public function profile(Request $request)
    {
        return $this->successResponse(
            'Profile admin berhasil diambil',
            $request->user()
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(
            'Logout admin berhasil'
        );
    }
}