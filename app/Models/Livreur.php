<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Livreur extends Model
{
    protected $table = 'livreurs';

    protected $fillable = [
        'user_id',
        'permis_conduire',
        'vehicule',
        'zone_couverture',
        'is_disponible',
    ];

    protected $casts = [
        'is_disponible' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
