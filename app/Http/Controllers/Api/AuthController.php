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

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'user',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Register berhasil',
            'data' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)
            ->where('role', 'user')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        Otp::where('user_id', $user->id)->update([
            'is_used' => true
        ]);

        $otpCode = (string) random_int(100000, 999999);

        Otp::create([
            'user_id' => $user->id,
            'otp_code' => $otpCode,
            'expired_at' => Carbon::now()->addMinutes(5),
            'is_used' => false,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Login berhasil, OTP telah dibuat',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'otp_code' => $otpCode
            ]
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'otp_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $otp = Otp::where('user_id', $request->user_id)
            ->where('otp_code', $request->otp_code)
            ->where('is_used', false)
            ->first();

        if (!$otp) {
            return response()->json([
                'status' => false,
                'message' => 'OTP tidak valid'
            ], 401);
        }

        if (Carbon::now()->gt(Carbon::parse($otp->expired_at))) {
            return response()->json([
                'status' => false,
                'message' => 'OTP sudah kedaluwarsa'
            ], 401);
        }

        $otp->update([
            'is_used' => true
        ]);

        $user = User::find($request->user_id);

        $token = $user->createToken('user_auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'OTP berhasil diverifikasi',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]
        ]);
    }
}