<?php
// SUPPORT
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

// AUTH 
Route::middleware(AuthMiddleware::class)->group(function () {
    // GET METHOD
    Route::get('/login', [AuthController::class, 'index'])->name('login');

    // POST METHOD
    Route::post('/login-proses', [AuthController::class, 'loginProses'])->name('login.process');
});


// DASHBOARD
Route::middleware(DashboardMiddleware::class)->group(function () {
    // DASHBOARD CONTROLLER
    Route::controller(DashboardController::class)->group(function () {
        // GET
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/profile', 'profile')->name('profile');
    });

    // MASTER CONTROLLER
    Route::controller(MasterController::class)->group(function () {
        // GET
        Route::get('/master/karyawan', 'karyawan')->name('master.karyawan');
        Route::get('/master/departemen', 'departemen')->name('master.departemen');
        
        // POST
        // DEPARTEMEN
        Route::post('/master/departemen/insert', 'insert_departemen')->name('master.departemen.insert');
        Route::post('/master/departemen/update', 'update_departemen')->name('master.departemen.update');
        // Rute untuk "delete" tetap POST untuk konsistensi dengan form
        Route::post('/master/departemen/delete', 'delete_departemen')->name('master.departemen.delete'); 

        // Rute berikut dihapus karena tidak lagi diperlukan
        // Route::post('/master/departemen/get', 'get_departemen')->name('get.departemen');

        // KARYAWAN
        Route::post('/master/karyawan/update', 'update_user')->name('update.karyawan');
        Route::post('/master/karyawan/insert', 'insert_user')->name('insert.karyawan');
        Route::post('/master/karyawan/delete', 'delete_user')->name('delete.karyawan'); 
    });

    // PRESENSI CONTROLLER
    Route::controller(PresensiController::class)->group(function () {
        // GET
        Route::get('/jenis/perizinan', 'jenis')->name('presensi.jenis');

        // POST
        // JENIS
        Route::post('/presensi/jenis/update', 'update_jenis')->name('update.jenis');
        Route::post('/presensi/jenis/insert', 'insert_jenis')->name('insert.jenis');
    });

    // DATATABLE
    Route::controller(TableManagement::class)->group(function () {
        // MASTER
        // Rute berikut dihapus karena tidak lagi menggunakan DataTables berbasis AJAX
        // Route::post('table/departemen', 'table_departemen')->name('table.departemen'); 
        Route::post('table/karyawan', 'table_karyawan')->name('table.karyawan');
        // PRESENSI
        Route::post('table/jenis', 'table_jenis')->name('table.jenis');
    });

    // SETTING CONTROLLER
    Route::controller(SettingController::class)->group(function(){
        // GET
        Route::get('/setting', 'index')->name('setting');

        // POST
        Route::post('/setting/logo', 'updateLogo')->name('setting.logo');
        Route::post('/setting/seo', 'updateSeo')->name('setting.seo');
        Route::post('/setting/sosmed', 'setupSosmed')->name('setting.sosmed');
        Route::post('/setting/insert/sosmed', 'insert_sosmed')->name('insert.sosmed');
        Route::post('/setting/update/sosmed', 'update_sosmed')->name('update.sosmed');

        // GLOBAL
        Route::post('/switch/{db?}', 'switch');
        Route::post('/delete', 'hapusdata');
        Route::post('/single/{db?}/{id?}', 'single');
        Route::post('/allDelete/{db?}/{id?}', 'allDelete');
    });
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');