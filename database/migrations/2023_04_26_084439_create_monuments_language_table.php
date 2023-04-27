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
        Schema::table('monuments', function(Blueprint $table) {
            $table->dropColumn(['name', 'description', 'historical_significance', 'type', 'accessibility', 'used_materials']);
        });

        Schema::create('monuments_language', function (Blueprint $table) {
            $table->id();
            $table->integer('monument_id');
            $table->enum('language', [
                        'Dutch',
                        'English'
                    ]);
            $table->string('name');
            $table->text('description');
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
                        'Natural Monuments,Tombs and Mausoleums', 
                        'Oorlogsmonumenten',
                        'Beelden en sculpturen',
                        'Historische gebouwen en plaatsen',
                        'Nationale Monumenten',
                        'Archeologische sites',
                        'Culturele en religieuze monumenten',
                        'Openbare Kunstinstallaties',
                        'Herdenkingen voor historische gebeurtenissen',
                        'Natuurmonumenten,Graven en Mausolea'
                    ]);
                    $table->enum('accessibility', [
                        'wheelchair-friendly',
                        'near parking areas',
                        'low-slope ramps',
                        'power-assisted doors',
                        'elevators', 
                        'accessible washrooms',
                        'rolstoelvriendelijk',
                        'dichtbij parkeerplaatsen',
                        'hellingen met lage helling',
                        'elektrisch bediende deuren',
                        'liften, toegankelijke toiletten',
                    ])->nullable();
            $table->json('used_materials')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monuments_language');
    }
};
