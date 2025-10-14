<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensi';
    protected $primaryKey = 'id_presensi';
    public $timestamps = true;

    protected $fillable = [
        'id_user',
        'id_shift',
        'tanggal_presensi',
        'scan_in',
        'scan_out',
        'hadir',
        'terlambat',
        'status_terlambat',
        'status_pulang_cepat',
        'lat_in',
        'lng_in',
        'lat_out',
        'lng_out',
        'lembur',
        'pulang_cepat',
        'waktu_terlambat',
        'keterangan'
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Relasi ke Shift
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'id_shift', 'id_shift');
    }
}
