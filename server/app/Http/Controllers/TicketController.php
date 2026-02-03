<?php

namespace App\Http\Controllers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Ticket;

use App\Http\Requests\StoreTicketRequest;
use App\Mail\TicketMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Új jegy vásárlása, PDF generálása és küldése e-mailben.
     */
    public function store(StoreTicketRequest $request)
    {
        // Adatok mentése tranzakcióban
        $ticket = DB::transaction(function () use ($request) {
            $ticket = Ticket::create($request->validated());

            $ticket->load([
                'game.team_home',
                'game.team_away',
                'seat.sector',
                'user'
            ]);

            $pdf = Pdf::loadView('ticket', compact('ticket'));

            Mail::to('jegyfoglaloteszt@gmail.com')->send(new TicketMail($ticket, $pdf->output()));

            return $ticket;
        });

        // Sima JSON válasz a Laravel beépített függvényével
        return response()->json([
            'message' => 'Sikeres vásárlás!',
            'data' => $ticket
        ], 201);
    }

    /**
     * Összes jegy listázása.
     */
    public function index()
    {
        return $this->apiResponse(function () {
            return Ticket::with(['game', 'user', 'seat'])->get();
        });
    }

    /**
     * Egy konkrét jegy lekérése ID alapján.
     */
    public function show($id)
    {
        return $this->apiResponse(function () use ($id) {
            return Ticket::with(['game', 'user', 'seat.sector'])->findOrFail($id);
        });
    }

    /**
     * Jegy adatainak módosítása (pl. státusz állítása).
     */
    public function update(StoreTicketRequest $request, $id)
    {
        return $this->apiResponse(function () use ($request, $id) {
            $ticket = Ticket::findOrFail($id);
            $ticket->update($request->validated());
            return $ticket;
        });
    }

    /**
     * Jegy törlése.
     */
    public function destroy($id)
    {
        return $this->apiResponse(function () use ($id) {
            $ticket = Ticket::findOrFail($id);
            $ticket->delete();
            return ['message' => 'Jegy sikeresen törölve'];
        });
    }
}
