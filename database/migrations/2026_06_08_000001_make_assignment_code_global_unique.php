<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('responsability_cards', function (Blueprint $table) {
            // Add index for FK before dropping the composite index that covers it
            $table->index('civil_servant_id', 'responsability_cards_civil_servant_id_index');

            try {
                $table->dropUnique('responsability_cards_servant_type_code_unique');
            } catch (\Exception $e) {
                // may not exist; ignore
            }

            // Unique per type: asignacion+descargo share a global sequence,
            // mal_estado and others have their own. App logic ensures no
            // cross-type collisions; the DB constraint prevents duplicates
            // within the same type as a safety net.
            $table->unique(['type', 'assignment_code'], 'responsability_cards_type_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('responsability_cards', function (Blueprint $table) {
            try {
                $table->dropUnique('responsability_cards_type_code_unique');
            } catch (\Exception $e) {
                // ignore
            }

            $table->unique(
                ['civil_servant_id', 'type', 'assignment_code'],
                'responsability_cards_servant_type_code_unique'
            );

            $table->dropIndex('responsability_cards_civil_servant_id_index');
        });
    }
};
