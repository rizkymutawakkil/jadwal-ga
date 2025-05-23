<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script>
      if (localStorage.getItem('sidebarCollapsed') === 'true') {
        document.documentElement.classList.add('sidebar-collapsed');
      }
    </script>
</head>
<body>
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-container text-center">
                <h3>SISTEM PENJADWALAN</h3>
            </div>
        </div>
        <div class="sidebar-content">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'jurusan.php' ? 'active' : ''; ?>" href="jurusan.php">
                        <i class="fas fa-university"></i>
                        <span>Jurusan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'dosen.php' ? 'active' : ''; ?>" href="dosen.php">
                        <i class="fas fa-user-tie"></i>
                        <span>Dosen</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'mata_kuliah.php' ? 'active' : ''; ?>" href="mata_kuliah.php">
                        <i class="fas fa-book"></i>
                        <span>Mata Kuliah</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'ruangan.php' ? 'active' : ''; ?>" href="ruangan.php">
                        <i class="fas fa-door-open"></i>
                        <span>Ruangan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="generate_jadwal.php" class="nav-link <?php echo $current_page == 'generate_jadwal.php' ? 'active' : ''; ?>">
                        <i class="fas fa-magic"></i>
                        <span>Generate Jadwal</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="jadwal.php?pola=222-23" class="nav-link <?php echo ($current_page == 'jadwal.php' && isset($_GET['pola']) && $_GET['pola'] == '222-23') ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Jadwal 222-23</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="jadwal.php?pola=33-22" class="nav-link <?php echo ($current_page == 'jadwal.php' && isset($_GET['pola']) && $_GET['pola'] == '33-22') ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Jadwal 33-22</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'perbandingan_pola.php' ? 'active' : ''; ?>" href="perbandingan_pola.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Perbandingan Pola</span>
                    </a>
                </li>
            </ul>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mainContent = document.querySelector('.main-content');
            const toggleIcon = sidebarToggle.querySelector('i');

            // Gunakan class pada <html> untuk collapsed
            const isSidebarCollapsed = document.documentElement.classList.contains('sidebar-collapsed');
            if (isSidebarCollapsed) {
                mainContent.classList.add('expanded');
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
            } else {
                toggleIcon.classList.remove('fa-chevron-right');
                toggleIcon.classList.add('fa-chevron-left');
            }

            sidebarToggle.addEventListener('click', function() {
                document.documentElement.classList.toggle('sidebar-collapsed');
                mainContent.classList.toggle('expanded');
                // Toggle icon
                if (document.documentElement.classList.contains('sidebar-collapsed')) {
                    toggleIcon.classList.remove('fa-chevron-left');
                    toggleIcon.classList.add('fa-chevron-right');
                } else {
                    toggleIcon.classList.remove('fa-chevron-right');
                    toggleIcon.classList.add('fa-chevron-left');
                }
                // Save state to localStorage
                localStorage.setItem('sidebarCollapsed', document.documentElement.classList.contains('sidebar-collapsed'));
            });
        });
    </script>
</body>
</html> 