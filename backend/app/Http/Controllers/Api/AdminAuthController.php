<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class AdminAuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validasi gagal',
                $validator->errors(),
                422
            );
        }

        $admin = User::where('email', $request->email)
            ->where('role', 'admin')
            ->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return $this->errorResponse(
                'Email atau password admin salah',
                null,
                401
            );
        }

        $token = $admin->createToken('admin_auth_token')->plainTextToken;

        return $this->successResponse(
            'Login admin berhasil',
            [
                'token' => $token,
                'token_type' => 'Bearer',
                'admin' => $admin
            ]
        );
    }
}