<?php

namespace App\Http\Controllers;

use App\Models\PlageHoraire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlageHoraireController extends Controller
{
    // 📋 Récupérer toutes les plages du médecin connecté
    public function getPlagesParMedecin()
    {
        $medecinId = auth()->id();

        $plages = PlageHoraire::where('medecin_id', $medecinId)
            ->orderBy('jour')
            ->orderBy('heureDebut')
            ->get();

        $plagesParJour = $plages->groupBy('jour');

        return response()->json($plagesParJour);
    }

    // 💾 Enregistrer toutes les plages (supprime les anciennes)
    public function savePlages(Request $request)
    {
        $medecinId = auth()->id();

        $validator = Validator::make($request->all(), [
            'plages' => 'required|array',
            'plages.*.jour' => 'required|in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche',
            'plages.*.heureDebut' => 'required|date_format:H:i',
            'plages.*.heureFin' => 'required|date_format:H:i|after:plages.*.heureDebut',
            'plages.*.estActive' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 🧠 Vérifier chevauchement côté backend
        $grouped = collect($request->plages)->groupBy('jour');
        foreach ($grouped as $jour => $plagesDuJour) {
            for ($i = 0; $i < count($plagesDuJour); $i++) {
                for ($j = $i + 1; $j < count($plagesDuJour); $j++) {
                    $p1 = $plagesDuJour[$i];
                    $p2 = $plagesDuJour[$j];

                    if ($p1['heureDebut'] < $p2['heureFin'] && $p1['heureFin'] > $p2['heureDebut']) {
                        return response()->json([
                            'error' => "Les plages du $jour se chevauchent ({$p1['heureDebut']}-{$p1['heureFin']}) et ({$p2['heureDebut']}-{$p2['heureFin']})"
                        ], 422);
                    }
                }
            }
        }

        // ✅ Si tout est OK : suppression + insertion
        PlageHoraire::where('medecin_id', $medecinId)->delete();

        $createdPlages = [];
        foreach ($request->plages as $plage) {
            $createdPlages[] = PlageHoraire::create([
                'medecin_id' => $medecinId,
                'jour' => $plage['jour'],
                'heureDebut' => $plage['heureDebut'],
                'heureFin' => $plage['heureFin'],
                'estActive' => $plage['estActive'],
            ]);
        }

        return response()->json([
            'message' => '✅ Plages horaires enregistrées avec succès',
            'plages' => $createdPlages
        ], 201);
    }


    // 🗑 Supprimer
    public function deletePlage($id)
    {
        $plage = PlageHoraire::find($id);

        if (!$plage) return response()->json(['error' => 'Plage horaire introuvable'], 404);

        $plage->delete();
        return response()->json(['message' => 'Plage horaire supprimée'], 200);
    }

    // 🔄 Toggle active/inactive
    public function togglePlage($id)
    {
        $plage = PlageHoraire::find($id);

        if (!$plage) return response()->json(['error' => 'Plage horaire introuvable'], 404);

        $plage->estActive = !$plage->estActive;
        $plage->save();

        return response()->json($plage, 200);
    }

    // 📋 Récupérer toutes les plages d’un médecin par son ID (admin ou autre utilisateur)
    public function getPlagesParMedecinId($medecinId)
    {
        // 1️⃣ Vérifier si l’ID est bien fourni
        if (!$medecinId) {
            return response()->json(['error' => 'ID du médecin manquant'], 400);
        }

        // 2️⃣ Récupérer toutes les plages liées à ce médecin
        $plages = PlageHoraire::where('medecin_id', $medecinId)
            ->orderBy('jour')
            ->orderBy('heureDebut')
            ->get();

        // 3️⃣ Vérifier si le médecin a des plages
        if ($plages->isEmpty()) {
            return response()->json(['message' => 'Aucune plage horaire trouvée pour ce médecin'], 404);
        }

        // 4️⃣ Regrouper par jour (lundi, mardi, etc.)
        $plagesParJour = $plages->groupBy('jour')->map(function ($plagesDuJour) {
            return $plagesDuJour->map(function ($plage) {
                return [
                    'id' => $plage->id,
                    'jour' => $plage->jour,
                    'heureDebut' => $plage->heureDebut,
                    'heureFin' => $plage->heureFin,
                    'estActive' => $plage->estActive,
                ];
            });
        });

        // 5️⃣ Retourner la réponse
        return response()->json([
            'medecin_id' => $medecinId,
            'plages' => $plagesParJour
        ], 200);
    }


}
