<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlageHoraire extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function medecin(){
        return $this->belongsTo(Utilisateur::class);
    }
}
