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
            $table->text('meta_keyword')->nullable(); // Meta keywords SEO
            $table->text('meta_description')->nullable(); // Meta description SEO
            $table->text('meta_author')->nullable(); // Meta author
            $table->text('meta_address')->nullable(); // Alamat lengkap
            $table->string('meta_phone',200)->nullable(); // Alamat lengkap
            $table->string('meta_email',200)->nullable(); // Alamat lengkap
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
