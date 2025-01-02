<?php

use App\Helpers\Helper;
use App\Http\Controllers\API\UserAuthController;
use App\Models\Festivita;
use App\Models\Presenza;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;


Route::post('register', [UserAuthController::class, 'register']);
Route::post('login', [UserAuthController::class, 'login']);
Route::post('logout', [UserAuthController::class, 'logout'])
    ->middleware('auth:sanctum');


/**
 * Record presence
 *
 * This endpoint allows you to record the presence of a user in a specific location.
 *
 * URL: /api/check-in
 *
 * @queryParam latitude required Latitude. Example: 45.464664
 * @queryParam longitude required Longitude. Example: 9.190782
 * @queryParam user_id required User ID. Example: 1
 *
 * @response {
 * "message": "Presenza registrata con successo.",
 * "distance": 10.5
 * }
 *
 * @response 400 {
 * "error": "Parametri mancanti."
 * }
 *
 * @response 404 {
 * "error": "Utente non trovato."
 * }
 */
Route::post('/check-in', [UserAuthController::class, 'checkIn']);


// Record check-out
Route::post('/check-out', function (Request $request) {
    $validation = Helper::validateUserCheckIn($request, false);
    if (!is_array($validation)) {
        return $validation; // Ritorna errori se presenti
    }

    $anagrafica = $validation['anagrafica'];

    $presenza = Presenza::where('anagrafica_id', $anagrafica->id)->whereDate('data', now())->first();
    if (!$presenza) {
        return response()->json(['error' => 'Presenza non registrata.'], 400);
    }

    if ($presenza->ora_uscita) {
        return response()->json(['error' => 'Uscita già registrata.'], 400);
    }

    $presenza->ora_uscita = now();
    $presenza->coordinate_uscita_lat = $request->latitude;
    $presenza->coordinate_uscita_long = $request->longitude;
    $presenza->save();

    return response()->json(['message' => 'Uscita registrata con successo.',
        'data' => $presenza->data,
        'ora_entrata' => $presenza->ora_entrata,
        'ora_uscita' => $presenza->ora_uscita,
        'user' => $anagrafica->nome . ' ' . $anagrafica->cognome,
        'distance' => $validation['distance']], 200);
});


// import festivita
Route::post('/import-festivita', function (Request $request) {
    $request->validate([
        'data' => 'required|date',
        'nome' => 'required|string',
    ]);

    $data = $request->data;
    $nome = $request->nome;

    $festivita = Festivita::where('data', $data)->first();
    if ($festivita) {
        return response()->json(['error' => 'Festività già presente.'], 400);
    }

    Festivita::create([
        'data' => $data,
        'nome' => $nome,
    ]);

    return response()->json(['message' => 'Festività importata con successo.'], 200);
});


// for testing only. Reset all presenze
Route::get('/reset-presenze', function (Request $request) {
    Presenza::truncate();
    return response()->json(['message' => 'Presenze resettate con
successo.'], 200);
});

// for listing users
Route::get('/users', function (Request $request) {
    return User::all();
});