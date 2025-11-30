<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Utilisateur extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $guarded = [];


    protected $hidden = [
        'password', // Ne jamais renvoyer le mot de passe dans les réponses
    ];

    // ✅ conversion automatique JSON <-> tableau
    protected $casts = [
        'allergies' => 'array',
        'antecedentMedicaux' => 'array',
        'traitementEnCours' => 'array',
    ];


    // ⭐ MÉTHODE 1 : Retourne l'identifiant unique de l'utilisateur pour le JWT
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Retourne l'ID de l'utilisateur (ex: 1, 2, 3...)
    }

    // ⭐ MÉTHODE 2 : Ajoute des informations personnalisées dans le JWT
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,  // On ajoute le rôle dans le token
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'dateNaissance' => $this->dateNaissance,
            'genre' => $this->genre,
            'adresse' => $this->adresse,
            'groupeSanguin' => $this->groupeSanguin,
            'poids' => $this->poids,
            'taille' => $this->taille,
            'allergies' => $this->allergies,
            'antecedentMedicaux' => $this->antecedentMedicaux,
            'traitementEnCours' => $this->traitementEnCours,
        ];
    }

    public function specialite()
    {
        return $this->belongsTo(Specialite::class);
    }

    public function rendezVousCommeMedecin()
    {
        return $this->hasMany(RendezVous::class, 'medecin_id');
    }


    public function rendezVousCommePatient()
    {
        return $this->hasMany(RendezVous::class, 'patient_id');
    }


    public function patients()
    {
        return $this->rendezVousCommeMedecin()
            ->with('patient')
            ->get()
            ->pluck('patient')
            ->unique('id')
            ->values();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }


}
