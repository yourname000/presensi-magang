<?php 
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AccountData extends Seeder
{
    public function run()
    {
        User::insert([
            [
                'nama' => 'Superadmin',
                'peran' => 1,
                'username' => 'admin',
                'kata_sandi' => Hash::make('12345'),
            ]
        ]);
    }
}
