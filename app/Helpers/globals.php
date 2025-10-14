<?php

use Illuminate\Support\Facades\File;
use Carbon\Carbon;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Color;


if (!function_exists('range_year')) {
    function range_year($dateStart, $dateEnd = null)
    {
        $start = Carbon::parse($dateStart);
        $end = $dateEnd ? Carbon::parse($dateEnd) : Carbon::now();

        return $start->diffInYears($end);
    }
}
if (!function_exists('getContrastColor')) {
    function getContrastColor($hexColor) {
        // hapus tanda #
        $hexColor = ltrim($hexColor, '#');

        // ambil RGB
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));

        // hitung luminance
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        // kalau terang -> hitam, kalau gelap -> putih
        return $luminance > 0.5 ? '#000000' : '#FFFFFF';
    }
}
if (!function_exists('limit_words')) {
    function limit_words($text, $limit = 100, $end = '...')
    {
        $words = preg_split('/\s+/', strip_tags($text));

        if (count($words) <= $limit) {
            return implode(' ', $words);
        }

        return implode(' ', array_slice($words, 0, $limit)) . $end;
    }
}

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

                if (($user['presensi'][$date]['status_terlambat'] ?? null) === 'Y') {
                    $arrKet[] = 'Terlambat ' . $user['presensi'][$date]['terlambat'] . ' Menit Dengan Izin';
                } elseif (($user['presensi'][$date]['status_terlambat'] ?? null) === 'N') {
                    $arrKet[] = 'Terlambat ' . $user['presensi'][$date]['terlambat'] . ' Menit Tanpa Izin';
                }

                if (($user['presensi'][$date]['status_pulang_cepat'] ?? null) === 'Y') {
                    $arrKet[] = 'Pulang Cepat ' . $user['presensi'][$date]['pulang_cepat'] . ' Menit Dengan Izin';
                } elseif (($user['presensi'][$date]['status_pulang_cepat'] ?? null) === 'N') {
                    $arrKet[] = 'Pulang Cepat ' . $user['presensi'][$date]['pulang_cepat'] . ' Menit Tanpa Izin';
                }

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





if (!function_exists('salamWaktu')) {
    function salamWaktu($jam = null)
    {
        // Jika tidak ada parameter, ambil jam sekarang
        if (is_null($jam)) {
            $jam = now()->format('H');
        } else {
            // Pastikan formatnya integer (misal input 08 atau '08' tetap oke)
            $jam = (int) $jam;
        }

        $arr['message'] = '';
        $arr['dark'] = false;
        if ($jam >= 5 && $jam < 12) {
            $arr['message'] = 'Selamat Pagi';
        } elseif ($jam >= 12 && $jam < 15) {
            $arr['message'] = 'Selamat Siang';
        } elseif ($jam >= 15 && $jam < 18) {
            $arr['message'] = 'Selamat Sore';
        } else {
            $arr['message'] = 'Selamat Malam';
            $arr['dark'] = true;
        }

        $arr = json_encode($arr);
        
        return json_decode($arr);
    }

}


if (!function_exists('ckeditor_check')) {
    function ckeditor_check($content = '')
    {
    // Hapus semua tag HTML
    $clean_content = strip_tags($content, '<p><br>'); // Biarkan <p> dan <br> untuk diproses lebih lanjut
    // Hapus tag <p><br></p> yang sering muncul sebagai konten kosong
    $clean_content = preg_replace('/<p>(&nbsp;|\s|<br>|<\/?p>)*<\/p>/i', '', $clean_content);
    // Hapus whitespace yang tersisa
    $clean_content = trim($clean_content);

    return $clean_content;
    }
}

if (!function_exists('selisih_hari')) {
    /**
     * Hitung selisih hari antara dua tanggal, termasuk hari pertama.
     *
     * @param string $start
     * @param string $end
     * @return int
     */
    function selisih_hari($start, $end)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        return $start->diffInDays($end) + 1;
    }
}

if (!function_exists('date_range_parse')) {
    function date_range_parse($date)
    {
        if (strpos($date, ' to ') !== false) {
            [$start_date, $end_date] = explode(' to ', $date);
        } else {
            $start_date = $end_date = $date;
        }

        if (empty($end_date)) {
            $end_date = $start_date;
        }

        return [
            'start' => trim($start_date),
            'end' => trim($end_date),
        ];
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

if (!function_exists('short_text')) {
    function short_text($text, $batas = 5, $pengganti = '...', $link = '')
    {
        if (strlen($text) > $batas) {
            return substr($text, 0, $batas) . $pengganti;
        }
        return $text;
    }
}

if (!function_exists('phone_format')) {
    function phone_format($phoneNumber)
    {
        // Hapus karakter selain angka
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Pastikan nomor memiliki minimal 10 digit
        if (strlen($phoneNumber) >= 10) {
            return sprintf("(%s) %s-%s",
                substr($phoneNumber, 0, 4),
                substr($phoneNumber, 4, 4),
                substr($phoneNumber, 8, 6)
            );
        }

        return "Invalid phone number";
    }
}

if (!function_exists('set_submenu_active')) {
    function set_submenu_active($controller, $arrTarget = [], $c2 = '', $arrTarget2 = [], $class = 'active', $exc = '') {
        if ($controller && in_array($controller, $arrTarget)) {
            if ($c2) {
                return in_array($c2, $arrTarget2) ? $class : $exc;
            }
            return $exc;
        }
        return $exc;
    }
}


if (!function_exists('rupiah')) {
    function rupiah($angka, $format = "Rp. ") {
        return $format . number_format($angka, 0, ',', '.');
    }
}


if (!function_exists('base64url_encode')) {
    function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_decode')) {
    function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}


if (!function_exists('getMonthById')) {
    function getMonthById($id)
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[$id] ?? 'Bulan tidak valid';
    }
}

if (!function_exists('ifnull')) {
    function ifnull($value = '', $replace = 0)
    {
        if ($value == '') {
            return $replace;
        }else{
            return $value;
        }
    }
}