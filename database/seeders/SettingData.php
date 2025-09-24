<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pengaturan;

class SettingData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pengaturan::create([
            'id_pengaturan' => 1,
            'logo' => 'logo.png',
            'icon' => 'icon.png',
            'meta_title' => '',
            'meta_author' => '',
            'meta_keyword' => '',
            'meta_description' => '',
            'meta_address' => '',
            'updated_at' => now(),
        ]);
    }
}
