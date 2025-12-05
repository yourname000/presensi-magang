<?php

use Illuminate\Support\Facades\File;
use Carbon\Carbon;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Color;

if (!function_exists('cetak_excel')) {
    function cetak_excel($filename, $headings, $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $colIndex = 'A';
        foreach ($headings as $heading) {
            $sheet->setCellValue($colIndex . '1', $heading);
            $sheet->getStyle($colIndex . '1')->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $colIndex++;
        }

        // Set data
        $row = 2;
        if ($data) {
            foreach ($data as $item) {
                $colIndex = 'A';
                foreach ($item as $value) {
                    $sheet->setCellValue($colIndex . $row, $value);
                    $sheet->getStyle($colIndex . $row)->getBorders()->getAllBorders()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $colIndex++;
                }
                $row++;
            }
        }

        // Auto-size columns
        $lastCol = chr(ord('A') + count($headings) - 1);
        foreach (range('A', $lastCol) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Output Excel
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename . '.xlsx')->deleteFileAfterSend(true);
    }
}



if (!function_exists('cetak_laporan_presensi')) {
    function cetak_laporan_presensi($filename, $data, $startDate, $endDate, $keterangan = [])
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Generate daftar tanggal
        $dates = [];
        $cur = $startDate->copy()->startOfDay();
        $endDate = $endDate->copy()->startOfDay();

        while ($cur->lte($endDate)) {
            $dates[] = $cur->format('d/m/Y');
            $cur->addDay();
        }

        // HEADER
        $sheet->setCellValue([1, 1], 'NO.');
        $sheet->setCellValue([2, 1], 'NIK');
        $sheet->setCellValue([3, 1], 'Nama');

        $col = 4; // mulai dari kolom D
        foreach ($dates as $date) {
            $sheet->setCellValue([$col, 1], $date);
            $col++;
        }

        // kolom total kerja
        $sheet->setCellValue([$col, 1], 'Total Kerja (Hari)');
        $lastTotalCol = $col;

        // DATA
        $row = 2;
        $no  = 1;
        foreach ($data as $user) {
            $sheet->setCellValue([1, $row], $no++);
            $sheet->setCellValue([2, $row], $user['nik']);
            $sheet->setCellValue([3, $row], $user['nama']);

            $col = 4;
            $totalKerja = 0;

            foreach ($dates as $date) {
                $top   = $user['presensi'][$date]['top']   ?? '';
                $shift = $user['presensi'][$date]['shift'] ?? '';

                // baris atas (top)
                $sheet->setCellValue([$col, $row], $top);

                if ($top === '0') {
                    $sheet->getStyle([$col, $row])
                          ->getFont()
                          ->getColor()
                          ->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
                }

                if ($top === '1') {
                    $totalKerja++;
                }

                // baris bawah (shift)
                $sheet->setCellValue([$col, $row + 1], $shift);
                if (!empty($shift)) {
                    $sheet->getStyle([$col, $row + 1])->getFont()->setBold(true);
                }

                // baris keterangan
                $ketRow = $row + 2;
                $ketText = '';
                $arrKet = [];

                if ($user['presensi'][$date]['keterangan'] ?? false) {
                    $arrKet[] = $user['presensi'][$date]['keterangan'];
                }
                //Komentari kode ini untuk tidak menampilkan jumlah terlambat dan pulang cepat tiap harinya apada karyawan
                // if (($user['presensi'][$date]['status_terlambat'] ?? null) === 'Y') {
                //     $arrKet[] = 'Terlambat ' . $user['presensi'][$date]['terlambat'] . ' Menit Dengan Izin';
                // } elseif (($user['presensi'][$date]['status_terlambat'] ?? null) === 'N') {
                //     $arrKet[] = 'Terlambat ' . $user['presensi'][$date]['terlambat'] . ' Menit Tanpa Izin';
                // }


                // if (($user['presensi'][$date]['status_pulang_cepat'] ?? null) === 'Y') {
                //     $arrKet[] = 'Pulang Cepat ' . $user['presensi'][$date]['pulang_cepat'] . ' Menit Dengan Izin';
                // } elseif (($user['presensi'][$date]['status_pulang_cepat'] ?? null) === 'N') {
                //     $arrKet[] = 'Pulang Cepat ' . $user['presensi'][$date]['pulang_cepat'] . ' Menit Tanpa Izin';
                // }

                if (!empty($arrKet)) {
                    $ketText = implode(' | ', $arrKet);
                }

                $sheet->setCellValue([$col, $ketRow], $ketText);
                $sheet->getStyle([$col, $ketRow])->getFont()->getColor()
                      ->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);

                $col++;
            }

            // set kolom total kerja
            $sheet->setCellValue([$lastTotalCol, $row], $totalKerja);
            $sheet->getStyle([$lastTotalCol, $row])->getFont()->setBold(true);

            // naik 3 baris per user
            $row += 3;
        }

        // Keterangan bawah
        $row++;
        $sheet->setCellValue([2, $row], 'Ket:');
        $sheet->getStyle([2, $row])->getFont()->setBold(true);
        $row++;

        foreach ($keterangan as $ket) {
            $sheet->setCellValue([2, $row], $ket);
            $row++;
        }

        // CENTER ALIGN
        $lastCol = $lastTotalCol;
        $lastRow = $row - count($keterangan) - 3;

        $sheet->getStyle([4, 2, $lastCol, $lastRow])
              ->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
              ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // BORDER
        $sheet->getStyle([1, 1, $lastCol, $lastRow])
              ->getBorders()
              ->getAllBorders()
              ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // AUTO SIZE
        for ($c = 1; $c <= $lastCol; $c++) {
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
        }

        // OUTPUT
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename . '.xlsx')->deleteFileAfterSend(true);
    }
}


if (!function_exists('image_check')) {
    function image_check($image = null, $path = null, $rename = null)
    {
        $defaultImage = $rename ? $rename : 'notfound';
        $path = $path ?? 'error';  // Default 'error' kalau $path kosong

        if (!$image) {
            $file = "default/{$defaultImage}.jpg";
        } else {
            $filePath = public_path("data/{$path}/{$image}"); // Path ke file

            if (File::exists($filePath)) {
                $file = "{$path}/{$image}";
            } else {
                $file = "default/{$defaultImage}.jpg";
            }
        }

        return asset("data/{$file}"); // URL lengkap untuk diakses
    }
}