<?php
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 
class Formateur extends Model
{
    use HasFactory;
 
    protected $table = 'formateurs';
 
    protected $fillable = [
        'user_id',
        'artisan_id',
        'biographie',
        'specialite',
        'diplomes',
        'langues',
        'experience_annees',
        'est_externe',
        'organisme',
        'tarif_journee',
        'is_disponible',
    ];
 
    protected $casts = [
        'est_externe'   => 'boolean',
        'is_disponible' => 'boolean',
        'tarif_journee' => 'decimal:2',
    ];
 
    // ════════════════════════════════════════════════════════════
    // RELATIONS
    // ════════════════════════════════════════════════════════════
 
    // Formateur → User (1-1)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
 
    // Formateur → Artisan (1-1, nullable si externe)
    public function artisan()
    {
        return $this->belongsTo(Artisan::class, 'artisan_id');
    }
 
    // Formateur ↔ Formations (N-N via formation_formateurs)
    public function formations()
    {
        return $this->belongsToMany(
            Formation::class,
            'formation_formateurs',
            'formateur_id',
            'formation_id'
        )->withPivot('role')
         ->withTimestamps();
    }
 
    // ════════════════════════════════════════════════════════════
    // SCOPES
    // ════════════════════════════════════════════════════════════
 
    public function scopeDisponibles($query)
    {
        return $query->where('is_disponible', true);
    }
 
    public function scopeExternes($query)
    {
        return $query->where('est_externe', true);
    }
 
    public function scopeInternes($query)
    {
        return $query->where('est_externe', false);
    }
 
    // ════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════
 
    public function isExterne(): bool { return $this->est_externe; }
    public function isInterne(): bool { return !$this->est_externe; }
 
    public function getNomCompletAttribute(): string
    {
        if ($this->user) {
            return $this->user->nom_complet;
        }
        if ($this->artisan?->user) {
            return $this->artisan->user->nom_complet;
        }

        return $this->organisme ?? 'Formateur';
    }
}
 