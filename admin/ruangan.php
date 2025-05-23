<?php
require_once 'auth_check.php';
require_once 'config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $kode_ruangan = $_POST['kode_ruangan'];
            $kode_jurusan = isset($_POST['kode_jurusan']) ? $_POST['kode_jurusan'] : null;

            // Validasi input
            if (empty($kode_ruangan) || empty($kode_jurusan)) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Semua kolom harus diisi!'
                ];
                header('Location: ruangan.php');
                exit();
            }

            // Check for duplicate kode_ruangan
            $check_sql = "SELECT COUNT(*) as count FROM ruangan WHERE kode_ruangan = '$kode_ruangan'";
            $check_result = mysqli_query($conn, $check_sql);
            $row = mysqli_fetch_assoc($check_result);

            if ($row['count'] > 0) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Kode Ruangan sudah ada! Silakan gunakan kode ruangan yang berbeda.'
                ];
                header('Location: ruangan.php');
                exit();
            } else {
                // Check if kode_jurusan exists in jurusan table
                if ($kode_jurusan) {
                    $check_jurusan_sql = "SELECT COUNT(*) as count FROM jurusan WHERE kode_jurusan = '$kode_jurusan'";
                    $check_jurusan_result = mysqli_query($conn, $check_jurusan_sql);
                    $jurusan_row = mysqli_fetch_assoc($check_jurusan_result);

                    if ($jurusan_row['count'] == 0) {
                        $_SESSION['notification'] = [
                            'type' => 'error',
                            'message' => 'Kode Jurusan tidak valid! Silakan pilih jurusan yang tersedia.'
                        ];
                        header('Location: ruangan.php');
                        exit();
                    }
                }

                $sql = "INSERT INTO ruangan (kode_ruangan, kode_jurusan) 
                        VALUES ('$kode_ruangan', " . ($kode_jurusan ? "'$kode_jurusan'" : "NULL") . ")";
                if (mysqli_query($conn, $sql)) {
                    $_SESSION['notification'] = [
                        'type' => 'success',
                        'message' => 'Data ruangan berhasil ditambahkan!'
                    ];
                    header('Location: ruangan.php');
                    exit();
                } else {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'Gagal menambahkan data ruangan!'
                    ];
                    header('Location: ruangan.php');
                    exit();
                }
            }
        } elseif ($_POST['action'] == 'edit') {
            $id = $_POST['id'];
            $kode_ruangan = $_POST['kode_ruangan'];
            $kode_jurusan = isset($_POST['kode_jurusan']) ? $_POST['kode_jurusan'] : null;

            // Validasi input
            if (empty($kode_ruangan) || empty($kode_jurusan)) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Semua kolom harus diisi!'
                ];
                header('Location: ruangan.php');
                exit();
            }

            // Check for duplicate kode_ruangan (excluding current record)
            $check_sql = "SELECT COUNT(*) as count FROM ruangan WHERE kode_ruangan = '$kode_ruangan' AND id != $id";
            $check_result = mysqli_query($conn, $check_sql);
            $row = mysqli_fetch_assoc($check_result);

            if ($row['count'] > 0) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Kode Ruangan sudah ada! Silakan gunakan kode ruangan yang berbeda.'
                ];
                header('Location: ruangan.php');
                exit();
            }

            // Check if kode_jurusan exists in jurusan table
            if ($kode_jurusan) {
                $check_jurusan_sql = "SELECT COUNT(*) as count FROM jurusan WHERE kode_jurusan = '$kode_jurusan'";
                $check_jurusan_result = mysqli_query($conn, $check_jurusan_sql);
                $jurusan_row = mysqli_fetch_assoc($check_jurusan_result);

                if ($jurusan_row['count'] == 0) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'Kode Jurusan tidak valid! Silakan pilih jurusan yang tersedia.'
                    ];
                    header('Location: ruangan.php');
                    exit();
                }
            }

            $sql = "UPDATE ruangan SET 
                    kode_ruangan = '$kode_ruangan', 
                    kode_jurusan = " . ($kode_jurusan ? "'$kode_jurusan'" : "NULL") . " 
                    WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Data ruangan berhasil diperbarui!'
                ];
                header('Location: ruangan.php');
                exit();
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Gagal memperbarui data ruangan!'
                ];
                header('Location: ruangan.php');
                exit();
            }
        } elseif ($_POST['action'] == 'delete') {
            $id = $_POST['id'];
            $sql = "DELETE FROM ruangan WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Data ruangan berhasil dihapus!'
                ];
                header('Location: ruangan.php');
                exit();
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Gagal menghapus data ruangan!'
                ];
                header('Location: ruangan.php');
                exit();
            }
        }
    }
}

// Fetch all ruangan
$sql = "SELECT r.*, j.nama_jurusan 
        FROM ruangan r 
        LEFT JOIN jurusan j ON r.kode_jurusan = j.kode_jurusan 
        ORDER BY j.nama_jurusan ASC, 
                 SUBSTRING(r.kode_ruangan, 1, 1) ASC,
                 CAST(SUBSTRING(r.kode_ruangan, 2) AS UNSIGNED) ASC";
$result = mysqli_query($conn, $sql);

// Fetch all jurusan for dropdown
$jurusan_sql = "SELECT * FROM jurusan ORDER BY kode_jurusan";
$jurusan_result = mysqli_query($conn, $jurusan_sql);
$jurusan_data = [];
while ($row = mysqli_fetch_assoc($jurusan_result)) {
    $jurusan_data[] = $row;
}

// Isi data ruangan secara otomatis jika belum ada data
$check_ruangan = "SELECT COUNT(*) as count FROM ruangan";
$result_check = mysqli_query($conn, $check_ruangan);
$row_check = mysqli_fetch_assoc($result_check);

if ($row_check['count'] == 0) {
    // Check if jurusan exists first
    $check_jurusan = "SELECT kode_jurusan FROM jurusan WHERE kode_jurusan IN ('IF', 'SI', 'TI')";
    $jurusan_result = mysqli_query($conn, $check_jurusan);
    $available_jurusan = [];
    while ($row = mysqli_fetch_assoc($jurusan_result)) {
        $available_jurusan[] = $row['kode_jurusan'];
    }
    
    $ruangan_data = [
        ['R001', 'IF'],
        ['R002', 'SI'],
        ['R003', 'TI']
    ];
    
    foreach ($ruangan_data as $ruangan) {
        $kode = $ruangan[0];
        $jurusan = $ruangan[1];
        
        // Only insert if jurusan exists
        if (in_array($jurusan, $available_jurusan)) {
            $query = "INSERT INTO ruangan (kode_ruangan, kode_jurusan) VALUES ('$kode', '$jurusan')";
            mysqli_query($conn, $query);
        } else {
            // If jurusan doesn't exist, insert with NULL kode_jurusan
            $query = "INSERT INTO ruangan (kode_ruangan, kode_jurusan) VALUES ('$kode', NULL)";
            mysqli_query($conn, $query);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruangan - Sistem Penjadwalan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        /* Custom styles for view modal */
        .swal-wide {
            max-width: 600px !important;
        }
        .swal2-popup {
            font-size: 0.9rem !important;
        }
        .swal2-title {
            font-size: 1.5rem !important;
            margin-bottom: 1.5rem !important;
        }
        .swal2-html-container {
            margin: 0 !important;
        }
        .swal2-close {
            font-size: 1.5rem !important;
            color: #6c757d !important;
        }
        .swal2-close:hover {
            color: #343a40 !important;
        }
        .table th {
            font-weight: 600 !important;
        }
        .badge {
            font-size: 0.85rem !important;
            padding: 0.5em 0.75em !important;
        }
        .table td, .table th {
            padding: 0.75rem !important;
            vertical-align: middle !important;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.02) !important;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3><i class="fas fa-door-open"></i> Data Ruangan</h3>
                            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="fas fa-plus"></i> Tambah Ruangan
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="ruanganTable">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-hashtag"></i> Kode Ruangan</th>
                                            <th><i class="fas fa-graduation-cap"></i> Jurusan</th>
                                            <th><i class="fas fa-cogs"></i> Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $row['kode_ruangan']; ?></td>
                                            <td><?php echo $row['nama_jurusan'] ?? '-'; ?></td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm view-btn" 
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-kode="<?php echo $row['kode_ruangan']; ?>"
                                                        data-jurusan="<?php echo $row['nama_jurusan'] ?? '-'; ?>"
                                                        title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-warning btn-sm edit-btn" 
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-kode="<?php echo $row['kode_ruangan']; ?>"
                                                        data-jurusan="<?php echo $row['kode_jurusan'] ?? ''; ?>"
                                                        title="Edit Data">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm delete-btn" 
                                                        data-id="<?php echo $row['id']; ?>"
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Tambah Ruangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-hashtag"></i> Kode Ruangan</label>
                            <input type="text" class="form-control" name="kode_ruangan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-graduation-cap"></i> Jurusan</label>
                            <select class="form-select" name="kode_jurusan" required>
                                <option value="">Pilih Jurusan</option>
                                <?php foreach ($jurusan_data as $jurusan): ?>
                                    <option value="<?php echo $jurusan['kode_jurusan']; ?>">
                                        <?php echo $jurusan['kode_jurusan'] . ' - ' . $jurusan['nama_jurusan']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Ruangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-hashtag"></i> Kode Ruangan</label>
                            <input type="text" class="form-control" name="kode_ruangan" id="edit_kode" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-graduation-cap"></i> Jurusan</label>
                            <select class="form-select" name="kode_jurusan" id="edit_jurusan" required>
                                <option value="">Pilih Jurusan</option>
                                <?php foreach ($jurusan_data as $jurusan): ?>
                                    <option value="<?php echo $jurusan['kode_jurusan']; ?>">
                                        <?php echo $jurusan['kode_jurusan'] . ' - ' . $jurusan['nama_jurusan']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.13.7/sorting/natural.js"></script>
    <script>
        $(document).ready(function() {
            // Tampilkan notifikasi jika ada
            <?php if (isset($_SESSION['notification'])): ?>
                showNotification('<?php echo $_SESSION['notification']['type']; ?>', '<?php echo $_SESSION['notification']['message']; ?>');
                <?php unset($_SESSION['notification']); ?>
            <?php endif; ?>

            $('#ruanganTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                order: [[1, 'asc'], [0, 'asc']],
                columnDefs: [
                    { type: 'natural', targets: 0 }
                ],
                dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center"f>>rt<"d-flex justify-content-between align-items-center"<"d-flex align-items-center"i><"d-flex align-items-center"p>>',
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control');
                    $('.dataTables_length select').addClass('form-select');
                }
            });
        });

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

        // View button click handler
        document.querySelectorAll('.view-btn').forEach(button => {
            button.addEventListener('click', function() {
                const kode = this.dataset.kode;
                const nama = this.dataset.jurusan;
                const kapasitas = this.dataset.kapasitas;
                const jenis = this.dataset.jenis;
                const lokasi = this.dataset.lokasi;

                Swal.fire({
                    title: '<div class="d-flex align-items-center gap-2"><i class="fas fa-info-circle text-dark"></i> Detail Ruangan</div>',
                    html: `
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <tbody>
                                    <tr>
                                        <th class="bg-light" style="width: 200px;">
                                            <i class="fas fa-hashtag text-dark me-2"></i> Kode Ruangan
                                        </th>
                                        <td class="fw-bold">${kode}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">
                                            <i class="fas fa-graduation-cap text-dark me-2"></i> Jurusan
                                        </th>
                                        <td class="fw-bold">${nama}</td>
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
                const id = this.dataset.id;
                const kode = this.dataset.kode;
                const jurusan = this.dataset.jurusan;

                document.getElementById('edit_id').value = id;
                document.getElementById('edit_kode').value = kode;
                document.getElementById('edit_jurusan').value = jurusan;

                var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                editModal.show();
            });
        });

        // Delete button click handler
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                
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
                            <input type="hidden" name="id" value="${id}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
</body>
</html> 