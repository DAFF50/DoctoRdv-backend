<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DossierMedicalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlageHoraireController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\SpecialiteController;
use App\Http\Controllers\UtilisateurController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 🌐 ROUTES PUBLIQUES
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);


Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);

    Route::get('dossier-medicaux', [DossierMedicalController::class, 'index']);
    Route::get('dossier-medicaux/{id}', [DossierMedicalController::class, 'show']);
    Route::get('notifications', [NotificationController::class, 'index']);

    Route::middleware('role:admin')->group(function () {
        Route::apiResource("specialites",SpecialiteController::class);
        Route::apiResource("utilisateurs",UtilisateurController::class);
        Route::get('rendez-vous-admin', [RendezVousController::class, 'index']);
    });

    Route::middleware('role:patient')->group(function () {
        Route::get("medecins",[UtilisateurController::class, 'getMedecins']);
        Route::get('medecins/{id}', [UtilisateurController::class, 'getMedecinById']);
        Route::get('plages-horaires/medecin/{id}', [PlageHoraireController::class, 'getPlagesParMedecinId']);
        Route::apiResource("rendez-vous",RendezVousController::class);
        Route::get('rendez-vous-patient/{id}', [RendezVousController::class, 'getRendezVousByPatient']);
        Route::post('profile/update', [AuthController::class, 'updateProfile']);
        Route::post('profile/updatePassword', [AuthController::class, 'updatePassword']);
        Route::get('/patients/me/statistiques', [UtilisateurController::class, 'getDashboardStatsPatient']);
    });

    Route::middleware('role:medecin')->group(function () {
        Route::get('mes-plages', [PlageHoraireController::class, 'getPlagesParMedecin']);
        Route::post('mes-plages', [PlageHoraireController::class, 'savePlages']);
        Route::post('plages-horaires', [PlageHoraireController::class, 'addPlage']);
        Route::delete('plages-horaires/{id}', [PlageHoraireController::class, 'deletePlage']);
        Route::patch('plages-horaires/{id}/toggle', [PlageHoraireController::class, 'togglePlage']);
        Route::get('rendez-vous-medecin/{id}', [RendezVousController::class, 'getRendezVousByMedecin']);
        Route::get('medecin/patients', [UtilisateurController::class, 'getMedecinPatients']);
        Route::post('dossier-medicaux', [DossierMedicalController::class, 'store']);
        Route::post('document-medicaux/{id}', [DocumentController::class, 'store']);
    });
});
