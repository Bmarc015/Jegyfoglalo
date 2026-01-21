<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use Illuminate\Database\QueryException;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try {
            $rows = Ticket::all();
            $status = 200;
            $data = [
                'message' => 'OK',
                'data' => $rows
            ];
        } catch (\Exception $e) {
            $status = 500;
            $data = [
                'message' => "Server error {$e->getCode()}",
                'data' => []
            ];
        }
        return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
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
    public function show(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //
    }
}
