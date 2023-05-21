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
        Schema::table('audiovisual_sources', function(Blueprint $table) {
            $table->dropColumn(['title']);
        });

        Schema::create('audiovisual_sources_language', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audiovisual_source_id');
            $table->foreign('audiovisual_source_id')->references('id')->on('audiovisual_sources')->onDelete('cascade');
            $table->string('title');
            $table->string('language');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audiovisual_sources_language');
    }
};
