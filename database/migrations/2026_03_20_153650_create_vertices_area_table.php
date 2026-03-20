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
        Schema::create('vertices_area', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("x");
            $table->bigInteger("y");
            $table->unsignedBigInteger("id_area");
            $table->timestamps();

            $table->foreign("id_area")->references("id")->on("areas")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vertices_area');
    }
};
