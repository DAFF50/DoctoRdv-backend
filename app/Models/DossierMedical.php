<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DossierMedical extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function medecin()
    {
        return $this->belongsTo(Utilisateur::class, 'medecin_id');
    }

    public function patient()
    {
        return $this->belongsTo(Utilisateur::class, 'patient_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'dossier_medical_id');
    }

}
