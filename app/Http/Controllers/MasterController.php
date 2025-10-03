<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Departemen;

class MasterController extends Controller
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
    
    // GET VIEW
    public function karyawan(Request $request)
    {
        // SET TITLE
        $data['title'] = 'Data Karyawan';
        $data['icon'] = '<i class="fa-solid text-white fa-users fs-3x me-4"></i>';
        $data['subtitle'] = 'Kelola data dan informasi karyawan secara lengkap dan terstruktur';

        // GET DATA
        // Mulai query dengan filter peran terlebih dahulu
        $query = User::where('peran', 2);

        // Tambahkan logika pencarian jika ada input dari user
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nik', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%");
            });
        }
        
        // Ambil data setelah filter diterapkan
        $karyawan = $query->get();
        $departemen = Departemen::get();

        // SET DATA
        $data['departemen'] = $departemen;
        $data['karyawan'] = $karyawan;

        return view('master.karyawan', $data);
    }

    public function departemen(Request $request)
    {
        // SET TITLE
        $data['title'] = 'Data Departemen';
        $data['icon'] = '<i class="fa-solid text-white fa-layer-group fs-3x me-4"></i>';
        $data['subtitle'] = 'Kelola data dan informasi departemen secara lengkap dan terstruktur';

        // GET DATA
        $query = Departemen::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('kode', 'LIKE', "%{$search}%")
                  ->orWhere('nama', 'LIKE', "%{$search}%");
        }

        $departemen = $query->get();

        // SET DATA
        $data['departemen'] = $departemen;

        return view('master.departemen', $data);
    }
    
    public function insert_user(Request $request)
    {
        // Aturan validasi
        $rules = [
            'nama' => 'required',
            'nik' => 'required|numeric|unique:users,nik',
            'username' => 'required|unique:users,username|regex:/^\S*$/u',
            'kata_sandi' => 'required',
        ];

        // Validasi departemen hanya jika role = 2 (karyawan)
        if ($request->input('peran') == 2) {
            $rules['id_departemen'] = 'required';
        }

        $request->validate($rules, [
            'nama.required' => 'Nama lengkap tidak boleh kosong!',
            'nik.required' => 'NIK tidak boleh kosong!',
            'nik.numeric' => 'NIK hanya boleh berisi angka!',
            'nik.unique' => 'NIK sudah terdaftar!',
            'username.required' => 'Nama pengguna tidak boleh kosong!',
            'username.unique' => 'Nama pengguna sudah dipakai!',
            'username.regex' => 'Nama pengguna tidak boleh mengandung spasi!',
            'kata_sandi.required' => 'Kata sandi tidak boleh kosong!',
            'id_departemen.required' => 'Departemen tidak boleh kosong!',
        ]);

        try {
            $post = $request->except(['_token', 'kata_sandi']);
            $post['kata_sandi'] = Hash::make($request->kata_sandi);
            $post['created_by'] = session('app_id_user');
            
            User::create($post);
            Session::flash('success', 'Data Karyawan Berhasil Ditambahkan!');
        } catch (\Exception $e) {
            Session::flash('error', 'Data Karyawan Gagal Ditambahkan!');
        }

        return redirect()->route('master.karyawan');
    }

    public function update_user(Request $request)
    {
        // Validasi
        $rules = [
            'nama' => 'required',
            'nik' => ['required', 'numeric', Rule::unique('users')->ignore($request->id_user, 'id_user')],
            'username' => ['required', Rule::unique('users')->ignore($request->id_user, 'id_user'), 'regex:/^\S*$/u'],
        ];

        if ($request->input('peran') == 2) {
            $rules['id_departemen'] = 'required';
        }

        $request->validate($rules, [
            'nama.required' => 'Nama lengkap tidak boleh kosong!',
            'nik.required' => 'NIK tidak boleh kosong!',
            'nik.numeric' => 'NIK hanya boleh berisi angka!',
            'nik.unique' => 'NIK sudah terdaftar oleh pengguna lain!',
            'username.required' => 'Nama pengguna tidak boleh kosong!',
            'username.unique' => 'Nama pengguna sudah dipakai oleh pengguna lain!',
            'username.regex' => 'Nama pengguna tidak boleh mengandung spasi!',
            'id_departemen.required' => 'Departemen tidak boleh kosong!',
        ]);

        $user = User::findOrFail($request->id_user);
        
        try {
            $post = $request->except(['_token', 'kata_sandi']);
            if ($request->filled('kata_sandi')) {
                $post['kata_sandi'] = Hash::make($request->kata_sandi);
            }
            
            $user->update($post);
            Session::flash('success', 'Data Karyawan Berhasil Diperbarui!');
        } catch (\Exception $e) {
            Session::flash('error', 'Data Karyawan Gagal Diperbarui!');
        }

        return redirect()->route('master.karyawan');
    }

    /**
     * Fungsi untuk menghapus data user secara massal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete_multiple_users(Request $request)
    {
        // Ambil ID dari data yang dipilih.
        // Pastikan nama input di form Anda adalah 'id_user[]'.
        $selectedIds = $request->input('id_user');

        // Jika tidak ada ID yang dipilih, kembalikan ke halaman sebelumnya.
        if (empty($selectedIds)) {
            Session::flash('error', 'Tidak ada data karyawan yang dipilih!');
            return redirect()->route('master.karyawan');
        }

        try {
            // Hapus data dari tabel 'users' di mana 'id_user' ada di dalam array $selectedIds.
            User::whereIn('id_user', $selectedIds)->delete();
            Session::flash('success', 'Data karyawan yang dipilih berhasil dihapus!');
        } catch (\Exception $e) {
            // Tangkap error jika proses penghapusan gagal.
            Session::flash('error', 'Gagal menghapus data karyawan yang dipilih!');
        }

        return redirect()->route('master.karyawan');
    }

    // ---
    // DEPARTEMEN
    // Tambah Departemen
    public function insert_departemen(Request $request){
        $request->validate([
            'nama' => 'required',
            'kode' => 'required|unique:departemen,kode|alpha_dash',
            'warna' => 'required',
        ], [
            'nama.required' => 'Nama departemen tidak boleh kosong!',
            'kode.required' => 'Kode departemen tidak boleh kosong!',
            'kode.unique' => 'Kode departemen sudah terdaftar!',
            'kode.alpha_dash' => 'Kode departemen hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah!',
            'warna.required' => 'Warna tidak boleh kosong!',
        ]);

        try {
            Departemen::create($request->all());
            Session::flash('success', 'Data Departemen Berhasil Ditambahkan!');
        } catch (\Exception $e) {
            Session::flash('error', 'Data Departemen Gagal Ditambahkan!');
        }

        return redirect()->route('master.departemen');
    }

    // Edit Departemen
    public function update_departemen(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'kode' => 'required|alpha_dash|unique:departemen,kode,' . $request->id_departemen . ',id_departemen',
            'warna' => 'required',
        ], [
            'nama.required' => 'Nama departemen tidak boleh kosong!',
            'kode.required' => 'Kode departemen tidak boleh kosong!',
            'kode.alpha_dash' => 'Kode departemen hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah!',
            'kode.unique' => 'Kode departemen sudah terdaftar!',
            'warna.required' => 'Warna tidak boleh kosong!',
        ]);

        $departemen = Departemen::findOrFail($request->id_departemen);

        try {
            $departemen->update($request->all());
            Session::flash('success', 'Data Departemen Berhasil Diperbarui!');
        } catch (\Exception $e) {
            Session::flash('error', 'Data Departemen Gagal Diperbarui!');
        }

        return redirect()->route('master.departemen');
    }

    // Hapus Departemen
    public function delete_departemen(Request $request)
    {
        try {
            $departemen = Departemen::findOrFail($request->id_departemen);
            $departemen->delete();
            Session::flash('success', 'Departemen berhasil dihapus!');
        } catch (\Exception $e) {
            Session::flash('error', 'Departemen gagal dihapus!');
        }

        return redirect()->route('master.departemen');
    }
}