<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users'; // Nama tabel
    protected $primaryKey = 'id_user'; // Primary key
    public $timestamps = false; // Karena pakai manual timestamps

    protected $fillable = [
        'username', 'nama', 'peran','kata_sandi','nik',
        'created_by', 'created_at', 'updated_at',
        'id_departemen'
    ];

    protected $hidden = ['kata_sandi'];

    // Auto-hash kata_sandi saat diset
    public function setKataSandiAttribute($password)
    {
        if (!empty($password)) {
            $this->attributes['kata_sandi'] = Hash::make($password);
        }
    }


    // Relasi ke user yang membuat
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    // Relasi ke tabel access
    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'id_departemen', 'id_departemen');
    }
}
