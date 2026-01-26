<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */

public function getMyTickets(Request $request)
{
    if (!$request->user()->tokenCan('usersme:get')) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    // A bejelentkezett user lekérése
    $user = $request->user();

    // Jegyek lekérése a kapcsolaton keresztül + eager loading a meccsnek és széknek
    $tickets = $user->tickets()->with(['game', 'seat.sector'])->get();

    // Ha nincs jegye
    if ($tickets->isEmpty()) {
        return response()->json([
            'success' => true,
            'message' => 'Ennek a felhasználónak nincsenek jegyei.',
            'data' => []
        ], 200);
    }

    // Ha vannak jegyek
    return response()->json([
        'success' => true,
        'data' => $tickets
    ], 200);
}
   public function index(Request $request)
{
    $user = $request->user();

    // 1. Ha ADMIN (role: 1 vagy '*' képesség)
    if ($user->tokenCan('*')) {
        // Az összes jegyet látja, minden userrel, meccsel, székkel együtt
        $tickets = \App\Models\Ticket::with(['user', 'game', 'seat.sector'])->get();
    } 
    // 2. Ha VÁSÁRLÓ vagy bárki más
    else {
        // Csak a saját jegyeit látja
        $tickets = $user->tickets()->with(['game', 'seat.sector'])->get();
    }

    // Válasz kezelése
    if ($tickets->isEmpty()) {
        return response()->json(['message' => 'Nincs megjeleníthető jegy.'], 200);
    }

    return response()->json($tickets);
}
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        try {
            $row = Ticket::create($request->validated());

            return response()->json([
                'message' => 'Ticket successfully created',
                'data' => $row
            ], 201, options: JSON_UNESCAPED_UNICODE);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Laravel automatikus validációs hibakezelés felülírása
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
                'input' => $request->all(), // visszaküldjük a beküldött adatokat
            ], 422, options: JSON_UNESCAPED_UNICODE);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                return response()->json([
                    'message' => 'Insert error: The given ticket is already reserved, please choose another one',
                    'data' => [
                        'user_id' => $request->input('user_id'),
                        'game_id' => $request->input('game_id'),
                        'seat_id' => $request->input('seat_id'),
                        'status'  => $request->input('status'),
                    ]
                ], 409, options: JSON_UNESCAPED_UNICODE);
            }

            throw $e; // ha más hiba, feldobjuk
        }
        
    }


    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        //
         $row = Ticket::find($id);
        if ($row) {
            # code...
            $status = 200;
            $data = [
                'message' => 'OK',
                'data' => $row
            ];
        } else {
            $status = 404;
            $data = [
                'message' => "Not_Found id: $id ",
                'data' => null
            ]; 
    }
    return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
}

    /**
     * Update the specified resource in storage.
     */
        public function update(UpdateTicketRequest $request, int $id)
    {
        //
        var_dump($request->all());
        die;
           try {
            $row = Ticket::find($id);
            if ($row) {
                $status = 200;
                $row->update($request->all());
                $data = [
                    'message' => 'OK',
                    'data' => [$row],

                ];
            } else {

                $status = 404;
                $data = [
                    'message' => "Patch error. Not found id: $id",
                    'data' => null
                ];

            }
            return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
        } catch (QueryException $e) {
            // Ellenőrizzük, hogy ez egy "Duplicate entry for key" hiba-e (MySQL hibakód: 23000 vagy 1062)
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                $data = [
                     'message' => 'Insert error: The given ticket is already reserved, please choose another one',
                    'data' => [
                        'user_id' => $request->input('user_id'),
                        'game_id' => $request->input('game_id'),
                        'seat_id' => $request->input('seat_id'),
                        'status'  => $request->input('status'),
                    ]
                ];
                // Kliens hiba, ami jelzi a kérés érvénytelenségét
                return response()->json($data, 409, options: JSON_UNESCAPED_UNICODE); // 409 Conflict ajánlott
            }

            // Ha nem ez a hiba volt, dobjuk tovább az eredeti kivételt, vagy kezeljük másképp
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
      public function destroy(int $id)
    {
        //
            $row = Ticket::find($id);
        if (!$row) {
            return response()->json([
                'message' => "Not_Found id: $id",
                'data' => null
            ], 404, options: JSON_UNESCAPED_UNICODE);
        }
        try {
            $row->delete();
            return response()->json([
                'message' => 'OK',
                'data' => ['id' => $id]
            ], 200, options: JSON_UNESCAPED_UNICODE);
        } catch (QueryException $e) {
            // VALÓDI MySQL hibakód
            $mysqlError = $e->errorInfo[1];
            if ($mysqlError == 1451) {
                return response()->json([
                    'message' => "Delete failed (FK constraint). Id: $id",
                    'data' => null
                ], 409, options: JSON_UNESCAPED_UNICODE);
            }
            throw $e; // egyéb hibák
        }
    }

}
