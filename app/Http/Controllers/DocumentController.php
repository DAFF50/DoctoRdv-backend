<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DossierMedical;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $id)
    {
        $user = auth()->user();

        $dossier = DossierMedical::find($id);
        if (!$dossier) {
            return response()->json(['message' => 'Dossier non trouvé'], 404);
        }

        if ($dossier->medecin_id !== $user->id) {
            return response()->json([
                'message' => 'Accès non autorisé'
            ], 403);
        }

        // Validation des champs
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'file' => 'required|mimes:pdf|max:10240',
        ]);


        $date = now()->format('Y-m-d_H-i-s');
        $uuid = Str::uuid();
        $originalName = pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $request->file('file')->getClientOriginalExtension();

        $fileName = $originalName . '_' . $date . '_' . $uuid . '.' . $extension;

        $path = $request->file('file')->storeAs('documents', $fileName, 'public');

        // Création du document
        $document = Document::create([
            'titre' => $validated['titre'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'cheminFichier' => $path,
            'dossier_medical_id' => $id,
        ]);

        return response()->json([
            'message' => 'Document ajouter avec succès',
            'document' => $document
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
