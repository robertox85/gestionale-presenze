<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Festivita extends Model
{
    protected $table = 'festivita';

    protected $fillable = [
        'country_code',
        'data_festivita',
        'descrizione',
        'sede_id',
    ];

    protected $casts = [
        'data_festivita' => 'datetime',
    ];


    // Festivita can have many Sedi
    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }
}
