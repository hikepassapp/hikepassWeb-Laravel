<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function index(): JsonResponse
    {
        // Show only check-outs that have not progressed to history (finished)
        $checkouts = Checkout::with(['checkin.reservation.mountain'])
            ->whereDoesntHave('history')
            ->get();
        
        return response()->json([
            'success' => true,
            'message' => 'List of check-outs',
            'data' => $checkouts
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_checkin' => 'required|exists:checkins,id|unique:checkouts,id_checkin',
            'item_list' => 'required|string',
            'checkout_date' => 'required|date|after_or_equal:today',
        ], [
            'id_checkin.unique' => 'Check-in ini sudah melakukan check-out',
            'id_checkin.exists' => 'Check-in tidak ditemukan',
            'checkout_date.after_or_equal' => 'Tanggal check-out tidak boleh sebelum hari ini'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $checkin = Checkin::find($request->id_checkin);
        if ($checkin && $request->checkout_date < $checkin->checkin_date) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => [
                    'checkout_date' => ['Tanggal check-out tidak boleh sebelum tanggal check-in']
                ]
            ], 422);
        }

        $checkout = Checkout::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Check-out created successfully',
            'data' => $checkout->load('checkin.reservation.mountain')
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $checkout = Checkout::with(['checkin.reservation.mountain'])->find($id);

        if (!$checkout) {
            return response()->json([
                'success' => false,
                'message' => 'Check-out not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-out detail',
            'data' => $checkout
        ], 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $checkout = Checkout::find($id);

        if (!$checkout) {
            return response()->json([
                'success' => false,
                'message' => 'Check-out not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_checkin' => 'sometimes|required|exists:checkins,id|unique:checkouts,id_checkin,' . $id,
            'item_list' => 'sometimes|required|string',
            'checkout_date' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('checkout_date')) {
            $checkin = Checkin::find($checkout->id_checkin);
            if ($checkin && $request->checkout_date < $checkin->checkin_date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => [
                        'checkout_date' => ['Tanggal check-out tidak boleh sebelum tanggal check-in']
                    ]
                ], 422);
            }
        }

        $checkout->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Check-out updated successfully',
            'data' => $checkout->load('checkin.reservation.mountain')
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $checkout = Checkout::find($id);

        if (!$checkout) {
            return response()->json([
                'success' => false,
                'message' => 'Check-out not found'
            ], 404);
        }

        $checkout->delete();

        return response()->json([
            'success' => true,
            'message' => 'Check-out deleted successfully'
        ], 200);
    }

    public function getByCheckin(string $idCheckin): JsonResponse
    {
        $checkout = Checkout::with(['checkin.reservation.mountain'])
                           ->where('id_checkin', $idCheckin)
                           ->first();

        if (!$checkout) {
            return response()->json([
                'success' => false,
                'message' => 'Check-out not found for this check-in'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-out detail',
            'data' => $checkout
        ], 200);
    }

    public function getByReservation(string $idReservation): JsonResponse
    {
        $checkout = Checkout::whereHas('checkin', function ($query) use ($idReservation) {
            $query->where('id_reservation', $idReservation);
        })->with(['checkin.reservation.mountain'])->first();

        if (!$checkout) {
            return response()->json([
                'success' => false,
                'message' => 'Check-out not found for this reservation'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-out detail',
            'data' => $checkout
        ], 200);
    }
}