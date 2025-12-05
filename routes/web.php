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
// AUTHENTICATION (TANPA MIDDLEWARE)
// ==========================================
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'index')->name('login');
    Route::post('/login-proses', 'loginProses')->name('login.process');
});

// ==========================================
// DASHBOARD PROTECTED AREA (LOGIN WAJIB)
// ==========================================
Route::middleware(DashboardMiddleware::class)->group(function () {

    // -------------------------
    // DASHBOARD
    // -------------------------
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::post('/profile', 'updateProfile')->name('update.profile');
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
        // Tambahan penting agar tabel presensi bisa tampil
        Route::get('/presensi/table', 'table_presensi')->name('presensi.table_presensi');
        // Presensi
        Route::post('/presensi/insert', 'insert_presensi')->name('insert.presensi');
        Route::post('/presensi/update',  'update_presensi')->name('update.presensi');
        Route::post('/export-presensi', 'export_presensi')->name('export.presensi');
        Route::post('/presensi/multiple-delete', [PresensiController::class, 'delete_multiple_presensi'])
         ->name('presensi.multiple_delete');
    });

    // -------------------------
    // SETTING / PENGATURAN
    // -------------------------
    Route::controller(SettingController::class)->group(function () {
        // GET
        Route::get('/pengaturan', 'index')->name('pengaturan');

        // POST
        Route::post('/setting/website', 'update_website')->name('setting.website');
        Route::post('/setting/lokasi', 'update_Location')->name('setup.location');



        // SHIFT
        Route::post('/setting/shift/insert', 'update_shift')->name('setup.shift.save');
        Route::get('/setting/shift/delete/{id}', 'delete_shift')->name('setup.shift.delete');
    });
});

// ==========================================
// LOGOUT
// ==========================================
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
