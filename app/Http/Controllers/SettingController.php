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
    public function index(Request $request)
    {
        $page = $request->query('page', '');

        $data = [];

        // GLBL
        $data['icon'] = '<i class="fa-solid fa-gear fs-3x text-white me-4"></i>';
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

    public function updateWebsite(Request $request)
    {
        $setting = Pengaturan::find(1);

        $arrVar['meta_title'] = 'Judul website';

        $post = [];
        $arrAccess = [];
        $data = [];

        foreach ($arrVar as $var => $value) {
            $$var = $request->input($var);
            if (!$$var && $$var !== '0') {
                $data['required'][] = ['req_' . $var, "$value tidak boleh kosong!"];
                $arrAccess[] = false;
            } else {
                $arrAccess[] = true;
            }
        }

        if (in_array(false, $arrAccess)) {
            return response()->json(['status' => false, 'required' => $data['required']]);
        }

        $tujuan = public_path('data/setting');
        if (!file_exists($tujuan)) {
            mkdir($tujuan, 0755, true);
        }

        if ($meta_title != $setting->meta_title) {
            $post['meta_title'] = $meta_title;
        }

        // Validasi minimal salah satu file/logo tersedia
        $name_icon = $request->input('name_icon', '');
        $name_logo = $request->input('name_logo', '');

        $arrAccess[] = $request->hasFile('logo') || $name_logo || $setting->logo;
        $arrAccess[] = $request->hasFile('icon') || $name_icon || $setting->icon;

        if (in_array(true, $arrAccess)) {

            // LOGO
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $nama = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($tujuan, $nama);
                $post['logo'] = $nama;
                if ($name_logo && file_exists($tujuan . '/' . $name_logo)) {
                    unlink($tujuan . '/' . $name_logo);
                }
            } elseif (!$name_logo && $setting->logo && file_exists($tujuan . '/' . $setting->logo)) {
                unlink($tujuan . '/' . $setting->logo);
                $post['logo'] = '';
            }

            // ICON
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $nama = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($tujuan, $nama);
                $post['icon'] = $nama;
                if ($name_icon && file_exists($tujuan . '/' . $name_icon)) {
                    unlink($tujuan . '/' . $name_icon);
                }
            } elseif (!$name_icon && $setting->icon && file_exists($tujuan . '/' . $setting->icon)) {
                unlink($tujuan . '/' . $setting->icon);
                $post['icon'] = '';
            }

            if (count($post) > 0) {
                $updated = $setting->update($post);
                if ($updated) {
                    return response()->json([
                        'status' => true,
                        'alert' => ['message' => 'Pengaturan Website Berhasil Disimpan'],
                        'reload' => true
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'alert' => ['message' => 'Pengaturan Website Gagal Disimpan']
                    ]);
                }
            }

            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Tidak ada data di rubah']
            ]);
        }

        return response()->json([
            'status' => false,
            'alert' => ['message' => 'Tidak ada data di rubah']
        ]);
    }

    public function updateLocation(Request $request)
    {
        $setting = Pengaturan::find(1);
        $arrVar['lat'] = 'Latitude';
        $arrVar['lng'] = 'Longitude';
        $arrVar['radius'] = 'Radius';

        $post = [];
        $arrAccess = [];
        $data = [];

        foreach ($arrVar as $var => $value) {
            $$var = $request->input($var);
            if (!$$var && $$var !== '0') {
                $data['required'][] = ['req_' . $var, "$value tidak boleh kosong!"];
                $arrAccess[] = false;
            } else {
                $post[$var] = trim($$var);
                $arrAccess[] = true;
            }
        }

        if (in_array(false, $arrAccess)) {
            return response()->json(['status' => false, 'required' => $data['required']]);
        }

        // ✅ Validasi tambahan
        if (!is_numeric($post['lat']) || !is_numeric($post['lng'])) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Latitude dan Longitude harus berupa angka!']
            ]);
        }

        if (!is_numeric($post['radius']) || $post['radius'] < 0) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Radius harus berupa angka dan tidak boleh negatif!']
            ]);
        }

        $post['lokasi'] = $request->input('lokasi') ?? '';
        $insert = $setting->update($post);

        if ($insert) {
            return response()->json([
                'status' => true,
                'alert' => ['message' => 'Data Lokasi Berhasil Disimpan!']
            ]);
        } else {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Data Lokasi Gagal Disimpan!']
            ]);
        }
    }

    public function updateShift(Request $request)
    {
        $arrVar = [
            'kode'       => 'Kode Shift',
            'nama'       => 'Nama Shift',
            'jam_masuk'  => 'Jam Masuk',
            'jam_pulang' => 'Jam Pulang',
            'lembur'     => 'Batas Lembur'
        ];

        $data = [];
        $arrAccess = [];

        // ========================
        // VALIDASI ADD SHIFT BARU
        // ========================
        if ($request->has('kode')) {
            $insertData = [];
            foreach ($request->kode as $i => $kode) {
                $rowAccess = true;
                $row = [];
                foreach ($arrVar as $var => $label) {
                    $value = $request->{$var}[$i] ?? null;
                    if (!$value && $var !== 'lembur') { // lembur boleh kosong
                        $data['required'][] = ['req_' . $var . '_' . $i, "$label tidak boleh kosong!"];
                        $rowAccess = false;
                    } else {
                        $row[$var] = $value;
                    }
                }
                if ($rowAccess) {
                    $insertData[] = $row;
                }
                $arrAccess[] = $rowAccess;
            }
            if (!in_array(false, $arrAccess) && count($insertData) > 0) {
                Shift::insert($insertData);
            }
        }

        // ========================
        // VALIDASI EDIT SHIFT
        // ========================
        $editData = [];
        if ($request->has('edit_kode')) {
            foreach ($request->edit_kode as $id_shift => $kode) {
                $rowAccess = true;
                $row = ['id_shift' => $id_shift];
                foreach ($arrVar as $var => $label) {
                    $editName = 'edit_' . $var;
                    $value = $request->{$editName}[$id_shift] ?? null;
                    if (!$value && $var !== 'lembur') {
                        $data['required'][] = ['req_' . $var . '_' . $id_shift, "$label tidak boleh kosong!"];
                        $rowAccess = false;
                    } else {
                        $row[$var] = $value;
                    }
                }
                if ($rowAccess) {
                    $editData[] = $row;
                }
                $arrAccess[] = $rowAccess;
            }

            // Update batch
            if (!in_array(false, $arrAccess) && count($editData) > 0) {
                foreach ($editData as $row) {
                    Shift::where('id_shift', $row['id_shift'])->update([
                        'kode'       => $row['kode'],
                        'nama'       => $row['nama'],
                        'jam_masuk'  => $row['jam_masuk'],
                        'jam_pulang' => $row['jam_pulang'],
                        'lembur'     => $row['lembur'],
                    ]);
                }
            }
        }

        if (in_array(false, $arrAccess)) {
            return response()->json(['status' => false, 'required' => $data['required']]);
        }

        return response()->json([
            'status' => true,
            'alert' => ['message' => 'Data Shift berhasil disimpan!'],
            'reload' => true
        ]);
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
