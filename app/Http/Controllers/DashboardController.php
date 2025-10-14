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

     // POST FUNCTION
    public function absenLocation(Request $request)
    {
        // pastikan request juga dikonversi ke float
        $latUser = (float) $request->latitude;
        $lngUser = (float) $request->longitude;

        $pengaturan = Pengaturan::first();

        if (!$pengaturan || !$pengaturan->lat || !$pengaturan->lng) {
            return response()->json([
                'status' => 'error',
                'message' => 'Titik absensi belum ditentukan oleh admin!'
            ]);
        }

        // casting lat/lng dari DB ke float
        $latSetting = (float) $pengaturan->lat;
        $lngSetting = (float) $pengaturan->lng;
        $radius     = (float) $pengaturan->radius; // meter

        // Hitung jarak aktual
        $jarak = $this->haversineDistance($latUser, $lngUser, $latSetting, $lngSetting);
        $jarak = round($jarak, 2);

        $pusat = $pengaturan->lokasi ?? 'Pusat';

        if (empty($radius) || $radius == 0) {
            return response()->json([
                'status' => true,
                'message' => 'Anda berada di jarak <b>'.number_format($jarak,0,',','.').' Meter</b> dari <b>'.$pusat.'</b>'
            ]);
        }

        if ($jarak <= $radius) {
            return response()->json([
                'status' => true,
                'message' => 'Anda berada di jarak <b>'.number_format($jarak,0,',','.').' Meter</b> dari <b>'.$pusat.'</b>'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Anda berada di luar radius!</br>(jarak anda <b>".number_format($jarak,0,',','.')." Meter</b> dari <b>".$pusat."</b>)"
            ]);
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
        // PARAMETER
        $prefix  = config('session.prefix');
        $peran   = session($prefix.'_peran');
        $id_user = session($prefix.'_id_user');

        // SET DATA
        $data['title']    = 'Dashboard Admin';
        $data['icon']     = '<i class="fa-solid fa-gauge fs-3x text-white me-4"></i>';
        $data['subtitle'] = 'Selamat datang di sistem absensi karyawan!';

        // GET FILTER
        $id_departemen = $request->input('id_departemen');
        $bulan         = $request->input('bulan') ?? date('m');
        $tahun         = $request->input('tahun') ?? date('Y');

        // bikin cutoff date (akhir bulan filter)
        $cutoffDate = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth();

        // MASTER DATA
        $departemen = Departemen::all();

        // KARYAWAN
        $employeeQuery = User::where('peran', 2);

        if ($id_departemen && $id_departemen !== 'all') {
            $employeeQuery->where('id_departemen', $id_departemen);
        }

        // filter karyawan hanya yang created_at <= cutoff date
        $employeeQuery->where('created_at', '<=', $cutoffDate);

        $employee = $employeeQuery->count();

       

        // Kehadiran
        $kehadiran = $this->getRataRataKehadiran($bulan, $tahun, $id_departemen);

        // Siapin data untuk Chart.js Kehadiran
        $labels = $kehadiran['labels'] ?? []; 
        $values = $kehadiran['values'] ?? []; 

        // TOP 6 TERLAMBAT
        $topTerlambat = $this->getTopTerlambat(6, $bulan, $tahun);

        // TOP 6 LEMBUR
        $topLembur = $this->getTopLembur(6, $bulan, $tahun);

        // SET DATA UNTUK VIEW
        $data['departemen']     = $departemen;
        $data['id_departemen']  = $id_departemen;
        $data['bulan']          = $bulan;
        $data['tahun']          = $tahun;
        $data['employee']       = $employee;
        $data['values']         = $values;
        $data['labels']         = $labels;

        // tambahan chart baru
        $data['topTerlambatLabels'] = $topTerlambat['labels'];
        $data['topTerlambatValues'] = $topTerlambat['values'];

        $data['topLemburLabels']    = $topLembur['labels'];
        $data['topLemburValues']    = $topLembur['values'];

        return view('dashboard.index', $data);
    }




    private function getRataRataKehadiran($bulan = null, $tahun = null, $id_departemen = null)
    {
        // cutoff date = akhir bulan filter
        $cutoffDate = \Carbon\Carbon::create($tahun ?? date('Y'), $bulan ?? date('m'), 1)->endOfMonth();

        $query = Departemen::query()
            ->selectRaw('departemen.id_departemen')
            ->selectRaw('departemen.nama as departemen')
            ->selectRaw('COUNT(presensi.id_presensi) as total_presensi')
            ->selectRaw("SUM(CASE WHEN presensi.hadir = 'Y' THEN 1 ELSE 0 END) as jumlah_hadir")
            ->leftJoin('users', function($join) use ($cutoffDate) {
                $join->on('departemen.id_departemen', '=', 'users.id_departemen')
                    ->where('users.peran', 2)
                    ->where('users.created_at', '<=', $cutoffDate);
            })
            ->leftJoin('presensi', function ($join) use ($bulan, $tahun) {
                $join->on('users.id_user', '=', 'presensi.id_user');
                
                if ($bulan && $bulan !== 'all') {
                    $join->whereMonth('presensi.tanggal_presensi', $bulan);
                }
                if ($tahun && $tahun !== 'all') {
                    $join->whereYear('presensi.tanggal_presensi', $tahun);
                }
            })
            ->groupBy('departemen.id_departemen', 'departemen.nama')
            ->orderBy('departemen.id_departemen', 'asc');

        if ($id_departemen && $id_departemen !== 'all') {
            $query->where('departemen.id_departemen', $id_departemen);
        }

        $result = $query->get()->map(function ($row) {
            $row->persentase = $row->total_presensi > 0
                ? round(($row->jumlah_hadir / $row->total_presensi) * 100, 2)
                : 0;
            return $row;
        });

        return [
            'labels' => $result->pluck('departemen')->toArray(),
            'values' => $result->pluck('persentase')->toArray(),
        ];
    }

    private function getTopTerlambat($limit = 6, $bulan = null, $tahun = null)
    {
        $cutoffDate = \Carbon\Carbon::create($tahun ?? date('Y'), $bulan ?? date('m'), 1)->endOfMonth();

        $query = User::query()
            ->select('users.id_user', 'users.nama')
            ->selectRaw('COUNT(presensi.id_presensi) as total_terlambat')
            ->leftJoin('presensi', function ($join) use ($bulan, $tahun) {
                $join->on('users.id_user', '=', 'presensi.id_user')
                    ->where('presensi.terlambat', 'Y');

                if ($bulan && $bulan !== 'all') {
                    $join->whereMonth('presensi.tanggal_presensi', $bulan);
                }
                if ($tahun && $tahun !== 'all') {
                    $join->whereYear('presensi.tanggal_presensi', $tahun);
                }
            })
            ->where('users.peran', 2)
            ->where('users.created_at', '<=', $cutoffDate)
            ->groupBy('users.id_user', 'users.nama')
            ->orderByDesc('total_terlambat')
            ->limit($limit);

        $result = $query->get();

        return [
            'labels' => $result->pluck('nama')->toArray(),
            'values' => $result->pluck('total_terlambat')->toArray(),
        ];
    }

    private function getTopLembur($limit = 6, $bulan = null, $tahun = null)
    {
        $cutoffDate = \Carbon\Carbon::create($tahun ?? date('Y'), $bulan ?? date('m'), 1)->endOfMonth();

        $query = User::query()
            ->select('users.id_user', 'users.nama')
            ->selectRaw('COALESCE(SUM(presensi.lembur), 0) as total_lembur')
            ->leftJoin('presensi', function ($join) use ($bulan, $tahun) {
                $join->on('users.id_user', '=', 'presensi.id_user');

                if ($bulan && $bulan !== 'all') {
                    $join->whereMonth('presensi.tanggal_presensi', $bulan);
                }
                if ($tahun && $tahun !== 'all') {
                    $join->whereYear('presensi.tanggal_presensi', $tahun);
                }
            })
            ->where('users.peran', 2)
            ->where('users.created_at', '<=', $cutoffDate)
            ->groupBy('users.id_user', 'users.nama')
            ->orderByDesc('total_lembur')
            ->limit($limit);

        $result = $query->get();

        return [
            'labels' => $result->pluck('nama')->toArray(),
            'values' => $result->pluck('total_lembur')->toArray(),
        ];
    }


    // Fungsi haversineDistance (TIDAK BERUBAH)
    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    public function insert_presensi(Request $request)
    {
        // 🚨 VALIDASI UMUM: Selfie dan Koordinat Wajib
        if (!$request->input('selfie')) {
            return response()->json(['status' => false, 'required' => [['req_selfie', "Foto Selfie tidak boleh kosong!"]]]);
        }

        if (!$request->filled('latitude') || !$request->filled('longitude')) {
            return response()->json(['status' => false, 'message' => 'Gagal mendapatkan data lokasi (Latitude/Longitude).']);
        }

        $lat = $request->latitude;
        $lng = $request->longitude;

        $prefix = config('session.prefix');
        $id_user = session($prefix . '_id_user');

        if (!$id_user) {
            return response()->json(['status' => false, 'message' => 'Sesi pengguna tidak valid. Silakan login ulang.']);
        }

        $now = Carbon::now('Asia/Jakarta');
        $tanggal_presensi = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');

        // 1️⃣ CEK SUDAH PRESENSI MASUK BELUM
        $presensiHariIni = DB::table('presensi')
            ->where('id_user', $id_user)
            ->where('tanggal_presensi', $tanggal_presensi)
            ->first();

        // --- LOGIC SCAN IN ---
        if (!$presensiHariIni || !$presensiHariIni->scan_in) {

            if (!$request->input('id_shift')) {
                return response()->json(['status' => false, 'required' => [['req_id_shift', "Pilih Shift tidak boleh kosong!"]]]);
            }

            $shift = DB::table('shift')->where('id_shift', $request->id_shift)->first();
            if (!$shift) {
                return response()->json(['status' => false, 'message' => 'Data shift tidak ditemukan.']);
            }

            $jamMasukShift = Carbon::parse($tanggal_presensi . ' ' . $shift->jam_masuk);
            $scanInTime = Carbon::parse($tanggal_presensi . ' ' . $currentTime);

            $terlambat = 'N';
            $waktuTerlambat = 0;

            if ($scanInTime->greaterThan($jamMasukShift)) {
                $terlambat = 'Y';
                $waktuTerlambat = abs($scanInTime->diffInMinutes($jamMasukShift));
            }

            // Simpan Gambar Selfie Masuk
            $selfieData = $request->input('selfie');
            list($type, $selfieData) = explode(';', $selfieData);
            list(, $selfieData) = explode(',', $selfieData);
            $selfieData = base64_decode($selfieData);

            $tujuan = public_path('data/presensi/');
            $fileName = 'selfie_in_' . $id_user . '_' . $now->format('YmdHis') . '.jpeg';
            $filePath = $tujuan . $fileName;

            if (!File::exists($tujuan)) {
                File::makeDirectory($tujuan, 0755, true, true);
            }
            File::put($filePath, $selfieData);

            $dataInsert = [
                'id_user' => $id_user,
                'id_shift' => $request->id_shift,
                'tanggal_presensi' => $tanggal_presensi,
                'scan_in' => $currentTime,
                'hadir' => 'Y',
                'image_in' => $fileName,
                'lat_in' => $lat,
                'lng_in' => $lng,
                'terlambat' => $terlambat,
                'waktu_terlambat' => $waktuTerlambat,
                'created_at' => $now,
                'updated_at' => $now
            ];

            $insert = DB::table('presensi')->insert($dataInsert);

            if ($insert) {
                return response()->json(['status' => true, 'message' => 'Presensi Masuk Berhasil! Selamat bekerja.']);
            } else {
                File::delete($filePath);
                return response()->json(['status' => false, 'message' => 'Gagal menyimpan data presensi masuk ke database.']);
            }
        }

        // --- LOGIC SCAN OUT ---
        elseif ($presensiHariIni && !$presensiHariIni->scan_out) {

            $shift = DB::table('shift')->where('id_shift', $presensiHariIni->id_shift)->first();
            if (!$shift) {
                return response()->json(['status' => false, 'message' => 'Data shift dari presensi masuk tidak ditemukan.']);
            }

            $scanOutTime = Carbon::parse($tanggal_presensi . ' ' . $currentTime);
            $jamPulangShift = Carbon::parse($tanggal_presensi . ' ' . $shift->jam_pulang);

            // ✅ Tambahkan lembur dari shift (dalam menit)
            $jamPulangDenganLembur = $jamPulangShift->copy()->addMinutes((int)$shift->lembur);

            $pulangCepat = 'N';
            $waktuPulangCepat = 0;
            $lembur = 0;

            // Pulang cepat kalau scan_out < jam_pulang_shift
            if ($scanOutTime->lessThan($jamPulangShift)) {
                $pulangCepat = 'Y';
                $waktuPulangCepat = abs($jamPulangShift->diffInMinutes($scanOutTime));
            }
            // Lembur kalau scan_out > (jam_pulang + lembur_shift)
            elseif ($scanOutTime->greaterThan($jamPulangDenganLembur)) {
                $lembur = abs($scanOutTime->diffInMinutes($jamPulangShift));
            }

            // Simpan Gambar Selfie Pulang
            $selfieData = $request->input('selfie');
            list($type, $selfieData) = explode(';', $selfieData);
            list(, $selfieData) = explode(',', $selfieData);
            $selfieData = base64_decode($selfieData);

            $tujuan = public_path('data/presensi/');
            $fileNamePulang = 'selfie_out_' . $id_user . '_' . $now->format('YmdHis') . '.jpeg';
            $filePathPulang = $tujuan . $fileNamePulang;

            if (!File::exists($tujuan)) {
                File::makeDirectory($tujuan, 0755, true, true);
            }
            File::put($filePathPulang, $selfieData);

            $updateData = [
                'scan_out' => $currentTime,
                'pulang_cepat' => $waktuPulangCepat,
                'lembur' => $lembur,
                'image_out' => $fileNamePulang,
                'lat_out' => $lat,
                'lng_out' => $lng,
                'updated_at' => $now
            ];

            $update = DB::table('presensi')
                ->where('id_presensi', $presensiHariIni->id_presensi)
                ->update($updateData);

            if ($update) {
                return response()->json(['status' => true, 'message' => 'Presensi Pulang Berhasil!']);
            } else {
                File::delete($filePathPulang);
                return response()->json(['status' => false, 'message' => 'Gagal menyimpan data presensi pulang ke database.']);
            }
        }

        // --- SUDAH ABSEN MASUK DAN PULANG ---
        else {
            return response()->json(['status' => false, 'message' => 'Anda sudah melakukan presensi masuk dan pulang hari ini.']);
        }
    }

    public function update_profile(Request $request)
    {
        $prefix  = config('session.prefix');
        $peran = session($prefix.'_peran');
        $id_user = session($prefix.'_id_user');
        
        $user = User::where('id_user', $id_user)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'User tidak ditemukan!']
            ]);
        }

        $post = [];
        $arrAccess = [];
        $data = [];

        $arrAccess[] = true;

        if ($request->filled('kata_sandi')) {
            $arrVar['kata_sandi_baru'] = 'Kata sandi baru';
            $arrVar['kata_sandi_konfirm'] = 'Konfirmasi sandi baru';

            foreach ($arrVar as $var => $value) {
                $$var = $request->input($var);
                if (!$$var) {
                    $data['required'][] = ['req_' . $var, "$value tidak boleh kosong!"];
                    $arrAccess[] = false;
                } else {
                    $post[$var] = trim($$var);
                    $arrAccess[] = true;
                }
            }
        }
        

        if (in_array(false, $arrAccess)) {
            return response()->json(['status' => false, 'required' => $data['required']]);
        }

        if ($request->filled('kata_sandi')) {
            if (Hash::check($request->kata_sandi, $user->kata_sandi)) {
                if ($kata_sandi_baru != trim($kata_sandi_konfirm)) {
                    return response()->json([
                        'status' => 700,
                        'alert' => ['message' => 'Konfirmasi kata sandi tidak sama!']
                    ]);
                }else{
                    $post['kata_sandi'] = $kata_sandi_baru;
                }
            }else{
                return response()->json([
                    'status' => 700,
                    'alert' => ['message' => 'Kata sandi tidak valid!']
                ]);
            }
        }
        
        $tujuan = public_path('data/user/');
        $name_image = $request->name_image;
        if (!File::exists($tujuan)) {
            File::makeDirectory($tujuan, 0755, true, true);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move($tujuan, $fileName);

            if ($user->image && file_exists($tujuan . $user->image)) {
                unlink($tujuan . $user->image);
            }

            $post['image'] = $fileName;
        } elseif (!$name_image) {
            if ($user->image && file_exists($tujuan . $user->image)) {
                unlink($tujuan . $user->image);
            }
            $post['image'] = null;
        }

        $update = $user->update($post);

        if ($update) {
            return response()->json([
                'status' => true,
                'alert' => ['message' => 'Data profil berhasil diperbarui!'],
                'reload' => true
            ]);
        } else {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Gagal memperbarui data!']
            ]);
        }

        return response()->json(['status' => false]);
    }

}
