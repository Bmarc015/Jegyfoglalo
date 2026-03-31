<?php

namespace App\Http\Controllers;

use App\Models\Seat as CurrentModel;
use App\Http\Requests\StoreSeatRequest as StoreCurrentModelRequest;
use App\Http\Requests\UpdateSeatRequest as UpdateCurrentModelRequest;
use App\Models\Game;
use App\Models\Ticket;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\TicketMail;

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

        $seats = CurrentModel::where('game_id', $request->game_id)
            ->where('sector_id', $request->sector_id)
            ->with(['ticket'])
            ->get();

        $formattedSeats = $seats->map(function ($seat) {
            return [
                'id'     => $seat->id,
                'row'    => $seat->row,
                'col'    => $seat->col,
                'status' => $seat->ticket ? 2 : 1,
            ];
        });

        return response()->json($formattedSeats);
    }

    public function saveLayout(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                CurrentModel::where('game_id', $request->game_id)
                    ->where('sector_id', $request->sector_id)
                    ->delete();

                foreach ($request->seats as $seatData) {
                    CurrentModel::create([
                        'game_id'   => $request->game_id,
                        'sector_id' => $request->sector_id,
                        'row'       => $seatData['row'],
                        'col'       => $seatData['col'],
                        'status'    => 1
                    ]);
                }

                return response()->json(['message' => 'Saved successfully!']);
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
            'user_id' => 'required|integer',
        ]);

        try {
            $bookedTickets = DB::transaction(function () use ($request) {
                $tickets = [];

                foreach ($request->seat_ids as $seatId) {
                    $isAlreadyBooked = Ticket::where('game_id', $request->game_id)
                        ->where('seat_id', $seatId)
                        ->exists();

                    if ($isAlreadyBooked) {
                        throw new \Exception("Seat $seatId is already sold.");
                    }

                    $tickets[] = Ticket::create([
                        'game_id' => $request->game_id,
                        'seat_id' => $seatId,
                        'user_id' => $request->user_id,
                        'status'  => 'confirmed'
                    ]);
                }

                return $tickets;
            });

            $mailErrors = [];
            foreach ($bookedTickets as $ticket) {
                $ticket->load([
                    'game.homeTeam',
                    'game.awayTeam',
                    'seat.sector',
                    'user'
                ]);

                try {
                    $pdf = Pdf::loadView('ticket', compact('ticket'));
                    Mail::to($ticket->user->email)->send(new TicketMail($ticket, $pdf->output()));
                } catch (\Exception $mailError) {
                    $mailErrors[] = $mailError->getMessage();
                    Log::error('Ticket email failed', ['error' => $mailError->getMessage()]);
                }
            }

            return response()->json([
                'message' => 'Purchase successful!',
                'count' => count($bookedTickets),
                'mail_errors' => $mailErrors,
            ]);
        } catch (\Exception $e) {
            Log::error('Ticket purchase failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Ticket purchase failed. Please try again.'], 422);
        }
    }
}
