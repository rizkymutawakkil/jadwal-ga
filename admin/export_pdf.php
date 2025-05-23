<?php
ob_start();

require_once 'auth_check.php';
require_once 'config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Fix TCPDF namespace
use TCPDF as BaseTCPDF;

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

// Tambahkan filter pencarian jika ada
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

class PDF extends BaseTCPDF {
    protected $last_page_flag = false;
    protected $last_ruangan = '';
    protected $ruangan_rows = 0;
    protected $ruangan_start_y = 0;

    public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache);
        
        // Set default font
        $this->SetFont('helvetica', '', 10);
        
        // Set margins
        $this->SetMargins(15, 25, 15);
        
        // Set auto page breaks
        $this->SetAutoPageBreak(TRUE, 20);
        
        // Set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // Set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }

    public function Close() {
        $this->last_page_flag = true;
        parent::Close();
    }

    // Add getter and setter for protected properties
    public function getRuanganRows() {
        return $this->ruangan_rows;
    }

    public function getRuanganStartY() {
        return $this->ruangan_start_y;
    }

    public function Header() {
        // Set font untuk judul
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(46, 89, 217); // Warna biru seperti di Excel
        
        // Title
        $this->Cell(0, 15, 'Jadwal Perkuliahan Pola ' . $_GET['pola'], 0, 1, 'C');
        
        // Garis bawah judul
        $this->SetDrawColor(78, 115, 223);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY(), $this->GetPageWidth() - 15, $this->GetY());
        
        // Spasi setelah garis
        $this->Ln(5);

        // Jika ada pencarian, tampilkan
        if (!empty($_GET['search'])) {
            $this->SetFont('helvetica', '', 10);
            $this->SetTextColor(33, 33, 33);
            $this->Cell(0, 8, 'Filter Pencarian: "' . $_GET['search'] . '"', 0, 1, 'L');
            $this->Ln(2);
        }
    }
    
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(108, 117, 125);
        
        // Info waktu cetak di kiri
        $this->Cell(0, 10, 'Dicetak pada: ' . date('d/m/Y H:i:s'), 0, 0, 'L');
        
        // Page number di kanan
        $this->Cell(0, 10, 'Halaman ' . $this->getAliasNumPage() . ' dari ' . $this->getAliasNbPages(), 0, 0, 'R');
    }

    public function MultiCellTable($w, $h, $txt, $border='LR', $align='L', $fill=false) {
        // Get current position
        $x = $this->GetX();
        $y = $this->GetY();
        
        // Calculate height needed
        $this->startTransaction();
        $this->MultiCell($w, $h, $txt, $border, $align, $fill);
        $height = $this->GetY() - $y;
        $this->rollbackTransaction(true);
        
        // Write cell with calculated height
        $this->MultiCell($w, $height, $txt, $border, $align, $fill);
        
        // Reset position to right of cell
        $this->SetXY($x + $w, $y);
        
        return $height;
    }

    public function PrintHeader($header, $w) {
        $x = $this->GetX();
        $y = $this->GetY();
        
        // Set style untuk header seperti di Excel
        $this->SetFillColor(78, 115, 223); // Biru Excel
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(46, 89, 217);
        $this->SetLineWidth(0.3);
        $this->SetFont('helvetica', 'B', 10);
        
        foreach($header as $i => $txt) {
            $this->Cell($w[$i], 7, $txt, 1, 0, 'C', true);
        }
        $this->Ln(7);
        
        return $this->GetY() - $y;
    }

    public function PrintRow($data, $w, $maxh, $fill, $merge_ruangan, $x, $y) {
        // Set warna untuk baris alternatif seperti di Excel
        if ($fill) {
            $this->SetFillColor(248, 249, 252); // Light blue-gray seperti Excel
        } else {
            $this->SetFillColor(255, 255, 255);
        }
        
        // Set border color seperti Excel
        $this->SetDrawColor(227, 230, 240); // Border color Excel
        $this->SetTextColor(33, 33, 33);

        // Handle ruangan column
        if ($merge_ruangan) {
            if ($data[0] != $this->last_ruangan) {
                if ($this->ruangan_rows > 0) {
                    $this->Line($x, $this->ruangan_start_y, $x, $y);
                    $this->Line($x + $w[0], $this->ruangan_start_y, $x + $w[0], $y);
                }
                
                $this->last_ruangan = $data[0];
                $this->ruangan_rows = 1;
                $this->ruangan_start_y = $y;
                
                $this->SetFont('helvetica', 'B', 10);
                $this->Cell($w[0], $maxh, $data[0], 1, 0, 'C', $fill);
            } else {
                $this->ruangan_rows++;
                $this->Cell($w[0], $maxh, '', 'LR', 0, 'C', $fill);
            }
        } else {
            $this->SetFont('helvetica', 'B', 10);
            $this->Cell($w[0], $maxh, $data[0], 1, 0, 'C', $fill);
        }

        // Print remaining cells dengan style Excel
        for($i = 1; $i < count($data); $i++) {
            if($i == 3 || $i == 4 || $i == 5) { // Mata Kuliah, Dosen, Jurusan
                $this->SetFont('helvetica', '', 9);
                $this->MultiCell($w[$i], $maxh, $data[$i], 1, 'L', $fill);
                $this->SetXY($x + array_sum(array_slice($w, 0, $i + 1)), $y);
            } else {
                $this->SetFont('helvetica', '', 9);
                $align = ($i == 1 || $i == 2 || $i == 6 || $i == 7) ? 'C' : 'L';
                $this->Cell($w[$i], $maxh, $data[$i], 1, 0, $align, $fill);
            }
        }
        
        $this->Ln($maxh);
    }

    public function Row($data, $w, $h, $fill=false, $merge_ruangan=false) {
        // Store current page
        $startPage = $this->getPage();
        
        // Get current position
        $x = $this->GetX();
        $y = $this->GetY();
        
        // Initialize maximum height
        $maxh = $h;

        // First pass: Calculate maximum height needed
        $this->startTransaction();
        
        $curr_x = $x;
        $this->SetX($curr_x);
        
        // Calculate heights for all columns that might wrap
        $heights = array();
        
        for($i = 0; $i < count($data); $i++) {
            $this->SetX($curr_x);
            if($i == 3 || $i == 4 || $i == 5) { // Mata Kuliah, Dosen, Jurusan
                $this->MultiCell($w[$i], $h, $data[$i], 0, 'L');
                $heights[] = $this->GetY() - $y;
            } else {
                $this->Cell($w[$i], $h, $data[$i], 0, 0);
                $heights[] = $h;
            }
            $curr_x += $w[$i];
        }
        
        // Get the maximum height
        $maxh = max($heights);
        $this->rollbackTransaction(true);
        
        // Add padding
        $maxh += 4;

        // Check if we need a page break
        if ($y + $maxh > $this->GetPageHeight() - $this->getBreakMargin()) {
            // Complete the border of current merged cell if any
            if ($merge_ruangan && $this->last_ruangan == $data[0] && $this->ruangan_rows > 0) {
                $this->SetDrawColor(189, 195, 199);
                $this->Line($x, $this->ruangan_start_y, $x, $y);
                $this->Line($x + $w[0], $this->ruangan_start_y, $x + $w[0], $y);
            }
            
            $this->AddPage($this->CurOrientation);
            
            // Reset positions
            $y = $this->GetY();
            $x = $this->GetX();
            
            // Reset ruangan merging
            if ($merge_ruangan) {
                $this->last_ruangan = '';
                $this->ruangan_rows = 0;
            }
            
            // Reprint header with proper styling
            $header = array('Ruangan', 'Hari', 'Jam', 'Mata Kuliah', 'Dosen', 'Jurusan', 'Kelas', 'SKS');
            $this->PrintHeader($header, $w);
            
            // Reset style for data
            $this->SetTextColor(33, 33, 33);
            $this->SetDrawColor(227, 230, 240);
            $this->SetFont('helvetica', '', 9);
        }

        // Set position
        $this->SetXY($x, $y);
        
        // Print the row
        $this->PrintRow($data, $w, $maxh, $fill, $merge_ruangan, $x, $y);
        
        // Return to the last position
        $this->SetXY($x, $y + $maxh);
        
        return $maxh;
    }
}

// Buat instance PDF
$pdf = new PDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Set informasi dokumen
$pdf->SetCreator('Sistem Penjadwalan');
$pdf->SetAuthor('Sistem Penjadwalan');
$pdf->SetTitle('Jadwal Perkuliahan Pola ' . $pola);

// Set margin yang optimal untuk tampilan Excel-like
$pdf->SetMargins(15, 25, 15);
$pdf->SetHeaderMargin(15);
$pdf->SetFooterMargin(15);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 20);

// Tambah halaman
$pdf->AddPage();

// Header tabel dengan lebar yang sama seperti Excel
$header = array('Ruangan', 'Hari', 'Jam', 'Mata Kuliah', 'Dosen', 'Jurusan', 'Kelas', 'SKS');
$w = array(15, 15, 20, 45, 45, 35, 10, 8); // Match Excel column widths

// Output header
$pdf->PrintHeader($header, $w);

// Style untuk data
$pdf->SetTextColor(33, 33, 33);
$pdf->SetDrawColor(227, 230, 240);
$pdf->SetLineWidth(0.2);
$pdf->SetFont('helvetica', '', 9);

// Data
$fill = true;
$current_ruangan = '';
$start_row = $pdf->GetY();
$same_ruangan_height = 0;

while($row = mysqli_fetch_assoc($result)) {
    // Persiapkan data baris
    $line_data = array(
        $row['kode_ruangan'],
        $row['hari'],
        $row['jam'],
        $row['nama_mk'],
        $row['dosen'],
        $row['nama_jurusan'],
        $row['kelas'],
        $row['sks']
    );

    // Output baris dengan perhitungan tinggi otomatis dan merge ruangan
    $height = $pdf->Row($line_data, $w, 6, $fill, true);
    
    // Jika halaman baru, reset header
    if ($pdf->getPage() > $pdf->getNumPages() - 1) {
        // Reset header tabel di halaman baru
        $pdf->SetFillColor(52, 73, 94);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(44, 62, 80);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->PrintHeader($header, $w);
        
        // Reset style untuk data
        $pdf->SetTextColor(44, 62, 80);
        $pdf->SetDrawColor(189, 195, 199);
        $pdf->SetFont('helvetica', '', 9);
    }
    
    $fill = !$fill;
}

// Draw final border for last merged ruangan cell if any
if ($pdf->getRuanganRows() > 0) {
    $pdf->SetDrawColor(189, 195, 199);
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->Line($x, $pdf->getRuanganStartY(), $x, $y);
    $pdf->Line($x + $w[0], $pdf->getRuanganStartY(), $x + $w[0], $y);
}

// Garis penutup tabel
$pdf->Cell(array_sum($w), 0, '', 'T');

while (ob_get_level()) {
    ob_end_clean();
}

// Output PDF
$pdf->Output('Jadwal_' . $pola . '.pdf', 'D');
exit; 