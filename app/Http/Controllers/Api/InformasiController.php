<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Informasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class InformasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $informasi = Informasi::all();
        
        return response()->json([
            'success' => true,
            'data' => $informasi
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['judul', 'deskripsi']);

        // Handle file upload
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('informasi', $filename, 'public');
            $data['gambar'] = $path;
        }

        $informasi = Informasi::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Informasi berhasil dibuat',
            'data' => $informasi
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $informasi = Informasi::find($id);

        if (!$informasi) {
            return response()->json([
                'success' => false,
                'message' => 'Informasi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $informasi
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $informasi = Informasi::find($id);

        if (!$informasi) {
            return response()->json([
                'success' => false,
                'message' => 'Informasi tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'sometimes|required|string|max:255',
            'deskripsi' => 'sometimes|required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['judul', 'deskripsi']);

        // Handle file upload
        if ($request->hasFile('gambar')) {
            // Delete old image
            if ($informasi->gambar && Storage::disk('public')->exists($informasi->gambar)) {
                Storage::disk('public')->delete($informasi->gambar);
            }

            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('informasi', $filename, 'public');
            $data['gambar'] = $path;
        }

        $informasi->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Informasi berhasil diupdate',
            'data' => $informasi
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $informasi = Informasi::find($id);

        if (!$informasi) {
            return response()->json([
                'success' => false,
                'message' => 'Informasi tidak ditemukan'
            ], 404);
        }

        // Delete image file
        if ($informasi->gambar && Storage::disk('public')->exists($informasi->gambar)) {
            Storage::disk('public')->delete($informasi->gambar);
        }

        $informasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Informasi berhasil dihapus'
        ], 200);
    }
}