<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function index(): JsonResponse
    {
        // Show only reservations that have not progressed to check-in
        $reservations = Reservation::with('mountain')
            ->whereDoesntHave('checkin')
            ->get();
        
        return response()->json([
            'success' => true,
            'message' => 'List of reservations',
            'data' => $reservations
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_mountain' => 'required|exists:mountains,id',
            'start_date' => 'required|date',
            'name' => 'required|string|max:255',
            'nik' => 'required|string|max:255',
            'gender' => 'required|string|in:male,female',
            'phone_number' => 'required|string|max:255',
            'address' => 'required|string',
            'citizen' => 'required|string|max:255',
            'id_card' => 'required|string|max:255',
            'price' => 'required|integer',
        ], [
            'id_mountain.required' => 'Pilih gunung terlebih dahulu',
            'id_mountain.exists' => 'Gunung tidak ditemukan',
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'start_date.date' => 'Format tanggal tidak valid',
            'name.required' => 'Nama lengkap wajib diisi',
            'nik.required' => 'NIK wajib diisi',
            'gender.required' => 'Jenis kelamin wajib dipilih',
            'gender.in' => 'Jenis kelamin harus male atau female',
            'phone_number.required' => 'Nomor telepon wajib diisi',
            'address.required' => 'Alamat wajib diisi',
            'citizen.required' => 'Kewarganegaraan wajib diisi',
            'id_card.required' => 'Nomor KTP/ID Card wajib diisi',
            'price.required' => 'Harga wajib diisi',
            'price.integer' => 'Harga harus berupa angka bulat',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $reservation = Reservation::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Reservation created successfully',
            'data' => $reservation->load('mountain')
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $reservation = Reservation::with('mountain')->find($id);

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reservation detail',
            'data' => $reservation
        ], 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_mountain' => 'sometimes|required|exists:mountains,id',
            'start_date' => 'sometimes|required|date',
            'name' => 'sometimes|required|string|max:255',
            'nik' => 'sometimes|required|string|max:255',
            'gender' => 'sometimes|required|string|in:male,female',
            'phone_number' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'citizen' => 'sometimes|required|string|max:255',
            'id_card' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $reservation->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Reservation updated successfully',
            'data' => $reservation->load('mountain')
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found'
            ], 404);
        }

        $reservation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reservation deleted successfully'
        ], 200);
    }
}