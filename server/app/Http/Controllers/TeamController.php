<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use Illuminate\Database\QueryException;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
          try {
            $rows = Team::all();
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
    public function store(StoreTeamRequest $request)
    {
        //
         try {
            $row = Team::create($request->all());

            $data = [
                'message' => 'ok',
                'data' => $row
            ];
            // Sikeres válasz: 201 Created kód ajánlott új erőforrás létrehozásakor
            return response()->json($data, 201, options: JSON_UNESCAPED_UNICODE);
        } catch (QueryException $e) {
            // Ellenőrizzük, hogy ez egy "Duplicate entry for key" hiba-e (MySQL hibakód: 23000 vagy 1062)
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                $data = [
                    'message' => 'Insert error: The given name already exists, please choose another one',
                    'data' => [
                        'team_name' => $request->input('team_name') // Visszaküldhetjük, mi volt a hibás
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
     * Display the specified resource.
     */
        public function show(int $id)
    {
        //
         $row = Team::find($id);
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
    public function update(UpdateTeamRequest $request, int $id)
    {
        //
           try {
            $row = Team::find($id);
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
                     'message' => 'Insert error: The given name already exists, please choose another one',
                    'data' => [
                        'team_name' => $request->input('team_name') // Visszaküldhetjük, mi volt a hibás
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
            $row = Team::find($id);
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
