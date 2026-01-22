<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Models\Sector;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

//endpoint
Route::get('/x', function () {
    return 'API';
});


//region users
//User kezelés, login, logout
//Mindenki
Route::post('users/login', [UserController::class, 'login']);
Route::post('users/logout', [UserController::class, 'logout']);
Route::post('users', [UserController::class, 'store']);
Route::get('students', [GameController::class, 'index']);

//Admin: 
//minden user lekérdezése
Route::get('users', [UserController::class, 'index'])
    ->middleware('auth:sanctum', 'ability:admin');
//Egy user lekérése    
Route::get('users/{id}', [UserController::class, 'show'])
    ->middleware('auth:sanctum', 'ability:admin');
//User adatok módosítása      
Route::patch('users/{id}', [UserController::class, 'update'])
    ->middleware('auth:sanctum', 'ability:admin');
//User törlés
Route::delete('users/{id}', [UserController::class, 'destroy'])
    ->middleware('auth:sanctum', 'ability:admin');

//User self (Amit a user önmagával csinálhat) parancsok
Route::delete('usersme', [UserController::class, 'destroySelf'])
    ->middleware('auth:sanctum', 'ability:usersme:delete');

Route::patch('usersme', [UserController::class, 'updateSelf'])
    ->middleware('auth:sanctum', 'ability:usersme:patch');

Route::patch('usersmeupdatepassword', [UserController::class, 'updatePassword'])
    ->middleware('auth:sanctum', 'ability:usersme:updatePassword');

Route::get('usersme', [UserController::class, 'indexSelf'])
    ->middleware('auth:sanctum', 'ability:usersme:get');


//Games 
Route::get('games', [GameController::class, 'index']);

Route::get('games/{id}', [GameController::class, 'show'])
    ->middleware('auth:sanctum', 'ability:admin');

Route::post('games', [GameController::class, 'store'])
->middleware('auth:sanctum', 'ability:admin');

Route::patch('games/{id}', [GameController::class, 'update'])
    ->middleware('auth:sanctum', 'ability:admin');

Route::delete('games/{id}', [GameController::class, 'destroy'])
    ->middleware('auth:sanctum', 'ability:admin');
//Seats 
Route::get('seats', [SeatController::class, 'index']);

Route::get('seats/{id}', action: [SeatController::class, 'show'])
    ->middleware('auth:sanctum', 'ability:admin');

Route::post('seats', [SeatController::class, 'store'])
->middleware('auth:sanctum', 'ability:admin');

Route::patch('seats/{id}', [SeatController::class, 'update'])
    ->middleware('auth:sanctum', 'ability:admin');

Route::delete('seats/{id}', [SeatController::class, 'destroy'])
    ->middleware('auth:sanctum', 'ability:admin');
//Sectors 
Route::get('sectors', [SectorController::class, 'index']);

Route::get('sectors/{id}', [SectorController::class, 'show'])
    ->middleware('auth:sanctum', 'ability:admin');

Route::post('sectors', [SectorController::class, 'store'])
->middleware('auth:sanctum', 'ability:admin');

Route::patch('sectors/{id}', [SectorController::class, 'update'])
    ->middleware('auth:sanctum', 'ability:admin');

Route::delete('sectors/{id}', [SectorController::class, 'destroy'])
    ->middleware('auth:sanctum', 'ability:admin');
//Teams 
Route::get('teams', [TeamController::class, 'index']);

Route::get('teams/{id}', [TeamController::class, 'show'])
    ->middleware('auth:sanctum', 'ability:admin');

Route::post('teams', [TeamController::class, 'store'])
->middleware('auth:sanctum', 'ability:admin');

Route::patch('teams/{id}', [TeamController::class, 'update'])
    ->middleware('auth:sanctum', 'ability:admin');

Route::delete('teams/{id}', [TeamController::class, 'destroy'])
    ->middleware('auth:sanctum', 'ability:admin');
//Tickets 
Route::get('tickets', [TicketController::class, 'index']);

Route::get('tickets/{id}', [TicketController::class, 'show'])
    ->middleware('auth:sanctum', 'ability:admin');

Route::post('tickets', [TicketController::class, 'store'])
->middleware('auth:sanctum', 'ability:admin');

Route::patch('tickets/{id}', [TicketController::class, 'update'])
    ->middleware('auth:sanctum', 'ability:admin');

Route::delete('tickets/{id}', [TicketController::class, 'destroy'])
    ->middleware('auth:sanctum', 'ability:admin');


//endregion
