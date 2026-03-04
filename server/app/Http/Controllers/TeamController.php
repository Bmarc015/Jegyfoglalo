<?php

namespace App\Http\Controllers;

use App\Models\Team as CurrentModel;
use App\Http\Requests\StoreTeamRequest as StoreCurrentModelRequest;
use App\Http\Requests\UpdateTeamRequest as UpdateCurrentModelRequest;
use App\Models\Team;
use Illuminate\Database\QueryException;

class TeamController extends Controller
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
        public function indexPaging($page, $per_page = 10, $column, $direction, $search = null)
    {
        return $this->apiResponse(function () use ($page, $per_page, $column, $direction, $search) {
            if (!is_numeric($page) || $page < 1) {
                $page = 1;
            }

            if (!is_numeric($per_page) || $per_page < 1) {
                $per_page = 10; // Maximáljuk is a lapméretet, ne lehessen 1 milliót kérni
            }

            // 1. A lekérdezés alapjainak felépítése (Query Builder)
            //késleltett betöltés: láncilással építjük a lekérdezést
            $query = Team::query();

            // 2. Szűrés (ha van keresőszó)
            if (!empty($search) && $search !== 'all') {
                $query->where(function ($q) use ($search) {
                    $q->where('team_name', 'like', "%{$search}%")
                        ->orWhere('team_city', 'like', "%{$search}%");
                });
            }

            // 3. Sorbarendezés
            $allowedColumns = ['id', 'team_name', 'team_city']; // Biztonsági lista
            $sortColumn = in_array($column, $allowedColumns) ? $column : 'id';
            $sortDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortColumn, $sortDirection);
            //Felépült a query, de még nem nyúltunk az adatbázishoz

            // 4. ELSŐ PRÓBÁLKOZÁS: Lekérjük a kért oldalt
            // A 4. paraméter ($page) mondja meg a paginátornak, hanyadik oldalt akarjuk
            $rows = $query->paginate($per_page, ['*'], 'page', $page);

            // 5. ELLENŐRZÉS: Ha túlmentünk a határon (üres, de van tartalom)
            if ($rows->isEmpty() && $rows->lastPage() > 0 && $page > $rows->lastPage()) {
                $lastPage = $rows->lastPage();

                // MÁSODIK PRÓBÁLKOZÁS: Lekérjük az utolsó létező oldalt
                // Fontos: a $query-t újra kell futtatni az utolsó oldallal
                $rows = $query->paginate($per_page, ['*'], 'page', $lastPage);
            }
            return [
                'data' => $rows->items(), // Csak a tiszta modellek listája
                'meta' => [
                    'current_page' => $rows->currentPage(),
                    'last_page' => $rows->lastPage(),
                    'total' => $rows->total(),
                ]
            ];
        });
    }
}
