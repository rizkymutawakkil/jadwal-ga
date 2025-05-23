<?php
require_once 'auth_check.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => 'Anda harus login terlebih dahulu!'
    ];
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes') {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // First delete from mata_kuliah_dosen (foreign key constraint)
            $sql_delete_dosen = "DELETE FROM mata_kuliah_dosen";
            if (!mysqli_query($conn, $sql_delete_dosen)) {
                throw new Exception("Gagal menghapus data dosen mata kuliah: " . mysqli_error($conn));
            }
            
            // Then delete from mata_kuliah
            $sql_delete_mk = "DELETE FROM mata_kuliah";
            if (!mysqli_query($conn, $sql_delete_mk)) {
                throw new Exception("Gagal menghapus data mata kuliah: " . mysqli_error($conn));
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Semua data mata kuliah berhasil dihapus!'
            ];
            header('Location: mata_kuliah.php');
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Data Mata Kuliah - Sistem Penjadwalan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-exclamation-triangle text-danger"></i> Hapus Semua Data Mata Kuliah</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger">
                                <h4 class="alert-heading"><i class="fas fa-exclamation-circle"></i> PERINGATAN!</h4>
                                <p>Anda akan menghapus SEMUA data mata kuliah dari database. Tindakan ini:</p>
                                <ul>
                                    <li>Tidak dapat dibatalkan</li>
                                    <li>Menghapus semua data mata kuliah</li>
                                    <li>Menghapus semua relasi dosen dengan mata kuliah</li>
                                </ul>
                                <hr>
                                <p class="mb-0">Pastikan Anda telah membackup data sebelum melanjutkan.</p>
                            </div>
                            
                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua data mata kuliah? Tindakan ini tidak dapat dibatalkan!');">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="confirm" value="yes" id="confirmCheck" required>
                                    <label class="form-check-label" for="confirmCheck">
                                        Saya mengerti dan ingin melanjutkan penghapusan semua data mata kuliah
                                    </label>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash-alt"></i> Hapus Semua Data
                                    </button>
                                    <a href="mata_kuliah.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
</body>
</html> 