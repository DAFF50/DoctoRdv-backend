<?php

namespace App\Http\Controllers;

use App\Models\DossierMedical;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DossierMedicalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // Base query avec relations à charger (ex: specialite, patient, medecin)
        $query = DossierMedical::with(['patient', 'medecin']);

        // Filtrer selon le rôle
        if ($user->role === 'patient') {
            $query->where('patient_id', $user->id);
        } elseif ($user->role === 'medecin') {
            $query->where('medecin_id', $user->id);
        }

        $dossiers = $query->get();

        return response()->json($dossiers, 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'patient_id' => [
                'required',
                Rule::exists('utilisateurs', 'id')->where(function ($query) {
                    $query->where('role', 'patient');
                }),
            ],
        ]);


        $annee = Carbon::now()->format('Y');
        // Nombre d’utilisateurs
        $count = DossierMedical::count() + 1;
        $sequentiel = str_pad($count, 3, '0', STR_PAD_LEFT);

        $medecin = auth()->user();

        // 🛡️ Vérification supplémentaire : le patient doit appartenir au médecin connecté
        $patient = $medecin->patients()
            ->where('id', $validated['patient_id'])
            ->first();

        if (!$patient) {
            return response()->json([
                'message' => 'Patient introuvable',
            ], 404);
        }

        $validated['matricule'] = "Dos_" . $patient->id . $medecin->id . "_" . $annee . "_" . $sequentiel;
        $validated['medecin_id'] = $medecin->id;

        $dossierMedical = DossierMedical::create($validated);

        return response()->json($dossierMedical, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();

        // Récupération du dossier
        $dossier = DossierMedical::with([
            'patient',
            'medecin',
            'medecin.specialite',
            'documents'
        ])->find($id);

        
        foreach ($dossier->documents as $doc) {
            $tailleBrute = Storage::disk('public')->size($doc->cheminFichier);
            $doc->taille_formattee = $this->formatSize($tailleBrute);
        }

        if (!$dossier) {
            return response()->json([
                'message' => 'Dossier médical introuvable'
            ], 404);
        }

        if ($dossier->patient_id !== $user->id && $dossier->medecin_id !== $user->id) {
            return response()->json([
                'message' => 'Accès non autorisé'
            ], 403);
        }

        // Accès autorisé
        return response()->json($dossier, 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    private function formatSize($size)
    {
        if ($size >= 1073741824) {
            return number_format($size / 1073741824, 2) . ' Go';
        } elseif ($size >= 1048576) {
            return number_format($size / 1048576, 2) . ' Mo';
        } elseif ($size >= 1024) {
            return number_format($size / 1024, 2) . ' Ko';
        } else {
            return $size . ' o';
        }
    }

}


