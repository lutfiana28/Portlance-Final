<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserPaymentController extends Controller
{
    public function store(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order tidak ditemukan atau bukan milik user'
            ], 404);
        }

        if ($order->payment) {
            return response()->json([
                'status' => false,
                'message' => 'Pembayaran untuk order ini sudah pernah diupload'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'sender_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $uploadedFile = $request->file('proof_file');

        $extension = $uploadedFile->getClientOriginalExtension();
        $originalName = $uploadedFile->getClientOriginalName();

        $fileName = $order->order_code . '_payment_' . Str::random(8) . '.' . $extension;

        $storedPath = $uploadedFile->storeAs(
            'payment_proofs/' . $order->id,
            $fileName,
            'public'
        );

        $payment = Payment::create([
            'order_id' => $order->id,
            'sender_name' => $request->sender_name,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'proof_file' => $storedPath,
            'notes' => $request->notes,
            'verification_status' => 'pending',
        ]);

        $order->update([
            'payment_status' => 'waiting_verification',
            'order_status' => 'waiting_payment_verification',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Pembayaran berhasil diupload dan menunggu verifikasi admin',
            'data' => [
                'payment' => [
                    'id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'sender_name' => $payment->sender_name,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'notes' => $payment->notes,
                    'verification_status' => $payment->verification_status,
                    'proof_file' => $payment->proof_file,
                    'proof_file_url' => asset('storage/' . $payment->proof_file),
                ],
                'order' => [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'payment_status' => $order->payment_status,
                    'order_status' => $order->order_status,
                ]
            ]
        ], 201);
    }
}