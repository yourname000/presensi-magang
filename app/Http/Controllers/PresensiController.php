<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\User;
use App\Models\Shift;
use App\Models\Departemen;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // 🚨 Tambahkan ini

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

    // Filter status tambahan
    if ($filterStatus != 'all') {
        if ($filterStatus == 'L') {
            $baseQuery .= " AND presensi.lembur > 0";
        } elseif ($filterStatus == 'T') {
            $baseQuery .= " AND presensi.terlambat = 'Y'";
        } elseif ($filterStatus == 'I') {
            $baseQuery .= " AND presensi.hadir = 'N' AND presensi.keterangan IS NOT NULL AND presensi.keterangan != ''";
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
            'shift' => $row->shift_nama,
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

    public function single_presensi($id_presensi = null, $id_user = null)
    {
        $presensi = null;
        $user = null;

        if ($id_presensi) {
            $presensi = Presensi::with('shift')->find($id_presensi);
        }

        if ($id_user) {
            $user = User::find($id_user);
        }

        return view('presensi.single', compact('presensi', 'user'));
    }

  public function update_presensi(Request $request)
{
    $id_presensi = $request->input('id_presensi');
    $id_user = $request->input('id_user');
    $tanggal_presensi = $request->input('tanggal_presensi') ?? now()->toDateString();
    $status = $request->input('status') ?? 'H'; // default H biar gak kosong
    $keterangan = $request->input('keterangan') ?? '';

    try {
        // Cari data presensi berdasarkan ID atau user + tanggal
        $presensi = null;

        if ($id_presensi) {
            $presensi = Presensi::find($id_presensi);
        }

        if (!$presensi) {
            $presensi = Presensi::where('id_user', $id_user)
                ->whereDate('tanggal_presensi', $tanggal_presensi)
                ->first();
        }

        // Jika belum ada → buat baru
        if (!$presensi) {
            $presensi = new Presensi();
            $presensi->id_user = $id_user;
            $presensi->tanggal_presensi = $tanggal_presensi;
            $presensi->hadir = $status == 'H' ? 'Y' : 'N';
        }

        // Update data (baik baru maupun lama)
        $presensi->id_departemen = $request->input('id_departemen');
        $presensi->id_shift = $request->input('id_shift');
        $presensi->scan_in = $request->input('scan_in');
        $presensi->scan_out = $request->input('scan_out');
        $presensi->terlambat = $request->input('waktu_terlambat') > 0 ? 'Y' : 'N';
        $presensi->waktu_terlambat = $request->input('waktu_terlambat') ?? 0;
        $presensi->status_terlambat = $request->input('status_terlambat') ?? 'N';
        $presensi->status_pulang_cepat = $request->input('status_pulang_cepat') ?? 'N';
        $presensi->pulang_cepat = $request->input('pulang_cepat') ?? 0;
        $presensi->lembur = $request->input('lembur') ?? 0;
        $presensi->lat_in = $request->input('latitude');
        $presensi->lng_in = $request->input('longitude');
        $presensi->keterangan = $keterangan;

        $presensi->save();

        return redirect()
            ->route('presensi.report')
            ->with('success', 'Presensi berhasil diperbarui!');
    } catch (\Exception $e) {
        // kalau gagal
        return redirect()
            ->back()
            ->with('error', 'Presensi gagal diperbarui! Error: ' . $e->getMessage());
    }
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
            } elseif ($request->filter_status == 'I') {
                $heading = ['NO', 'NAMA', 'DEPARTEMEN', 'TANGGAL','KETERANGAN'];
                foreach ($rows as $row) {
                    $data[] = [
                        $no++,
                        $row->nama,
                        $row->departemen_nama,
                        date('d/m/Y', strtotime($row->tanggal_presensi)),
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