<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function index()
    {
        $data['title'] = 'Login';
        return view('auth.index',$data);
    }

    public function loginProses(Request $request)
    {
        // Ambil data dari input
        $username   = strtolower(trim($request->username));
        $kata_sandi = $request->kata_sandi;

        // Validasi input
        if (!$username || !$kata_sandi) {
            return back()->with('error', 'Username dan kata sandi harus diisi!');
        }

        // Validasi format username (hanya huruf, angka, underscore, tanpa spasi)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return back()->with('error', 'Username hanya boleh berisi huruf, angka, dan underscore tanpa spasi!');
        }

        // Cek user berdasarkan username
        $user = User::where('username', $username)->first();

        if (!$user) {
            return back()->with('error', 'Username tidak terdaftar dalam sistem!');
        }

        // Cek kata sandi
        if (!Hash::check($kata_sandi, $user->kata_sandi)) {
            return back()->with('error', 'Kata sandi tidak valid! Silahkan coba lagi.');
        }

        // Login berhasil - set session manual saja
        $prefix = config('session.prefix', 'app');
        Session::put("{$prefix}_id_user", $user->id_user);
        Session::put("{$prefix}_peran", $user->peran);
        Session::put("{$prefix}_nama", $user->nama);
        Session::put("{$prefix}_username", $user->username);
        
        // Regenerate session untuk keamanan
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Selamat datang ' . $user->nama);
    }

    public function logout(Request $request)
    {
        // Hapus session manual
        $prefix = config('session.prefix', 'app');
        Session::forget("{$prefix}_id_user");
        Session::forget("{$prefix}_peran");
        Session::forget("{$prefix}_nama");
        Session::forget("{$prefix}_username");
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}