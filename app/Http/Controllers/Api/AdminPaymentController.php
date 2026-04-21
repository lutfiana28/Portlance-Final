<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminPaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with([
            'order.user',
            'order.template',
            'order.package',
            'verifier'
        ])->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar pembayaran berhasil diambil',
            'data' => $payments
        ]);
    }

    public function verify(Request $request, $id)
    {
        $payment = Payment::with('order')->find($id);

        if (!$payment) {
            return response()->json([
                'status' => false,
                'message' => 'Data pembayaran tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'verification_status' => 'required|in:valid,invalid',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($payment->verification_status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Pembayaran ini sudah pernah diverifikasi'
            ], 422);
        }

        $payment->update([
            'verification_status' => $request->verification_status,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'notes' => $request->notes ?? $payment->notes,
        ]);

        if ($request->verification_status === 'valid') {
            $payment->order->update([
                'payment_status' => 'paid',
                'order_status' => 'in_progress',
            ]);
        }

        if ($request->verification_status === 'invalid') {
            $payment->order->update([
                'payment_status' => 'rejected',
                'order_status' => 'waiting_payment',
            ]);
        }

        $payment->load([
            'order.user',
            'order.template',
            'order.package',
            'verifier'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Pembayaran berhasil diverifikasi',
            'data' => $payment
        ]);
    }
}