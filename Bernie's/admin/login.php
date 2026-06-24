<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if(isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id_admin, username, password, nama FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if(password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_nama'] = $admin['nama'];
            
            header("Location: index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}

$page_title = 'Admin Login';
include '../includes/admin_header.php';
?>

<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);">
    <div class="form-card fade-in" style="width: 100%; max-width: 400px; padding: 40px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <h2 style="font-family: 'Playfair Display', serif; color: var(--primary); margin-bottom: 10px; font-size: 2rem;">🧁 Bernie's Lovely</h2>
        <p style="color: var(--text-muted); margin-bottom: 30px;">Login Panel Administrator</p>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger" style="text-align: left; font-size: 0.9rem;">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST" style="text-align: left;">
            <div class="form-group">
                <label class="form-label">Username</label>
                <div class="search-box">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" class="search-input" required placeholder="admin" style="width: 100%;">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="search-box">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="search-input" required placeholder="admin123" style="width: 100%;">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px; padding: 12px;">Login Admin</button>
        </form>
        <div style="margin-top: 20px;">
            <a href="/Bernie's/" style="font-size: 0.9rem; color: var(--text-muted);"><i class="fas fa-arrow-left"></i> Kembali ke Website</a>
        </div>
    </div>
</div>

<script src="/Bernie's/assets/js/script.js"></script>
</body>
</html>
