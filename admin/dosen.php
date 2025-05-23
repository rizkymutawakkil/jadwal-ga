<?php
require_once 'auth_check.php';
require_once 'config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $kode_dosen = $_POST['kode_dosen'];
            $nama_dosen = $_POST['nama_dosen'];

            $sql = "INSERT INTO dosen (kode_dosen, nama_dosen) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $kode_dosen, $nama_dosen);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Data dosen berhasil ditambahkan!'
                ];
                header('Location: dosen.php');
                exit();
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Data dosen gagal ditambahkan!'
                ];
                header('Location: dosen.php');
                exit();
            }
        } else if ($_POST['action'] == 'edit') {
            $kode_dosen = $_POST['kode_dosen'];
            $nama_dosen = $_POST['nama_dosen'];

            $sql = "UPDATE dosen SET nama_dosen = ? WHERE kode_dosen = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $nama_dosen, $kode_dosen);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Data dosen berhasil diperbarui!'
                ];
                header('Location: dosen.php');
                exit();
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Data dosen gagal diperbarui!'
                ];
                header('Location: dosen.php');
                exit();
            }
        } else if ($_POST['action'] == 'delete') {
            $kode_dosen = $_POST['kode_dosen'];
            
            try {
                // Mulai transaksi
                mysqli_begin_transaction($conn);
                
                // Cek apakah dosen masih terhubung dengan mata kuliah
                $sql_check_mk = "SELECT COUNT(*) as count FROM mata_kuliah_dosen WHERE kode_dosen = ?";
                $stmt_check_mk = mysqli_prepare($conn, $sql_check_mk);
                mysqli_stmt_bind_param($stmt_check_mk, "s", $kode_dosen);
                mysqli_stmt_execute($stmt_check_mk);
                $result_check_mk = mysqli_stmt_get_result($stmt_check_mk);
                $row_check_mk = mysqli_fetch_assoc($result_check_mk);
                
                // Cek apakah dosen masih terhubung dengan jadwal
                $sql_check_jadwal = "SELECT COUNT(*) as count FROM jadwal WHERE kode_dosen = ?";
                $stmt_check_jadwal = mysqli_prepare($conn, $sql_check_jadwal);
                mysqli_stmt_bind_param($stmt_check_jadwal, "s", $kode_dosen);
                mysqli_stmt_execute($stmt_check_jadwal);
                $result_check_jadwal = mysqli_stmt_get_result($stmt_check_jadwal);
                $row_check_jadwal = mysqli_fetch_assoc($result_check_jadwal);
                
                if ($row_check_mk['count'] > 0 || $row_check_jadwal['count'] > 0) {
                    // Hapus relasi dengan mata kuliah terlebih dahulu
                    $sql_delete_relasi = "DELETE FROM mata_kuliah_dosen WHERE kode_dosen = ?";
                    $stmt_delete_relasi = mysqli_prepare($conn, $sql_delete_relasi);
                    mysqli_stmt_bind_param($stmt_delete_relasi, "s", $kode_dosen);
                    if (!mysqli_stmt_execute($stmt_delete_relasi)) {
                        throw new Exception("Gagal menghapus relasi dengan mata kuliah");
                    }
                    
                    // Hapus relasi dengan jadwal jika ada
                    $sql_delete_jadwal = "DELETE FROM jadwal WHERE kode_dosen = ?";
                    $stmt_delete_jadwal = mysqli_prepare($conn, $sql_delete_jadwal);
                    mysqli_stmt_bind_param($stmt_delete_jadwal, "s", $kode_dosen);
                    if (!mysqli_stmt_execute($stmt_delete_jadwal)) {
                        throw new Exception("Gagal menghapus relasi dengan jadwal");
                    }
                }
                
                // Baru hapus dosen
                $sql = "DELETE FROM dosen WHERE kode_dosen = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "s", $kode_dosen);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Commit transaksi jika semua berhasil
                    mysqli_commit($conn);
                    $_SESSION['notification'] = [
                        'type' => 'success',
                        'message' => 'Data dosen berhasil dihapus!'
                    ];
                } else {
                    throw new Exception("Gagal menghapus data dosen");
                }
            } catch (mysqli_sql_exception $e) {
                // Rollback transaksi jika ada error
                mysqli_rollback($conn);
                error_log("Error menghapus dosen: " . $e->getMessage());
                if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'Dosen tidak dapat dihapus karena masih terhubung dengan data mata kuliah atau jadwal!'
                    ];
                } else {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'Terjadi kesalahan saat menghapus data dosen!'
                    ];
                }
            } catch (Exception $e) {
                // Rollback transaksi jika ada error
                mysqli_rollback($conn);
                error_log("Error menghapus dosen: " . $e->getMessage());
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Terjadi kesalahan saat menghapus data dosen!'
                ];
            }
            header('Location: dosen.php');
            exit();
        }
    }
}

// Fetch all dosen
$sql = "SELECT d.*, GROUP_CONCAT(mk.nama_mk) as mata_kuliah 
        FROM dosen d 
        LEFT JOIN mata_kuliah_dosen md ON d.kode_dosen = md.kode_dosen 
        LEFT JOIN mata_kuliah mk ON md.kode_mk = mk.kode_mk 
        GROUP BY d.kode_dosen 
        ORDER BY d.kode_dosen";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosen - Sistem Penjadwalan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/dosen.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3><i class="fas fa-chalkboard-teacher"></i> Data Dosen</h3>
                            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="fas fa-plus"></i> Tambah Dosen
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="dosenTable">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-hashtag"></i> Kode Dosen</th>
                                            <th><i class="fas fa-user"></i> Nama Dosen</th>
                                            <th><i class="fas fa-cogs"></i> Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>";
                                            echo "<td>" . $row['kode_dosen'] . "</td>";
                                            echo "<td>" . $row['nama_dosen'] . "</td>";
                                            echo "<td>
                                                    <button type='button' class='btn btn-info btn-sm view-btn' 
                                                            data-kode='" . $row['kode_dosen'] . "'
                                                            data-nama='" . $row['nama_dosen'] . "'
                                                            title='Lihat Detail'>
                                                        <i class='fas fa-eye'></i>
                                                    </button>
                                                    <button type='button' class='btn btn-warning btn-sm edit-btn' 
                                                            data-kode='" . $row['kode_dosen'] . "'
                                                            data-nama='" . $row['nama_dosen'] . "'
                                                            title='Edit Data'>
                                                        <i class='fas fa-pencil-alt'></i>
                                                    </button>
                                                    <button type='button' class='btn btn-danger btn-sm delete-btn' 
                                                            data-kode='" . $row['kode_dosen'] . "'
                                                            title='Hapus Data'>
                                                        <i class='fas fa-trash-alt'></i>
                                                    </button>
                                                </td>";
                                            echo "</tr>";
                                        }
                                        ?>
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Tambah Dosen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-hashtag"></i> Kode Dosen</label>
                            <input type="text" class="form-control" name="kode_dosen" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user"></i> Nama Dosen</label>
                            <input type="text" class="form-control" name="nama_dosen" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Dosen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-hashtag"></i> Kode Dosen</label>
                            <input type="text" class="form-control" name="kode_dosen" id="edit_kode" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user"></i> Nama Dosen</label>
                            <input type="text" class="form-control" name="nama_dosen" id="edit_nama" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-save"></i> Update
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
            $('#dosenTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                order: [[0, 'asc']],
                dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center"f>>rt<"d-flex justify-content-between align-items-center"<"d-flex align-items-center"i><"d-flex align-items-center"p>>',
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control');
                    $('.dataTables_length select').addClass('form-select');
                }
            });

            // View button click handler
            document.querySelectorAll('.view-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const kode = this.dataset.kode;
                    const nama = this.dataset.nama;

                    Swal.fire({
                        title: '<div class="d-flex align-items-center gap-2"><i class="fas fa-info-circle text-primary"></i> Detail Dosen</div>',
                        html: `
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <tbody>
                                        <tr>
                                            <th class="bg-light" style="width: 200px;">
                                                <i class="fas fa-hashtag text-primary me-2"></i> Kode Dosen
                                            </th>
                                            <td class="fw-bold">${kode}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">
                                                <i class="fas fa-user text-primary me-2"></i> Nama Dosen
                                            </th>
                                            <td>${nama}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        `,
                        customClass: {
                            popup: 'swal-wide',
                            title: 'text-primary fw-bold',
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

                    document.getElementById('edit_kode').value = kode;
                    document.getElementById('edit_nama').value = nama;

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
                        icon: 'warning',
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
                                <input type="hidden" name="kode_dosen" value="${kode}">
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