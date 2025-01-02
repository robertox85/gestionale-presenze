<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use function Livewire\before;


class Presenza extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'presenze';


    protected $fillable = [
        'anagrafica_id',
        'data',
        'ora_entrata',
        'coordinate_entrata_lat',
        'coordinate_entrata_long',
        'ora_uscita',
        'coordinate_uscita_lat',
        'coordinate_uscita_long',
        'uscita_automatica',
        'note',
    ];

    public function anagrafica(): BelongsTo
    {
        return $this->belongsTo(Anagrafica::class);
    }





    protected static function boot()
    {
        parent::boot();

        // static::saving(function (Presenza $presenza) {
        //     // registra la presenza solo se è entro il raggio, altrimenti logga un warning
        //     if (!$presenza->isWithinRadius()) {
        //         Log::log('warning', 'Tentativo di registrazione di presenza fuori raggio', [
        //             'presenza' => $presenza->toArray(),
        //         ]);

        //         return false;
        //     }

        //     Log::log('info', 'Presenza registrata', [
        //         'presenza' => $presenza->toArray(),
        //     ]);

        //     return true;
        // });
    }
}
