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
        // Debug logging
        \Log::info('📥 Reservation request received', [
            'has_file' => $request->hasFile('id_card'),
            'all_files' => array_keys($request->allFiles()),
            'content_type' => $request->header('Content-Type'),
            'id_card_exists' => $request->has('id_card'),
        ]);

        // Simplified validation - always expect file upload for id_card
        $validator = Validator::make($request->all(), [
            'id_mountain' => 'required|exists:mountains,id',
            'start_date' => 'required|date',
            'name' => 'required|string|max:255',
            'nik' => 'required|string|max:255',
            'gender' => 'required|string|in:male,female',
            'phone_number' => 'required|string|max:255',
            'address' => 'required|string',
            'citizen' => 'required|string|max:255',
            'price' => 'required|integer',
            'id_card' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            \Log::error('❌ Validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Handle file upload for id_card if it's a file
        if ($request->hasFile('id_card')) {
            $file = $request->file('id_card');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('id_cards', $filename, 'public');
            $data['id_card'] = $path;
        }

        $reservation = Reservation::create($data);

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

        // Flexible validation - accept either file or string for id_card
        $rules = [
            'id_mountain' => 'sometimes|required|exists:mountains,id',
            'start_date' => 'sometimes|required|date',
            'name' => 'sometimes|required|string|max:255',
            'nik' => 'sometimes|required|string|max:255',
            'gender' => 'sometimes|required|string|in:male,female',
            'phone_number' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'citizen' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|integer',
        ];

        // Check if id_card is a file or string
        if ($request->hasFile('id_card')) {
            $rules['id_card'] = 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
        } else {
            $rules['id_card'] = 'sometimes|nullable|string|max:255';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Handle file upload for id_card if it's a file
        if ($request->hasFile('id_card')) {
            // Delete old file if exists and is a file path
            if ($reservation->id_card && str_contains($reservation->id_card, '/') && \Storage::disk('public')->exists($reservation->id_card)) {
                \Storage::disk('public')->delete($reservation->id_card);
            }

            $file = $request->file('id_card');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('id_cards', $filename, 'public');
            $data['id_card'] = $path;
        }

        $reservation->update($data);

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