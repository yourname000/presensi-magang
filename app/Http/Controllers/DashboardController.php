<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB; // Tambahin ini
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

use App\Models\User;
use App\Models\Pengaturan;
use App\Models\Departemen;
use App\Models\Shift;
use App\Models\Presensi;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // PARAMETER
        $prefix  = config('session.prefix');
        $peran = session($prefix.'_peran');
        $id_user = session($prefix.'_id_user');

        if ($peran == 1) {
            return $this->adminDashboard($request);  // kirim request
        } else {
            return $this->employeeDashboard($request); // kirim request
        }
    }

    // FUNCTION PRIVATE
   
    private function employeeDashboard(Request $request)
    {
        // PARAMETER
        $prefix  = config('session.prefix');
        $peran   = session($prefix.'_peran');
        $id_user = session($prefix.'_id_user');

        $profile = User::with(['departemen'])->find($id_user);
        Carbon::setLocale('id'); // set bahasa Indonesia
        
        // tanggal untuk display
        $nowdate = Carbon::now()->translatedFormat('l, d F Y');
        // tanggal untuk query presensi
        $now     = Carbon::today()->toDateString();

        // GET DATA
        $presensi = Presensi::whereDate('tanggal_presensi', $now)
                    ->where('id_user', $id_user)
                    ->first();
        $shift = Shift::get();
        $profile = User::where('id_user',$id_user)->first();
        // SET TITLE
        $data['title']    = ucwords($profile->nama);
        $data['icon']     = '<i class="fa-solid text-white fa-user fs-3x me-4"></i>';
        $data['subtitle'] = 'NIK : '.$profile->nik.' | Departemen : '.$profile->departemen->nama;
        $data['nowdate']  = $nowdate;
        $data['presensi'] = $presensi;
        $data['shift'] = $shift;
        $data['profile'] = $profile;

        return view('dashboard.employee', $data);
    }

     private function adminDashboard(Request $request)
    {
        // TODO: Implement admin dashboard logic
        return view('dashboard.admin');
    }
}