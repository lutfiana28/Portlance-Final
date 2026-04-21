<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PortfolioFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PortfolioFileController extends Controller
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

        $validator = Validator::make($request->all(), [
            'file_type' => 'required|string|in:profile_photo,cv,certificate,project_screenshot,supporting_document',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $uploadedFile = $request->file('file');

        $extension = $uploadedFile->getClientOriginalExtension();
        $originalName = $uploadedFile->getClientOriginalName();

        $fileName = $order->order_code . '_' . $request->file_type . '_' . Str::random(8) . '.' . $extension;

        $storedPath = $uploadedFile->storeAs(
            'portfolio_files/' . $order->id,
            $fileName,
            'public'
        );

        $portfolioFile = PortfolioFile::create([
            'order_id' => $order->id,
            'file_type' => $request->file_type,
            'file_path' => $storedPath,
            'original_name' => $originalName,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'File berhasil diupload',
            'data' => [
                'id' => $portfolioFile->id,
                'order_id' => $portfolioFile->order_id,
                'file_type' => $portfolioFile->file_type,
                'original_name' => $portfolioFile->original_name,
                'file_path' => $portfolioFile->file_path,
                'file_url' => asset('storage/' . $portfolioFile->file_path),
                'created_at' => $portfolioFile->created_at,
            ]
        ], 201);
    }
}