<?php

namespace App\Http\Controllers;

use App\Models\RendezVous;
use App\Models\Utilisateur;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RendezVousController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rendez_vous = RendezVous::with(['medecin.specialite', 'patient'])->get();
        return response()->json($rendez_vous, 200);
    }

    public function getRendezVousByPatient($patient_id)
    {
        // Récupère uniquement les rendez-vous du patient donné
        $rendez_vous = RendezVous::with(['medecin.specialite'])
            ->where('patient_id', $patient_id)
            ->get();

        return response()->json($rendez_vous, 200);
    }


    public function getRendezVousByMedecin($medecin_id)
    {
        // Récupère uniquement les rendez-vous du médecin donné
        $rendez_vous = RendezVous::with(['patient'])
            ->where('medecin_id', $medecin_id)
            ->get();

        return response()->json($rendez_vous, 200);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation des champs
        $validated = $request->validate([
            'matricule' => 'required|string|max:255',
            'dateRdv' => 'required|date',
            'heure' => 'required|date_format:H:i',
            'statut' => 'required|string',
            'motif' => 'required|string|max:255',
            'modePaiement' => 'required|string|in:en ligne,cabinet',
            'montant' => 'required|integer',
            'cheminJustificatifPdf' => 'nullable|string|max:255',
            'medecin_id' => ['required', Rule::exists('utilisateurs', 'id')->where('role', 'medecin')],
            'patient_id' => ['required', Rule::exists('utilisateurs', 'id')->where('role', 'patient')],
        ]);

        // Création du rendez-vous
        $rendezVous = RendezVous::create($validated);

        // Création de la notification
        $medecin = Utilisateur::with('specialite')->find($validated['medecin_id']);
        $message = "Votre rendez-vous à la clinique DoctoRdv a été confirmé, Merci de consulter les détails en dessous pour plus d'informations.";
        $details = [
            'Date' => Carbon::parse($validated['dateRdv'] . ' ' . $validated['heure'])
                ->locale('fr')
                ->isoFormat('dddd D MMMM YYYY [à] HH:mm'),
            'Medecin' => $medecin->prenom. " " .$medecin->nom. " - " .$medecin->matricule,
            'Spécialité' => $medecin->specialite->libelle,
            'Statut' => 'Payé '
                . ($validated['modePaiement'] == 'cabinet' ? 'au cabinet' : 'en ligne')
                . ' (' . number_format($medecin->specialite?->tarif ?? 0, 0, ',', ' ') . ' FCFA)',
        ];

        NotificationService::create($validated['patient_id'], 'rdv-confirme', 'Rendez-vous confirmé', $message, $details, '📅');

        return response()->json([
            'message' => 'Rendez-vous créé avec succès',
            'rendez-vous' => $rendezVous
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
}
