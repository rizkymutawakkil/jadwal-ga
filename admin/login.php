<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Ganti dengan logika autentikasi yang sesuai
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['user'] = [
            'username' => $username,
            'role' => 'admin'
        ];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Penjadwalan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.34), rgba(0, 0, 0, 0.26)), url('assets/img/81310.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.37);
            padding: 2rem;
            width: 100%;
            max-width: 450px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.2);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header i {
            font-size: 3rem;
            color: #34495e;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        .login-header h1 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        .login-header p {
            color: #34495e;
            margin-bottom: 0;
        }
        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            border-color: #34495e;
        }
        .btn-login {
            background: #34495e;
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: #34495e;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .alert {
            border-radius: 0.5rem;
            border: none;
        }
        .input-group .btn-outline-secondary {
            border-color: #ced4da;
            color: #6c757d;
            transition: all 0.3s ease;
        }
        .input-group .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            border-color: #ced4da;
            color: #6c757d;
        }
        .input-group .form-control:focus + .btn-outline-secondary {
            border-color: #86b7fe;
        }
        .form-label {
            color: #2c3e50;
            font-weight: 600;
        }
        .form-label i {
            color: #34495e;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-calendar-check"></i>
            <h1>Sistem Penjadwalan Mata Kuliah</h1>
            <p>Silakan login untuk melanjutkan</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-user me-2"></i>Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-4">
                <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" name="password" id="password" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            // Toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle the eye icon
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html> 