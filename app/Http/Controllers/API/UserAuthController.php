<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;

use App\Models\Presenza;
use App\Models\User;

use Illuminate\Http\Request;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class UserAuthController extends Controller
{
    private const DISTANCE_TOLERANCE = 20; // Tolleranza di 20 metri

    // Method to handle user authentication and token generation
    public function generateToken(Request $request)
    {

        $request->validate([

            'email' => 'required|email',

            'password' => 'required',

        ]);


        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {

            throw ValidationException::withMessages([

                'email' => ['The provided credentials are incorrect.'],

            ]);

        }

        $token = $user->createToken

        ('my-app-token')->plainTextToken;

        return response()->json(['token' => $token], 200);

    }

    // Method to handle user registration
    public function register(Request $request)
    {

        try {
            $request->validate([

                'name' => 'required|string',

                'email' => 'required|email|unique:users,email',

                'password' => 'required|string|confirmed',

            ]);

            $data = [

                'name' => $request->name,

                'email' => $request->email,

                'password' => Hash::make($request->password),

            ];

            $user = User::create($data);

            $token = $user->createToken('my-app-token')->plainTextToken;

            return response()->json(['token' => $token], 201);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Registration failed.'], 500);
        }

    }

    // Method to handle user login
    public function login(Request $request)
    {

        try {
            $request->validate([

                'email' => 'required|email',

                'password' => 'required',

                'device_name' => 'required',  // Nome del dispositivo che utilizza il token

            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {

                return response()->json([
                    'message' => 'Invalid Credentials'
                ], 401);

            }

            return ['token' => $user->createToken($request->device_name)->plainTextToken];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Login failed.'], 500);
        }

    }

    // Method to handle user logout and token revocation

    public function logout(Request $request)

    {

        // Revoke all tokens...

        $request->user()->tokens()->delete();

        // // Revoke the current token

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'You have been successfully logged out.'], 200);

    }


    // Method to handle user check-in
    public function checkIn(Request $request)
    {
        try {

            // TODO:
            // - Verifica latitudine e longitudine
            // - Calcolo distanza GPS
            // - Aggiungi validazione per la distanza
            // - Aggiungi validazione per il fuso orario
            // - Aggiungi validazione per l'orario di lavoro
            // - Aggiungi validazione per l'orario di ingresso
            // - Aggiungi validazione per il giorno lavorativo
            // - Aggiungi validazione per la presenza già registrata
            // - Aggiungi validazione per l'orario di uscita

            // Validazione dei dati
            $this->validateRequest($request);

            // Recupera l'utente e la sede associata
            $user = $this->getUserWithSede($request->user_id);
            $sede = $user->anagrafica->sede;

            // Verifica il fuso orario e ottieni l'ora attuale nella sede
            $timezone = $sede->fuso_orario ?? 'UTC'; // Default a UTC se non configurato
            $nowInSedeTimezone = Carbon::now($timezone);

            // Verifica se oggi è un giorno lavorativo
            $this->validateWorkingDay($sede, $nowInSedeTimezone);

            // Recupera gli orari lavorativi
            $this->validateWorkingHours($sede, $request->orario_entrata, $timezone, $nowInSedeTimezone);

            // Calcola la distanza GPS (placeholder per implementazione futura)
            $this->validateDistance($request->latitude, $request->longitude, $sede);

            // Registra la presenza
            $this->registerPresence($user, $request, $nowInSedeTimezone);


            return response()->json([
                'message' => 'Presenza registrata con successo.',
                'distance' => $this->calculateDistance($request->latitude, $request->longitude, $sede),
                'device_name' => $request->device_name,
                'user_id' => $request->user_id,
                'data' => now()->format('Y-m-d'),
                'ora_entrata' => now()->format('H:i:s'),
                'fuso_orario' => $sede->fuso_orario,
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' =>$e->getMessage() ], 500);
        }
    }

    protected function validateRequest(Request $request): void
    {
        $rules = [
            'latitude' => [
                'required',
                'numeric',
                'min:-90',
                'max:90',
                'regex:/^-?\d{1,2}\.\d+$/',
            ],
            'longitude' => [
                'required',
                'numeric',
                'min:-180',
                'max:180',
                'regex:/^-?\d{1,3}\.\d+$/',
            ],
            'user_id' => [
                'required',
                'exists:users,id',
                'integer',
            ],
            'orario_entrata' => [
                'required',
                'date_format:H:i:s',
            ],
            'device_name' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->messages()->all());
        }
    }

    protected function getUserWithSede(int $userId): User
    {
        $user = User::with('anagrafica.sede')->find($userId);

        if (!$user || !$user->anagrafica || !$user->anagrafica->sede) {
            throw new \Exception('User not found or missing related data.');
        }

        return $user;
    }

    protected function validateWorkingDay($sede, $nowInSedeTimezone): void
    {
        if (!$sede->giorni_feriali) {
            throw new \Exception('Working days not configured.');
        }

        if (!$sede->isWorkingDay($nowInSedeTimezone)) {
            throw new \Exception('Oggi non è un giorno lavorativo.');
        }
    }

    protected function validateWorkingHours($sede, string $orarioEntrata, string $timezone, Carbon $nowInSedeTimezone): void
    {
        $orarioLavorativoInizio = Carbon::parse($sede->orario_inizio, $timezone);
        $orarioLavorativoFine = Carbon::parse($sede->orario_fine, $timezone);
        $orarioUtente = Carbon::createFromFormat('H:i:s', $orarioEntrata, $timezone);

        // Verifica che l'orario di ingresso non sia nel passato
        if ($orarioUtente->lt($nowInSedeTimezone)) {
            throw new \Exception('Stai cercando di registrare un orario di ingresso passato.');
        }

        // Verifica che l'orario di ingresso sia all'interno dell'intervallo lavorativo
        if ($orarioUtente->lt($orarioLavorativoInizio) || $orarioUtente->gt($orarioLavorativoFine)) {
            throw new \Exception('Stai cercando di registrare un orario di ingresso fuori dall\'orario lavorativo.');
        }
    }

    protected function registerPresence(User $user, Request $request, Carbon $nowInSedeTimezone)
    {
        // Registra la presenza
        Presenza::create([
            'anagrafica_id' => $user->anagrafica->id,
            'data' => $nowInSedeTimezone->toDateString(),
            'ora_entrata' => $nowInSedeTimezone->format('H:i:s'),
            'coordinate_entrata_lat' => $request->latitude,
            'coordinate_entrata_long' => $request->longitude,
        ]);
    }

    public function validateDistance($latitude, $longitude, $sede): bool
    {

        // Calcoliamo la distanza
        $distance = self::calculateDistance(
            $latitude,
            $longitude,
            $sede
        );


        if ($distance > self::DISTANCE_TOLERANCE) {
            throw new \Exception('Distanza maggiore di ' . self::DISTANCE_TOLERANCE . ' metri.');
        }

        return true;
    }

    protected function calculateDistance(mixed $latitude, mixed $longitude, $sede)
    {
        $earthRadius = 6371000; // Raggio terrestre in metri

        $latFrom = deg2rad($latitude);
        $lonFrom = deg2rad($longitude);
        $latTo = deg2rad($sede->latitudine);
        $lonTo = deg2rad($sede->longitudine);

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

}
