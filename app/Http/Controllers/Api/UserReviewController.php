<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserReviewController extends Controller
{
    public function submitRevision(Request $request, $id)
    {
        $order = Order::with(['package', 'revisions', 'finalResult'])
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order tidak ditemukan atau bukan milik user'
            ], 404);
        }

        if ($order->order_status !== 'waiting_user_review') {
            return response()->json([
                'status' => false,
                'message' => 'Revisi hanya bisa diajukan saat order menunggu review user'
            ], 422);
        }

        if (!$order->finalResult || !$order->finalResult->preview_link) {
            return response()->json([
                'status' => false,
                'message' => 'Preview belum tersedia untuk order ini'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'section_name' => 'required|string|max:255',
            'revision_note' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $usedRevisionCount = $order->revisions()->count();
        $revisionLimit = $order->package->revision_limit ?? 0;

        if ($usedRevisionCount >= $revisionLimit) {
            return response()->json([
                'status' => false,
                'message' => 'Batas revisi untuk package ini sudah habis',
                'data' => [
                    'revision_limit' => $revisionLimit,
                    'used_revision' => $usedRevisionCount
                ]
            ], 422);
        }

        $revision = Revision::create([
            'order_id' => $order->id,
            'revision_number' => $usedRevisionCount + 1,
            'section_name' => $request->section_name,
            'revision_note' => $request->revision_note,
            'status' => 'submitted',
        ]);

        $order->update([
            'order_status' => 'revision',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Revisi berhasil diajukan',
            'data' => [
                'revision' => $revision,
                'remaining_revision' => $revisionLimit - ($usedRevisionCount + 1),
                'order' => $order->fresh(['package', 'revisions', 'finalResult'])
            ]
        ], 201);
    }

    public function approve(Request $request, $id)
    {
        $order = Order::with(['finalResult'])
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order tidak ditemukan atau bukan milik user'
            ], 404);
        }

        if ($order->order_status !== 'waiting_user_review') {
            return response()->json([
                'status' => false,
                'message' => 'Order belum berada pada tahap review user'
            ], 422);
        }

        if (!$order->finalResult || !$order->finalResult->preview_link) {
            return response()->json([
                'status' => false,
                'message' => 'Preview belum tersedia'
            ], 422);
        }

        $order->update([
            'order_status' => 'completed',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Hasil preview disetujui user',
            'data' => $order->fresh(['template', 'package', 'portfolio', 'files', 'payment', 'revisions', 'finalResult'])
        ]);
    }
}