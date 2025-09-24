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

    // Mass assignable fields
    protected $fillable = [
        'logo',
        'icon',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'meta_author',
        'meta_address',
        'meta_phone',
        'meta_email',
    ];
}
