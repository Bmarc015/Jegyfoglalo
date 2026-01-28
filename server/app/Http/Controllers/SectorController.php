<?php

namespace App\Http\Controllers;

use App\Models\Sector as CurrentModel;
use App\Http\Requests\StoreSectorRequest as StoreCurrentModelRequest;
use App\Http\Requests\UpdateSectorRequest as UpdtateCurrentModelRequest;
use Illuminate\Database\QueryException;

class SectorController extends Controller
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
    public function update(UpdtateCurrentModelRequest $request, int $id)
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
