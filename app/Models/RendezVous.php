<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'rendez_vous';

    public function medecin()
    {
        return $this->belongsTo(Utilisateur::class, 'medecin_id');
    }

    public function patient()
    {
        return $this->belongsTo(Utilisateur::class, 'patient_id');
    }
}
