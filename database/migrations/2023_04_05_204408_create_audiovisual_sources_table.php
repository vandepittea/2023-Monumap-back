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
        if (!Schema::hasTable('audiovisual_sources')) {
        Schema::create('audiovisual_sources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('url');
            $table->enum('type', [
                'audio',
                'video'
            ]);
            $table->timestamps();
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audiovisual_sources');
    }
};
