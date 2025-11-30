<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'details' => 'array', // ✅ Laravel convertira automatiquement en JSON
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

}
