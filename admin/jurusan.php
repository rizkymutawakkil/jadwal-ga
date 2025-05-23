<?php
require_once 'auth_check.php';
require_once 'config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $kode_jurusan = $_POST['kode_jurusan'];
            $nama_jurusan = $_POST['nama_jurusan'];

            $sql = "INSERT INTO jurusan (kode_jurusan, nama_jurusan) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $kode_jurusan, $nama_jurusan);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Data jurusan berhasil ditambahkan!'
                ];
                header('Location: jurusan.php');
                exit();
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Data jurusan gagal ditambahkan!'
                ];
                header('Location: jurusan.php');
                exit();
            }
        } else if ($_POST['action'] == 'edit') {
            $kode_jurusan = $_POST['kode_jurusan'];
            $nama_jurusan = $_POST['nama_jurusan'];

            $sql = "UPDATE jurusan SET nama_jurusan = ? WHERE kode_jurusan = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $nama_jurusan, $kode_jurusan);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Data jurusan berhasil diperbarui!'
                ];
                header('Location: jurusan.php');
                exit();
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Data jurusan gagal diperbarui!'
                ];
                header('Location: jurusan.php');
                exit();
            }
        } else if ($_POST['action'] == 'delete') {
            $kode_jurusan = $_POST['kode_jurusan'];
            
            try {
                // Mulai transaksi
                mysqli_begin_transaction($conn);
                
                // Update foreign key constraint untuk tabel dosen
                $alter_dosen = "ALTER TABLE dosen DROP FOREIGN KEY dosen_ibfk_1";
                mysqli_query($conn, $alter_dosen);
                
                $alter_dosen_new = "ALTER TABLE dosen ADD CONSTRAINT dosen_ibfk_1 FOREIGN KEY (kode_jurusan) REFERENCES jurusan(kode_jurusan) ON DELETE SET NULL";
                mysqli_query($conn, $alter_dosen_new);
                
                // Cek apakah jurusan digunakan di tabel mata_kuliah
                $check_mk = "SELECT COUNT(*) as count FROM mata_kuliah WHERE kode_jurusan = ?";
                $stmt_mk = mysqli_prepare($conn, $check_mk);
                mysqli_stmt_bind_param($stmt_mk, "s", $kode_jurusan);
                mysqli_stmt_execute($stmt_mk);
                $result_mk = mysqli_stmt_get_result($stmt_mk);
                $row_mk = mysqli_fetch_assoc($result_mk);
                
                // Cek apakah jurusan digunakan di tabel ruangan
                $check_ruangan = "SELECT COUNT(*) as count FROM ruangan WHERE kode_jurusan = ?";
                $stmt_ruangan = mysqli_prepare($conn, $check_ruangan);
                mysqli_stmt_bind_param($stmt_ruangan, "s", $kode_jurusan);
                mysqli_stmt_execute($stmt_ruangan);
                $result_ruangan = mysqli_stmt_get_result($stmt_ruangan);
                $row_ruangan = mysqli_fetch_assoc($result_ruangan);
                
                if ($row_mk['count'] > 0 || $row_ruangan['count'] > 0) {
                    throw new Exception("Jurusan tidak dapat dihapus karena masih terhubung dengan data mata kuliah atau ruangan!");
                }
                
                // Hapus jurusan jika tidak ada relasi
                $sql = "DELETE FROM jurusan WHERE kode_jurusan = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "s", $kode_jurusan);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Commit transaksi jika semua berhasil
                    mysqli_commit($conn);
                    $_SESSION['notification'] = [
                        'type' => 'success',
                        'message' => 'Data jurusan berhasil dihapus!'
                    ];
                } else {
                    throw new Exception("Gagal menghapus data jurusan");
                }
            } catch (Exception $e) {
                // Rollback transaksi jika ada error
                mysqli_rollback($conn);
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => $e->getMessage()
                ];
            }
            header('Location: jurusan.php');
            exit();
        }
    }
}

// Fetch all jurusan
$sql = "SELECT j.*, 
        (SELECT COUNT(*) FROM mata_kuliah WHERE kode_jurusan = j.kode_jurusan) as jumlah_mk,
        (SELECT COUNT(*) FROM ruangan WHERE kode_jurusan = j.kode_jurusan) as jumlah_ruangan
        FROM jurusan j 
        ORDER BY j.kode_jurusan";
$result = mysqli_query($conn, $sql);

// Ambil distribusi SKS untuk setiap jurusan
$sks_distributions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $kode_jurusan = $row['kode_jurusan'];
    $sks_query = "SELECT sks, COUNT(*) as jumlah 
                  FROM mata_kuliah 
                  WHERE kode_jurusan = ? 
                  GROUP BY sks 
                  ORDER BY sks";
    $stmt = mysqli_prepare($conn, $sks_query);
    mysqli_stmt_bind_param($stmt, "s", $kode_jurusan);
    mysqli_stmt_execute($stmt);
    $sks_result = mysqli_stmt_get_result($stmt);
    
    $distribution = [];
    while ($sks_row = mysqli_fetch_assoc($sks_result)) {
        $distribution[] = $sks_row['sks'] . " SKS: " . $sks_row['jumlah'];
    }
    $sks_distributions[$kode_jurusan] = implode("<br>", $distribution);
}

// Reset result pointer
mysqli_data_seek($result, 0);

// Isi data jurusan secara otomatis jika belum ada data
$check_jurusan = "SELECT COUNT(*) as count FROM jurusan";
$result_check = mysqli_query($conn, $check_jurusan);
$row_check = mysqli_fetch_assoc($result_check);

if ($row_check['count'] == 0) {
    $jurusan_data = [
        ['IF', 'Informatika'],
        ['SI', 'Sistem Informasi'],
        ['TI', 'Teknik Informatika']
    ];
    
    foreach ($jurusan_data as $jurusan) {
        $kode = $jurusan[0];
        $nama = $jurusan[1];
        $query = "INSERT INTO jurusan (kode_jurusan, nama_jurusan) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $kode, $nama);
        mysqli_stmt_execute($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurusan - Sistem Penjadwalan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/jurusan.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3><i class="fas fa-university"></i> Data Jurusan</h3>
                            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="fas fa-plus"></i> Tambah Jurusan
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="jurusanTable">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-hashtag"></i> Kode Jurusan</th>
                                            <th><i class="fas fa-university"></i> Nama Jurusan</th>
                                            <th><i class="fas fa-book"></i> Jumlah MK</th>
                                            <th><i class="fas fa-door-open"></i> Jumlah Ruangan</th>
                                            <th><i class="fas fa-clock"></i> Distribusi SKS</th>
                                            <th><i class="fas fa-cogs"></i> Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>";
                                            echo "<td>" . $row['kode_jurusan'] . "</td>";
                                            echo "<td>" . $row['nama_jurusan'] . "</td>";
                                            echo "<td>" . $row['jumlah_mk'] . "</td>";
                                            echo "<td>" . $row['jumlah_ruangan'] . "</td>";
                                            echo "<td>" . (isset($sks_distributions[$row['kode_jurusan']]) ? $sks_distributions[$row['kode_jurusan']] : '-') . "</td>";
                                            echo "<td>
                                                    <button type='button' class='btn btn-info btn-sm view-btn' 
                                                            data-kode='" . $row['kode_jurusan'] . "'
                                                            data-nama='" . $row['nama_jurusan'] . "'
                                                            data-mk='" . $row['jumlah_mk'] . "'
                                                            data-ruangan='" . $row['jumlah_ruangan'] . "'
                                                            title='Lihat Detail'>
                                                        <i class='fas fa-eye'></i>
                                                    </button>
                                                    <button type='button' class='btn btn-warning btn-sm edit-btn' 
                                                            data-kode='" . $row['kode_jurusan'] . "'
                                                            data-nama='" . $row['nama_jurusan'] . "'
                                                            title='Edit Data'>
                                                        <i class='fas fa-pencil-alt'></i>
                                                    </button>
                                                    <button type='button' class='btn btn-danger btn-sm delete-btn' 
                                                            data-kode='" . $row['kode_jurusan'] . "'
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
                    <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Tambah Jurusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-hashtag"></i> Kode Jurusan</label>
                            <input type="text" class="form-control" name="kode_jurusan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-university"></i> Nama Jurusan</label>
                            <input type="text" class="form-control" name="nama_jurusan" required>
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
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Jurusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-hashtag"></i> Kode Jurusan</label>
                            <input type="text" class="form-control" name="kode_jurusan" id="edit_kode" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-university"></i> Nama Jurusan</label>
                            <input type="text" class="form-control" name="nama_jurusan" id="edit_nama" required>
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
            $('#jurusanTable').DataTable({
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
                    const mk = this.dataset.mk;
                    const ruangan = this.dataset.ruangan;

                    Swal.fire({
                        title: '<div class="d-flex align-items-center gap-2"><i class="fas fa-info-circle text-primary"></i> Detail Jurusan</div>',
                        html: `
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <tbody>
                                        <tr>
                                            <th class="bg-light" style="width: 200px;">
                                                <i class="fas fa-hashtag text-primary me-2"></i> Kode Jurusan
                                            </th>
                                            <td class="fw-bold">${kode}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">
                                                <i class="fas fa-university text-primary me-2"></i> Nama Jurusan
                                            </th>
                                            <td>${nama}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">
                                                <i class="fas fa-book text-primary me-2"></i> Jumlah Mata Kuliah
                                            </th>
                                            <td>${mk}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">
                                                <i class="fas fa-door-open text-primary me-2"></i> Jumlah Ruangan
                                            </th>
                                            <td>${ruangan}</td>
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
                                <input type="hidden" name="kode_jurusan" value="${kode}">
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