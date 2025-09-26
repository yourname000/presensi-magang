<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'pengaturan';

    // Primary key
    protected $primaryKey = 'id_pengaturan';

    // PK auto increment
    public $incrementing = true;
    protected $keyType = 'int';

    // timestamps true karena kamu pakai $table->timestamps()
    public $timestamps = true;

    // Kolom yang boleh diisi
    protected $fillable = [
        'logo',
        'icon',
        'meta_title',
        'lokasi',
        'lat',
        'lng',
        'radius',
    ];
}
