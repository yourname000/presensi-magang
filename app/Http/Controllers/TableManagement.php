<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB; // Tambahin ini


use App\Models\User;
use App\Models\Departemen;

class TableManagement extends Controller
{
    //DEPARTEMEN
    public function table_departemen(Request $request)
{
    $search = $request->search['value'] ?? '';
    $start = (int)($request->start ?? 0);
    $length = (int)($request->length ?? 10);
    $orderColumn = $request->order[0]['column'] ?? null;
    $orderDir = $request->order[0]['dir'] ?? 'asc';
    $prefix = config('session.prefix');
    $id_user = session($prefix . '_id_user');

    // Kolom mapping sesuai urutan di frontend DataTables
    $columns = [
        'departemen.kode',   // #
        'departemen.nama',
        null
    ];

    // Query dasar
    $query = Departemen::select('*');

    // Search
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('departemen.kode', 'like', "%{$search}%")
              ->orWhere('departemen.nama', 'like', "%{$search}%");
        });
    }

    // Sorting
    if ($orderColumn !== null && isset($columns[$orderColumn])) {
        $query->orderBy($columns[$orderColumn], $orderDir);
    } else {
        $query->orderBy('departemen.created_at', 'desc'); // Default sorting
    }

    // Total record
    $totalRecords = $query->count();

    // Pagination
    $data = $query->skip($start)->take($length)->get();

    // Format output
    $result = [];
    foreach ($data as $item) {
        // Action buttons (berbentuk persegi panjang dengan teks dan icon)
        $action = '
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-warning btn-sm px-3" title="Edit"
                    onclick="ubah_data(this,'.$item->id_departemen.')" data-bs-toggle="modal" data-bs-target="#modalDepartemen">
                    <i class="fa-solid fa-edit me-1"></i> Edit
                </button>
                <button type="button" onclick="hapus_data(' . $item->id_departemen . ', \'table_departemen\')" 
                    class="btn btn-danger btn-sm px-3" 
                    title="Hapus">
                    <i class="fa-solid fa-trash me-1"></i> Hapus
                </button>
            </div>';

        // Kode badge
        $kode = '<div class="w-100 d-flex justify-content-center">';
        $kode .= '<span class="badge text-center d-flex justify-content-center align-items-center" style="min-width:80px;background-color:'.$item->warna.';color:'.getContrastColor($item->warna).';">'.$item->kode.'</span>';
        $kode .= '</div>';

        // Nama
        $nama = '<div class="w-100 d-flex justify-content-center">';
        $nama .= e($item->nama);
        $nama .= '</div>';

        $result[] = [
            $kode,
            $nama,
            $action
        ];
    }

    // Return response
    return response()->json([
        'draw' => intval($request->draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $result
    ]);
}



    //KARYAWAN
    public function table_karyawan(Request $request)
    {
        $ch = $request->input('checked_items') ?? '';
        $search = $request->search['value'] ?? '';
        $start = (int)($request->start ?? 0);
        $length = (int)($request->length ?? 10);
        $orderColumn = $request->order[0]['column'] ?? null;
        $orderDir = $request->order[0]['dir'] ?? 'asc';
        $prefix = config('session.prefix');
        $id_user = session($prefix.'_id_user');

        // Kolom mapping sesuai urutan di frontend DataTables
        $columns = [
            null,                   // kolom action
            'users.nik',            // kolom NIK
            'departemen.nama',      // kolom Departemen
            'users.nama',           // kolom Nama
            'users.username',       // kolom Username
            'users.email',          // kolom Email
        ];

        // Query dasar pakai join manual
        $baseQuery = User::select(
                'users.*',
                'departemen.nama as departemen_nama',
                'departemen.warna as departemen_warna'
            )
            ->leftJoin('departemen', 'departemen.id_departemen', '=', 'users.id_departemen')
            ->where('users.id_user', '!=', $id_user)
            ->where('users.peran', 2);

        // Total semua record (sebelum filter)
        $totalRecords = $baseQuery->count();

        // Apply filter (search)
        $filteredQuery = clone $baseQuery;
        if (!empty($search)) {
            $filteredQuery->where(function ($q) use ($search) {
                $q->where('users.nama', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%")
                    ->orWhere('users.nik', 'like', "%{$search}%")
                    ->orWhere('departemen.nama', 'like', "%{$search}%");
            });
        }

        // Total setelah filter
        $recordsFiltered = $filteredQuery->count();

        // Sorting
        if ($orderColumn !== null && isset($columns[$orderColumn]) && $columns[$orderColumn] !== null) {
            $filteredQuery->orderBy($columns[$orderColumn], $orderDir);
        } else {
            $filteredQuery->orderBy('users.created_at', 'DESC');
        }

        // Pagination
        $data = $filteredQuery->skip($start)->take($length)->get();

        // Format output
        $result = [];
        foreach ($data as $item) {

            // ACTION
            $action = '
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-warning btn-sm px-3" title="Edit"
                    onclick="ubah_data(this,'.$item->id_user.')" 
                    data-image="'.image_check($item->image,'user','user').'" 
                    data-ktp="'.image_check($item->ktp,'user').'" 
                    data-bs-toggle="modal" 
                    data-bs-target="#modalKaryawan">
                    <i class="fa-solid fa-edit me-1"></i> Edit
                </button>
                <button type="button" onclick="hapus_data(' . $item->id_user . ', \'table_karyawan\')" 
                    class="btn btn-danger btn-sm px-3" 
                    title="Hapus">
                    <i class="fa-solid fa-trash me-1"></i> Hapus
                </button>
            </div>';

            // DEPARTEMEN badge
            $departemen = '<div class="w-100 d-flex justify-content-center">';
            if ($item->departemen_nama) {
                $warna = $item->departemen_warna ?? '#999';
                $departemen .= '<span class="badge text-center d-flex justify-content-center align-items-center" 
                    style="min-width:80px;background-color:'.$warna.';color:'.getContrastColor($warna).';">
                    '.$item->departemen_nama.'
                </span>';
            } else {
                $departemen .= '<span class="badge bg-secondary">-</span>';
            }
            $departemen .= '</div>';
            

            $nik = '<div class="w-100 d-flex justify-content-center">';
            $nik .= $item->nik;
            $nik .= '</div>';

            $nama = '<div class="w-100 d-flex justify-content-center">';
            $nama .= $item->nama;
            $nama .= '</div>';

            $username = '<div class="w-100 d-flex justify-content-center">';
            $username .= $item->username;
            $username .= '</div>';

            $checkbox = '<div class="form-check d-flex justify-content-center align-items-center">';
            $checkbox .= '<input class="form-check-input checkbox-table" type="checkbox" value="'.$item->id_user.'" onchange="checkbox_action(this)">';
            $checkbox .= '</div>';

            // Susun row sesuai urutan $columns
            $result[] = [
                $checkbox,
                $nik,
                $departemen,
                $nama,
                $username,
                $action,
            ];
        }

        $return = [
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => $result
        ];

        return response()->json($return);
    }



}