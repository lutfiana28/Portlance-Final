<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::where('is_active', true)->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar package berhasil diambil',
            'data' => $packages
        ]);
    }

    public function show($id)
    {
        $package = Package::where('is_active', true)->find($id);

        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Package tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail package berhasil diambil',
            'data' => $package
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:packages,name',
            'price' => 'required|numeric|min:0',
            'revision_limit' => 'required|integer|min:0',
            'allowed_fields' => 'nullable|array',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $package = Package::create([
            'name' => $request->name,
            'price' => $request->price,
            'revision_limit' => $request->revision_limit,
            'allowed_fields' => $request->allowed_fields,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Package berhasil ditambahkan',
            'data' => $package
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $package = Package::find($id);

        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Package tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:packages,name,' . $package->id,
            'price' => 'required|numeric|min:0',
            'revision_limit' => 'required|integer|min:0',
            'allowed_fields' => 'nullable|array',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $package->update([
            'name' => $request->name,
            'price' => $request->price,
            'revision_limit' => $request->revision_limit,
            'allowed_fields' => $request->allowed_fields,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? $request->is_active : $package->is_active,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Package berhasil diperbarui',
            'data' => $package->fresh()
        ]);
    }

    public function destroy($id)
    {
        $package = Package::find($id);

        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Package tidak ditemukan'
            ], 404);
        }

        $package->delete();

        return response()->json([
            'status' => true,
            'message' => 'Package berhasil dihapus'
        ]);
    }
}