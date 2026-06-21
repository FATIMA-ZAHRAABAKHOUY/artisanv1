<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormationFormateur extends Model
{
    use HasFactory;
 
    protected $table = 'formation_formateurs';
 
    public $timestamps = false;
 
    protected $fillable = [
        'formation_id',
        'formateur_id',
        'role',
    ];
 
    public function formation()
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }
 
    public function formateur()
    {
        return $this->belongsTo(Formateur::class, 'formateur_id');
    }
}
 