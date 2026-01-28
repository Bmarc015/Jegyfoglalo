<?php

namespace App\Http\Controllers;

use App\Models\Seat as CurrentModel;
use App\Http\Requests\StoreSeatRequest as StoreCurrentModelRequest;
use App\Http\Requests\UpdateSeatRequest as UpdateCurrentModelRequest;
use App\Models\Game;
use Illuminate\Database\QueryException;

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
}
