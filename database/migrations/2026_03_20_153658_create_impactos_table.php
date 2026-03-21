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
        Schema::create('impactos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("x_impacto");
            $table->bigInteger("y_impacto");
            $table->dateTime("momento_impacto");
            $table->boolean("efectivo")->default(false);
            $table->double("eficacia")->nullable();
            $table->unsignedBigInteger("id_area");
            $table->unsignedBigInteger("id_arma");
            $table->timestamps();

            $table->foreign("id_area")->references("id")->on("areas")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("id_arma")->references("id")->on("armas")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impactos');
    }
};
