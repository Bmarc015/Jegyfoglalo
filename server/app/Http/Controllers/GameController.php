<?php

namespace App\Http\Controllers;

use App\Models\Game as CurrentModel;
use App\Http\Requests\StoreGameRequest as StoreCurrentModelRequest;
use App\Http\Requests\UpdateGameRequest as UpdateCurrentModelRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GameController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     * Módosítva, hogy alapból behúzza a csapatneveket.
     */
    public function index()
    {
        return $this->apiResponse(
            function () {
                // Itt is hozzáadjuk a relációkat, hogy a sima lekérésnél is legyenek nevek
                return CurrentModel::with(['homeTeam', 'awayTeam'])->get();
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
            // Egyedi megtekintésnél is érdemes látni a csapatokat
            return CurrentModel::with(['homeTeam', 'awayTeam'])->findOrFail($id);
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
            return $row->load(['homeTeam', 'awayTeam']); // Frissítés után töltsük vissza a neveket
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

    /**
     * Lapozható és rendezhető lista relációkkal.
     */
    public function getPaging(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $sortColumn = $request->input('sort_column', 'game_date');
        $direction = $request->input('direction', 'asc');
        $search = $request->input('search', '');

        // Validáljuk az oszlopnevet
        $allowedColumns = ['id', 'game_date', 'home_team_id', 'away_team_id'];
        if (!in_array($sortColumn, $allowedColumns)) {
            $sortColumn = 'game_date';
        }
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        $query = CurrentModel::with(['homeTeam', 'awayTeam']);

        // Keresés támogatása
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('game_date', 'like', "%{$search}%")
                  ->orWhereHas('homeTeam', function ($q) use ($search) {
                      $q->where('team_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('awayTeam', function ($q) use ($search) {
                      $q->where('team_name', 'like', "%{$search}%");
                  });
            });
        }

        $games = $query->orderBy($sortColumn, $direction)->paginate($perPage);

        return response()->json([
            'data' => $games->items(),
            'meta' => [
                'current_page' => $games->currentPage(),
                'last_page' => $games->lastPage(),
                'total' => $games->total(),
            ]
        ]);
    }
}
