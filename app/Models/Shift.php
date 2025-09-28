<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    // Nama tabel
    protected $table = 'shift';

    // Primary key
    protected $primaryKey = 'id_shift';

    // Kalau primary key bukan increment int default, bisa ditulis:
    public $incrementing = true;
    protected $keyType = 'int';

    // Aktifkan timestamp (kalau pakai created_at & updated_at bawaan Laravel)
    public $timestamps = false; // karena di migration kamu set default manual

    // Kolom yang bisa diisi mass assignment
    protected $fillable = [
        'kode',
        'nama',
        'jam_masuk',
        'jam_pulang',
        'lembur',
        'created_at',
        'updated_at'
    ];
}
