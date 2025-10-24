<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use App\Exports\DynamicExport;
// use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;



use App\Models\Pengaturan;
use App\Models\Shift;

class SettingController extends Controller
{
    public function __construct()
    {
        // cek peran user
        $prefix = config('session.prefix');
        if (session($prefix . '_peran') != 1) {
            redirect()->route('dashboard')->send(); 
            // pakai send() supaya langsung berhenti eksekusi constructor
        }
    }

    public function index(Request $request)
    {
        $page = $request->query('page', '');

        $data = [];

        // GLBL
        $data['icon'] = '<i class="fa-solid text-white fa-users fs-3x me-4"></i>';
        $data['title'] = 'Pengaturan';
        $data['subtitle'] = 'Atur lokasi dan waktu kerja karyawan dengan mudah';

        // GET DATA
        $setting = Pengaturan::find(1); // get_single
        $shift = Shift::get();
        
        // SET DATA
        $data['result'] = $setting;
        $data['page'] = $page;
        $data['shift'] = $shift;

        // DISPLAY
        return view('pengaturan.index', $data);
    }


    // FUNCTION

    public function update_website(Request $request)
    {
        $setting = Pengaturan::find(1);

        // Validasi input
        $request->validate([
            'meta_title' => 'required|string|max:255',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
            'icon' => 'nullable|file|mimes:png,jpg,jpeg,ico|max:2048',
        ], [
            'meta_title.required' => 'Judul website tidak boleh kosong.',
            'logo.mimes' => 'Logo harus berformat png/jpg/jpeg.',
            'icon.mimes' => 'Icon harus berformat png/jpg/jpeg/ico.',
        ]);

        $post = [];

        // Update judul website bila berubah
        if ($request->meta_title !== $setting->meta_title) {
            $post['meta_title'] = $request->meta_title;
        }

        // Pastikan folder tujuan ada
        $tujuan = public_path('data/setting');
        if (!file_exists($tujuan)) {
            mkdir($tujuan, 0755, true);
        }

        // === Upload Logo ===
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            if ($file->isValid()) {
                $namaLogo = uniqid('logo_') . '.' . $file->getClientOriginalExtension();
                $file->move($tujuan, $namaLogo);

                // Simpan nama lama sebagai riwayat
                if ($setting->logo && file_exists($tujuan . '/' . $setting->logo)) {
                    rename($tujuan . '/' . $setting->logo, $tujuan . '/old_' . $setting->logo);
                }

                $post['logo'] = $namaLogo;
            }
        }

        // === Upload Icon ===
        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            if ($file->isValid()) {
                $namaIcon = uniqid('icon_') . '.' . $file->getClientOriginalExtension();
                $file->move($tujuan, $namaIcon);

                // Simpan nama lama sebagai riwayat
                if ($setting->icon && file_exists($tujuan . '/' . $setting->icon)) {
                    rename($tujuan . '/' . $setting->icon, $tujuan . '/old_' . $setting->icon);
                }

                $post['icon'] = $namaIcon;
            }
        }

        // Simpan perubahan
        if (count($post) > 0) {
            $setting->update($post);
            return redirect()->route('pengaturan', ['page' => 'website'])->with('success_web', 'Pengaturan Website berhasil disimpan!');
        }

        return redirect()->back()->with('info', 'Tidak ada perubahan yang disimpan.');
    }


    // ---PENGATURAN LOKASI DAN RADIUS---
    public function update_location(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'required|numeric|min:0',
            'lokasi' => 'nullable|string',
        ], [
            'lat.required' => 'Latitude tidak boleh kosong!',
            'lng.required' => 'Longitude tidak boleh kosong!',
            'radius.required' => 'Radius tidak boleh kosong!',
            'radius.numeric' => 'Radius harus berupa angka!',
            'radius.min' => 'Radius tidak boleh negatif!',
        ]);

        $setting = Pengaturan::find(1);

        if (!$setting) {
            return redirect()->back()->with('error', 'Data pengaturan tidak ditemukan!');
        }

        $update = $setting->update([
            'lat' => $request->lat,
            'lng' => $request->lng,
            'radius' => $request->radius,
            'lokasi' => $request->lokasi,
        ]);

        if ($update) {
            return redirect()->route('pengaturan', ['page' => 'lokasi'])->with('success', 'Data lokasi berhasil disimpan!');
        }

        return redirect()->route('pengaturan', ['page' => 'lokasi'])->with('error', 'Data lokasi gagal disimpan!');
    }


    // ---PENGATURAN SHIFT---
    public function update_shift(Request $request)
    {
        foreach ($request->kode as $i => $kode) {
            $id = $request->id[$i] ?? null;

            if ($id) {
                // Update shift lama
                $shift = Shift::find($id);
                if ($shift) {
                    $shift->update([
                        'kode'       => $kode,
                        'nama'       => $request->nama[$i],
                        'jam_masuk'  => $request->jam_masuk[$i],
                        'jam_pulang' => $request->jam_pulang[$i],
                        'lembur'     => $request->lembur[$i],
                    ]);
                }
            } else {
                // Insert shift baru
                Shift::create([
                    'kode'       => $kode,
                    'nama'       => $request->nama[$i],
                    'jam_masuk'  => $request->jam_masuk[$i],
                    'jam_pulang' => $request->jam_pulang[$i],
                    'lembur'     => $request->lembur[$i],
                ]);
            }
        }

        return redirect()->route('pengaturan', ['page' => 'shift'])->with('success_shift', 'Shift berhasil disimpan!');

    }

    // Hapus Shift
    public function delete_shift($id)
    {
        \DB::table('shift')->where('id_shift', $id)->delete();
        return redirect()->back()->with('success_shift', 'Shift berhasil dihapus.');
    }


    // GLOBAL

    public function switch(Request $request, $db = 'user')
    {
        $id = $request->input('id');
        $action = $request->input('action');
        $primary = $request->input('primary') ?? "id_{$db}";
        $reason = $request->input('reason', '');

        // Check if the table exists in the database
        if (!DB::getSchemaBuilder()->hasTable($db)) {
            return response()->json([
                'status' => 500,
                'alert' => [
                    'icon' => 'warning',
                    'message' => 'Table not found!'
                ]
            ]);
        }

        // Check if the data exists in the table
        $res = DB::table($db)->where($primary, $id)->first();

        if (!$res) {
            return response()->json([
                'status' => 500,
                'alert' => [
                    'icon' => 'warning',
                    'message' => 'Data not found!'
                ]
            ]);
        }

        $prefix = config('session.prefix');
        $idhps = session($prefix.'_id_user');

        // Update status and reason
        $update = DB::table($db)->where($primary, $id)->update([
            'status' => $action,
            'reason' => $action == 'N' ? $reason : '',
            'blocked_date' => now(),
            'blocked_by' => $idhps
        ]);

        if ($update) {
            $message = $action == 'Y' ? 'Access successfully unlocked!' : 'Access successfully blocked!';
            if ($action == 'N' && $reason != '') {
                $message .= '</br><b>Reason: </b>"' . $reason . '"';
            }

            return response()->json([
                'status' => 200,
                'alert' => [
                    'icon' => 'success',
                    'message' => $message
                ]
            ]);
        } else {
            return response()->json([
                'status' => 500,
                'alert' => [
                    'icon' => 'warning',
                    'message' => $action == 'Y' ? 'Failed to unlock access!' : 'Failed to block access!'
                ]
            ]);
        }
    }


    public function hapusdata(Request $request)
    {
        $id = $request->input('id');
        $db = $request->input('db');
        $primary = $request->input('primary') ?? "id_{$db}";
        $reload = $request->input('reload', '');
        $permanent = $request->input('permanent', 0);

        // Check if the table exists
        if (!DB::getSchemaBuilder()->hasTable($db)) {
            return response()->json([
                'status' => 500,
                'alert' => [
                    'message' => 'Table not found!'
                ]
            ]);
        }

        if (!$id || !$db) {
            return response()->json([
                'status' => 500,
                'alert' => [
                    'message' => 'Invalid request data!'
                ]
            ]);
        }

        // Check if the data exists
        $res = DB::table($db)->where($primary, $id)->first();

        if (!$res) {
            return response()->json([
                'status' => 500,
                'alert' => [
                    'message' => 'Data not found!'
                ]
            ]);
        }

        if ($permanent != 'none') {
            $aksi = DB::table($db)->where($primary, $id)->delete();
        } else {
            $prefix = config('session.prefix');
            $idhps = session($prefix.'_id_user');
            // Soft delete
            $aksi = DB::table($db)->where($primary, $id)->update([
                'deleted' => 'Y',
                'deleted_at' => now(),
                'deleted_by' => $idhps
            ]);
        }

        if ($aksi) {
            return response()->json([
                'status' => 200,
                'alert' => [
                    'message' => 'Data successfully deleted!'
                ]
            ]);
        } else {
            return response()->json([
                'status' => 500,
                'alert' => [
                    'message' => 'Failed to delete data!'
                ]
            ]);
        }
    }


    public function single(Request $request, $db = 'user',$primary = '')
    {
        $id = $request->input('id');
        $primary = $primary ?? "id_{$db}";

        // Cek apakah tabel yang dimaksud ada di database
        if (!DB::getSchemaBuilder()->hasTable($db)) {
            return response()->json([
                'status' => 500,
                'alert' => [
                    'message' => 'Table not found!'
                ]
            ]);
        }

        // Cek apakah data ada di tabel
        $res = DB::table($db)->where($primary, $id)->first();
        if ($res) {
            return response()->json($res);
        } else {
            return response()->json(['message' => 'Data not found'], 404);
        }
    }

    public function allDelete(Request $request, $db = 'users', $primary = '')
    {
        $primary = $primary ?: "id_{$db}";
        $arr_data = $request->input('id') ?? [];

        // Cek array id kosong
        if (empty($arr_data)) {
            return response()->json([
                'status' => false,
                'alert' => [
                    'message' => 'Pilih data yang mau dihapus terlebih dahulu!'
                ]
            ]);
        }

        // Cek apakah tabel ada
        if (!DB::getSchemaBuilder()->hasTable($db)) {
            return response()->json([
                'status' => false,
                'alert' => [
                    'message' => "Table {$db} tidak ditemukan!"
                ]
            ]);
        }

        // Ambil data sesuai ID
        $exists = DB::table($db)->whereIn($primary, $arr_data)->get();

        if ($exists->isEmpty()) {
            return response()->json([
                'status' => false,
                'alert' => [
                    'message' => 'Data tidak ditemukan!'
                ]
            ]);
        }

        // Hapus data
        $deleted = DB::table($db)->whereIn($primary, $arr_data)->delete();

        return response()->json([
            'status' => true,
            'alert' => [
                'message' => "Data Berhasil Dihapus!"
            ]
        ]);
    }


    public function export(Request $request)
    {
        $db = $request->input('db');
        $primary = $request->input('primary') ?? "id_{$db}";
        $type = $request->input('type') ?? 'excel';

        // Check if the table exists
        if (!DB::getSchemaBuilder()->hasTable($db)) {
            return response()->json([
                'status' => 500,
                'alert' => [
                    'message' => 'Table not found!'
                ]
            ]);
        }

        // Check if the table has data
        $data = DB::table($db)->get();

        if ($data->isEmpty()) {
            return response()->json([
                'status' => 500,
                'alert' => [
                    'message' => 'No data available to export!'
                ]
            ]);
        }

        try {
            // You can customize the export logic here
            $filename = $db . '_export_' . now()->format('Ymd_His') . '.' . ($type === 'pdf' ? 'pdf' : 'xlsx');

            if ($type == 'pdf') {
                // Example PDF export logic (using dompdf/snappy/etc)
                $pdf = PDF::loadView("exports.{$db}", compact('data'));
                return $pdf->download($filename);
            } else {
                // Example Excel export (using maatwebsite/excel)
                return Excel::download(new GenericExport($db, $data), $filename);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'alert' => [
                    'message' => 'Export failed! ' . $e->getMessage()
                ]
            ]);
        }
    }
}
