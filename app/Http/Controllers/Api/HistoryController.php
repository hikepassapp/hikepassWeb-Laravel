<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\Checkout;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class HistoryController extends Controller
{
    public function index(): JsonResponse
    {
        $histories = History::with([
            'checkout.checkin.reservation.mountain'
        ])->latest()->get();
        
        return response()->json([
            'success' => true,
            'message' => 'List of histories',
            'data' => $histories
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_checkout' => 'required|exists:checkouts,id|unique:histories,id_checkout',
        ], [
            'id_checkout.unique' => 'Check-out ini sudah ada di history',
            'id_checkout.exists' => 'Check-out tidak ditemukan'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $history = History::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'History created successfully',
            'data' => $history->load('checkout.checkin.reservation.mountain')
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $history = History::with([
            'checkout.checkin.reservation.mountain'
        ])->find($id);

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'History not found'
            ], 404);
        }

        $formattedHistory = [
            'id' => $history->id,
            'id_checkout' => $history->id_checkout,
            'checkout_date' => $history->checkout->checkout_date ?? null,
            'checkout_item_list' => $history->checkout->item_list ?? null,
            'checkin_date' => $history->checkout->checkin->checkin_date ?? null,
            'checkin_item_list' => $history->checkout->checkin->item_list ?? null,
            'reservation' => [
                'id_reservation' => $history->checkout->checkin->reservation->id_reservation ?? null,
                'name' => $history->checkout->checkin->reservation->name ?? null,
                'nik' => $history->checkout->checkin->reservation->nik ?? null,
                'gender' => $history->checkout->checkin->reservation->gender ?? null,
                'phone_number' => $history->checkout->checkin->reservation->phone_number ?? null,
                'address' => $history->checkout->checkin->reservation->address ?? null,
                'citizen' => $history->checkout->checkin->reservation->citizen ?? null,
                'id_card' => $history->checkout->checkin->reservation->id_card ?? null,
                'price' => $history->checkout->checkin->reservation->price ?? null,
                'start_date' => $history->checkout->checkin->reservation->start_date ?? null,
            ],
            'mountain' => [
                'id' => $history->checkout->checkin->reservation->mountain->id ?? null,
                'name' => $history->checkout->checkin->reservation->mountain->name ?? null,
            ],
            'created_at' => $history->created_at,
            'updated_at' => $history->updated_at,
        ];

        return response()->json([
            'success' => true,
            'message' => 'History detail',
            'data' => $formattedHistory
        ], 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $history = History::find($id);

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'History not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_checkout' => 'sometimes|required|exists:checkouts,id|unique:histories,id_checkout,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $history->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'History updated successfully',
            'data' => $history->load('checkout.checkin.reservation.mountain')
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $history = History::find($id);

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'History not found'
            ], 404);
        }

        $history->delete();

        return response()->json([
            'success' => true,
            'message' => 'History deleted successfully'
        ], 200);
    }

    public function getByCheckout(string $idCheckout): JsonResponse
    {
        $history = History::with([
            'checkout.checkin.reservation.mountain'
        ])->where('id_checkout', $idCheckout)->first();

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'History not found for this check-out'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'History detail',
            'data' => $history
        ], 200);
    }

    public function getByReservation(string $idReservation): JsonResponse
    {
        $history = History::whereHas('checkout.checkin', function ($query) use ($idReservation) {
            $query->where('id_reservation', $idReservation);
        })->with([
            'checkout.checkin.reservation.mountain'
        ])->first();

        if (!$history) {
            return response()->json([
                'success' => false,
                'message' => 'History not found for this reservation'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'History detail',
            'data' => $history
        ], 200);
    }

    public function createFromCheckout(string $idCheckout): JsonResponse
    {
        $checkout = Checkout::find($idCheckout);
        
        if (!$checkout) {
            return response()->json([
                'success' => false,
                'message' => 'Check-out not found'
            ], 404);
        }

        $existingHistory = History::where('id_checkout', $idCheckout)->first();
        
        if ($existingHistory) {
            return response()->json([
                'success' => false,
                'message' => 'History already exists for this check-out'
            ], 422);
        }

        $history = History::create([
            'id_checkout' => $idCheckout
        ]);

        return response()->json([
            'success' => true,
            'message' => 'History created successfully from check-out',
            'data' => $history->load('checkout.checkin.reservation.mountain')
        ], 201);
    }
}