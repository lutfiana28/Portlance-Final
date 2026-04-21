<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminRevisionController extends Controller
{
    public function index()
    {
        $revisions = Revision::with([
            'order.user',
            'order.template',
            'order.package'
        ])->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar revisi berhasil diambil',
            'data' => $revisions
        ]);
    }

    public function update(Request $request, $id)
    {
        $revision = Revision::with('order')->find($id);

        if (!$revision) {
            return response()->json([
                'status' => false,
                'message' => 'Data revisi tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:submitted,processed',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $revision->update([
            'status' => $request->status,
        ]);

        if ($request->status === 'processed') {
            $revision->order->update([
                'order_status' => 'waiting_user_review',
                'admin_notes' => $request->admin_notes ?? $revision->order->admin_notes,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Status revisi berhasil diperbarui',
            'data' => $revision->fresh(['order.user', 'order.template', 'order.package'])
        ]);
    }
}