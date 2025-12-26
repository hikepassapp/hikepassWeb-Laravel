<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CheckinController extends Controller
{
    public function index(): JsonResponse
    {
        // Show only check-ins that have not progressed to check-out
        $checkins = Checkin::with('reservation.mountain')
            ->whereDoesntHave('checkout')
            ->get();
        
        return response()->json([
            'success' => true,
            'message' => 'List of check-ins',
            'data' => $checkins
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_reservation' => 'required|exists:reservations,id|unique:checkins,id_reservation',
            'item_list' => 'required|string',
            'checkin_date' => 'required|date',
        ], [
            'id_reservation.unique' => 'Reservasi ini sudah melakukan check-in',
            'id_reservation.exists' => 'Reservasi tidak ditemukan'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $checkin = Checkin::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Check-in created successfully',
            'data' => $checkin->load('reservation.mountain')
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $checkin = Checkin::with('reservation.mountain')->find($id);

        if (!$checkin) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-in detail',
            'data' => $checkin
        ], 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $checkin = Checkin::find($id);

        if (!$checkin) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_reservation' => 'sometimes|required|exists:reservations,id|unique:checkins,id_reservation,' . $id,
            'item_list' => 'sometimes|required|string',
            'checkin_date' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $checkin->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Check-in updated successfully',
            'data' => $checkin->load('reservation.mountain')
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $checkin = Checkin::find($id);

        if (!$checkin) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in not found'
            ], 404);
        }

        $checkin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Check-in deleted successfully'
        ], 200);
    }

    public function getByReservation(string $idReservation): JsonResponse
    {
        $checkin = Checkin::with('reservation.mountain')
                         ->where('id_reservation', $idReservation)
                         ->first();

        if (!$checkin) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in not found for this reservation'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-in detail',
            'data' => $checkin
        ], 200);
    }
}