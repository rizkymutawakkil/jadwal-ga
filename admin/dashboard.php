<?php
require_once 'auth_check.php';
require_once 'config/database.php';

// Buat tabel activity_log jika belum ada
$sql_create_table = "CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity VARCHAR(255) NOT NULL,
    icon VARCHAR(50) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql_create_table);

// Query untuk menghitung total data
$sql_mk = "SELECT COUNT(*) as total FROM mata_kuliah";
$result_mk = mysqli_query($conn, $sql_mk);
$row_mk = mysqli_fetch_assoc($result_mk);
$total_mk = $row_mk['total'];

$sql_dosen = "SELECT COUNT(*) as total FROM dosen";
$result_dosen = mysqli_query($conn, $sql_dosen);
$row_dosen = mysqli_fetch_assoc($result_dosen);
$total_dosen = $row_dosen['total'];

$sql_ruangan = "SELECT COUNT(*) as total FROM ruangan";
$result_ruangan = mysqli_query($conn, $sql_ruangan);
$row_ruangan = mysqli_fetch_assoc($result_ruangan);
$total_ruangan = $row_ruangan['total'];

$sql_jurusan = "SELECT COUNT(*) as total FROM jurusan";
$result_jurusan = mysqli_query($conn, $sql_jurusan);
$row_jurusan = mysqli_fetch_assoc($result_jurusan);
$total_jurusan = $row_jurusan['total'];

// Query untuk menghitung mata kuliah yang tidak terjadwal
$sql_unscheduled = "SELECT COUNT(*) as total FROM mata_kuliah mk 
                    LEFT JOIN jadwal j ON mk.kode_mk = j.kode_mk 
                    WHERE j.kode_mk IS NULL";
$result_unscheduled = mysqli_query($conn, $sql_unscheduled);
$row_unscheduled = mysqli_fetch_assoc($result_unscheduled);
$total_unscheduled = $row_unscheduled['total'];

// Query untuk menghitung total jadwal per minggu
$sql_weekly = "SELECT COUNT(DISTINCT kode_mk) as total FROM jadwal";
$result_weekly = mysqli_query($conn, $sql_weekly);
$row_weekly = mysqli_fetch_assoc($result_weekly);
$total_weekly = $row_weekly['total'];

// Query untuk total SKS dari semua mata kuliah
$sql_total_sks = "SELECT SUM(sks) as total_sks FROM mata_kuliah";
$result_sks = mysqli_query($conn, $sql_total_sks);
$row_sks = mysqli_fetch_assoc($result_sks);
$total_sks = $row_sks['total_sks'] ?? 0;

// Tambahkan beberapa data aktivitas contoh jika tabel kosong
$sql_check_empty = "SELECT COUNT(*) as count FROM activity_log";
$result_check = mysqli_query($conn, $sql_check_empty);
$row_check = mysqli_fetch_assoc($result_check);

if ($row_check['count'] == 0) {
    $sample_activities = [
        ['activity' => 'Jadwal berhasil digenerate', 'icon' => 'calendar-check'],
        ['activity' => 'Mata kuliah baru ditambahkan', 'icon' => 'book'],
        ['activity' => 'Dosen baru ditambahkan', 'icon' => 'user-plus'],
        ['activity' => 'Ruangan baru ditambahkan', 'icon' => 'door-open'],
        ['activity' => 'Jurusan baru ditambahkan', 'icon' => 'graduation-cap']
    ];

    foreach ($sample_activities as $activity) {
        $sql_insert = "INSERT INTO activity_log (activity, icon) VALUES ('" . 
                     mysqli_real_escape_string($conn, $activity['activity']) . "', '" . 
                     mysqli_real_escape_string($conn, $activity['icon']) . "')";
        mysqli_query($conn, $sql_insert);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Penjadwalan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card welcome-card">
                        <div class="card-body">
                            <h2><i class="fas fa-calendar-check me-3"></i>Selamat Datang di Sistem Penjadwalan</h2>
                            <p>Sistem ini membantu Anda mengelola jadwal perkuliahan dengan lebih efisien.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
            <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-graduation-cap stat-icon text-dark"></i>
                                <div>
                                    <div class="stat-value"><?php echo $total_jurusan; ?></div>
                                    <div class="stat-label">Jurusan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chalkboard-teacher stat-icon text-dark"></i>
                                <div>
                                    <div class="stat-value"><?php echo $total_dosen; ?></div>
                                    <div class="stat-label">Dosen</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-book stat-icon text-dark"></i>
                                <div>
                                    <div class="stat-value"><?php echo $total_mk; ?></div>
                                    <div class="stat-label">Mata Kuliah</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-door-open stat-icon text-dark"></i>
                                <div>
                                    <div class="stat-value"><?php echo $total_ruangan; ?></div>
                                    <div class="stat-label">Ruangan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

            <div class="row equal-height">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
                        </div>
                        <div class="card-body">
                            <div class="action-grid">
                                <a href="mata_kuliah.php" class="action-btn">
                                    <i class="fas fa-plus"></i>
                                    <span>Tambah Mata Kuliah</span>
                                </a>
                                <a href="dosen.php" class="action-btn">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Tambah Dosen</span>
                                </a>
                                <a href="ruangan.php" class="action-btn">
                                    <i class="fas fa-door-open"></i>
                                    <span>Tambah Ruangan</span>
                                </a>
                                <a href="generate_jadwal.php" class="action-btn">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Generate Jadwal</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-bar me-2"></i>Statistik Jadwal</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="stats-item">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-book-open text-dark"></i>
                                    <div>
                                        <div class="fw-bold">Total SKS Mata Kuliah</div>
                                        <div class="text-muted"><?php echo $total_sks; ?> SKS</div>
                                    </div>
                                </div>
                            </div>
                            <div class="stats-item">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-book-open text-dark"></i>
                                    <div>
                                        <div class="fw-bold">Mata Kuliah Tidak Terjadwal</div>
                                        <div class="text-muted"><?php echo $total_unscheduled; ?> mata kuliah</div>
                                    </div>
                                </div>
                            </div>
                            <div class="stats-item">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-week text-dark"></i>
                                    <div>
                                        <div class="fw-bold">Mata Kuliah Terjadwal/Minggu</div>
                                        <div class="text-muted"><?php echo $total_weekly; ?> dari <?php echo $total_mk; ?> mata kuliah</div>
                                    </div>
                                </div>
                            </div>
                            <div class="stats-item p-3">
                                <div class="progress">
                                    <div class="progress-bar bg-secondary text-white" role="progressbar" style="width: <?php echo ($total_weekly / $total_mk * 100); ?>%" aria-valuenow="<?php echo $total_weekly; ?>" aria-valuemin="0" aria-valuemax="<?php echo $total_mk; ?>">
                                        <?php echo round($total_weekly / $total_mk * 100); ?>%
                                    </div>
                                </div>
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