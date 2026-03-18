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
        Schema::create('responsability_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('civil_servant_id')->constrained('civil_servants')->onDelete('cascade');
            $table->string('assign_name')->nullable();
            $table->string('role')->nullable();
            $table->string('assignment_code')->unique();
            $table->dateTime('assign_date');
            $table->dateTime('update_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responsability_cards');
    }
};
