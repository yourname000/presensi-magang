<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

use App\Models\User;
use App\Models\Departemen;

class MasterController extends Controller
{
    // GET VIEW
    public function karyawan()
    {
         // SET TITLE
        $data['title'] = 'Data Karyawan';
        $data['icon'] = '<i class="fa-solid text-white fa-users fs-3x me-4"></i>';
        $data['subtitle'] = 'Kelola data dan informasi karyawan secara lengkap dan terstruktur';

        // GET DATA
        $departemen = Departemen::get();

        // SET DATA
        $data['departemen'] = $departemen;

        return view('master.karyawan', $data);
    }

    public function departemen()
    {
         // SET TITLE
        $data['title'] = 'Data Departemen';
        $data['icon'] = '<i class="fa-solid text-white fa-layer-group fs-3x me-4"></i>';
        $data['subtitle'] = 'Kelola data dan informasi karyawan secara lengkap dan terstruktur';


        return view('master.departemen', $data);
    }



    // FUNCTION

    // USER
    public function insert_user(Request $request)
    {
        $role = $request->input('role');
        if ($role == 2) {
            $arrVar['id_departemen'] = 'Departemen';
        }
        $arrVar['nama'] = 'Nama lengkap';
        $arrVar['nik'] = 'NIK';
        $arrVar['username'] = 'Nama pengguna';
        $arrVar['kata_sandi'] = 'Kata sandi';

        $post = [];
        $arrAccess = [];
        $data = [];

        foreach ($arrVar as $var => $value) {
            $$var = $request->input($var);
            if (!$$var) {
                $data['required'][] = ['req_' . $var, "$value tidak boleh kosong!"];
                $arrAccess[] = false;
            } else {
                if (!in_array($var, ['kata_sandi'])) {
                    $post[$var] = trim($$var);
                    $arrAccess[] = true;
                }
            }
        }

        if (in_array(false, $arrAccess)) {
            return response()->json(['status' => false, 'required' => $data['required']]);
        }

        // 🔎 Pengecekan tambahan
        // NIK tidak boleh ada spasi dan harus angka
        if (preg_match('/\s/', $request->nik) || !ctype_digit($request->nik)) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'NIK tidak valid! NIK hanya boleh berisi angka dan tanpa spasi!']
            ]);
        }

        // Nama pengguna tidak boleh ada spasi
        if (preg_match('/\s/', $request->username)) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Nama pengguna tidak boleh mengandung spasi!']
            ]);
        }

        // Cek apakah NIK sudah ada
        if (User::where('nik', $request->nik)->exists()) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'NIK sudah terdaftar!']
            ]);
        }

        // Cek apakah Nama pengguna sudah ada
        if (User::where('username', $request->username)->exists()) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Nama pengguna sudah dipakai!']
            ]);
        }

        $tujuan = public_path('data/user/');
        if (!File::exists($tujuan)) {
            File::makeDirectory($tujuan, 0755, true, true);
        }

        $prefix = config('session.prefix');
        $id_user = session($prefix . '_id_user');

        $post['kata_sandi'] = $request->kata_sandi;
        $post['created_by'] = $id_user;

        $insert = User::create($post);

        if ($insert) {
            return response()->json([
                'status' => true,
                'alert' => ['message' => 'Data Karyawan Berhasil Ditambahkan!'],
                'datatable' => 'table_karyawan',
                'modal' => ['id' => '#modalKaryawan', 'action' => 'hide'],
                'input' => ['all' => true]
            ]);
        } else {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Data Karyawan Gagal Ditambahkan!']
            ]);
        }
    }


    public function update_user(Request $request)
    {
        $id = $request->id_user;
        $user = User::where('id_user', $id)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'User tidak ditemukan!']
            ]);
        }

        $role = $request->input('role');
        if ($role == 2) {
            $arrVar['id_departemen'] = 'Departemen';
        }
        $arrVar['nama'] = 'Nama lengkap';
        $arrVar['nik'] = 'NIK';
        $arrVar['username'] = 'Nama pengguna';

        $post = [];
        $arrAccess = [];
        $data = [];

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

        if (in_array(false, $arrAccess)) {
            return response()->json(['status' => false, 'required' => $data['required']]);
        }

        // 🔎 Validasi tambahan
        if (preg_match('/\s/', $request->nik) || !ctype_digit($request->nik)) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'NIK tidak valid! NIK hanya boleh berisi angka dan tanpa spasi!']
            ]);
        }

        if (preg_match('/\s/', $request->username)) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Nama pengguna tidak boleh mengandung spasi!']
            ]);
        }

        // Cek unik NIK
        if (User::where('nik', $request->nik)->where('id_user', '!=', $id)->exists()) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'NIK sudah terdaftar oleh pengguna lain!']
            ]);
        }

        // Cek unik Username
        if (User::where('username', $request->username)->where('id_user', '!=', $id)->exists()) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Nama pengguna sudah dipakai oleh pengguna lain!']
            ]);
        }

        // Password (opsional)
        if ($request->filled('kata_sandi')) {
            $post['kata_sandi'] = $request->kata_sandi;
        }

        $update = $user->update($post);

        if ($update) {
            return response()->json([
                'status' => true,
                'alert' => ['message' => 'Data berhasil diperbarui!'],
                'datatable' => 'table_karyawan',
                'modal' => ['id' => 'modalKaryawan', 'action' => 'hide'],
                'input' => ['all' => true]
            ]);
        } else {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Gagal memperbarui data!']
            ]);
        }
    }



    // DEPARTEMEN
    // INSERT DEPARTEMEN
    public function insert_departemen(Request $request){
        $arrVar = [
            'nama' => 'Nama departemen',
            'kode' => 'Kode departemen',
            'warna' => 'Warna'
        ];

        $post = [];
        $arrAccess = [];
        $data = [];

        foreach ($arrVar as $var => $value) {
            $$var = $request->input($var);
            if (!$$var) {
                $data['required'][] = ['req_' . $var, "$value tidak boleh kosong!"];
                $arrAccess[] = false;
            } else {
                // Pengecekan spasi untuk 'kode'
                if ($var === 'kode' && str_contains($$var, ' ')) {
                    $data['required'][] = ['req_' . $var, "Kode departemen tidak boleh mengandung spasi!"];
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

        $cek_kode = Departemen::where('kode',$kode)->first();
        if ($cek_kode) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Kode sudah terdaftar!']
            ]);
        }

        $insert = Departemen::create($post);

        if ($insert) {
            return response()->json([
                'status' => true,
                'alert' => ['message' => 'Data Departemen Berhasil Ditambahkan!'],
                'datatable' => 'table_departemen',
                'modal' => ['id' => '#modalDepartemen', 'action' => 'hide'],
                'input' => ['all' => true]
            ]);
        } else {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Data Departemen Gagal Ditambahkan!']
            ]);
        }
    }


    // UPDATE DEPARTEMEN
    public function update_departemen(Request $request)
    {
        $id = $request->id_departemen;
        $dbdepartemen = Departemen::where('id_departemen', $id)->first();

        if (!$dbdepartemen) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Data Departemen Tidak Ditemukan!']
            ]);
        }

        $arrVar = [
            'nama' => 'Nama departemen',
            'kode' => 'Kode departemen',
            'warna' => 'Warna'
        ];

        $post = [];
        $arrAccess = [];
        $data = [];

        foreach ($arrVar as $var => $value) {
            $$var = $request->input($var);
            if (!$$var) {
                $data['required'][] = ['req_' . $var, "$value tidak boleh kosong!"];
                $arrAccess[] = false;
            } else {
                // Pengecekan spasi untuk 'kode'
                if ($var === 'kode' && str_contains($$var, ' ')) {
                    $data['required'][] = ['req_' . $var, "Kode departemen tidak boleh mengandung spasi!"];
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

        $cek_kode = Departemen::where('kode',$kode)->where('id_departemen','!=',$id)->first();
        if ($cek_kode) {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Kode sudah terdaftar!']
            ]);
        }

        $update = $dbdepartemen->update($post);

        if ($update) {
            return response()->json([
                'status' => true,
                'alert' => ['message' => 'Data berhasil diperbarui!'],
                'datatable' => 'table_departemen',
                'modal' => ['id' => '#modalDepartemen', 'action' => 'hide'],
                'input' => ['all' => true]
            ]);
        } else {
            return response()->json([
                'status' => false,
                'alert' => ['message' => 'Gagal memperbarui data!']
            ]);
        }
    }

    // GET SINGLE DEPARTEMEN (untuk Edit)
    public function get_departemen(Request $request)
    {
        $departemen = Departemen::where('id_departemen', $request->id)->first();
        if (!$departemen) {
            return response()->json(['status' => false, 'message' => 'Departemen tidak ditemukan!']);
        }
        return response()->json($departemen);
    }

    // DELETE DEPARTEMEN
    public function delete_departemen(Request $request)
    {
        $departemen = Departemen::where('id_departemen', $request->id)->first();
        if (!$departemen) {
            return response()->json(['status' => false, 'message' => 'Departemen tidak ditemukan!']);
        }

        $departemen->delete();
        return response()->json([
            'status' => true,
            'alert' => ['message' => 'Departemen berhasil dihapus!'],
            'datatable' => 'table_departemen'
        ]);
    }

}
