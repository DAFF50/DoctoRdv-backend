<?php

namespace App\Http\Controllers;

use App\Models\Specialite;
use Illuminate\Http\Request;

class SpecialiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $specialite = Specialite::all();
        return response()->json($specialite,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'libelle' => 'required|string',
            'tarif' => 'required|integer',
        ]);

        $specialites = Specialite::create($validated);
        return response()->json($specialites, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $specialite = Specialite::find($id);

        if (!$specialite) {
            return response()->json(['message' => 'Specialité non trouvée'], 404);
        }

        return response()->json($specialite, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $specialite = Specialite::find($id);

        if (!$specialite) {
            return response()->json(['message' => 'Specialité non trouvée'], 404);
        }

        $validated = $request->validate([
            'libelle' => 'required|string',
        ]);


        $specialite->update($validated);
        return response()->json($specialite, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $specialite = Specialite::find($id);

        if (!$specialite) {
            return response()->json(['message' => 'Specialité non trouvée'], 404);
        }

        $specialite->delete();

        return response()->json(['message' => 'Spécialité supprimée avec succée'], 204);
    }
}
