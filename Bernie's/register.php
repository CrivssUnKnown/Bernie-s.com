<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: profil.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $telepon = sanitize($_POST['telepon']);
    $alamat = sanitize($_POST['alamat']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];
    
    // Validations
    if($password !== $konfirmasi) {
        $error = "Password dan Konfirmasi Password tidak cocok!";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT id_pelanggan FROM pelanggan WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if($check->get_result()->num_rows > 0) {
            $error = "Email sudah terdaftar! Silahkan login.";
        } else {
            $new_id = generateId($conn, 'pelanggan', 'id_pelanggan', 'PLG');
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO pelanggan (id_pelanggan, nama, email, telepon, alamat, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $new_id, $nama, $email, $telepon, $alamat, $hashed_password);
            
            if($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $error = "Gagal mendaftar. Silahkan coba lagi.";
            }
        }
    }
}

$page_title = 'Daftar Akun Baru';
include 'includes/header.php';
?>

<div class="section" style="min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
    <div class="form-card fade-in" style="width: 100%; max-width: 600px; padding: 40px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="font-family: 'Playfair Display', serif; color: var(--primary); font-size: 2rem;">Buat Akun</h2>
            <p style="color: var(--text-muted);">Gabung untuk mulai memesan kue kesukaanmu!</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label class="form-label">Nama Lengkap *</label>
                <div class="search-box">
                    <i class="fas fa-user"></i>
                    <input type="text" name="nama" class="search-input" required placeholder="Budi Santoso" style="width: 100%;" value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <div class="search-box">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="search-input" required placeholder="email@contoh.com" style="width: 100%;" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">No. Telepon / WhatsApp *</label>
                    <div class="search-box">
                        <i class="fab fa-whatsapp"></i>
                        <input type="text" name="telepon" class="search-input" required placeholder="0812xxxxxx" style="width: 100%;" value="<?= isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : '' ?>">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Alamat Lengkap Pengiriman *</label>
                <textarea name="alamat" class="form-control" required placeholder="Jl. Mawar No. 123, Jakarta..." style="min-height: 80px;"><?= isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : '' ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <div class="search-box">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="search-input" required placeholder="Minimal 6 karakter" minlength="6" style="width: 100%;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password *</label>
                    <div class="search-box">
                        <i class="fas fa-check-double"></i>
                        <input type="password" name="konfirmasi" class="search-input" required placeholder="Ulangi password" minlength="6" style="width: 100%;">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px; padding: 12px; font-size: 1.1rem;">Daftar Sekarang</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <p style="color: var(--text-muted);">Sudah punya akun? <a href="login.php" style="color: var(--primary); font-weight: bold; text-decoration: none;">Login disini</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
