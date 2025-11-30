<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->id();
            $table->string('matricule');
            $table->date('dateRdv');
            $table->time('heure');
            $table->string('statut');
            $table->string('motif');
            $table->enum('modePaiement', ['en ligne', 'cabinet']);
            $table->string('moyenPaiement')->nullable();
            $table->integer('montant');
            $table->string('cheminJustificatifPdf')->nullable();
            $table->foreignId('patient_id')->constrained('utilisateurs');
            $table->foreignId('medecin_id')->constrained('utilisateurs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rendez_vouses');
    }
};
