<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

use App\Models\Pengaturan;
use App\Models\User;

class DashboardMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $prefix = config('session.prefix');
        $id_user = Session::get("{$prefix}_id_user");

        if (Schema::hasTable('pengaturan')) {
            $pengaturan = Pengaturan::where('id_pengaturan', 1)->first();
            View::share('setting', $pengaturan);
            App::instance('setting', $pengaturan); // optional
        }

       

        if (Schema::hasTable('users')) {
             $users = User::where('id_user', $id_user)->first();
            View::share('profils', $users);
            App::instance('profils', $users); // optional
            if (!$users) {
                return redirect('/login')->with('error', 'Silahkan login terlebih dahulu!');
            }
            
        }else{
            return redirect('/logout')->with('error', 'Silahkan login terlebih dahulu!');
        }

        
        if (!$id_user) {
            return redirect('/logout')->with('error', 'Silahkan login terlebih dahulu!');
        }

        return $next($request);
    }
}
