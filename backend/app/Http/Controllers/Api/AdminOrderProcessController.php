<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinalResult;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminOrderProcessController extends Controller
{
    public function updateStatus(Request $request, $id)
    {
        $order = Order::with(['user', 'template', 'package', 'payment', 'finalResult'])->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'order_status' => 'required|in:draft,waiting_payment,waiting_payment_verification,need_completion,in_progress,waiting_user_review,revision,completed,closed',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update([
            'order_status' => $request->order_status,
            'admin_notes' => $request->admin_notes ?? $order->admin_notes,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Status order berhasil diperbarui',
            'data' => $order->fresh(['user', 'template', 'package', 'payment', 'finalResult'])
        ]);
    }

    public function storePreview(Request $request, $id)
    {
        $order = Order::with(['finalResult', 'payment'])->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order tidak ditemukan'
            ], 404);
        }

        if ($order->payment_status !== 'paid') {
            return response()->json([
                'status' => false,
                'message' => 'Preview hanya bisa dikirim untuk order yang pembayarannya sudah valid'
            ], 422);
        }

        if ($order->finalResult && $order->finalResult->preview_link) {
            return response()->json([
                'status' => false,
                'message' => 'Preview link sudah pernah dibuat untuk order ini'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'preview_link' => 'required|url',
            'final_note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $finalResult = FinalResult::updateOrCreate(
            ['order_id' => $order->id],
            [
                'preview_link' => $request->preview_link,
                'final_note' => $request->final_note,
            ]
        );

        $order->update([
            'order_status' => 'waiting_user_review',
            'admin_notes' => $request->final_note ?? $order->admin_notes,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Preview berhasil dikirim ke user',
            'data' => [
                'order' => $order->fresh(['user', 'template', 'package', 'payment', 'finalResult']),
                'final_result' => $finalResult
            ]
        ], 201);
    }

    public function updatePreview(Request $request, $id)
    {
        $order = Order::with('finalResult')->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order tidak ditemukan'
            ], 404);
        }

        if (!$order->finalResult) {
            return response()->json([
                'status' => false,
                'message' => 'Preview belum pernah dibuat untuk order ini'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'preview_link' => 'required|url',
            'final_note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->finalResult->update([
            'preview_link' => $request->preview_link,
            'final_note' => $request->final_note ?? $order->finalResult->final_note,
        ]);

        $order->update([
            'order_status' => 'waiting_user_review',
            'admin_notes' => $request->final_note ?? $order->admin_notes,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Preview berhasil diperbarui',
            'data' => [
                'order' => $order->fresh(['user', 'template', 'package', 'payment', 'finalResult']),
                'final_result' => $order->finalResult->fresh()
            ]
        ]);
    }
}