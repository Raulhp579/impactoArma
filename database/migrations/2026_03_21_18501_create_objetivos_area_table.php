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
        Schema::create('objetivos_area', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("id_area");
            $table->string("nombre")->nullable(); // <--- NUEVO
            $table->double("x_zona");
            $table->double("y_zona");
            $table->timestamps();

            $table->foreign("id_area")->references("id")->on("areas")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objetivos_area');
    }
};
