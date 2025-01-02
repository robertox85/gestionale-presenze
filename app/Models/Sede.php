<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Sede extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'sedi';

    protected $fillable = [
        'country_code',
        'nome',
        'indirizzo',
        'latitudine',
        'longitudine',
        'fuso_orario',
        'orario_inizio',
        'orario_fine',
        'attiva',
        'giorni_feriali',
        'esclusione_festivi',
    ];

    protected $casts = [
        'giorni_feriali' => 'array',
        'esclusione_festivi' => 'boolean',
    ];




    public function anagrafiche(): HasMany
    {
        return $this->hasMany(Anagrafica::class);
    }

    public function updateCoordinates(): void
    {

        if ($this->indirizzo) {
            $response = Http::withHeaders([
                'User-Agent' => 'LaravelApp/1.0 (dimarco.roberto85@gmail.com)', // Cambia con i tuoi dettagli
            ])->get('https://api.mapbox.com/search/geocode/v6/forward', [
                'q' => $this->indirizzo,
                'proximity' => 'ip',
                'limit' => 1,
                'access_token' => 'pk.eyJ1Ijoicm9iZXJ0b2RpbWFyY28iLCJhIjoiY2sxbmlvdWM4MGIxazNjdGlkc25mM2R0dyJ9.cDLQKpBgWCpIwDR2piNa8w',
            ]);

            if ($response->successful()) {

                $data = $response->json();

                if (!empty($data)) {
                    if (isset($data['features'][0]['geometry']['coordinates'])) {
                        $coordinates = $data['features'][0]['geometry']['coordinates'];
                        $this->latitudine = $coordinates[0] ?? null;
                        $this->longitudine = $coordinates[1] ?? null;
                    }

                    if (isset($data['features'][0]['properties']['context'])) {
                        $context = $data['features'][0]['properties']['context'];
                        $this->country_code = $context['country']['country_code'] ?? null;
                    }
                }

            }
        }
    }

    public function isWorkingDay(Carbon $date): bool
    {
        $giorniFeriali = $this->giorni_feriali ?? [1, 2, 3, 4, 5]; // Default: lunedì-venerdì

        // Controlla se il giorno della settimana è un giorno lavorativo
        if (!in_array($date->dayOfWeekIso, $giorniFeriali)) {
            return false;
        }

        // Controlla se la sede esclude le festività
        if ($this->esclusione_festivi) {
            $festivita = Festivita::where('country_code', $this->country_code)
                ->where('data_festivita', $date->toDateString())
                ->exists();

            if ($festivita) {
                return false;
            }
        }

        return true;
    }
    protected static function boot()
    {
        parent::boot();

        static::saving(function (Sede $sede) {
            if ($sede->isDirty('indirizzo')) {
                $sede->updateCoordinates();
                // Registra l'evento
                Log::info('Sede coordinates updated', [
                    'sede_id' => $sede->id,
                    'latitudine' => $sede->latitudine,
                    'longitudine' => $sede->longitudine,
                ]);
                // Refresh the model to reflect the changes
                // $sede->refresh();
            }
        });
    }


}