<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validasi gagal',
                $validator->errors(),
                422
            );
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'user',
        ]);

        return $this->successResponse(
            'Register berhasil',
            $user,
            201
        );
    }

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

        $user = User::where('email', $request->email)
            ->where('role', 'user')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse(
                'Email atau password salah',
                null,
                401
            );
        }

        Otp::where('user_id', $user->id)->update([
            'is_used' => true
        ]);

        $otpCode = (string) random_int(100000, 999999);

        Otp::create([
            'user_id' => $user->id,
            'otp_code' => $otpCode,
            'expired_at' => now()->addMinutes(5),
            'is_used' => false,
        ]);

        return $this->successResponse(
            'Login berhasil, OTP telah dibuat',
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'otp_code' => $otpCode
            ]
        );
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'otp_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validasi gagal',
                $validator->errors(),
                422
            );
        }

        $otp = Otp::where('user_id', $request->user_id)
            ->where('otp_code', $request->otp_code)
            ->where('is_used', false)
            ->first();

        if (!$otp) {
            return $this->errorResponse('OTP tidak valid', null, 401);
        }

        if (now()->gt($otp->expired_at)) {
            return $this->errorResponse('OTP sudah kedaluwarsa', null, 401);
        }

        $otp->update([
            'is_used' => true
        ]);

        $user = User::find($request->user_id);

        $token = $user->createToken('user_auth_token')->plainTextToken;

        return $this->successResponse(
            'OTP berhasil diverifikasi',
            [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]
        );
    }
}