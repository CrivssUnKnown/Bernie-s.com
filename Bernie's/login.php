<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: profil.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id_pelanggan, nama, email, password FROM pelanggan WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id_pelanggan'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            
            // Redirect based on intent
            if(isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: " . $redirect);
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak terdaftar!";
    }
}

$page_title = 'Login Pelanggan';
include 'includes/header.php';
?>

<div class="section" style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
    <div class="form-card fade-in" style="width: 100%; max-width: 450px; padding: 40px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="font-family: 'Playfair Display', serif; color: var(--primary); font-size: 2rem;">Selamat Datang!</h2>
            <p style="color: var(--text-muted);">Silahkan masuk ke akun Anda</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Pendaftaran berhasil! Silahkan login.
            </div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label class="form-label">Email</label>
                <div class="search-box">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="search-input" required placeholder="email@contoh.com" style="width: 100%;">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="search-box">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="search-input" required placeholder="********" style="width: 100%;">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px; padding: 12px; font-size: 1.1rem;">Masuk</button>
        </form>
        
        <div style="text-align: center; margin-top: 25px; border-top: 1px solid #eee; padding-top: 20px;">
            <p style="color: var(--text-muted);">Belum punya akun?</p>
            <a href="register.php" class="btn btn-outline" style="width: 100%; margin-top: 10px;">Daftar Sekarang</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
