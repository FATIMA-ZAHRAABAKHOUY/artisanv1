<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;
 
    protected $table = 'notifications';
 
    // Pas de updated_at
    const UPDATED_AT = null;
 
    protected $fillable = [
        'user_id',
        'type',
        'titre',
        'message',
        'data',
        'is_read',
    ];
 
    protected $casts = [
        'data'       => 'array',
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
    ];
 
    // ── Relations ────────────────────────────────────────────────
 
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
 
    // ── Scopes ───────────────────────────────────────────────────
 
    public function scopeNonLues($query)
    {
        return $query->where('is_read', false);
    }
 
    public function scopeParType($query, string $type)
    {
        return $query->where('type', $type);
    }
 
    // ── Helpers ──────────────────────────────────────────────────
 
    public function marquerLue(): void
    {
        $this->update(['is_read' => true]);
    }
 
    // Créer une notification facilement
    public static function envoyer(int $userId, string $type, string $titre, string $message, array $data = []): void
    {
        static::create([
            'user_id' => $userId,
            'type'    => $type,
            'titre'   => $titre,
            'message' => $message,
            'data'    => $data,
            'is_read' => false,
        ]);
    }
}
