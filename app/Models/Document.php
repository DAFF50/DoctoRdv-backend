<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function dossierMedical()
    {
        return $this->belongsTo(DossierMedical::class, 'dossier_medical_id');
    }
}
