<?php
require_once 'auth_check.php';
require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

class JadwalManager {
    private $conn;
    private $pola;
    private $grouped_schedule;
    private $ruangan_list;
    private $jam_list;
    private $active_jam_list;
    private $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

    public function __construct($conn, $pola = '222-23') {
        $this->conn = $conn;
        $this->pola = $pola;
        $this->grouped_schedule = [];
        $this->ruangan_list = [];
        $this->jam_list = [];
        $this->active_jam_list = [];
    }

    public function getScheduleData() {
        // Set batas GROUP_CONCAT
        mysqli_query($this->conn, "SET SESSION group_concat_max_len = 1000000");
        
        // Query dasar untuk mengambil jadwal dengan INNER JOIN dan GROUP_CONCAT untuk dosen
        $sql = "SELECT j.*, mk.nama_mk, mk.sks, mk.kode_jurusan, j.kelas, jur.nama_jurusan, 
                       GROUP_CONCAT(DISTINCT d.nama_dosen ORDER BY d.nama_dosen SEPARATOR '|') as nama_dosen_list,
                       r.kode_ruangan 
                FROM jadwal j 
                INNER JOIN mata_kuliah mk ON j.kode_mk = mk.kode_mk 
                INNER JOIN jurusan jur ON mk.kode_jurusan = jur.kode_jurusan 
                INNER JOIN mata_kuliah_dosen md ON mk.kode_mk = md.kode_mk
                INNER JOIN dosen d ON md.kode_dosen = d.kode_dosen 
                INNER JOIN ruangan r ON j.kode_ruangan = r.kode_ruangan 
                WHERE j.pola = ?
                GROUP BY j.kode_mk, j.kelas, j.hari, j.jam_mulai, j.jam_selesai, j.kode_ruangan
                ORDER BY r.kode_ruangan,
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
        $stmt = mysqli_prepare($this->conn, $sql);
        if (!$stmt) {
            error_log("Error preparing statement: " . mysqli_error($this->conn));
            return false;
        }

        mysqli_stmt_bind_param($stmt, "s", $this->pola);
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Error executing statement: " . mysqli_error($this->conn));
            return false;
        }

        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            error_log("Error getting result: " . mysqli_error($this->conn));
            return false;
        }

        // Proses hasil query
        $has_data = false;
        while ($row = mysqli_fetch_assoc($result)) {
            $has_data = true;
            $ruangan = $row['kode_ruangan'];
            $hari = $row['hari'];
            $jam = $row['jam_mulai'] . ' - ' . $row['jam_selesai'];
            
            // Split nama dosen menjadi array
            $row['nama_dosen'] = explode('|', $row['nama_dosen_list']);
            
            if (!in_array($ruangan, $this->ruangan_list)) {
                $this->ruangan_list[] = $ruangan;
            }
            
            if (!in_array($jam, $this->jam_list)) {
                $this->jam_list[] = $jam;
            }
            
            if (!isset($this->grouped_schedule[$ruangan])) {
                $this->grouped_schedule[$ruangan] = [];
                foreach ($this->days as $day) {
                    $this->grouped_schedule[$ruangan][$day] = [];
                }
            }
            
            $this->grouped_schedule[$ruangan][$hari][] = $row;
        }

        if (!$has_data) {
            error_log("No schedule data found for pola: " . $this->pola);
            return false;
        }

        $this->processScheduleData();
        return true;
    }

    private function processScheduleData() {
        // Urutkan ruangan
        usort($this->ruangan_list, function($a, $b) {
            $a_parts = $this->extractRuanganParts($a);
            $b_parts = $this->extractRuanganParts($b);
            
            $prefix_cmp = strcmp($a_parts['prefix'], $b_parts['prefix']);
            if ($prefix_cmp !== 0) {
                return $prefix_cmp;
            }
            
            return strnatcmp($a, $b);
        });

        sort($this->jam_list);

        // Filter jam yang aktif
        foreach ($this->jam_list as $jam) {
            $has_schedule = false;
            foreach ($this->grouped_schedule as $ruangan => $hari_jadwal) {
                foreach ($hari_jadwal as $hari => $jadwal) {
                    foreach ($jadwal as $row) {
                        if (($row['jam_mulai'] . ' - ' . $row['jam_selesai']) === $jam) {
                            $has_schedule = true;
                            break 3;
                        }
                    }
                }
            }
            if ($has_schedule) {
                $this->active_jam_list[] = $jam;
            }
        }

        // Urutkan jadwal berdasarkan jam
        foreach ($this->grouped_schedule as $ruangan => $hari_jadwal) {
            foreach ($hari_jadwal as $hari => $jadwal) {
                usort($jadwal, function($a, $b) {
                    return strtotime($a['jam_mulai']) - strtotime($b['jam_mulai']);
                });
                $this->grouped_schedule[$ruangan][$hari] = $jadwal;
            }
        }
    }

    private function extractRuanganParts($ruangan) {
        preg_match('/([A-Za-z]+)(\d+)/', $ruangan, $matches);
        return [
            'prefix' => $matches[1] ?? '',
            'number' => intval($matches[2] ?? 0)
        ];
    }

    public function renderSchedule() {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Jadwal <?php echo $this->pola; ?> - Sistem Penjadwalan</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <link rel="stylesheet" href="assets/css/jadwal.css">
            <style>
                .search-container {
                    margin-bottom: 1rem;
                    display: flex;
                    justify-content: flex-end;
                }
                .search-box {
                    position: relative;
                    width: 300px;
                }
                .search-box input {
                    padding-left: 2.5rem;
                    border-radius: 0.25rem;
                    border: 1px solid #ced4da;
                    width: 100%;
                    height: 38px;
                    transition: all 0.3s ease;
                }
                .search-box input:focus {
                    border-color: #80bdff;
                    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
                    outline: none;
                }
                .search-box i {
                    position: absolute;
                    left: 0.75rem;
                    top: 50%;
                    transform: translateY(-50%);
                    color: #6c757d;
                }
                .search-box .clear-search {
                    position: absolute;
                    right: 0.75rem;
                    top: 50%;
                    transform: translateY(-50%);
                    color: #6c757d;
                    cursor: pointer;
                    display: none;
                }
                .search-box .clear-search:hover {
                    color: #dc3545;
                }
                .search-box input:not(:placeholder-shown) + .clear-search {
                    display: block;
                }
                .search-info {
                    font-size: 0.875rem;
                    color: #6c757d;
                    margin-top: 0.5rem;
                    text-align: right;
                }
                .time-slot {
                    background: #f8f9fc;
                    border-radius: 0.25rem;
                    padding: 0.75rem;
                    margin-bottom: 0.5rem;
                    border-left: 4px solid #4E73DF;
                }
                .time-slot:last-child {
                    margin-bottom: 0;
                }
                .course {
                    font-weight: 600;
                    color: #2e59d9;
                    margin-bottom: 0.25rem;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .course .sks {
                    background: #e3e6f0;
                    padding: 0.2rem 0.5rem;
                    border-radius: 0.25rem;
                    font-size: 0.75rem;
                }
                .details {
                    font-size: 0.875rem;
                    color: #6c757d;
                }
                .details i {
                    width: 1rem;
                    margin-right: 0.5rem;
                }
                .dosen-list {
                    margin-top: 0.5rem;
                    padding-top: 0.5rem;
                    border-top: 1px dashed #e3e6f0;
                }
                .dosen-item {
                    display: flex;
                    align-items: center;
                    margin-bottom: 0.25rem;
                }
                .dosen-item:last-child {
                    margin-bottom: 0;
                }
                .dosen-item i {
                    color: #4E73DF;
                    margin-right: 0.5rem;
                }
            </style>
        </head>
        <body>
            <?php include 'sidebar.php'; ?>

            <div class="main-content">
                <div class="schedule-container">
                    <div class="schedule-header">
                        <h2><i class="fas fa-calendar-alt"></i> Jadwal Perkuliahan Pola <?php echo $this->pola; ?></h2>
                        <p class="text-muted">Sistem Penjadwalan Perkuliahan</p>
                    </div>

                    <div class="header-actions mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <a href="export_pdf.php?pola=<?php echo urlencode($this->pola); ?>&search=" class="btn btn-primary shadow-sm" id="exportPdfBtn" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </a>
                                <a href="export_excel.php?pola=<?php echo urlencode($this->pola); ?>" class="btn btn-success shadow-sm">
                                    <i class="fas fa-file-excel"></i> Export Excel
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="search-container">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Cari di semua kolom...">
                            <span class="clear-search"><i class="fas fa-times"></i></span>
                        </div>
                    </div>
                    <div class="search-info" id="searchInfo"></div>

                    <div class="table-responsive shadow-sm rounded">
                        <table class="calendar-table">
                            <thead>
                                <tr>
                                    <th>Ruangan</th>
                                    <th>Hari</th>
                                    <?php foreach ($this->active_jam_list as $jam): ?>
                                    <th><?php echo $jam; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($this->ruangan_list as $ruangan): ?>
                                    <?php foreach ($this->days as $hari): ?>
                                    <?php
                                    $has_schedule = false;
                                    foreach ($this->active_jam_list as $jam) {
                                        if (!empty($this->grouped_schedule[$ruangan][$hari])) {
                                            foreach ($this->grouped_schedule[$ruangan][$hari] as $row) {
                                                if (($row['jam_mulai'] . ' - ' . $row['jam_selesai']) === $jam) {
                                                    $has_schedule = true;
                                                    break 2;
                                                }
                                            }
                                        }
                                    }
                                    if ($has_schedule):
                                    ?>
                                    <tr>
                                        <td class="ruangan-header"><?php echo $ruangan; ?></td>
                                        <td class="day-header"><?php echo $hari; ?></td>
                                        <?php
                                        $jadwal_by_jam = [];
                                        foreach ($this->grouped_schedule[$ruangan][$hari] as $row) {
                                            $jam = $row['jam_mulai'] . ' - ' . $row['jam_selesai'];
                                            if (!isset($jadwal_by_jam[$jam])) {
                                                $jadwal_by_jam[$jam] = [];
                                            }
                                            $jadwal_by_jam[$jam][] = $row;
                                        }
                                        
                                        foreach ($this->active_jam_list as $jam) {
                                            echo '<td>';
                                            if (isset($jadwal_by_jam[$jam])) {
                                                // Kelompokkan jadwal berdasarkan mata kuliah
                                                $grouped_by_mk = [];
                                                foreach ($jadwal_by_jam[$jam] as $row) {
                                                    $key = $row['kode_mk'] . '_' . $row['kelas'];
                                                    if (!isset($grouped_by_mk[$key])) {
                                                        $grouped_by_mk[$key] = [
                                                            'nama_mk' => $row['nama_mk'],
                                                            'sks' => $row['sks'],
                                                            'nama_jurusan' => $row['nama_jurusan'],
                                                            'kelas' => $row['kelas'],
                                                            'dosen' => $row['nama_dosen'] // Array dosen sudah dari query
                                                        ];
                                                    }
                                                }
                                                
                                                // Tampilkan jadwal yang dikelompokkan
                                                foreach ($grouped_by_mk as $mk_data) {
                                                    echo '<div class="time-slot">';
                                                    echo '<div class="course">';
                                                    echo '<span>' . $mk_data['nama_mk'] . '</span>';
                                                    echo '<span class="sks">' . $mk_data['sks'] . ' SKS</span>';
                                                    echo '</div>';
                                                    echo '<div class="details">';
                                                    echo '<div><i class="fas fa-graduation-cap"></i>' . $mk_data['nama_jurusan'] . ' - ' . $mk_data['kelas'] . '</div>';
                                                    echo '<div class="dosen-list">';
                                                    foreach ($mk_data['dosen'] as $dosen) {
                                                        echo '<div class="dosen-item">';
                                                        echo '<i class="fas fa-user-tie"></i>' . $dosen;
                                                        echo '</div>';
                                                    }
                                                    echo '</div>';
                                                    echo '</div>';
                                                    echo '</div>';
                                                }
                                            }
                                            echo '</td>';
                                        }
                                        ?>
                                    </tr>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const searchInput = document.getElementById('searchInput');
                    const clearSearch = document.querySelector('.clear-search');
                    const searchInfo = document.getElementById('searchInfo');
                    const table = document.querySelector('.calendar-table');
                    const rows = table.querySelectorAll('tbody tr');
                    let originalContent = {};

                    // Simpan konten asli setiap sel
                    rows.forEach((row, rowIndex) => {
                        const cells = row.querySelectorAll('td');
                        cells.forEach((cell, cellIndex) => {
                            const id = `cell-${rowIndex}-${cellIndex}`;
                            cell.id = id;
                            originalContent[id] = cell.innerHTML;
                        });
                    });

                    function resetHighlights() {
                        rows.forEach(row => {
                            const cells = row.querySelectorAll('td');
                            cells.forEach(cell => {
                                if (originalContent[cell.id]) {
                                    cell.innerHTML = originalContent[cell.id];
                                }
                            });
                            row.style.display = '';
                        });
                        searchInfo.textContent = '';
                    }

                    function performSearch() {
                        const searchTerm = searchInput.value.toLowerCase().trim();
                        
                        // Update export PDF button URL with search term
                        const exportPdfBtn = document.getElementById('exportPdfBtn');
                        const currentUrl = new URL(exportPdfBtn.href);
                        currentUrl.searchParams.set('search', searchTerm);
                        exportPdfBtn.href = currentUrl.toString();
                        
                        if (!searchTerm) {
                            resetHighlights();
                            return;
                        }

                        let matchCount = 0;
                        let totalRows = 0;
                        
                        rows.forEach(row => {
                            const cells = row.querySelectorAll('td');
                            let found = false;
                            
                            cells.forEach(cell => {
                                // Kembalikan ke konten asli terlebih dahulu
                                if (originalContent[cell.id]) {
                                    cell.innerHTML = originalContent[cell.id];
                                }

                                const text = cell.textContent.toLowerCase();
                                if (text.includes(searchTerm)) {
                                    found = true;
                                    matchCount++;
                                    
                                    // Temukan semua elemen yang berisi teks di dalam sel
                                    const textNodes = [];
                                    const walk = document.createTreeWalker(
                                        cell,
                                        NodeFilter.SHOW_TEXT,
                                        null,
                                        false
                                    );
                                    
                                    let node;
                                    while (node = walk.nextNode()) {
                                        textNodes.push(node);
                                    }

                                    // Highlight teks yang cocok
                                    textNodes.forEach(textNode => {
                                        const text = textNode.textContent;
                                        const regex = new RegExp(searchTerm, 'gi');
                                        const matches = text.matchAll(regex);
                                        
                                        let lastIndex = 0;
                                        const fragment = document.createDocumentFragment();
                                        
                                        for (const match of matches) {
                                            // Tambahkan teks sebelum match
                                            if (match.index > lastIndex) {
                                                fragment.appendChild(
                                                    document.createTextNode(
                                                        text.substring(lastIndex, match.index)
                                                    )
                                                );
                                            }
                                            
                                            // Tambahkan teks yang di-highlight
                                            const span = document.createElement('span');
                                            span.className = 'highlight';
                                            span.textContent = match[0];
                                            fragment.appendChild(span);
                                            
                                            lastIndex = match.index + match[0].length;
                                        }
                                        
                                        // Tambahkan teks yang tersisa
                                        if (lastIndex < text.length) {
                                            fragment.appendChild(
                                                document.createTextNode(
                                                    text.substring(lastIndex)
                                                )
                                            );
                                        }
                                        
                                        // Ganti textNode dengan fragment
                                        textNode.parentNode.replaceChild(fragment, textNode);
                                    });
                                }
                            });
                            
                            if (found) {
                                row.style.display = '';
                                totalRows++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        // Update search info
                        if (matchCount > 0) {
                            searchInfo.textContent = `Menampilkan ${totalRows} baris dengan ${matchCount} hasil pencarian`;
                        } else {
                            searchInfo.textContent = 'Tidak ada hasil yang ditemukan';
                        }
                    }

                    // Event listeners
                    searchInput.addEventListener('input', function() {
                        performSearch();
                    });

                    clearSearch.addEventListener('click', function() {
                        searchInput.value = '';
                        resetHighlights();
                    });

                    // Tambahkan style untuk highlight
                    const style = document.createElement('style');
                    style.textContent = `
                        .highlight {
                            background-color: #ffd700;
                            padding: 0.1rem 0.2rem;
                            border-radius: 0.2rem;
                            font-weight: bold;
                        }
                    `;
                    document.head.appendChild(style);
                });
            </script>
        </body>
        </html>
        <?php
    }
}

// Inisialisasi dan tampilkan jadwal
$pola = isset($_GET['pola']) ? $_GET['pola'] : '222-23';
$jadwalManager = new JadwalManager($conn, $pola);
$jadwalManager->getScheduleData();
$jadwalManager->renderSchedule();
?> 

<style>
.header-actions {
    background: #fff;
    padding: 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.input-group-text {
    border-right: none;
}

.input-group .form-control {
    border-left: none;
}

.input-group .form-control:focus {
    box-shadow: none;
    border-color: #ced4da;
}

.input-group:focus-within {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn {
    font-weight: 500;
    padding: 0.5rem 1rem;
}

.calendar-table {
    background: #fff;
    border-radius: 0.5rem;
    overflow: hidden;
}

.calendar-table th {
    background: #4E73DF;
    color: white;
    font-weight: 600;
    padding: 1rem;
    text-align: center;
    vertical-align: middle;
}

.calendar-table td {
    padding: 0.75rem;
    vertical-align: middle;
}

.time-slot {
    background: #f8f9fc;
    border-radius: 0.25rem;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
}

.time-slot:last-child {
    margin-bottom: 0;
}

.course {
    font-weight: 600;
    color: #2e59d9;
    margin-bottom: 0.25rem;
}

.details {
    font-size: 0.875rem;
    color: #6c757d;
}

.details i {
    width: 1rem;
    margin-right: 0.5rem;
}

.highlight {
    background-color: #fff3cd;
    padding: 0.1rem 0.2rem;
    border-radius: 0.2rem;
}

.search-match {
    background-color: #f8f9fa;
}
</style> 