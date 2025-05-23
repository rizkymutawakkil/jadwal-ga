<?php
require_once 'config/database.php';

// Fungsi untuk mendapatkan statistik jadwal
function getScheduleStats($pola) {
    global $conn;
    
    $stats = [
        'total_kelas' => 0,
        'total_sks' => 0,
        'total_jam' => 0,
        'konflik_ruangan' => 0,
        'konflik_dosen' => 0,
        'konflik_kelas' => 0,
        'distribusi_hari' => [
            'Senin' => 0,
            'Selasa' => 0,
            'Rabu' => 0,
            'Kamis' => 0,
            'Jumat' => 0
        ],
        'distribusi_jam' => []
    ];
    
    // Ambil semua jadwal untuk pola tertentu
    $sql = "SELECT j.*, mk.sks, mk.nama_mk, d.nama_dosen, r.kode_ruangan 
            FROM jadwal j 
            LEFT JOIN mata_kuliah mk ON j.kode_mk = mk.kode_mk 
            LEFT JOIN dosen d ON j.kode_dosen = d.kode_dosen 
            LEFT JOIN ruangan r ON j.kode_ruangan = r.kode_ruangan 
            WHERE j.pola = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $pola);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $jadwal = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stats['total_kelas']++;
        $stats['total_sks'] += $row['sks'];
        
        // Hitung total jam
        $jam_mulai = strtotime($row['jam_mulai']);
        $jam_selesai = strtotime($row['jam_selesai']);
        $durasi = ($jam_selesai - $jam_mulai) / 3600; // Konversi ke jam
        $stats['total_jam'] += $durasi;
        
        // Hitung distribusi hari
        $stats['distribusi_hari'][$row['hari']]++;
        
        // Hitung distribusi jam
        $jam_key = $row['jam_mulai'] . ' - ' . $row['jam_selesai'];
        if (!isset($stats['distribusi_jam'][$jam_key])) {
            $stats['distribusi_jam'][$jam_key] = 0;
        }
        $stats['distribusi_jam'][$jam_key]++;
        
        $jadwal[] = $row;
    }
    
    // Hitung konflik
    for ($i = 0; $i < count($jadwal); $i++) {
        for ($j = $i + 1; $j < count($jadwal); $j++) {
            // Konflik ruangan
            if ($jadwal[$i]['kode_ruangan'] == $jadwal[$j]['kode_ruangan'] &&
                $jadwal[$i]['hari'] == $jadwal[$j]['hari'] &&
                $jadwal[$i]['jam_mulai'] == $jadwal[$j]['jam_mulai']) {
                $stats['konflik_ruangan']++;
            }
            
            // Konflik dosen
            if ($jadwal[$i]['kode_dosen'] && $jadwal[$j]['kode_dosen'] &&
                $jadwal[$i]['kode_dosen'] == $jadwal[$j]['kode_dosen'] &&
                $jadwal[$i]['hari'] == $jadwal[$j]['hari'] &&
                $jadwal[$i]['jam_mulai'] == $jadwal[$j]['jam_mulai']) {
                $stats['konflik_dosen']++;
            }
            
            // Konflik kelas
            if ($jadwal[$i]['kode_jurusan'] == $jadwal[$j]['kode_jurusan'] &&
                $jadwal[$i]['kelas'] == $jadwal[$j]['kelas'] &&
                $jadwal[$i]['hari'] == $jadwal[$j]['hari'] &&
                $jadwal[$i]['jam_mulai'] == $jadwal[$j]['jam_mulai']) {
                $stats['konflik_kelas']++;
            }
        }
    }
    
    return $stats;
}

// Ambil statistik untuk kedua pola
$stats_22223 = getScheduleStats('222-23');
$stats_3322 = getScheduleStats('33-22');

// Hitung skor optimalitas
function calculateOptimalityScore($stats) {
    $total_possible_score = 0;
    $actual_score = 0;
    
    // Hitung total jadwal yang mungkin (100%)
    $total_possible_score += $stats['total_kelas'] * 100;
    $actual_score += $stats['total_kelas'] * 100;
    
    // Hitung total SKS yang terjadwal (100%)
    $total_possible_score += $stats['total_sks'] * 100;
    $actual_score += $stats['total_sks'] * 100;
    
    // Hitung total jam yang terjadwal (100%)
    $total_possible_score += $stats['total_jam'] * 100;
    $actual_score += $stats['total_jam'] * 100;
    
    // Hitung konflik (0% untuk setiap konflik)
    $total_possible_score += ($stats['konflik_ruangan'] + $stats['konflik_dosen'] + $stats['konflik_kelas']) * 100;
    $actual_score += 0; // Konflik tidak memberikan nilai
    
    // Hitung distribusi hari (100% jika merata)
    $hari_values = array_values($stats['distribusi_hari']);
    $hari_std = stats_standard_deviation($hari_values);
    $total_possible_score += 100;
    $actual_score += (100 - ($hari_std * 10)); // Kurangi 10% untuk setiap standar deviasi
    
    // Hitung persentase fitness
    $fitness_percentage = ($actual_score / $total_possible_score) * 100;
    
    return round($fitness_percentage, 2); // Bulatkan ke 2 desimal
}

// Fungsi untuk menghitung standar deviasi
function stats_standard_deviation($array) {
    $count = count($array);
    if ($count < 2) return 0;
    
    $mean = array_sum($array) / $count;
    $squared_diff_sum = 0;
    
    foreach ($array as $value) {
        $squared_diff_sum += pow($value - $mean, 2);
    }
    
    return sqrt($squared_diff_sum / $count);
}

$score_22223 = calculateOptimalityScore($stats_22223);
$score_3322 = calculateOptimalityScore($stats_3322);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perbandingan Pola - Sistem Penjadwalan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <style>
        .stats-card {
            margin-bottom: 20px;
        }
        .optimal {
            background-color: rgba(212, 237, 218, 0.31);
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-chart-bar"></i> Perbandingan Pola Jadwal</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card stats-card <?php echo $score_22223 > $score_3322 ? 'optimal' : ''; ?>">
                                        <div class="card-header">
                                            <h4>Pola 222-23</h4>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Total Kelas:</strong> <?php echo $stats_22223['total_kelas']; ?></p>
                                            <p><strong>Total SKS:</strong> <?php echo $stats_22223['total_sks']; ?></p>
                                            <p><strong>Total Jam:</strong> <?php echo number_format($stats_22223['total_jam'], 1); ?></p>
                                            <p><strong>Konflik Ruangan:</strong> <?php echo $stats_22223['konflik_ruangan']; ?></p>
                                            <p><strong>Konflik Dosen:</strong> <?php echo $stats_22223['konflik_dosen']; ?></p>
                                            <p><strong>Konflik Kelas:</strong> <?php echo $stats_22223['konflik_kelas']; ?></p>
                                            <p><strong>Distribusi Hari:</strong></p>
                                            <ul>
                                                <?php foreach ($stats_22223['distribusi_hari'] as $hari => $jumlah): ?>
                                                <li><?php echo $hari; ?>: <?php echo $jumlah; ?> jadwal</li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <p><strong>Skor Optimalitas:</strong> <?php echo $score_22223; ?>%</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card stats-card <?php echo $score_3322 > $score_22223 ? 'optimal' : ''; ?>">
                                        <div class="card-header">
                                            <h4>Pola 33-22</h4>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Total Kelas:</strong> <?php echo $stats_3322['total_kelas']; ?></p>
                                            <p><strong>Total SKS:</strong> <?php echo $stats_3322['total_sks']; ?></p>
                                            <p><strong>Total Jam:</strong> <?php echo number_format($stats_3322['total_jam'], 1); ?></p>
                                            <p><strong>Konflik Ruangan:</strong> <?php echo $stats_3322['konflik_ruangan']; ?></p>
                                            <p><strong>Konflik Dosen:</strong> <?php echo $stats_3322['konflik_dosen']; ?></p>
                                            <p><strong>Konflik Kelas:</strong> <?php echo $stats_3322['konflik_kelas']; ?></p>
                                            <p><strong>Distribusi Hari:</strong></p>
                                            <ul>
                                                <?php foreach ($stats_3322['distribusi_hari'] as $hari => $jumlah): ?>
                                                <li><?php echo $hari; ?>: <?php echo $jumlah; ?> jadwal</li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <p><strong>Skor Optimalitas:</strong> <?php echo $score_3322; ?>%</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h3>Kesimpulan</h3>
                                <?php if ($score_22223 > $score_3322): ?>
                                    <div class="alert alert-success" style="background-color: rgba(212, 237, 218, 0.31);">
                                        <h4>Pola 222-23 Lebih Optimal</h4>
                                        <p>Pola 222-23 memiliki fitness lebih tinggi dengan perbedaan <?php echo round($score_22223 - $score_3322, 2); ?>%.</p>
                                    </div>
                                <?php elseif ($score_3322 > $score_22223): ?>
                                    <div class="alert alert-success" style="background-color: rgba(212, 237, 218, 0.31);">
                                        <h4>Pola 33-22 Lebih Optimal</h4>
                                        <p>Pola 33-22 memiliki fitness lebih tinggi dengan perbedaan <?php echo round($score_3322 - $score_22223, 2); ?>%.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <h4>Kedua Pola Sama Optimal</h4>
                                        <p>Kedua pola memiliki fitness yang sama.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 