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
            $table->unsignedBigInteger('monument_id');
            $table->foreign('monument_id')->references('id')->on('monuments')->onDelete('cascade');
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
                        'Natural Monuments',
                        'Tombs and Mausoleums', 
                        'Oorlogsmonumenten',
                        'Beelden en sculpturen',
                        'Historische Gebouwen en Plaatsen',
                        'Nationale Monumenten',
                        'Archeologische Plaatsen',
                        'Culturele en Religieuze Monumenten',
                        'Openbare Kunstinstallaties',
                        'Gedenktekens voor Historische Evenementen',
                        'Natuurmonumenten en Graven en Mausoleums'
                    ]);
            $table->json('accessibility')->nullable();
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
