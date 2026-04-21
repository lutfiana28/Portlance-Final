<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinalResult;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminFinalResultController extends Controller
{
    public function store(Request $request, $id)
    {
        $order = Order::with(['finalResult'])->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order tidak ditemukan'
            ], 404);
        }

        if (!in_array($order->order_status, ['completed', 'waiting_user_review'])) {
            return response()->json([
                'status' => false,
                'message' => 'Order belum siap untuk finalisasi hasil'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'final_link' => 'required|url',
            'final_note' => 'nullable|string',
            'final_file' => 'nullable|file|mimes:zip,rar,pdf,doc,docx|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $storedPath = null;

        if ($request->hasFile('final_file')) {
            $uploadedFile = $request->file('final_file');
            $extension = $uploadedFile->getClientOriginalExtension();

            $fileName = $order->order_code . '_final_' . Str::random(8) . '.' . $extension;

            $storedPath = $uploadedFile->storeAs(
                'final_results/' . $order->id,
                $fileName,
                'public'
            );
        }

        $finalResult = FinalResult::updateOrCreate(
            ['order_id' => $order->id],
            [
                'preview_link' => optional($order->finalResult)->preview_link,
                'final_link' => $request->final_link,
                'final_file' => $storedPath ?? optional($order->finalResult)->final_file,
                'final_note' => $request->final_note,
            ]
        );

        $order->update([
            'order_status' => 'completed',
            'admin_notes' => $request->final_note ?? $order->admin_notes,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Hasil final berhasil dikirim',
            'data' => [
                'order' => $order->fresh(['user', 'template', 'package', 'portfolio', 'files', 'payment', 'revisions', 'finalResult']),
                'final_result' => [
                    'id' => $finalResult->id,
                    'order_id' => $finalResult->order_id,
                    'preview_link' => $finalResult->preview_link,
                    'final_link' => $finalResult->final_link,
                    'final_file' => $finalResult->final_file,
                    'final_file_url' => $finalResult->final_file ? asset('storage/' . $finalResult->final_file) : null,
                    'final_note' => $finalResult->final_note,
                ]
            ]
        ], 201);
    }
}