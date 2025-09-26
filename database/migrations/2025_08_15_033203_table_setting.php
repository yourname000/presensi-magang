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
        
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id('id_pengaturan');
            $table->string('logo', 199)->nullable();
            $table->string('icon', 199)->nullable();
            $table->string('meta_title', 255)->nullable(); // Title untuk halaman web
            $table->string('lokasi', 255)->nullable();
            $table->string('lat',200)->nullable();
            $table->string('lng',200)->nullable();
            $table->integer('radius')->nullable();
            $table->timestamps(); // created_at & updated_at otomatis
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
