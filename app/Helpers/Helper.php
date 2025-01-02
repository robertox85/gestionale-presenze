<?php

namespace App\Helpers;

use App\Models\Presenza;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class Helper
{
    /**
     * Calcola la distanza tra due coordinate geografiche usando la formula dell'haversine.
     *
     * @param float $lat1 Latitudine del primo punto
     * @param float $lon1 Longitudine del primo punto
     *
     * @param float $lat2 Latitudine del secondo punto
     * @param float $lon2 Longitudine del secondo punto
     *
     * @return float Distanza in metri
     */
    static function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // Raggio terrestre in metri

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a)); // Calcola l'angolo tra due punti

        $mt = $earthRadius * $c; // Distanza in metri


        // Calcola se la distanza è superiore a 1000 metri, restituendo i chilometri
        if ($mt > 1000) {
            return round($mt / 1000, 2);
        }

        // Round to 2 decimal places
        return round($mt, 2);
    }

    static function validateUserCheckIn(Request $request, $checkIn = true)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $user_id = $request->input('user_id');

        if (!$latitude || !$longitude || !$user_id) {
            return response()->json(['error' => 'Parametri mancanti.'], 400);
        }

        if (!is_numeric($latitude) || $latitude < -90 || $latitude > 90) {
            return response()->json(['error' => 'Latitudine non valida.'], 400);
        }

        if (!is_numeric($longitude) || $longitude < -180 || $longitude > 180) {
            return response()->json(['error' => 'Longitudine non valida.'], 400);
        }

        $user = User::find($user_id);
        if (!$user) {
            return response()->json(['error' => 'Utente non trovato.'], 404);
        }

        $anagrafica = $user->anagrafica;
        if (!$anagrafica) {
            return response()->json(['error' => 'Anagrafica non trovata.'], 404);
        }

        if (!$anagrafica->attivo) {
            return response()->json(['error' => 'Anagrafica non attiva.'], 400);
        }

        $sede = $anagrafica->sede;

        $validSede = Helper::isValidSede($sede);
        if (!$validSede) {
            return response()->json(['error' => 'Sede non valida.'], 400);
        }

        if ($checkIn && !Helper::isInWorkingHours($sede)) {
            return response()->json(['error' => 'Fuori dall\'orario lavorativo.'], 400);
        }

        $existingPresence = Helper::existingPresence($anagrafica, $sede);
        if ($existingPresence) {
            return response()->json(['error' => 'Presenza già registrata per questa sede.'], 400);
        }

        // Calcola la distanza
        $distance = Helper::calculateDistance($sede->latitudine, $sede->longitudine, $latitude, $longitude);
        if ($distance > 20) {
            return response()->json(['error' => 'Distanza maggiore di 20 metri.', 'distance' => $distance], 400);
        }

        return [
            'user' => $user,
            'anagrafica' => $anagrafica,
            'sede' => $sede,
            'distance' => $distance,
        ];
    }

    static function existingPresence($anagrafica, $sede)
    {
        return Presenza::where('anagrafica_id', $anagrafica->id)
            ->where('sede_id', $sede->id)
            ->whereDate('data', now())
            ->first();
    }

    static function isInWorkingHours($sede): bool
    {
        $now = Carbon::now($sede->fuso_orario);
        $start = Carbon::createFromTimeString($sede->orario_inizio, $sede->fuso_orario);
        $end = Carbon::createFromTimeString($sede->orario_fine, $sede->fuso_orario);

        return $now->gte($start) && $now->lte($end);
    }

    static function isValidSede($sede): bool
    {
        return $sede->attiva && $sede->latitudine && $sede->longitudine;
    }
}
