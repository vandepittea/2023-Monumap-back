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
        if (!Schema::hasTable('monuments')) {
        Schema::create('monuments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->string('historical_significance')->nullable();
            $table->enum('type', [
                'War Memorials',
                'Statues and Sculptures',
                'Historical Buildings and Sites',
                'National Monuments',
                'Archaeological Sites',
                'Cultural and Religious Monuments',
                'Public Art Installations',
                'Memorials for Historical Events',
                'Natural Monuments and Tombs and Mausoleums',
                'Oorlogsmonumenten',
                'Beelden en Sculpturen',
                'Historische Gebouwen en Plaatsen',
                'Nationale Monumenten',
                'Archeologische Plaatsen',
                'Culturele en Religieuze Monumenten',
                'Openbare Kunstinstallaties',
                'Gedenktekens voor Historische Evenementen',
                'Natuurmonumenten en Graven en Mausoleums',
            ]);
            $table->integer('year_of_construction');
            $table->string('monument_designer');
            $table->json('accessibility')->nullable()->check(
            'wheelchair-friendly',
            'near parking areas',
            'low-slope ramps',
            'power-assisted doors and elevators',
            'accessible washrooms',
            'rolstoelvriendelijk',
            'in de buurt van parkeerterreinen',
            'laaghellende opritten',
            'elektrisch ondersteunde deuren en liften',
            'toegankelijke toiletten'
        );
            $table->json('used_materials')->nullable();
            $table->foreignId('dimensions_id')->nullable()->constrained('dimensions')->onDelete('cascade');
            $table->integer('weight')->nullable();
            $table->decimal('cost_to_construct', 10, 2)->nullable();
            $table->foreignId('audiovisual_source_id')->nullable()->constrained('audiovisual_sources')->onDelete('cascade');
            $table->enum('language', [
                'Dutch',
                'English'
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
        Schema::dropIfExists('monuments');
    }
};
