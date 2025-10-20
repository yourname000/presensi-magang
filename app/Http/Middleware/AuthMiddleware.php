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

class AuthMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $prefix = config('session.prefix');
        $id_user = Session::get("{$prefix}_id_user");
        $id_role = Session::get("{$prefix}_id_role");

        if (Schema::hasTable('pengaturan')) {
            $pengaturan = Pengaturan::where('id_pengaturan', 1)->first();
            View::share('setting', $pengaturan);
            App::instance('setting', $pengaturan); // optional
        }

        if ($id_user) {
            return redirect('/dashboard');
        }

        return $next($request);
    }
}
