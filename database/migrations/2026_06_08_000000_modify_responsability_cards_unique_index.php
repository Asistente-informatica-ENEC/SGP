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
        Schema::table('responsability_cards', function (Blueprint $table) {
            // Eliminar índice único sobre assignment_code (si existe)
            try {
                $table->dropUnique(['assignment_code']);
            } catch (\Exception $e) {
                // index may not exist; ignore
            }

            // Crear índice único compuesto por civil_servant_id, type y assignment_code
            $table->unique(['civil_servant_id', 'type', 'assignment_code'], 'responsability_cards_servant_type_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('responsability_cards', function (Blueprint $table) {
            // Eliminar índice compuesto
            try {
                $table->dropUnique('responsability_cards_servant_type_code_unique');
            } catch (\Exception $e) {
                // ignore
            }

            // Restaurar índice único simple sobre assignment_code
            $table->unique('assignment_code', 'responsability_cards_assignment_code_unique');
        });
    }
};
