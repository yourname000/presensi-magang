<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\User;
use App\Models\Shift;
use App\Models\Departemen;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // 🚨 Tambahkan ini
use Illuminate\Support\Facades\Session;

class PresensiController extends Controller
{
    public function __construct()
    {
        $prefix = config('session.prefix');
        if (session($prefix . '_peran') != 1) {
            redirect()->route('dashboard')->send();
        }
    }

    public function report(Request $request)
{
    $data['title'] = 'Data Absensi';
    $data['icon'] = '<i class="fa-solid text-white fa-list-check fs-3x me-4"></i>';
    $data['subtitle'] = 'Pantau kehadiran karyawan secara akurat dan efisien';

    $data['departemen'] = Departemen::all();
    $data['shift'] = Shift::all();

    // Ambil filter dari request
    $filterDepartemen = $request->get('departemen');
    $filterBulan = $request->get('bulan') ?: date('m');
    $filterTahun = $request->get('tahun') ?: date('Y');
    $filterStatus = $request->get('status', 'all');

    // --- Default range tanggal: dari awal bulan sampai akhir bulan
    $startDate = Carbon::createFromDate($filterTahun, $filterBulan, 1);
    $endDate = $startDate->copy()->endOfMonth();

    $today = now()->today();
    if ($endDate->gt($today)) {
        $endDate = $today;
    }

    // Ambil semua user karyawan (peran = 2)
    $prefix = config('session.prefix');
    $id_user_login = session($prefix.'_id_user') ?? 0;
    $users = User::where('peran', 2)
        ->where('id_user', '!=', $id_user_login)
        ->when($filterDepartemen && $filterDepartemen != 'all', function($q) use ($filterDepartemen) {
            $q->where('id_departemen', $filterDepartemen);
        })
        ->get();

    // Cari tanggal awal yang sesuai dengan tanggal bergabung karyawan
    $minJoinDate = $users->min(function($u) {
        return $u->created_at;
    });

    if ($minJoinDate) {
        $minJoinDate = Carbon::parse($minJoinDate);
        // Jika tanggal gabung bulan ini, mulai dari awal bulan gabung
        if ($minJoinDate->isSameMonth($startDate)) {
            $startDate = $minJoinDate->copy()->startOfMonth();
        }
        // Jika karyawan gabung di bulan setelah bulan filter, maka mulai dari bulan gabung
        if ($minJoinDate->gt($startDate)) {
            $startDate = $minJoinDate->copy()->startOfMonth();
        }
    }

    $startDateStr = $startDate->toDateString();
    $endDateStr = $endDate->toDateString();

    // ==========================================
    // QUERY CROSS JOIN TANGGAL
    // ==========================================
    $baseQuery = "
        WITH RECURSIVE dates AS (
            SELECT DATE(?) AS tanggal
            UNION ALL
            SELECT DATE_ADD(tanggal, INTERVAL 1 DAY)
            FROM dates
            WHERE tanggal < DATE(?)
        )
        SELECT 
            users.id_user, users.nik, users.nama,
            departemen.nama as departemen_nama,
            dates.tanggal as tanggal_presensi,
            shift.kode as shift_kode,
            shift.nama as shift_nama,
            shift.jam_masuk as shift_jam_masuk,
            shift.jam_pulang as shift_jam_pulang,
            presensi.id_presensi, presensi.id_shift,  -- ✅ tambahkan ini
            presensi.id_presensi, presensi.scan_in, presensi.scan_out, presensi.hadir,
            presensi.keterangan, presensi.terlambat, presensi.lembur, presensi.pulang_cepat,
            presensi.waktu_terlambat, presensi.status_terlambat, presensi.status_pulang_cepat,
            users.created_at as tanggal_gabung
        FROM users
        CROSS JOIN dates
        LEFT JOIN presensi 
            ON presensi.id_user = users.id_user 
            AND presensi.tanggal_presensi = dates.tanggal
        LEFT JOIN shift 
            ON shift.id_shift = presensi.id_shift
        LEFT JOIN departemen 
            ON departemen.id_departemen = users.id_departemen
        WHERE users.peran = 2
        AND users.id_user != ?
        AND dates.tanggal >= DATE_FORMAT(users.created_at, '%Y-%m-01') -- mulai dari awal bulan bergabung
        AND dates.tanggal <= ?
    ";

    $bindings = [$startDateStr, $endDateStr, $id_user_login, $endDateStr];
        
    // ✅ Tambahkan filter departemen
    if ($filterDepartemen && $filterDepartemen != 'all') {
        $baseQuery .= " AND users.id_departemen = ?";
        $bindings[] = $filterDepartemen;
    }

    // Filter status tambahan
    if ($filterStatus != 'all') {
        if ($filterStatus == 'L') {
            $baseQuery .= " AND presensi.lembur > 0";
        } elseif ($filterStatus == 'T') {
            $baseQuery .= " AND presensi.terlambat = 'Y'";
        } elseif ($filterStatus == 'P') {
            $baseQuery .= " AND presensi.pulang_cepat > 0";
        }
    }

    $baseQuery .= " ORDER BY dates.tanggal DESC, users.nama ASC";

    $rows = DB::select($baseQuery, $bindings);

    // Format hasil
    $combinedPresensi = [];
    foreach ($rows as $row) {
        $combinedPresensi[] = (object) [
            'tanggal_presensi' => $row->tanggal_presensi,
            'user' => (object)[
                'nik' => $row->nik,
                'nama' => $row->nama,
                'id_user' => $row->id_user,
                'departemen' => (object)['nama' => $row->departemen_nama]
            ],
                'shift' => (object)[
                'id_shift' => $row->id_shift ?? null,
                'nama' => $row->shift_nama ?? null,
                'kode' => $row->shift_kode ?? null,
                'jam_masuk' => $row->shift_jam_masuk ?? null,
                'jam_pulang' => $row->shift_jam_pulang ?? null,
            ],
            'scan_in' => $row->scan_in,
            'scan_out' => $row->scan_out,
            'hadir' => $row->hadir,
            'keterangan' => $row->keterangan,
            'terlambat' => $row->terlambat,
            'waktu_terlambat' => $row->waktu_terlambat,
            'lembur' => $row->lembur,
            'pulang_cepat' => $row->pulang_cepat,
            'id_presensi' => $row->id_presensi,
        ];
    }

    $data['presensi'] = collect($combinedPresensi);

    $data['filter'] = [
        'departemen' => $filterDepartemen,
        'bulan' => $filterBulan,
        'tahun' => $filterTahun,
        'status' => $filterStatus,
    ];

    return view('presensi.report', $data);
}

    public function search_employee(Request $request)
    {
        $id_departemen = $request->input('id_departemen');
        $keyword = $request->input('keyword');

        $result = User::with(['departemen'])
            ->where('id_departemen', $id_departemen)
            ->where('peran', 2)
            ->where(function ($q) use ($keyword) {
                $q->where('users.nama', 'like', "%{$keyword}%")
                  ->orWhere('users.nik', 'like', "%{$keyword}%");
            })
            ->get();

        return view('presensi.search', compact('result'));
    }

public function update_presensi(Request $request)
{
    // Ambil data dari form
    $id_presensi        = $request->input('id_presensi');
    $id_user            = $request->input('id_user') ?? session(config('session.prefix') . '_id_user');
    $id_shift           = $request->input('id_shift');
    $tanggal_presensi   = $request->input('tanggal_presensi', now()->toDateString());
    $scan_in            = $request->input('scan_in');
    $scan_out           = $request->input('scan_out');
    $waktu_terlambat    = $request->input('waktu_terlambat', 0);
    $pulang_cepat       = $request->input('pulang_cepat', 0);
    $lembur             = $request->input('lembur', 0);
    $keterangan         = $request->input('keterangan');
    $lat_in             = $request->input('lat_in');
    $lng_in             = $request->input('lng_in');
    $lat_out            = $request->input('lat_out');
    $lng_out            = $request->input('lng_out');

    // 🔹 Tentukan status terlambat otomatis
    $status_terlambat   = ($waktu_terlambat > 0) ? 'Y' : 'N';

    // 🟡 Validasi minimal id_user agar tidak error
    if (!$id_user) {
        return redirect()->back()->with('error', 'ID User tidak ditemukan.');
    }

    // 🔹 Coba cari data presensi berdasarkan id_presensi
    $presensi = Presensi::find($id_presensi);

    // 🔹 Kalau belum ada, cari berdasarkan id_user + tanggal
    if (!$presensi) {
        $presensi = Presensi::where('id_user', $id_user)
            ->whereDate('tanggal_presensi', $tanggal_presensi)
            ->first();
    }

    // 🔹 Kalau tetap belum ada → buat baru otomatis
    if (!$presensi) {
        $presensi = Presensi::create([
            'id_user'          => $id_user,
            'id_shift'         => $id_shift,
            'tanggal_presensi' => $tanggal_presensi,
            'hadir'            => 'Y',
            'scan_in'          => $scan_in,
            'scan_out'         => $scan_out,
            'lat_in'           => $lat_in,
            'lng_in'           => $lng_in,
            'lat_out'          => $lat_out,
            'lng_out'          => $lng_out,
            'terlambat'        => $status_terlambat,
            'status_terlambat' => $status_terlambat,
            'waktu_terlambat'  => $waktu_terlambat,
            'pulang_cepat'     => $pulang_cepat,
            'lembur'           => $lembur,
            'keterangan'       => $keterangan,
        ]);
    } else {
        // 🔹 Kalau sudah ada → update data
        $presensi->update([
            'id_shift'         => $id_shift,
            'scan_in'          => $scan_in,
            'scan_out'         => $scan_out,
            'lat_in'           => $lat_in,
            'lng_in'           => $lng_in,
            'lat_out'          => $lat_out,
            'lng_out'          => $lng_out,
            'waktu_terlambat'  => $waktu_terlambat,
            'terlambat'        => $status_terlambat, 
            'status_terlambat' => $status_terlambat, 
            'pulang_cepat'     => $pulang_cepat,
            'lembur'           => $lembur,
            'keterangan'       => $keterangan,
        ]);
    }

    return redirect()->back()->with('success', 'Data presensi berhasil diperbarui.');
}



public function insert_presensi(Request $request)
{
    $id_user          = $request->input('id_user') ?? session(config('session.prefix').'_id_user');
    $id_departemen    = $request->input('id_departemen');
    $id_shift         = $request->input('id_shift');
    $tanggal_presensi = now()->toDateString();
    $status           = $request->input('status'); // Masuk atau Pulang
    $id_presensi      = $request->input('id_presensi');
    $waktu_terlambat  = $request->input('waktu_terlambat', 0);
   $status = $request->input('status');
    if ($status === 'Masuk') {
        $lat_user = $request->input('lat_in');
        $lng_user = $request->input('lng_in');
    } else {
        $lat_user = $request->input('lat_out');
        $lng_user = $request->input('lng_out');
    }


    if (empty($id_user) || empty($id_departemen)) {
        return redirect()->back()->with('error', 'Data karyawan dan departemen wajib diisi!');
    }

    // === Ambil lokasi kantor dari tabel Pengaturan ===
    $setting = Pengaturan::find(1);
    if (!$setting) {
        return redirect()->back()->with('error', 'Lokasi kantor belum diset!');
    }

    $lat_kantor = $setting->lat;
    $lng_kantor = $setting->lng;
    $radius_kantor = $setting->radius; // dalam meter

    // === Hitung jarak dengan Haversine ===
    $jarak = $this->hitungJarak($lat_kantor, $lng_kantor, $lat_user, $lng_user);

    // === Validasi apakah user berada dalam radius kantor ===
    if ($jarak > $radius_kantor) {
        return redirect()->back()->with('error', 'Kamu berada di luar area kantor! (Jarak: ' . round($jarak, 2) . ' meter)');
    }

    // === Jika dalam area, lanjut proses presensi ===
    $shift = Shift::find($id_shift);
    if (!$shift) {
        return redirect()->back()->with('error', 'Shift tidak ditemukan!');
    }

    $jam_masuk_shift  = Carbon::parse($shift->jam_masuk);
    $jam_pulang_shift = Carbon::parse($shift->jam_pulang);
    $sekarang         = Carbon::now();

    $presensi = Presensi::where('id_user', $id_user)
        ->whereDate('tanggal_presensi', $tanggal_presensi)
        ->first();

    // === PRESENSI MASUK ===
    if (!$presensi) {
        $status_terlambat = 'N';
        $menit_terlambat  = 0;
        $status_karyawan  = 'Masuk Tepat Waktu';

        if ($sekarang->gt($jam_masuk_shift)) {
            $status_terlambat = 'Y';
            $menit_terlambat  = abs($sekarang->diffInMinutes($jam_masuk_shift));
            $status_karyawan  = 'Terlambat';
        }

        Presensi::create([
            'id_user'          => $id_user,
            'id_shift'         => $id_shift,
            'tanggal_presensi' => $tanggal_presensi,
            'scan_in'          => $sekarang->toTimeString(),
            'hadir'            => 'Y',
            'terlambat'        => $status_terlambat,
            'waktu_terlambat'  => $menit_terlambat,
            'status'           => $status_karyawan,
            'lat_in'           => $lat_user,
            'lng_in'           => $lng_user
        ]);

        return redirect()->back()->with('success', 'Presensi masuk berhasil! (Jarak: ' . round($jarak, 2) . ' meter)');
    }

    // === PRESENSI PULANG ===
    if ($presensi->scan_out == null) {
        $status_pulang_cepat = 'N';
        $menit_pulang_cepat  = 0;
        $lembur_menit        = 0;
        $status_karyawan     = 'Pulang Normal';

        if ($sekarang->lt($jam_pulang_shift)) {
            $status_pulang_cepat = 'Y';
            $menit_pulang_cepat  = abs($jam_pulang_shift->diffInMinutes($sekarang));
            $status_karyawan     = 'Pulang Cepat';
        } elseif ($sekarang->gt($jam_pulang_shift)) {
            $lembur_menit    = abs($sekarang->diffInMinutes($jam_pulang_shift));
            $status_karyawan = 'Lembur';
        }

        $presensi->update([
            'scan_out'            => $sekarang->toTimeString(),
            'status_pulang_cepat' => $status_pulang_cepat,
            'pulang_cepat'        => $menit_pulang_cepat,
            'lembur'              => $lembur_menit,
            'lat_out'             => $request->lat_out,
            'lng_out'             => $request->lng_out,
            'status'              => $status_karyawan,
        ]);

        return redirect()->back()->with('success', 'Presensi pulang berhasil! (Jarak: ' . round($jarak, 2) . ' meter)');
    }

    return redirect()->back()->with('info', 'Presensi hari ini sudah lengkap (masuk & pulang).');
}

// === Fungsi Haversine ===
private function hitungJarak($lat1, $lon1, $lat2, $lon2)
{
    $R = 6371000; // radius bumi (meter)
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $R * $c; // hasil meter
}


public function delete_multiple_presensi(Request $request)
{
    // Ambil ID dari data yang dipilih.
    // Pastikan nama input di form adalah 'id_presensi[]'.
    $selectedIds = $request->input('id_presensi');

    // Jika tidak ada ID yang dipilih, kembalikan ke halaman sebelumnya.
    if (empty($selectedIds)) {
        Session::flash('error', 'Tidak ada data presensi yang dipilih!');
        return redirect()->route('presensi.report');
    }

    try {
        // Hapus data dari tabel 'presensi' berdasarkan id_presensi.
        \App\Models\Presensi::whereIn('id_presensi', $selectedIds)->delete();
        Session::flash('success', 'Data presensi yang dipilih berhasil dihapus!');
    } catch (\Exception $e) {
        // Tangkap error jika proses penghapusan gagal.
        Session::flash('error', 'Gagal menghapus data presensi yang dipilih!');
    }

    // Kembali ke halaman daftar presensi.
    return redirect()->route('presensi.report');
}


    public function export_presensi(Request $request)
    {
        $prefix = config('session.prefix');
        $id_user = session($prefix.'_id_user');

        // Gunakan today() untuk memastikan hanya tanggal tanpa jam
        $today = now()->today();

        // Default: bulan ini
        $startDate = $today->copy()->startOfMonth();
        $endDate   = $today;

        // Filter bulan/tahun
        if ($request->filter_bulan && $request->filter_tahun && $request->filter_bulan != 'all' && $request->filter_tahun != 'all') {
            $filterStart = \Carbon\Carbon::createFromDate($request->filter_tahun, $request->filter_bulan, 1);
            $filterEnd   = $filterStart->copy()->endOfMonth();

            if ($filterStart->gt($today->copy()->endOfMonth())) {
                return back()->with('error', 'Data untuk bulan yang dipilih belum tersedia.');
            } elseif ($filterStart->isSameMonth($today)) {
                $startDate = $filterStart;
                $endDate   = $today; // maksimal hari ini
            } else {
                $startDate = $filterStart;
                $endDate   = $filterEnd;
            }
        }

        $startDateStr = $startDate->toDateString();
        $endDateStr   = $endDate->toDateString();

        // =============================
        // Query sama kayak table_presensi (tanpa LIMIT)
        // =============================
        $query = "
            WITH RECURSIVE dates AS (
                SELECT DATE(?) AS tanggal
                UNION ALL
                SELECT DATE_ADD(tanggal, INTERVAL 1 DAY)
                FROM dates
                WHERE tanggal <= DATE(?)
            )
            SELECT 
                users.id_user, users.nik, users.nama,
                departemen.nama as departemen_nama,
                dates.tanggal as tanggal_presensi,
                shift.kode as shift_kode,
                shift.nama as shift_nama,
                presensi.id_presensi, presensi.scan_in, presensi.scan_out, presensi.hadir,
                presensi.keterangan, presensi.terlambat, presensi.lembur, presensi.pulang_cepat,
                presensi.waktu_terlambat, presensi.status_terlambat, presensi.status_pulang_cepat
            FROM users
            CROSS JOIN dates
            LEFT JOIN presensi 
                ON presensi.id_user = users.id_user 
                AND presensi.tanggal_presensi = dates.tanggal
            LEFT JOIN shift 
                ON shift.id_shift = presensi.id_shift
            LEFT JOIN departemen 
                ON departemen.id_departemen = users.id_departemen
            WHERE users.peran = 2
            AND users.id_user != ?
        ";

        $bindings = [$startDateStr, $endDateStr, $id_user];

        // Filter search
        if (!empty($request->filter_search)) {
            $query .= " AND (users.nama LIKE ? OR users.nik LIKE ? OR departemen.nama LIKE ?)";
            $bindings[] = "%{$request->filter_search}%";
            $bindings[] = "%{$request->filter_search}%";
            $bindings[] = "%{$request->filter_search}%";
        }

        // Filter departemen
        if ($request->filter_id_departemen !== null && $request->filter_id_departemen !== '' && $request->filter_id_departemen != 'all') {
            $query .= " AND departemen.id_departemen = ?";
            $bindings[] = $request->filter_id_departemen;
        }

        // Filter status
        if ($request->filter_status !== null && $request->filter_status != 'all') {
            if ($request->filter_status == 'L') {
                $query .= " AND presensi.lembur > 0";
            } elseif ($request->filter_status == 'T') {
                $query .= " AND presensi.terlambat = 'Y'";
            } elseif ($request->filter_status == 'P') {
                $query .= " AND presensi.pulang_cepat > 0";
            }
        }

        $query .= " ORDER BY users.nama ASC, dates.tanggal ASC";

        $rows = DB::select($query, $bindings);

        // =============================
        // Bentuk data untuk Excel
        // =============================
        $heading = [];
        $data    = [];
        $no      = 1;

        if ($request->filter_status == 'all') {
            // Bentuk associative untuk laporan detail (pakai helper cetak_laporan_presensi)
            foreach ($rows as $row) {
                $tgl = date('d/m/Y', strtotime($row->tanggal_presensi));

                if (!isset($data[$row->id_user])) {
                    $data[$row->id_user] = [
                        'nik' => $row->nik,
                        'nama' => $row->nama,
                        'presensi' => []
                    ];
                }

                if ($row->hadir === 'Y') {
                    $top = '1';
                } else {
                    $top = '0';
                }

                $shift = $row->shift_kode ?? '';

                $data[$row->id_user]['presensi'][$tgl] = [
                    'top' => $top,
                    'shift' => $shift,
                    'keterangan' => $row->keterangan ?? '',
                    'scan_in' => $row->scan_in ?? '',
                    'scan_out' => $row->scan_out ?? '',
                    'lembur' => $row->lembur ?? 0,
                    'pulang_cepat' => $row->pulang_cepat ?? 0,
                    'terlambat' => ($row->terlambat == 'Y') ? $row->waktu_terlambat : 0,
                    'status_terlambat' => $row->status_terlambat ?? null,
                    'status_pulang_cepat' => $row->status_pulang_cepat ?? null,
                ];
            }
        } else {
            // Heading & data untuk Excel tabular
            if ($request->filter_status == 'L') {
                $heading = ['NO', 'NAMA', 'DEPARTEMEN', 'TANGGAL', 'LEMBUR (M)','KETERANGAN'];
                foreach ($rows as $row) {
                    $data[] = [
                        $no++,
                        $row->nama,
                        $row->departemen_nama,
                        date('d/m/Y', strtotime($row->tanggal_presensi)),
                        $row->lembur ?? 0,
                        $row->keterangan
                    ];
                }
            } elseif ($request->filter_status == 'P') {
                $heading = ['NO', 'NAMA', 'DEPARTEMEN', 'TANGGAL','WAKTU PULANG CEPAT (M)','KETERANGAN'];
                foreach ($rows as $row) {
                    $data[] = [
                        $no++,
                        $row->nama,
                        $row->departemen_nama,
                        date('d/m/Y', strtotime($row->tanggal_presensi)),
                        $row->pulang_cepat ?? 0,
                        $row->keterangan
                    ];
                }
            } elseif ($request->filter_status == 'T') {
                $heading = ['NO', 'NAMA', 'DEPARTEMEN', 'TANGGAL', 'WAKTU TERLAMBAT (M)','KETERANGAN'];
                foreach ($rows as $row) {
                    $data[] = [
                        $no++,
                        $row->nama,
                        $row->departemen_nama,
                        date('d/m/Y', strtotime($row->tanggal_presensi)),
                        $row->waktu_terlambat ?? 0,
                        $row->keterangan
                    ];
                }
            }
        }

        // Ambil keterangan dinamis dari DB
        $shiftList = Shift::orderBy('kode')->get(['kode', 'nama']);
    

        $keterangan = [];
        foreach ($shiftList as $s) {
            $keterangan[] = "{$s->kode} : {$s->nama}";
        }

        $keterangan[] = '';
        $keterangan[] = 'Kode absensi:';
        $keterangan[] = "1 : Hadir";
        $keterangan[] = "0 : Alpha / Tidak Hadir";

    

        $arrSts = [
    'L' => 'Lembur',
    'T' => 'Terlambat',
    'P'=>'Pulang Cepat',
    ];

    // Tentukan judul default jika filter kosong
    $status = $request->filter_status ?? 'all';
    $judul = 'Laporan Presensi';

    if (isset($arrSts[$status])) {
        $judul = 'Laporan ' . $arrSts[$status];
    }

    if ($status == 'all') {
        return cetak_laporan_presensi('Laporan Presensi', $data, $startDate, $endDate, $keterangan);
    } else {
        return cetak_excel($judul, $heading, $data);
    }



}}