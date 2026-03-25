<?php

namespace App\Http\Controllers;

use App\Models\Seat as CurrentModel;
use App\Http\Requests\StoreSeatRequest as StoreCurrentModelRequest;
use App\Http\Requests\UpdateSeatRequest as UpdateCurrentModelRequest;
use App\Models\Game;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Request as HttpRequest;


class SeatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->apiResponse(
            function () {
                return CurrentModel::all();
            }
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCurrentModelRequest $request)
    {
        return $this->apiResponse(
            function () use ($request) {
                return CurrentModel::create($request->validated());
            }
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            return CurrentModel::findOrFail($id);
        });
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCurrentModelRequest $request, int $id)
    {
        return $this->apiResponse(function () use ($request, $id) {
            $row = CurrentModel::findOrFail($id);
            $row->update($request->validated());
            return $row;
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            CurrentModel::findOrFail($id)->delete();
            return ['id' => $id];
        });
    }
    public function getSeatsBySector(Request $request)
    {
        // Debug: Ha még mindig nem jó, vedd ki a kommentet az alábbi sorról, 
        // és nézd meg a böngészőben a Network fülön, hogy mit ír ki:
        // return response()->json($request->all()); 

        $seats = CurrentModel::where('game_id', $request->input('game_id'))
            ->where('sector_id', $request->input('sector_id'))
            ->get(['id', 'row', 'col', 'status']);

        return response()->json($seats);
    }
    public function getSeats(Request $request)
    {
        $request->validate([
            'game_id' => 'required',
            'sector_id' => 'required',
        ]);

        // Lekérjük a székeket, és "hozzácsapjuk" a hozzájuk tartozó jegyet (ha van)
        // Ehhez feltételezzük, hogy a Seat modellben van: public function ticket() { return $this->hasOne(Ticket::class); }
        $seats = CurrentModel::where('game_id', $request->game_id)
            ->where('sector_id', $request->sector_id)
            ->with(['ticket']) // Betöltjük a kapcsolódó ticketet
            ->get();

        $formattedSeats = $seats->map(function ($seat) {
            return [
                'id'     => $seat->id,
                'row'    => $seat->row,
                'col'    => $seat->col,
                // LOGIKA: 
                // 1. Ha van hozzá ticket -> 2 (Sold/Piros)
                // 2. Ha nincs ticket -> 1 (Available/Zöld)
                'status' => $seat->ticket ? 2 : 1,
            ];
        });

        return response()->json($formattedSeats);
    }
  public function saveLayout(Request $request)
{
    try {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            foreach ($request->seats as $seatData) {
                // Az updateOrCreate megnézi, létezik-e már. 
                // Ha igen, nem csinál semmit, ha nem, létrehozza.
                CurrentModel::updateOrCreate(
                    [
                        'game_id'   => $request->game_id,
                        'sector_id' => $request->sector_id,
                        'row'       => $seatData['row'],
                        'col'       => $seatData['col'],
                    ],
                    [
                        'status'    => 1 // Alapértelmezett státusz
                    ]
                );
            }
            return response()->json(['message' => 'Sikeres mentés!']);
        });
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
    public function bookTickets(Request $request)
    {
        $request->validate([
            'game_id' => 'required|integer',
            'seat_ids' => 'required|array',
            'user_id' => 'required|integer', // Kötelezővé tesszük
        ]);

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $bookedTickets = [];

                foreach ($request->seat_ids as $seatId) {
                    // Ellenőrizzük, hogy ez a szék létezik-e egyáltalán
                    // és nem foglalták-e le az utolsó pillanatban
                    $isAlreadyBooked = \App\Models\Ticket::where('game_id', $request->game_id)
                        ->where('seat_id', $seatId)
                        ->exists();

                    if ($isAlreadyBooked) {
                        throw new \Exception("A(z) $seatId azonosítójú szék már elkelt!");
                    }

                    $bookedTickets[] = \App\Models\Ticket::create([
                        'game_id' => $request->game_id,
                        'seat_id' => $seatId,
                        'user_id' => $request->user_id, // Itt mentjük el a valódi júzert
                        'status'  => 'confirmed'
                    ]);
                }

                return response()->json([
                    'message' => 'Sikeres vásárlás!',
                    'count' => count($bookedTickets)
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
