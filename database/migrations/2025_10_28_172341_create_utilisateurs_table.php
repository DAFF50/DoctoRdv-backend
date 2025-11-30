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
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('telephone')->nullable(); // facultatif
            $table->date('dateNaissance')->nullable(); // facultatif
            $table->enum('genre', ['Homme', 'Femme'])->nullable(); // facultatif
            $table->string('adresse')->nullable(); // facultatif
            $table->enum('role', ['admin', 'medecin', 'patient']); // obligatoire
            $table->foreignId('specialite_id')->nullable()->constrained('specialites');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
