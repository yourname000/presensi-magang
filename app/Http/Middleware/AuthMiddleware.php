<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

use App\Models\Pengaturan;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        // Load pengaturan untuk semua request
        if (Schema::hasTable('pengaturan')) {
            $pengaturan = Pengaturan::where('id_pengaturan', 1)->first();
            View::share('setting', $pengaturan);
        }

        // Cek session manual
        $prefix = config('session.prefix', 'app');
        $id_user = Session::get("{$prefix}_id_user");
        $id_role = Session::get("{$prefix}_id_role");
        
        // Cek apakah sedang mengakses halaman login
        $isLoginPage = $request->is('login') || $request->is('login-proses') || $request->is('/');
        
        // Jika user sudah login
        if ($id_user && $id_role) {
            // Jika sudah login tapi akses halaman login, redirect ke dashboard
            if ($isLoginPage) {
                return redirect('/dashboard');
            }
        } else {
            // User belum login
            // Jika belum login dan bukan halaman login, redirect ke login
            if (!$isLoginPage) {
                return redirect('/login');
            }
        }

        return $next($request);
    }
}