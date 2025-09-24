<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'departemen';

    // Primary key
    protected $primaryKey = 'id_departemen';

    // Apakah primary key auto increment
    public $incrementing = true;

    // Tipe primary key
    protected $keyType = 'int';

    // Aktifkan timestamps (karena di migration sudah ada created_at & updated_at)
    public $timestamps = true;

    // Jika nama kolom timestamps custom, bisa pakai konstanta ini
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Kolom yang bisa diisi mass assignment
    protected $fillable = [
        'warna',
        'kode',
        'nama',
        'created_at',
        'updated_at',
    ];
}
