<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{

    // 🔐 MÉTHODE 1 : CONNEXION (LOGIN)
    public function login(Request $request)
    {
        // 1️⃣ Récupérer email et password envoyés par Angular
        $credentials = $request->only('email', 'password');

        // 2️⃣ Vérifier si email et password sont corrects
        // auth()->attempt() vérifie dans la BD et compare les mots de passe hashés
        if (!$token = auth()->attempt($credentials)) {
            // ❌ Si incorrect, renvoyer erreur 401 (Non autorisé)
            return response()->json([
                'error' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        // ✅ Si correct, créer un token JWT et renvoyer les infos
        return response()->json([
            'access_token' => $token,  // Le token JWT
            'token_type' => 'bearer',   // Type de token (toujours "bearer" pour JWT)
            'user' => auth()->user(),   // Infos de l'utilisateur connecté
            'expires_in' => auth()->factory()->getTTL() * 60  // Durée de vie en secondes
        ]);
    }

    // 📝 MÉTHODE 2 : INSCRIPTION (REGISTER) - Si Admin crée un utilisateur

    public function register(Request $request)
    {
        // 1️⃣ Valider les données reçues
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email',
            'password' => 'required|string|min:6',
            'telephone' => 'required|string|min:8|max:20',
            'dateNaissance' => 'required|date',
            'genre' => 'required|in:Homme,Femme',
            'adresse' => 'required|string|max:255',
            'role' => 'required|in:admin,medecin,patient',
        ]);

        // Hash du mot de passe
        $validated['password'] = Hash::make($validated['password']);

        // Génération du matricule avant insertion
        $prenomInitial = strtoupper(substr($validated['prenom'], 0, 1));
        $nomInitial = strtoupper(substr($validated['nom'], 0, 1));
        $annee = Carbon::now()->format('Y');

        // Séquentiel basé sur le nombre total d'utilisateurs
        $count = Utilisateur::count() + 1;
        $sequentiel = str_pad($count, 3, '0', STR_PAD_LEFT);

        $validated['matricule'] = "User_{$prenomInitial}{$nomInitial}_{$annee}_{$sequentiel}";

        // ✅ Création de l'utilisateur en UNE SEULE INSERTION
        $utilisateur = Utilisateur::create($validated);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => $utilisateur
        ], 201);
    }



    public function updateProfile(Request $request)
    {
        // ✅ On récupère l'utilisateur connecté via le token
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        // ✅ Validation des champs (sans toucher au rôle ni à l'ID)
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'nullable|string|min:8|max:20',
            'dateNaissance' => 'nullable|date',
            'genre' => 'nullable|in:Homme,Femme',
            'adresse' => 'nullable|string|max:255',
        ]);

        // ✅ Mise à jour sécurisée du profil connecté
        $user->update($validated);

        return response()->json($user, 200);
    }


    public function updatePassword(Request $request)
    {
        // Récupération de l'utilisateur connecté
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        // Validation des champs
        $validated = $request->validate([
            'currentPassword' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:8']
        ]);

        // Vérification du mot de passe actuel
        if (!\Hash::check($validated['currentPassword'], $user->password)) {
            return response()->json(['message' => 'Le mot de passe actuel est incorrect'], 422);
        }

        // Mise à jour du mot de passe
        $user->password = \Hash::make($validated['newPassword']);
        $user->save();

        return response()->json(['message' => 'Mot de passe mis à jour avec succès'], 200);
    }



    // 👤 MÉTHODE 3 : Récupérer les infos de l'utilisateur connecté
    public function me()
    {
        // auth()->user() retourne l'utilisateur dont le token a été vérifié
        return response()->json(auth()->user());
    }


    // 🚪 MÉTHODE 4 : DÉCONNEXION (LOGOUT)
    public function logout()
    {
        // Invalide le token (il ne pourra plus être utilisé)
        auth()->logout();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }

    // 🔄 MÉTHODE 5 : RAFRAÎCHIR LE TOKEN
    public function refresh()
    {
        // Génère un nouveau token (l'ancien devient invalide)
        return response()->json([
            'access_token' => auth()->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
