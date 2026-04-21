<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with([
            'user',
            'template',
            'package',
            'payment'
        ])->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar order admin berhasil diambil',
            'data' => $orders
        ]);
    }

    public function show($id)
    {
        $order = Order::with([
            'user',
            'template',
            'package',
            'portfolio',
            'files',
            'payment.verifier',
            'revisions',
            'finalResult'
        ])->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail order admin berhasil diambil',
            'data' => $order
        ]);
    }
}