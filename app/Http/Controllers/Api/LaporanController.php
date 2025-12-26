<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class LaporanController extends Controller
{
    // GET semua laporan
    public function index(): JsonResponse
    {
        $laporans = Laporan::all();
        
        return response()->json([
            'success' => true,
            'data' => $laporans
        ], 200);
    }

    // POST buat laporan baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_pelapor' => 'required|string|max:255',
            'tanggal_kejadian' => 'required|date',
            'lokasi_kejadian' => 'required|string|max:255',
            'deskripsi_kejadian' => 'required|string',
            'foto_bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Handle upload foto
        if ($request->hasFile('foto_bukti')) {
            $file = $request->file('foto_bukti');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('laporan', $filename, 'public');
            $data['foto_bukti'] = $path;
        }

        $laporan = Laporan::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dibuat',
            'data' => $laporan
        ], 201);
    }

    // GET laporan by ID
    public function show($id)
    {
        $laporan = Laporan::find($id);

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $laporan
        ], 200);
    }

    // PUT/PATCH update laporan
    public function update(Request $request, $id)
    {
        $laporan = Laporan::find($id);

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_pelapor' => 'sometimes|required|string|max:255',
            'tanggal_kejadian' => 'sometimes|required|date',
            'lokasi_kejadian' => 'sometimes|required|string|max:255',
            'deskripsi_kejadian' => 'sometimes|required|string',
            'foto_bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Handle upload foto baru
        if ($request->hasFile('foto_bukti')) {
            // Hapus foto lama jika ada
            if ($laporan->foto_bukti) {
                Storage::disk('public')->delete($laporan->foto_bukti);
            }

            $file = $request->file('foto_bukti');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('laporan', $filename, 'public');
            $data['foto_bukti'] = $path;
        }

        $laporan->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diupdate',
            'data' => $laporan
        ], 200);
    }

    // DELETE hapus laporan
    public function destroy($id)
    {
        $laporan = Laporan::find($id);

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }

        // Hapus foto jika ada
        if ($laporan->foto_bukti) {
            Storage::disk('public')->delete($laporan->foto_bukti);
        }

        $laporan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dihapus'
        ], 200);
    }
}