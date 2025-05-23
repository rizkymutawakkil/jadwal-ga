<?php
require_once 'auth_check.php';
require_once 'config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $kode_mk = $_POST['kode_mk'];
            $nama_mk = $_POST['nama_mk'];
            $sks = $_POST['sks'];
            $kode_jurusan = $_POST['kode_jurusan'];
            $dosen_list = isset($_POST['dosen_list']) ? $_POST['dosen_list'] : [];
            $kelas_list = isset($_POST['kelas']) ? $_POST['kelas'] : [];
            $kelas = implode(',', $kelas_list);

            // Check for duplicate kode_mk
            $check_sql = "SELECT COUNT(*) as count FROM mata_kuliah WHERE kode_mk = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "s", $kode_mk);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            $row = mysqli_fetch_assoc($check_result);

            if ($row['count'] > 0) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Kode Mata Kuliah sudah ada! Silakan gunakan kode mata kuliah yang berbeda'
                ];
                header('Location: mata_kuliah.php');
                exit();
            }
            
            // Insert mata kuliah
            $sql = "INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, kode_jurusan, kelas) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssiss", $kode_mk, $nama_mk, $sks, $kode_jurusan, $kelas);
            
            if (mysqli_stmt_execute($stmt)) {
                // Insert dosen yang mengampu
                foreach ($dosen_list as $kode_dosen) {
                    $sql_dosen = "INSERT INTO mata_kuliah_dosen (kode_mk, kode_dosen) VALUES (?, ?)";
                    $stmt_dosen = mysqli_prepare($conn, $sql_dosen);
                    mysqli_stmt_bind_param($stmt_dosen, "ss", $kode_mk, $kode_dosen);
                    mysqli_stmt_execute($stmt_dosen);
                }
                
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Data mata kuliah berhasil ditambahkan!'
                ];
                header('Location: mata_kuliah.php');
                exit();
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Gagal menambahkan data mata kuliah!'
                ];
                header('Location: mata_kuliah.php');
                exit();
            }
        } elseif ($_POST['action'] == 'edit') {
            $kode_mk = $_POST['kode_mk'];
            $nama_mk = $_POST['nama_mk'];
            $sks = $_POST['sks'];
            $kode_jurusan = $_POST['kode_jurusan'];
            $dosen_list = isset($_POST['dosen_list']) ? $_POST['dosen_list'] : [];
            $kelas_list = isset($_POST['kelas']) ? $_POST['kelas'] : [];
            $kelas = implode(',', $kelas_list);

            // Update mata kuliah
            $sql = "UPDATE mata_kuliah SET nama_mk = ?, sks = ?, kode_jurusan = ?, kelas = ? WHERE kode_mk = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sisss", $nama_mk, $sks, $kode_jurusan, $kelas, $kode_mk);
            
            if (mysqli_stmt_execute($stmt)) {
                // Delete existing dosen assignments
                $sql_delete = "DELETE FROM mata_kuliah_dosen WHERE kode_mk = ?";
                $stmt_delete = mysqli_prepare($conn, $sql_delete);
                mysqli_stmt_bind_param($stmt_delete, "s", $kode_mk);
                mysqli_stmt_execute($stmt_delete);

                // Insert new dosen assignments
                foreach ($dosen_list as $kode_dosen) {
                    $sql_dosen = "INSERT INTO mata_kuliah_dosen (kode_mk, kode_dosen) VALUES (?, ?)";
                    $stmt_dosen = mysqli_prepare($conn, $sql_dosen);
                    mysqli_stmt_bind_param($stmt_dosen, "ss", $kode_mk, $kode_dosen);
                    mysqli_stmt_execute($stmt_dosen);
                }

                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Data mata kuliah berhasil diupdate!'
                ];
                header('Location: mata_kuliah.php');
                exit();
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Gagal mengupdate data mata kuliah!'
                ];
                header('Location: mata_kuliah.php');
                exit();
            }
        } elseif ($_POST['action'] == 'delete') {
            $kode_mk = $_POST['kode_mk'];
            
            // Mulai transaksi
            mysqli_begin_transaction($conn);
            
            try {
                // Cek apakah mata kuliah digunakan di tabel jadwal
                $check_jadwal = "SELECT COUNT(*) as count FROM jadwal WHERE kode_mk = ?";
                $stmt_jadwal = mysqli_prepare($conn, $check_jadwal);
                mysqli_stmt_bind_param($stmt_jadwal, "s", $kode_mk);
                mysqli_stmt_execute($stmt_jadwal);
                $result_jadwal = mysqli_stmt_get_result($stmt_jadwal);
                $row_jadwal = mysqli_fetch_assoc($result_jadwal);
                
                if ($row_jadwal['count'] > 0) {
                    throw new Exception("Mata kuliah tidak dapat dihapus karena masih digunakan dalam jadwal!");
                }
                
                // Delete dosen assignments first
                $sql_delete_dosen = "DELETE FROM mata_kuliah_dosen WHERE kode_mk = ?";
                $stmt_delete_dosen = mysqli_prepare($conn, $sql_delete_dosen);
                mysqli_stmt_bind_param($stmt_delete_dosen, "s", $kode_mk);
                if (!mysqli_stmt_execute($stmt_delete_dosen)) {
                    throw new Exception("Gagal menghapus data dosen: " . mysqli_error($conn));
                }
                
                // Then delete mata kuliah
                $sql = "DELETE FROM mata_kuliah WHERE kode_mk = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "s", $kode_mk);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Gagal menghapus data mata kuliah: " . mysqli_error($conn));
                }
                
                // Commit transaksi jika semua berhasil
                mysqli_commit($conn);
                
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Data mata kuliah berhasil dihapus!'
                ];
                header('Location: mata_kuliah.php');
                exit();
            } catch (Exception $e) {
                // Rollback transaksi jika ada error
                mysqli_rollback($conn);
                
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => $e->getMessage()
                ];
                header('Location: mata_kuliah.php');
                exit();
            }
        }
    }
}

// Fetch all mata kuliah with jurusan and dosen
$sql = "SELECT mk.*, j.nama_jurusan, 
        GROUP_CONCAT(DISTINCT mkd.kode_dosen) as dosen_kodes,
        GROUP_CONCAT(DISTINCT d.nama_dosen SEPARATOR '|') as dosen_list,
        mk.kelas
        FROM mata_kuliah mk 
        LEFT JOIN jurusan j ON mk.kode_jurusan = j.kode_jurusan 
        LEFT JOIN mata_kuliah_dosen mkd ON mk.kode_mk = mkd.kode_mk
        LEFT JOIN dosen d ON mkd.kode_dosen = d.kode_dosen
        GROUP BY mk.kode_mk, mk.nama_mk, mk.sks, j.nama_jurusan
        ORDER BY mk.kode_mk";
$result = mysqli_query($conn, $sql);

// Fetch all jurusan for dropdown
$jurusan_sql = "SELECT * FROM jurusan ORDER BY kode_jurusan";
$jurusan_result = mysqli_query($conn, $jurusan_sql);

// Fetch all dosen for dropdown
$dosen_sql = "SELECT * FROM dosen ORDER BY kode_dosen";
$dosen_result = mysqli_query($conn, $dosen_sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mata Kuliah - Sistem Penjadwalan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/mata_kuliah.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3><i class="fas fa-book"></i> Data Mata Kuliah</h3>
                            <div class="d-flex gap-2">
                                <a href="clear_mata_kuliah.php" class="btn btn-danger">
                                    <i class="fas fa-trash-alt"></i> Hapus Semua Data
                                </a>
                                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addModal">
                                    <i class="fas fa-plus"></i> Tambah Mata Kuliah
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-fixed" id="mataKuliahTable">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-hashtag"></i> Kode</th>
                                            <th><i class="fas fa-book"></i> Nama MK</th>
                                            <th><i class="fas fa-clock"></i> SKS</th>
                                            <th><i class="fas fa-graduation-cap"></i> Jurusan</th>
                                            <th><i class="fas fa-user-tie"></i> Dosen Pengampu</th>
                                            <th><i class="fas fa-users"></i> Kelas</th>
                                            <th><i class="fas fa-cogs"></i> Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $row['kode_mk']; ?></td>
                                            <td><?php echo $row['nama_mk']; ?></td>
                                            <td><?php echo $row['sks']; ?></td>
                                            <td><?php echo $row['nama_jurusan']; ?></td>
                                            <td>
                                                <?php
                                                if (!empty($row['dosen_list'])) {
                                                    $dosen_array = explode('|', $row['dosen_list']);
                                                    echo '<ol class="mb-0 ps-3">';
                                                    foreach ($dosen_array as $dosen) {
                                                        echo '<li>' . htmlspecialchars(trim($dosen)) . '</li>';
                                                    }
                                                    echo '</ol>';
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo $row['kelas']; ?></td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm view-btn" 
                                                        data-kode="<?php echo $row['kode_mk']; ?>"
                                                        data-nama="<?php echo $row['nama_mk']; ?>"
                                                        data-jurusan="<?php echo $row['nama_jurusan']; ?>"
                                                        data-sks="<?php echo $row['sks']; ?>"
                                                        data-dosen="<?php echo $row['dosen_list'] ?? '-'; ?>"
                                                        data-kelas="<?php echo $row['kelas'] ?? ''; ?>"
                                                        title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-warning btn-sm edit-btn" 
                                                        data-kode="<?php echo $row['kode_mk']; ?>"
                                                        data-nama="<?php echo $row['nama_mk']; ?>"
                                                        data-jurusan="<?php echo $row['kode_jurusan']; ?>"
                                                        data-sks="<?php echo $row['sks']; ?>"
                                                        data-dosen-kodes="<?php echo $row['dosen_kodes'] ?? ''; ?>"
                                                        data-kelas="<?php echo $row['kelas'] ?? ''; ?>"
                                                        title="Edit Data">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm delete-btn" 
                                                        data-kode="<?php echo $row['kode_mk']; ?>"
                                                        title="Hapus Data">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i> Tambah Mata Kuliah Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="addDataForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-hashtag text-secondary me-2"></i> Kode Mata Kuliah
                                    </label>
                                    <input type="text" class="form-control" name="kode_mk" required 
                                           placeholder="Masukkan kode mata kuliah">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-book text-secondary me-2"></i> Nama Mata Kuliah
                                    </label>
                                    <input type="text" class="form-control" name="nama_mk" required 
                                           placeholder="Masukkan nama mata kuliah">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-graduation-cap text-secondary me-2"></i> Jurusan
                                    </label>
                                    <select class="form-select" name="kode_jurusan" required>
                                        <option value="">Pilih Jurusan</option>
                                        <?php while ($jurusan = mysqli_fetch_assoc($jurusan_result)): ?>
                                            <option value="<?php echo $jurusan['kode_jurusan']; ?>">
                                                <?php echo $jurusan['kode_jurusan'] . ' - ' . $jurusan['nama_jurusan']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-clock text-secondary me-2"></i> SKS
                                    </label>
                                    <input type="number" class="form-control" name="sks" required min="1" max="6" 
                                           placeholder="Masukkan jumlah SKS">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-user-tie text-secondary me-2"></i> Dosen Pengampu
                                    </label>
                                    <select class="form-select" name="dosen_list[]" multiple required>
                                        <?php 
                                        mysqli_data_seek($dosen_result, 0);
                                        while ($dosen = mysqli_fetch_assoc($dosen_result)): 
                                        ?>
                                            <option value="<?php echo $dosen['kode_dosen']; ?>">
                                                <?php echo $dosen['nama_dosen']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Tekan Ctrl (Windows) atau Command (Mac) untuk memilih lebih dari satu dosen
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-users text-dark me-2"></i> Kelas
                                    </label>
                                    <select class="form-select" name="kelas[]" multiple required>
                                        <option value="A">Kelas A</option>
                                        <option value="B">Kelas B</option>
                                        <option value="C">Kelas C</option>
                                        <option value="D">Kelas D</option>
                                        <option value="E">Kelas E</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Tekan Ctrl (Windows) atau Command (Mac) untuk memilih lebih dari satu kelas
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-save me-2"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i> Edit Mata Kuliah
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editDataForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-hashtag text-secondary me-2"></i> Kode Mata Kuliah
                                    </label>
                                    <input type="text" class="form-control" name="kode_mk" id="edit_kode" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-book text-secondary me-2"></i> Nama Mata Kuliah
                                    </label>
                                    <input type="text" class="form-control" name="nama_mk" id="edit_nama" required 
                                           placeholder="Masukkan nama mata kuliah">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-graduation-cap text-secondary me-2"></i> Jurusan
                                    </label>
                                    <select class="form-select" name="kode_jurusan" id="edit_jurusan" required>
                                        <option value="">Pilih Jurusan</option>
                                        <?php 
                                        mysqli_data_seek($jurusan_result, 0);
                                        while ($jurusan = mysqli_fetch_assoc($jurusan_result)): 
                                        ?>
                                            <option value="<?php echo $jurusan['kode_jurusan']; ?>">
                                                <?php echo $jurusan['kode_jurusan'] . ' - ' . $jurusan['nama_jurusan']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-clock text-secondary me-2"></i> SKS
                                    </label>
                                    <input type="number" class="form-control" name="sks" id="edit_sks" required min="1" max="6" 
                                           placeholder="Masukkan jumlah SKS">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-user-tie text-secondary me-2"></i> Dosen Pengampu
                                    </label>
                                    <select class="form-select" name="dosen_list[]" id="edit_dosen" multiple required>
                                        <?php 
                                        mysqli_data_seek($dosen_result, 0);
                                        while ($dosen = mysqli_fetch_assoc($dosen_result)): 
                                        ?>
                                            <option value="<?php echo $dosen['kode_dosen']; ?>">
                                                <?php echo $dosen['nama_dosen']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Tekan Ctrl (Windows) atau Command (Mac) untuk memilih lebih dari satu dosen
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-users text-secondary me-2"></i> Kelas
                                    </label>
                                    <select class="form-select" name="kelas[]" id="edit_kelas" multiple required>
                                        <option value="A">Kelas A</option>
                                        <option value="B">Kelas B</option>
                                        <option value="C">Kelas C</option>
                                        <option value="D">Kelas D</option>
                                        <option value="E">Kelas E</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Tekan Ctrl (Windows) atau Command (Mac) untuk memilih lebih dari satu kelas
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-save me-2"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Fungsi untuk menampilkan notifikasi
        function showNotification(type, message) {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Berhasil!' : 'Gagal!',
                text: message,
                showConfirmButton: type === 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                timer: type === 'success' ? 1000 : null
            });
        }

        // Tampilkan notifikasi jika ada
        <?php if (isset($_SESSION['notification'])): ?>
            showNotification('<?php echo $_SESSION['notification']['type']; ?>', '<?php echo $_SESSION['notification']['message']; ?>');
            <?php unset($_SESSION['notification']); ?>
        <?php endif; ?>

        $(document).ready(function() {
            // Custom sorting function for alphanumeric values
            jQuery.extend(jQuery.fn.dataTableExt.oSort, {
                "alphanum-pre": function(a) {
                    return a.replace(/[^\d]/g, '').length ? a.replace(/(\d+)/g, function(n) { return +n+100000 }) : a;
                },
                "alphanum-asc": function(a, b) {
                    return a < b ? -1 : a > b ? 1 : 0;
                },
                "alphanum-desc": function(a, b) {
                    return a < b ? 1 : a > b ? -1 : 0;
                }
            });

            $('#mataKuliahTable').DataTable({
                responsive: true,
                order: [[0, 'asc']], // Sort by first column (kode) in ascending order
                columnDefs: [
                    { 
                        type: 'alphanum', 
                        targets: 0 // Apply to first column (kode)
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data yang ditampilkan",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    zeroRecords: "Tidak ada data yang cocok",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]]
            });

            // Inisialisasi Select2 untuk dropdown dosen dan kelas
            $('select[name="dosen_list[]"]').select2({
                theme: 'bootstrap-5',
                placeholder: "Pilih Dosen Pengampu",
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Tidak ada hasil yang ditemukan";
                    }
                }
            });
            
            $('select[name="kelas[]"]').select2({
                theme: 'bootstrap-5',
                placeholder: "Pilih Kelas",
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Tidak ada hasil yang ditemukan";
                    }
                }
            });
            
            // Inisialisasi untuk modal edit
            $('#edit_dosen').select2({
                theme: 'bootstrap-5',
                placeholder: "Pilih Dosen Pengampu",
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Tidak ada hasil yang ditemukan";
                    }
                }
            });
            
            $('#edit_kelas').select2({
                theme: 'bootstrap-5',
                placeholder: "Pilih Kelas",
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Tidak ada hasil yang ditemukan";
                    }
                }
            });

            // View button click handler
            document.querySelectorAll('.view-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const kode = this.dataset.kode;
                    const nama = this.dataset.nama;
                    const sks = this.dataset.sks;
                    const jurusan = this.dataset.jurusan;
                    const dosen = this.dataset.dosen;
                    const kelas = this.dataset.kelas;

                    // Split kelas dan format ulang
                    const kelasArray = kelas.split(',');
                    const formattedKelas = kelasArray.map(k => `<span class="badge bg-secondary">${k}</span>`).join(' ');

                    // Split dosen dan format ulang
                    const dosenArray = dosen.split('|');
                    const formattedDosen = dosenArray.map(d => `<li>${d}</li>`).join('');

                    Swal.fire({
                        title: '<div class="d-flex align-items-center gap-2"><i class="fas fa-info-circle text-dark"></i> Detail Mata Kuliah</div>',
                        html: `
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <tbody>
                                        <tr>
                                            <th class="bg-light" style="width: 200px;">
                                                <i class="fas fa-hashtag text-dark me-2"></i> Kode Mata Kuliah
                                            </th>
                                            <td class="fw-bold">${kode}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">
                                                <i class="fas fa-book text-dark me-2"></i> Nama Mata Kuliah
                                            </th>
                                            <td class="fw-bold">${nama}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">
                                                <i class="fas fa-clock text-dark me-2"></i> SKS
                                            </th>
                                            <td class="fw-bold">${sks}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">
                                                <i class="fas fa-graduation-cap text-dark me-2"></i> Jurusan
                                            </th>
                                            <td class="fw-bold">${jurusan}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">
                                                <i class="fas fa-user-tie text-dark me-2"></i> Dosen Pengampu
                                            </th>
                                            <td>
                                                <ol class="mb-0 fw-bold">
                                                    ${formattedDosen}
                                                </ol>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">
                                                <i class="fas fa-building text-dark me-2"></i> Kelas
                                            </th>
                                            <td class="fw-bold">${formattedKelas}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        `,
                        customClass: {
                            popup: 'swal-wide',
                            title: 'text-dark fw-bold',
                            htmlContainer: 'text-start'
                        },
                        showCloseButton: true,
                        showConfirmButton: false,
                        width: '600px'
                    });
                });
            });

            // Edit button click handler
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const kode = this.dataset.kode;
                    const nama = this.dataset.nama;
                    const jurusan = this.dataset.jurusan;
                    const sks = this.dataset.sks;
                    const dosenKodes = this.dataset.dosenKodes ? this.dataset.dosenKodes.split(',') : [];
                    const kelas = this.dataset.kelas ? this.dataset.kelas.split(',') : [];

                    document.getElementById('edit_kode').value = kode;
                    document.getElementById('edit_nama').value = nama;
                    document.getElementById('edit_jurusan').value = jurusan;
                    document.getElementById('edit_sks').value = sks;
                    
                    // Set selected dosen
                    $('#edit_dosen').val(dosenKodes).trigger('change');
                    
                    // Set selected kelas
                    $('#edit_kelas').val(kelas).trigger('change');

                    var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                });
            });

            // Delete button click handler
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const kode = this.dataset.kode;
                    
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data yang dihapus tidak dapat dikembalikan!",
                        icon: 'dark',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: '<i class="fas fa-trash-alt"></i> Ya, hapus!',
                        cancelButtonText: '<i class="fas fa-times"></i> Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.innerHTML = `
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="kode_mk" value="${kode}">
                            `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
</body>
</html> 