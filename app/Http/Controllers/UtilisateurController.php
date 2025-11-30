<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $utilisateur = Utilisateur::with('specialite')->get();
        return response()->json($utilisateur, 200);
    }

    public function getMedecins()
    {
        $medecins = Utilisateur::where('role', 'medecin')
            ->with('specialite')
            ->get();

        return response()->json($medecins, 200);
    }

    public function getMedecinById(string $id)
    {
        $utilisateur = Utilisateur::with('specialite')->find($id);

        if (!$utilisateur) {
            return response()->json(['message' => 'Médecin non trouvé'], 404);
        }

        return response()->json($utilisateur, 200);
    }


    public function getMedecinPatients()
    {
        $medecin = auth()->user();

        $patients = $medecin->patients();

        return response()->json($patients, 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email',
            'password' => 'required|string|min:6',
            'telephone' => $request->role === 'medecin' ? 'required|string|min:8|max:20' : 'nullable|string|min:8|max:20',
            'role' => 'required|in:admin,medecin,patient',
            'specialite_id' => $request->role === 'medecin' ? 'required|exists:specialites,id' : 'nullable',
        ]);

        // Hash du mot de passe
        $validated['password'] = Hash::make($validated['password']);

        // Génération du matricule
        $prenomInitial = strtoupper(substr($validated['prenom'], 0, 1));
        $nomInitial = strtoupper(substr($validated['nom'], 0, 1));
        $annee = Carbon::now()->format('Y');

        // Nombre d’utilisateurs
        $count = Utilisateur::count() + 1;

        $sequentiel = str_pad($count, 3, '0', STR_PAD_LEFT);

        // Matricule final
        $validated['matricule'] = "User_{$prenomInitial}{$nomInitial}_{$annee}_{$sequentiel}";

        // === UNE SEULE INSERTION ===
        $utilisateur = Utilisateur::create($validated);

        return response()->json($utilisateur, 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $utilisateurs = Utilisateur::find($id);

        if (!$utilisateurs) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        return response()->json($utilisateurs, 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $utilisateurs = Utilisateur::find($id);

        if (!$utilisateurs) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email,' . $id,
            'telephone' => $request->role === 'medecin' ? 'required|string|min:8|max:20' : 'nullable|string|min:8|max:20',
            'dateNaissance' => 'nullable|date',
            'genre' => 'nullable|in:Homme,Femme',
            'adresse' => 'nullable|string|max:255',
            'role' => 'required|in:admin,medecin,patient',
        ]);


        $utilisateurs->update($validated);
        return response()->json($utilisateurs, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $utilisateurs = Utilisateur::find($id);

        if (!$utilisateurs) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $utilisateurs->delete();

        return response()->json(204);
    }
}
