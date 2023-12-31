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
        if (!Schema::hasTable('images')) {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('monument_id');
            $table->string('url');
            $table->string('caption');
            $table->timestamps();
            $table->foreign('monument_id')->references('id')->on('monuments')->onDelete('cascade');
        });
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
