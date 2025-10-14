<?php

use Illuminate\Support\Facades\Route;

// MIDDLEWARE
use App\Http\Middleware\DashboardMiddleware;
use App\Http\Middleware\AuthMiddleware;

// CONTROLLER
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\TableManagement;
use App\Http\Controllers\SettingController;

Route::get('/', function () {
    return redirect('/login');
});

// ==========================================
// AUTHENTICATION
// ==========================================
Route::middleware(AuthMiddleware::class)->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login-proses', [AuthController::class, 'loginProses'])->name('login.process');
});

// ==========================================
// DASHBOARD PROTECTED AREA
// ==========================================
Route::middleware(DashboardMiddleware::class)->group(function () {

    // -------------------------
    // DASHBOARD
    // -------------------------
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/profile', 'profile')->name('profile');
        Route::post('/absen-location', 'absenLocation')->name('absen.location');
    });

    // -------------------------
    // MASTER DATA
    // -------------------------
    Route::controller(MasterController::class)->group(function () {
        // GET
        Route::get('/master/karyawan', 'karyawan')->name('master.karyawan');
        Route::get('/master/departemen', 'departemen')->name('master.departemen');

        // POST - Departemen
        Route::post('/master/departemen/insert', 'insert_departemen')->name('master.departemen.insert');
        Route::post('/master/departemen/update', 'update_departemen')->name('master.departemen.update');
        Route::post('/master/departemen/delete', 'delete_departemen')->name('master.departemen.delete'); 

        // POST - Karyawan
        Route::post('/master/karyawan/insert', 'insert_user')->name('master.karyawan.insert');
        Route::post('/master/karyawan/update', 'update_user')->name('master.karyawan.update');
        Route::post('/master/karyawan/delete', 'delete_user')->name('master.karyawan.delete');
        Route::delete('/master/karyawan/delete-multiple', 'delete_multiple_users')->name('master.karyawan.delete_multiple');
    });

    // -------------------------
    // PRESENSI (ABSENSI)
    // -------------------------
    Route::controller(PresensiController::class)->group(function () {
        // GET
        Route::get('/jenis/perizinan', 'jenis')->name('presensi.jenis');
        Route::get('/perizinan', 'izin')->name('presensi.izin');
        Route::get('/laporan/presensi', 'report')->name('presensi.report');
        Route::get('/presensi/foto', 'gallery')->name('presensi.foto');

        // Tambahan penting ar tabel presensi bisa tampil
        Route::get('/presensi/table', 'table_presensi')->name('presensi.table_presensi');

        // POST
        // Jenis izin
        Route::post('/presensi/jenis/insert', 'insert_jenis')->name('insert.jenis');
        Route::post('/presensi/jenis/update', 'update_jenis')->name('update.jenis');

        // Izin
        Route::post('/presensi/izin/insert', 'insert_izin')->name('insert.izin');
        Route::post('/presensi/izin/update', 'update_izin')->name('update.izin');
        Route::post('/search-employee', 'search_employee')->name('search.employee');
        Route::post('/single-izin', 'single_izin')->name('single.izin');

        // Presensi
        Route::post('/presensi/insert', 'insert_presensi')->name('insert.presensi');
        Route::post('/presensi/update', 'update_presensi')->name('update.presensi');
        Route::post('/card-image', 'card_image')->name('card.image');
        Route::post('/single-presensi', 'single_presensi')->name('single.presensi');
        Route::post('/export-presensi', 'export_presensi')->name('export.presensi');
        Route::post('/delete-pic', 'delete_pic')->name('delete.pic');

        // Profil
        Route::post('/update-profile','update_profile')->name('update.profile');
    });

    // -------------------------
    // DATATABLES MANAGEMENT
    // -------------------------
    Route::controller(TableManagement::class)->group(function () {
        Route::post('/table/departemen', 'table_departemen')->name('table.departemen');
        Route::post('/table/karyawan', 'table_karyawan')->name('table.karyawan');
        Route::post('/table/presensi', 'table_presensi')->name('table.presensi');
    });

    // -------------------------
    // SETTING / PENGATURAN
    // -------------------------
    Route::controller(SettingController::class)->group(function () {
        // GET
        Route::get('/pengaturan', 'index')->name('pengaturan');

        // POST
        Route::post('/setting/website', 'update_website')->name('setting.website');
        Route::post('/setting/lokasi', 'updateLocation')->name('setup.location');
        Route::post('/setting/sosmed', 'setupSosmed')->name('setting.sosmed');
        Route::post('/setting/insert/sosmed', 'insert_sosmed')->name('insert.sosmed');
        Route::post('/setting/update/sosmed', 'update_sosmed')->name('update.sosmed');

        // SHIFT
        Route::post('/setting/shift/insert', 'update_shift')->name('setup.shift.save');
        Route::get('/setting/shift/delete/{id}', 'delete_shift')->name('setup.shift.delete');

        // GLOBAL
        Route::post('/switch/{db?}', 'switch');
        Route::post('/delete', 'hapusdata');
        Route::post('/single/{db?}/{id?}', 'single');
        Route::post('/allDelete/{db?}/{id?}', 'allDelete');
    });
});

// ==========================================
// LOGOUT
// ==========================================
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

