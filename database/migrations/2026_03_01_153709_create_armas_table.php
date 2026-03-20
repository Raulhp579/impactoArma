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
        Schema::create('armas', function (Blueprint $table) {
            $table->id();
            $table->string("tipo");
            $table->string("nombre");
            $table->string("descripcion")->nullable();
            $table->bigInteger("cord_x")->nullable();
            $table->bigInteger("cord_y")->nullable();
            $table->unsignedBigInteger("id_grupo");
            $table->timestamps();

            $table->foreign("id_grupo")->references("id")->on("grupos")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('armas');
    }
};
