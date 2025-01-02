<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anagrafica extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'anagrafiche';

    protected $fillable = [
        'user_id',
        'sede_id',
        'nome',
        'cognome',
        'attivo',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function presenze(): HasMany
    {
        return $this->hasMany(Presenza::class);
    }

}
