<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->id('id_presensi'); // AUTO_INCREMENT
            $table->unsignedBigInteger('id_user')->nullable();
            $table->unsignedBigInteger('id_shift')->nullable();
            $table->date('tanggal_presensi')->nullable();
            $table->time('scan_in')->nullable();
            $table->time('scan_out')->nullable();
            $table->enum('hadir', ['Y', 'N'])->default('Y')->nullable();
            $table->enum('terlambat', ['Y', 'N'])->default('N')->nullable();
            $table->enum('status_terlambat', ['Y', 'N'])->default('N')->nullable();
            $table->enum('status_pulang_cepat', ['Y', 'N'])->default('N')->nullable();
            $table->string('lat_in', 200)->nullable();
            $table->string('lng_in', 200)->nullable();
            $table->string('lat_out', 200)->nullable();
            $table->string('lng_out', 200)->nullable();
            $table->integer('lembur')->nullable();
            $table->integer('pulang_cepat')->nullable();
            $table->integer('waktu_terlambat')->nullable();
            $table->text('keterangan')->nullable();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('id_user')
                    ->references('id_user')->on('users')
                    ->onUpdate('CASCADE')
                    ->onDelete('CASCADE');
            $table->foreign('id_shift')
                    ->references('id_shift')->on('shift')
                    ->onUpdate('CASCADE')
                    ->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
