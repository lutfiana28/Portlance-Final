<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPortfolio;
use App\Models\Template;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['template', 'package', 'portfolio'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar order berhasil diambil',
            'data' => $orders
        ]);
    }

    public function show(Request $request, $id)
    {
        $order = Order::with([
                'template',
                'package',
                'portfolio',
                'files',
                'payment',
                'revisions',
                'finalResult'
            ])
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail order berhasil diambil',
            'data' => $order
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|integer|exists:templates,id',
            'package_id' => 'required|integer|exists:packages,id',

            'full_name' => 'nullable|string|max:255',
            'photo_profile' => 'nullable|string|max:255',
            'profession' => 'nullable|string|max:255',
            'short_bio' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:50',
            'domicile' => 'nullable|string|max:255',

            'social_links' => 'nullable|array',
            'skills' => 'nullable|array',
            'tools' => 'nullable|array',
            'capability_summary' => 'nullable|string',

            'projects' => 'nullable|array',
            'services' => 'nullable|array',
            'testimonials' => 'nullable|array',
            'certificates' => 'nullable|array',
            'faqs' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $template = Template::where('id', $request->template_id)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return response()->json([
                'status' => false,
                'message' => 'Template tidak ditemukan atau tidak aktif'
            ], 404);
        }

        $package = Package::where('id', $request->package_id)
            ->where('is_active', true)
            ->first();

        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Package tidak ditemukan atau tidak aktif'
            ], 404);
        }

        $allPortfolioFields = [
            'full_name',
            'photo_profile',
            'profession',
            'short_bio',
            'contact_email',
            'phone_number',
            'domicile',
            'social_links',
            'skills',
            'tools',
            'capability_summary',
            'projects',
            'services',
            'testimonials',
            'certificates',
            'faqs',
        ];

        $allowedFields = $package->allowed_fields ?? [];

        $disallowedFields = [];
        foreach ($allPortfolioFields as $field) {
            if ($request->has($field) && !in_array($field, $allowedFields)) {
                $disallowedFields[] = $field;
            }
        }

        if (!empty($disallowedFields)) {
            return response()->json([
                'status' => false,
                'message' => 'Ada field yang tidak diizinkan untuk package ini',
                'data' => [
                    'package' => $package->name,
                    'disallowed_fields' => $disallowedFields,
                    'allowed_fields' => $allowedFields,
                ]
            ], 422);
        }

        $portfolioData = [];
        foreach ($allowedFields as $field) {
            $portfolioData[$field] = $request->input($field);
        }

        $orderCode = 'ORD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));

        DB::beginTransaction();

        try {
            $order = Order::create([
                'order_code' => $orderCode,
                'user_id' => $request->user()->id,
                'template_id' => $template->id,
                'package_id' => $package->id,
                'total_price' => $package->price,
                'payment_status' => 'unpaid',
                'order_status' => 'waiting_payment',
                'admin_notes' => null,
            ]);

            $portfolioData['order_id'] = $order->id;

            OrderPortfolio::create($portfolioData);

            DB::commit();

            $order->load(['template', 'package', 'portfolio']);

            return response()->json([
                'status' => true,
                'message' => 'Order berhasil dibuat',
                'data' => $order
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat membuat order',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}