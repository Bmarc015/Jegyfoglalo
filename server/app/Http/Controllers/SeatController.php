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
        // Validáljuk a bejövő adatokat
        $request->validate([
            'game_id' => 'required|integer',
            'sector_id' => 'required|integer',
        ]);

        // Csak azokat a székeket kérjük le, amik "aktívak" (vagy az összeset, ha úgy döntöttél)
        $seats = CurrentModel::where('game_id', $request->game_id)
                     ->where('sector_id', $request->sector_id)
                     ->get(['id', 'row', 'col', 'status']);

        return response()->json($seats);
    }
    public function saveLayout(Request $request)
    {
        $gameId = $request->game_id;
        $sectorId = $request->sector_id;
        $activeSeats = $request->seats; // Ez a Vue-ból érkező tömb (sor/oszlop adatokkal)

        // 1. Töröljük a régi leosztást ehhez a meccshez és szektorhoz
        // Így tiszta lappal indulunk, nem lesz 7,8 millióról 15 millió sorod
        CurrentModel::where('game_id', $gameId)
            ->where('sector_id', $sectorId)
            ->delete();

        // 2. Az új "aktív" székek beszúrása
        $dataToInsert = [];
        foreach ($activeSeats as $seat) {
            $dataToInsert[] = [
                'game_id' => $gameId,
                'sector_id' => $sectorId,
                'row' => $seat['row'],
                'col' => $seat['col'],
                'status' => 0, // Alapból szabad
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Chunk-olva szúrjuk be, ha véletlenül túl sok lenne egyszerre
        foreach (array_chunk($dataToInsert, 100) as $chunk) {
            CurrentModel::insert($chunk);
        }

        return response()->json(['message' => 'Sikeres mentés!']);
    }
}
