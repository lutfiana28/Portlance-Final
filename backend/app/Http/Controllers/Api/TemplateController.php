<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::where('is_active', true)->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar template berhasil diambil',
            'data' => $templates
        ]);
    }

    public function show($id)
    {
        $template = Template::where('is_active', true)->find($id);

        if (!$template) {
            return response()->json([
                'status' => false,
                'message' => 'Template tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail template berhasil diambil',
            'data' => $template
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:templates,slug',
            'description' => 'nullable|string',
            'preview_image' => 'nullable|string',
            'style' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $slug = $request->slug ?: Str::slug($request->name);

        if (Template::where('slug', $slug)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Slug template sudah digunakan'
            ], 422);
        }

        $template = Template::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'preview_image' => $request->preview_image,
            'style' => $request->style,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Template berhasil ditambahkan',
            'data' => $template
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $template = Template::find($id);

        if (!$template) {
            return response()->json([
                'status' => false,
                'message' => 'Template tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:templates,slug,' . $template->id,
            'description' => 'nullable|string',
            'preview_image' => 'nullable|string',
            'style' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $slug = $request->slug ?: Str::slug($request->name);

        $template->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'preview_image' => $request->preview_image,
            'style' => $request->style,
            'is_active' => $request->has('is_active') ? $request->is_active : $template->is_active,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Template berhasil diperbarui',
            'data' => $template->fresh()
        ]);
    }

    public function destroy($id)
    {
        $template = Template::find($id);

        if (!$template) {
            return response()->json([
                'status' => false,
                'message' => 'Template tidak ditemukan'
            ], 404);
        }

        $template->delete();

        return response()->json([
            'status' => true,
            'message' => 'Template berhasil dihapus'
        ]);
    }
}