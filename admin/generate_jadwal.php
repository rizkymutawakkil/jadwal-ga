<?php
require_once 'auth_check.php';
require_once 'config/database.php';

// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Class JadwalGenerator
 * Kelas untuk menangani proses generate jadwal menggunakan algoritma genetika
 */
class JadwalGenerator {
    private $conn;          // Koneksi database
    private $pola;          // Pola jadwal (222-23 atau 33-22)
    private $data;          // Data mata kuliah dan ruangan
    private $population_size = 200;  
    private $generations = 1000;     
    private $mutation_rate = 0.3;    
    private $elitism = 10;           
    private $cache = [];             // Cache untuk menyimpan data yang sering digunakan

    public function __construct($conn, $pola = '222-23') {
        $this->conn = $conn;
        $this->pola = $pola;
        $this->data = $this->getData();
    }

    /**
     * Mengambil data mata kuliah dan ruangan dari database dengan optimasi
     * @return array|null Data mata kuliah dan ruangan
     */
    private function getData() {
        // Set batas GROUP_CONCAT untuk menghindari truncate data
        mysqli_query($this->conn, "SET SESSION group_concat_max_len = 1000000");
        
        // Query untuk mengambil data mata kuliah beserta dosen dan jurusan dengan optimasi
        $sql_mk = "SELECT mk.*, 
                          GROUP_CONCAT(DISTINCT d.kode_dosen) as kode_dosen, 
                          GROUP_CONCAT(DISTINCT d.nama_dosen) as nama_dosen, 
                          j.nama_jurusan 
                   FROM mata_kuliah mk 
                   LEFT JOIN mata_kuliah_dosen md ON mk.kode_mk = md.kode_mk 
                   LEFT JOIN dosen d ON md.kode_dosen = d.kode_dosen 
                   LEFT JOIN jurusan j ON mk.kode_jurusan = j.kode_jurusan 
                   WHERE mk.kode_mk IS NOT NULL
                   GROUP BY mk.kode_mk, mk.nama_mk, mk.sks, mk.kode_jurusan, j.nama_jurusan";
        
        // Tambahkan index jika belum ada
        $this->addIndexes();
        
        $result_mk = mysqli_query($this->conn, $sql_mk);

        if (!$result_mk) {
            error_log("Error in getData() - mata kuliah query: " . mysqli_error($this->conn));
            return null;
        }

        // Proses data mata kuliah dengan caching
        $mata_kuliah = array();
        while ($row = mysqli_fetch_assoc($result_mk)) {
            $key = $row['kode_mk'];
            if (!isset($this->cache['mata_kuliah'][$key])) {
                $this->cache['mata_kuliah'][$key] = array(
                    'kode_mk' => $row['kode_mk'],
                    'nama_mk' => $row['nama_mk'],
                    'sks' => $row['sks'],
                    'kode_jurusan' => $row['kode_jurusan'],
                    'nama_jurusan' => $row['nama_jurusan'],
                    'kode_dosen' => $row['kode_dosen'] ? explode(',', $row['kode_dosen']) : [],
                    'nama_dosen' => $row['nama_dosen'] ? explode(',', $row['nama_dosen']) : [],
                    'kelas' => $row['kelas']
                );
            }
            $mata_kuliah[] = $this->cache['mata_kuliah'][$key];
        }
        
        if (empty($mata_kuliah)) {
            return ['success' => false, 'message' => 'Tidak ada data mata kuliah'];
        }
        
        // Query untuk mengambil data ruangan dengan optimasi
        $ruangan_sql = "SELECT r.*, j.kode_jurusan 
                        FROM ruangan r 
                        LEFT JOIN jurusan j ON r.kode_jurusan = j.kode_jurusan
                        WHERE r.kode_ruangan IS NOT NULL";
        $ruangan_result = mysqli_query($this->conn, $ruangan_sql);
        
        if (!$ruangan_result) {
            error_log("Error in getData() - ruangan query: " . mysqli_error($this->conn));
            return null;
        }
        
        // Proses data ruangan dengan caching
        $ruangan = [];
        while ($row = mysqli_fetch_assoc($ruangan_result)) {
            $key = $row['kode_ruangan'];
            if (!isset($this->cache['ruangan'][$key])) {
                $this->cache['ruangan'][$key] = $row;
            }
            $ruangan[] = $this->cache['ruangan'][$key];
        }
        
        if (empty($ruangan)) {
            return ['success' => false, 'message' => 'Tidak ada data ruangan'];
        }
        
        return [
            'mata_kuliah' => $mata_kuliah,
            'ruangan' => $ruangan
        ];
    }

    /**
     * Menambahkan index pada tabel untuk optimasi query
     */
    private function addIndexes() {
        $indexes = [
            "ALTER TABLE mata_kuliah ADD INDEX idx_kode_mk (kode_mk)",
            "ALTER TABLE mata_kuliah ADD INDEX idx_kode_jurusan (kode_jurusan)",
            "ALTER TABLE mata_kuliah_dosen ADD INDEX idx_kode_mk (kode_mk)",
            "ALTER TABLE mata_kuliah_dosen ADD INDEX idx_kode_dosen (kode_dosen)",
            "ALTER TABLE ruangan ADD INDEX idx_kode_jurusan (kode_jurusan)"
        ];

        foreach ($indexes as $sql) {
            try {
                mysqli_query($this->conn, $sql);
            } catch (Exception $e) {
                // Index mungkin sudah ada, lanjutkan
                continue;
            }
        }
    }

    /**
     * Memulai proses generate jadwal
     * @return array Hasil generate jadwal
     */
    public function generateSchedule() {
        // Set waktu eksekusi maksimum menjadi 15 menit
        set_time_limit(900);
        
        if (empty($this->data)) {
            return ['success' => false, 'message' => 'Data tidak lengkap'];
        }

        if (is_array($this->data) && isset($this->data['success']) && !$this->data['success']) {
            return $this->data;
        }

        // Load fungsi pattern sesuai pola yang dipilih
        if ($this->pola == '222-23') {
            require_once 'patterns/pattern_22223.php';
            $chromosome = createChromosome22223($this->data['mata_kuliah'], $this->data['ruangan']);
        } else {
            require_once 'patterns/pattern_3322.php';
            $chromosome = createChromosome3322($this->data['mata_kuliah'], $this->data['ruangan']);
        }

        if (empty($chromosome)) {
            return ['success' => false, 'message' => 'Gagal membuat kromosom awal'];
        }

        // Jalankan algoritma genetika
        $best_schedule = $this->geneticAlgorithm($chromosome);

        if (empty($best_schedule)) {
            return ['success' => false, 'message' => 'Gagal menghasilkan jadwal'];
        }

        // Simpan jadwal ke database
        if ($this->saveScheduleToDatabase($best_schedule)) {
            return ['success' => true, 'message' => 'Jadwal berhasil digenerate'];
        } else {
            return ['success' => false, 'message' => 'Gagal menyimpan jadwal ke database'];
        }
    }

    /**
     * Algoritma genetika untuk mengoptimasi jadwal dengan optimasi
     */
    private function geneticAlgorithm($initial_chromosome) {
        $population = [$initial_chromosome];
        $best_fitness = 0;
        $best_schedule = null;
        $stagnation_counter = 0;
        $last_best_fitness = 0;
        $start_time = microtime(true);
        $checkpoint_interval = 50; // Cek waktu setiap 50 generasi

        // Iterasi untuk setiap generasi
        for ($gen = 0; $gen < $this->generations; $gen++) {
            // Cek waktu eksekusi setiap checkpoint_interval generasi
            if ($gen % $checkpoint_interval === 0) {
                $current_time = microtime(true);
                $elapsed_time = $current_time - $start_time;
                
                // Jika sudah melewati 14 menit, hentikan proses
                if ($elapsed_time > 840) {
                    break;
                }
            }

            // Evaluasi fitness setiap kromosom dengan caching
            $fitness_scores = [];
            foreach ($population as $index => $chromosome) {
                $cache_key = md5(json_encode($chromosome));
                if (isset($this->cache['fitness'][$cache_key])) {
                    $fitness = $this->cache['fitness'][$cache_key];
                } else {
                    $fitness = $this->calculateFitness($chromosome);
                    $this->cache['fitness'][$cache_key] = $fitness;
                }
                $fitness_scores[] = $fitness;
                
                if ($fitness > $best_fitness) {
                    $best_fitness = $fitness;
                    $best_schedule = $chromosome;
                    $stagnation_counter = 0;
                }
            }

            // Cek stagnasi dengan threshold yang lebih rendah
            if ($best_fitness == $last_best_fitness) {
                $stagnation_counter++;
                if ($stagnation_counter > 20) { // Dikurangi dari 30
                    $this->mutation_rate = 0.4; // Dikurangi dari 0.5
                }
            } else {
                $stagnation_counter = 0;
                $this->mutation_rate = 0.3;
            }
            $last_best_fitness = $best_fitness;

            // Jika fitness sudah cukup baik, hentikan iterasi
            if ($best_fitness > 850) { // Dikurangi dari 900
                break;
            }

            // Seleksi dan reproduksi untuk generasi baru
            $new_population = [];
            
            // Elitism: Simpan kromosom terbaik
            $elite_indices = array_keys($fitness_scores);
            array_multisort($fitness_scores, SORT_DESC, $elite_indices);
            for ($i = 0; $i < min($this->elitism, count($population)); $i++) {
                $new_population[] = $population[$elite_indices[$i]];
            }

            // Isi populasi baru
            while (count($new_population) < $this->population_size) {
                // Tournament selection dengan ukuran tournament yang lebih kecil
                $parent1 = $this->tournamentSelection($population, $fitness_scores, 8);
                $parent2 = $this->tournamentSelection($population, $fitness_scores, 8);
                
                // Crossover dengan probabilitas tinggi
                $child = $this->crossover($parent1, $parent2);
                
                // Mutasi adaptif
                $child = $this->mutate($child, $this->data, $this->pola);
                
                $new_population[] = $child;
            }
            
            $population = $new_population;
        }

        return $best_schedule;
    }

    /**
     * Menghitung fitness score untuk sebuah jadwal
     * @param array $chromosome Jadwal yang akan dievaluasi
     * @return int Skor fitness
     */
    private function calculateFitness($chromosome) {
        if (empty($chromosome)) {
            return 0;
        }

        $fitness = 1000;  // Skor awal
        $conflicts = 0;   // Jumlah konflik
        
        // Tracker untuk mendeteksi konflik
        $schedule_tracker = [
            'ruangan' => [],  // Konflik ruangan
            'dosen' => [],    // Konflik dosen
            'kelas' => []     // Konflik kelas
        ];
        
        foreach ($chromosome as $schedule) {
            if (!isset($schedule['hari'], $schedule['kode_jam'], 
                      $schedule['kode_ruangan'], $schedule['kode_dosen'],
                      $schedule['kode_jurusan'], $schedule['kelas'])) {
                continue;
            }
            
            $time_key = $schedule['hari'] . '_' . $schedule['kode_jam'];
            
            // Cek konflik ruangan
            $room_key = $time_key . '_' . $schedule['kode_ruangan'];
            if (isset($schedule_tracker['ruangan'][$room_key])) {
                $conflicts++;
            } else {
                $schedule_tracker['ruangan'][$room_key] = true;
            }
            
            // Cek konflik dosen
            $dosen_key = $time_key . '_' . $schedule['kode_dosen'];
            if (isset($schedule_tracker['dosen'][$dosen_key])) {
                $conflicts++;
            } else {
                $schedule_tracker['dosen'][$dosen_key] = true;
            }
            
            // Cek konflik kelas
            $kelas_key = $time_key . '_' . $schedule['kode_jurusan'] . '_' . $schedule['kelas'];
            if (isset($schedule_tracker['kelas'][$kelas_key])) {
                $conflicts++;
            } else {
                $schedule_tracker['kelas'][$kelas_key] = true;
            }
        }
        
        // Kurangi skor berdasarkan jumlah konflik
        $fitness -= ($conflicts * 50);
        return max(0, $fitness);
    }

    /**
     * Tournament selection untuk memilih parent
     * @param array $population Populasi kromosom
     * @param array $fitness_scores Skor fitness untuk setiap kromosom
     * @return array Parent terpilih
     */
    private function tournamentSelection($population, $fitness_scores, $tournament_size = 3) {
        $best_idx = array_rand($population);
        $best_fitness = $fitness_scores[$best_idx];
        
        // Pilih kromosom terbaik dari tournament
        for ($i = 1; $i < $tournament_size; $i++) {
            $idx = array_rand($population);
            if ($fitness_scores[$idx] > $best_fitness) {
                $best_idx = $idx;
                $best_fitness = $fitness_scores[$idx];
            }
        }
        
        return $population[$best_idx];
    }

    /**
     * Crossover untuk menggabungkan dua parent
     * @param array $parent1 Parent pertama
     * @param array $parent2 Parent kedua
     * @return array Child hasil crossover
     */
    private function crossover($parent1, $parent2) {
        if (empty($parent1) || empty($parent2)) {
            return null;
        }

        $child = [];
        $crossover_point = rand(0, count($parent1) - 1);
        
        // Gabungkan gen dari kedua parent
        for ($i = 0; $i < count($parent1); $i++) {
            if ($i < $crossover_point) {
                $child[] = $parent1[$i];
            } else {
                $child[] = $parent2[$i];
            }
        }
        
        return $child;
    }

    /**
     * Mutasi untuk menambah variasi pada jadwal
     * @param array $chromosome Kromosom yang akan dimutasi
     * @param array $data Data mata kuliah dan ruangan
     * @param string $pola Pola jadwal
     * @return array Kromosom hasil mutasi
     */
    private function mutate($chromosome, $data, $pola) {
        if (empty($chromosome)) {
            return null;
        }

        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        
        foreach ($chromosome as &$jadwal) {
            if (rand(0, 100) < $this->mutation_rate * 100) {
                // Mutasi hari
                $jadwal['hari'] = $hari[array_rand($hari)];
                
                // Mutasi ruangan
                $available_rooms = array_filter($data['ruangan'], function($r) use ($jadwal) {
                    return (isset($r['kode_jurusan']) && $r['kode_jurusan'] === $jadwal['kode_jurusan']) || 
                           (isset($r['kode_jurusan']) && $r['kode_jurusan'] === null);
                });
                
                if (!empty($available_rooms)) {
                    $random_room = array_rand($available_rooms);
                    $jadwal['kode_ruangan'] = $available_rooms[$random_room]['kode_ruangan'];
                }
            }
        }
        
        return $chromosome;
    }

    /**
     * Menyimpan jadwal ke database
     * @param array $schedule Jadwal yang akan disimpan
     * @return bool Status penyimpanan
     */
    private function saveScheduleToDatabase($schedule) {
        if (empty($schedule)) {
            error_log("Error: Schedule is empty");
            return false;
        }

        // Mulai transaksi
        mysqli_begin_transaction($this->conn);
        
        try {
            // Hapus jadwal lama terlebih dahulu
            $delete_sql = "DELETE FROM jadwal WHERE pola = ?";
            $delete_stmt = mysqli_prepare($this->conn, $delete_sql);
            if (!$delete_stmt) {
                throw new Exception("Error preparing delete statement: " . mysqli_error($this->conn));
            }
            
            mysqli_stmt_bind_param($delete_stmt, "s", $this->pola);
            if (!mysqli_stmt_execute($delete_stmt)) {
                throw new Exception("Error executing delete statement: " . mysqli_error($this->conn));
            }

            // Simpan jadwal baru
            $sql = "INSERT INTO jadwal (kode_mk, kode_ruangan, hari, jam_mulai, jam_selesai, kelas, pola) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($this->conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing insert statement: " . mysqli_error($this->conn));
            }

            $success_count = 0;
            foreach ($schedule as $item) {
                // Validasi data sebelum insert
                if (empty($item['kode_mk']) || empty($item['kode_ruangan']) || 
                    empty($item['hari']) || empty($item['jam_mulai']) || 
                    empty($item['jam_selesai']) || empty($item['kelas'])) {
                    error_log("Invalid schedule item: " . json_encode($item));
                    continue;
                }

                mysqli_stmt_bind_param($stmt, "sssssss", 
                    $item['kode_mk'],
                    $item['kode_ruangan'],
                    $item['hari'],
                    $item['jam_mulai'],
                    $item['jam_selesai'],
                    $item['kelas'],
                    $this->pola
                );
                
                if (!mysqli_stmt_execute($stmt)) {
                    error_log("Error inserting schedule item: " . mysqli_error($this->conn));
                    continue;
                }
                $success_count++;
            }

            if ($success_count === 0) {
                throw new Exception("No schedule items were successfully inserted");
            }

            // Commit transaksi
            mysqli_commit($this->conn);
            error_log("Successfully saved $success_count schedule items");
            return true;

        } catch (Exception $e) {
            // Rollback transaksi jika terjadi error
            mysqli_rollback($this->conn);
            error_log("Error saving schedule: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Menghapus jadwal berdasarkan pola
     * @return array Hasil penghapusan
     */
    public function deleteSchedule() {
        // Cek apakah ada jadwal dengan pola tersebut
        $check_sql = "SELECT COUNT(*) as total FROM jadwal WHERE pola = ?";
        $check_stmt = mysqli_prepare($this->conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $this->pola);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        $row = mysqli_fetch_assoc($result);
        
        if ($row['total'] == 0) {
            return ['success' => false, 'message' => 'Tidak ada jadwal dengan pola ' . $this->pola . ' yang dapat dihapus'];
        }

        // Mulai transaksi
        mysqli_begin_transaction($this->conn);
        
        try {
            // Hapus jadwal
            $delete_sql = "DELETE FROM jadwal WHERE pola = ?";
            $delete_stmt = mysqli_prepare($this->conn, $delete_sql);
            mysqli_stmt_bind_param($delete_stmt, "s", $this->pola);
            
            if (!mysqli_stmt_execute($delete_stmt)) {
                throw new Exception("Gagal menghapus jadwal");
            }
            
            // Commit transaksi
            mysqli_commit($this->conn);
            
            return [
                'success' => true, 
                'message' => 'Jadwal dengan pola ' . $this->pola . ' berhasil dihapus',
                'deleted_count' => mysqli_stmt_affected_rows($delete_stmt)
            ];
        } catch (Exception $e) {
            // Rollback transaksi jika terjadi error
            mysqli_rollback($this->conn);
            return ['success' => false, 'message' => 'Terjadi kesalahan saat menghapus jadwal: ' . $e->getMessage()];
        }
    }
}

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pola = isset($_POST['pola']) ? $_POST['pola'] : '222-23';
    $action = isset($_POST['action']) ? $_POST['action'] : 'generate';
    
    $generator = new JadwalGenerator($conn, $pola);
    
    if ($action === 'delete') {
        $result = $generator->deleteSchedule();
    } else {
        $result = $generator->generateSchedule();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Jadwal - Sistem Penjadwalan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <style>
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .btn-delete {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        .btn-delete:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }
        .btn-delete:active {
            transform: translateY(0);
            box-shadow: none;
        }
        .btn-delete i {
            font-size: 1rem;
        }
        /* Loading Screen Styles */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            color: white;
        }
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        .loading-text {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .loading-time {
            font-size: 14px;
            color: #ccc;
            margin-bottom: 10px;
        }
        .progress-container {
            width: 80%;
            max-width: 500px;
            background-color: #f3f3f3;
            border-radius: 10px;
            margin: 10px 0;
        }
        .progress-bar {
            width: 0%;
            height: 20px;
            background-color: #4CAF50;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        .progress-text {
            font-size: 14px;
            margin-top: 5px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <!-- Loading Screen -->
    <div class="loading-screen" style="display: none;">
        <div class="loading-spinner"></div>
        <div class="loading-text">Sedang memproses...</div>
        <div class="loading-time">Waktu: <span id="loadingTime">0</span> detik</div>
        <div class="progress-container">
            <div class="progress-bar" id="progressBar"></div>
        </div>
        <div class="progress-text" id="progressText">0%</div>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-magic me-2"></i>Generate Jadwal</h2>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="generateForm">
                                <div class="mb-4">
                                    <label for="pola" class="form-label">Pilih Pola Jadwal</label>
                                    <select class="form-select" id="pola" name="pola" required>
                                        <option value="222-23" <?php echo (isset($pola) && $pola == '222-23') ? 'selected' : ''; ?>>Pola 222-23</option>
                                        <option value="33-22" <?php echo (isset($pola) && $pola == '33-22') ? 'selected' : ''; ?>>Pola 33-22</option>
                                    </select>
                                </div>

                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle me-2"></i>Informasi</h5>
                                    <p>Sebelum melakukan generate jadwal, pastikan:</p>
                                    <ul>
                                        <li>Data mata kuliah sudah lengkap</li>
                                        <li>Data dosen sudah lengkap</li>
                                        <li>Data ruangan sudah lengkap</li>
                                        <li>Data jurusan sudah lengkap</li>
                                    </ul>
                                </div>

                                <div class="action-buttons">
                                    <button type="submit" name="action" value="generate" class="btn btn-primary">
                                        <i class="fas fa-sync-alt"></i> Generate Jadwal
                                    </button>
                                    <button type="submit" name="action" value="delete" class="btn btn-delete" id="deleteButton">
                                        <i class="fas fa-trash-alt"></i> Hapus Jadwal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
        // Fungsi untuk menampilkan SweetAlert2
        function showAlert(title, text, icon, confirmText, cancelText = null) {
            const options = {
                title: title,
                text: text,
                icon: icon,
                confirmButtonText: confirmText,
                confirmButtonColor: '#3085d6',
                allowOutsideClick: false
            };

            if (cancelText) {
                options.showCancelButton = true;
                options.cancelButtonText = cancelText;
                options.cancelButtonColor = '#6c757d';
            }

            return Swal.fire(options);
        }

        // Fungsi untuk menampilkan loading screen
        function showLoading() {
            const loadingScreen = document.querySelector('.loading-screen');
            const loadingTime = document.getElementById('loadingTime');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            loadingScreen.style.display = 'flex';
            
            let seconds = 0;
            let progress = 0;
            const timer = setInterval(() => {
                seconds++;
                loadingTime.textContent = seconds;
                
                // Update progress bar (simulasi)
                if (progress < 90) {
                    progress += Math.random() * 2;
                    if (progress > 90) progress = 90;
                    progressBar.style.width = progress + '%';
                    progressText.textContent = Math.round(progress) + '%';
                }
            }, 1000);
            
            return timer;
        }

        // Fungsi untuk menyembunyikan loading screen
        function hideLoading(timer) {
            const loadingScreen = document.querySelector('.loading-screen');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            
            // Set progress ke 100% sebelum menyembunyikan
            progressBar.style.width = '100%';
            progressText.textContent = '100%';
            
            setTimeout(() => {
                loadingScreen.style.display = 'none';
                clearInterval(timer);
            }, 500);
        }

        // Event listener untuk tombol generate
        document.querySelector('button[value="generate"]').addEventListener('click', function(e) {
            e.preventDefault();
            const pola = document.getElementById('pola').value;
            
            showAlert(
                'Konfirmasi Generate Jadwal',
                `Apakah Anda yakin ingin generate jadwal dengan pola ${pola}?`,
                'question',
                'Ya, Generate',
                'Batal'
            ).then((result) => {
                if (result.isConfirmed) {
                    const timer = showLoading();
                    document.getElementById('generateForm').submit();
                }
            });
        });

        // Event listener untuk tombol hapus
        document.getElementById('deleteButton').addEventListener('click', function(e) {
            e.preventDefault();
            const pola = document.getElementById('pola').value;
            
            Swal.fire({
                title: '<strong>Konfirmasi Hapus Jadwal</strong>',
                html: `
                    <div style="text-align: left; margin: 20px 0;">
                        <p style="margin-bottom: 15px; font-size: 16px;">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Anda akan menghapus semua jadwal dengan pola <strong>${pola}</strong>
                        </p>
                        <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;">
                            <p style="margin: 0; color: #856404;">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                Tindakan ini akan menghapus semua data jadwal dan tidak dapat dibatalkan.
                            </p>
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash-alt me-2"></i>Ya, Hapus Sekarang',
                cancelButtonText: '<i class="fas fa-times me-2"></i>Batal',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan loading screen
                    const timer = showLoading();
                    
                    // Kirim form dengan action delete
                    const form = document.getElementById('generateForm');
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'delete';
                    form.appendChild(actionInput);
                    form.submit();
                }
            });
        });

        // Tampilkan hasil operasi jika ada
        <?php if (isset($result)): ?>
            <?php 
            $execution_time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
            $execution_time = round($execution_time, 2);
            ?>
            <?php if ($result['success']): ?>
                <?php if ($action === 'delete'): ?>
                    Swal.fire({
                        title: '<strong>Berhasil!</strong>',
                        html: `
                            <div style="text-align: left; margin: 20px 0;">
                                <p style="margin-bottom: 10px; font-size: 16px;">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <?php echo $result['message']; ?>
                                </p>
                                <p style="margin-bottom: 10px; color: #6c757d;">
                                    <i class="fas fa-clock me-2"></i>
                                    Waktu proses: <?php echo $execution_time; ?> detik
                                </p>
                                <?php if (isset($result['deleted_count'])): ?>
                                <p style="margin-bottom: 0; color: #6c757d;">
                                    <i class="fas fa-list me-2"></i>
                                    Jumlah jadwal yang dihapus: <?php echo $result['deleted_count']; ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: '<i class="fas fa-check me-2"></i>OK',
                        confirmButtonColor: '#28a745'
                    });
                <?php else: ?>
                    showAlert(
                        'Berhasil!',
                        '<?php echo $result['message']; ?>\nWaktu proses: <?php echo $execution_time; ?> detik',
                        'success',
                        '<i class="fas fa-calendar-alt me-2"></i>Lihat Jadwal',
                        '<i class="fas fa-times me-2"></i>Tetap di Halaman'
                    ).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'jadwal.php?pola=<?php echo urlencode($pola); ?>';
                        }
                    });
                <?php endif; ?>
            <?php else: ?>
                Swal.fire({
                    title: '<strong>Gagal!</strong>',
                    html: `
                        <div style="text-align: left; margin: 20px 0;">
                            <p style="margin-bottom: 10px; font-size: 16px;">
                                <i class="fas fa-times-circle text-danger me-2"></i>
                                <?php echo $result['message']; ?>
                            </p>
                            <p style="margin-bottom: 0; color: #6c757d;">
                                <i class="fas fa-clock me-2"></i>
                                Waktu proses: <?php echo $execution_time; ?> detik
                            </p>
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonText: '<i class="fas fa-times me-2"></i>OK',
                    confirmButtonColor: '#dc3545'
                });
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html> 