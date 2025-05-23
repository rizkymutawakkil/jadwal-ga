<?php
ob_start();

require_once 'auth_check.php';
require_once 'config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

ob_clean();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set batas GROUP_CONCAT
mysqli_query($conn, "SET SESSION group_concat_max_len = 1000000");

$pola = isset($_GET['pola']) ? $_GET['pola'] : '222-23';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query dasar untuk mengambil jadwal
$sql = "SELECT 
        j.kode_ruangan,
        j.hari,
        CONCAT(j.jam_mulai, ' - ', j.jam_selesai) as jam,
        mk.nama_mk,
        GROUP_CONCAT(DISTINCT d.nama_dosen ORDER BY d.nama_dosen SEPARATOR ', ') as dosen,
        jur.nama_jurusan,
        j.kelas,
        mk.sks,
        CAST(REGEXP_REPLACE(j.kode_ruangan, '[^0-9]', '') AS UNSIGNED) as ruangan_number,
        REGEXP_REPLACE(j.kode_ruangan, '[0-9]', '') as ruangan_letter
        FROM jadwal j 
        LEFT JOIN mata_kuliah mk ON j.kode_mk = mk.kode_mk 
        LEFT JOIN jurusan jur ON mk.kode_jurusan = jur.kode_jurusan 
        LEFT JOIN mata_kuliah_dosen md ON mk.kode_mk = md.kode_mk
        LEFT JOIN dosen d ON md.kode_dosen = d.kode_dosen 
        WHERE j.pola = ?";

if (!empty($search)) {
    $search_param = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (
        mk.nama_mk LIKE ? OR 
        d.nama_dosen LIKE ? OR 
        j.kode_ruangan LIKE ? OR 
        jur.nama_jurusan LIKE ? OR 
        j.kelas LIKE ?
    )";
}

$sql .= " GROUP BY j.kode_ruangan, j.kode_mk, j.kelas, j.hari, j.jam_mulai, j.jam_selesai
          ORDER BY ruangan_letter, ruangan_number,
          CASE j.hari
            WHEN 'Senin' THEN 1
            WHEN 'Selasa' THEN 2
            WHEN 'Rabu' THEN 3
            WHEN 'Kamis' THEN 4
            WHEN 'Jumat' THEN 5
            ELSE 6
          END,
          j.jam_mulai";

// Prepare statement
$stmt = mysqli_prepare($conn, $sql);

if (!empty($search)) {
    $search_param = "%{$search}%";
    mysqli_stmt_bind_param($stmt, "ssssss", $pola, $search_param, $search_param, $search_param, $search_param, $search_param);
} else {
    mysqli_stmt_bind_param($stmt, "s", $pola);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Buat spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set properti dokumen
$spreadsheet->getProperties()
    ->setCreator('Sistem Penjadwalan')
    ->setLastModifiedBy('Sistem Penjadwalan')
    ->setTitle('Jadwal Perkuliahan Pola ' . $pola)
    ->setSubject('Jadwal Perkuliahan')
    ->setDescription('Jadwal Perkuliahan Pola ' . $pola)
    ->setKeywords('jadwal perkuliahan pola ' . $pola)
    ->setCategory('Jadwal');

// Set page setup
$sheet->getPageSetup()
    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
    ->setPaperSize(PageSetup::PAPERSIZE_A4)
    ->setFitToWidth(1)
    ->setFitToHeight(0);

// Set judul
$sheet->setCellValue('A1', 'Jadwal Perkuliahan Pola ' . $pola);
$sheet->mergeCells('A1:H1');

// Style untuk judul
$titleStyle = [
    'font' => [
        'bold' => true,
        'size' => 14,
        'color' => ['rgb' => '2E59D9'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'F8F9FC'],
    ],
    'borders' => [
        'bottom' => [
            'borderStyle' => Border::BORDER_MEDIUM,
            'color' => ['rgb' => '4E73DF'],
        ],
    ],
];
$sheet->getStyle('A1:H1')->applyFromArray($titleStyle);
$sheet->getRowDimension(1)->setRowHeight(30);

// Tambahkan informasi pencarian jika ada
if (!empty($search)) {
    $sheet->setCellValue('A2', 'Filter Pencarian: "' . $search . '"');
    $sheet->mergeCells('A2:H2');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getRowDimension(2)->setRowHeight(20);
}

// Header tabel
$header = array('Ruangan', 'Hari', 'Jam', 'Mata Kuliah', 'Dosen', 'Jurusan', 'Kelas', 'SKS');
$sheet->fromArray($header, NULL, 'A4');

// Style untuk header
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4E73DF'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '2E59D9'],
        ],
    ],
];
$sheet->getStyle('A4:H4')->applyFromArray($headerStyle);
$sheet->getRowDimension(4)->setRowHeight(25);

// Set lebar kolom
$sheet->getColumnDimension('A')->setWidth(15); // Ruangan
$sheet->getColumnDimension('B')->setWidth(15); // Hari
$sheet->getColumnDimension('C')->setWidth(20); // Jam
$sheet->getColumnDimension('D')->setWidth(45); // Mata Kuliah
$sheet->getColumnDimension('E')->setWidth(45); // Dosen
$sheet->getColumnDimension('F')->setWidth(35); // Jurusan
$sheet->getColumnDimension('G')->setWidth(10); // Kelas
$sheet->getColumnDimension('H')->setWidth(8);  // SKS

// Data
$row = 5;
$fill = false;
$current_ruangan = '';
$start_row = 5;

while($data = mysqli_fetch_assoc($result)) {
    // Set nilai sel
    if ($current_ruangan !== '' && $current_ruangan !== $data['kode_ruangan']) {
        if ($row - 1 > $start_row) {
            $sheet->mergeCells('A' . $start_row . ':A' . ($row - 1));
        }
        $start_row = $row;
    }
    
    $current_ruangan = $data['kode_ruangan'];
    $sheet->setCellValue('A' . $row, $data['kode_ruangan']);
    $sheet->setCellValue('B' . $row, $data['hari']);
    $sheet->setCellValue('C' . $row, $data['jam']);
    $sheet->setCellValue('D' . $row, $data['nama_mk']);
    $sheet->setCellValue('E' . $row, $data['dosen']);
    $sheet->setCellValue('F' . $row, $data['nama_jurusan']);
    $sheet->setCellValue('G' . $row, $data['kelas']);
    $sheet->setCellValue('H' . $row, $data['sks']);
    
    // Style untuk data
    $dataStyle = [
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => $fill ? 'F8F9FC' : 'FFFFFF'],
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'E3E6F0'],
            ],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true,
        ],
    ];
    $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray($dataStyle);
    
    // Alignment khusus untuk kolom
    $sheet->getStyle('A' . $row . ':C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('D' . $row . ':F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('G' . $row . ':H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Set tinggi baris
    $sheet->getRowDimension($row)->setRowHeight(-1);
    
    $row++;
    $fill = !$fill;
}

// Merge sel ruangan terakhir
if ($current_ruangan !== '' && $row - 1 > $start_row) {
    $sheet->mergeCells('A' . $start_row . ':A' . ($row - 1));
}

// Auto-filter
$sheet->setAutoFilter('A4:H' . ($row - 1));

// Freeze panes
$sheet->freezePane('A5');

// Set print area
$sheet->getPageSetup()->setPrintArea('A1:H' . ($row - 1));

// Header dan footer untuk cetakan
$sheet->getHeaderFooter()
    ->setOddHeader('&C&B' . 'Jadwal Perkuliahan Pola ' . $pola)
    ->setOddFooter('&L&B' . date('d/m/Y H:i:s') . '&R&P dari &N');

while (ob_get_level()) {
    ob_end_clean();
}

// Set header untuk download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Jadwal_' . $pola . '.xlsx"');
header('Cache-Control: max-age=0');

// Output file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit; 