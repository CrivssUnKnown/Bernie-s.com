<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Edit Pelanggan';
$current_page = 'pelanggan';

$id = isset($_GET['id']) ? sanitize($_GET['id']) : '';

// Get existing data
$stmt = $conn->prepare("SELECT * FROM pelanggan WHERE id_pelanggan = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$pelanggan = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $telepon = sanitize($_POST['telepon']);
    $alamat = sanitize($_POST['alamat']);
    
    $upd_stmt = $conn->prepare("UPDATE pelanggan SET nama=?, email=?, telepon=?, alamat=? WHERE id_pelanggan=?");
    $upd_stmt->bind_param("sssss", $nama, $email, $telepon, $alamat, $id);
    
    if ($upd_stmt->execute()) {
        header("Location: index.php?msg=success");
        exit;
    } else {
        $error = "Gagal mengupdate data: " . $conn->error;
    }
}

include '../../includes/admin_header.php';
?>

<div class="form-card fade-in">
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">ID Pelanggan</label>
            <input type="text" class="form-control" name="id_pelanggan" value="<?= $pelanggan['id_pelanggan'] ?>" readonly>
        </div>
        
        <div class="form-group">
            <label class="form-label">Nama Lengkap *</label>
            <input type="text" class="form-control" name="nama" required value="<?= htmlspecialchars($pelanggan['nama']) ?>">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($pelanggan['email']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">No. Telepon / WhatsApp</label>
                <input type="text" class="form-control" name="telepon" value="<?= htmlspecialchars($pelanggan['telepon']) ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Alamat Lengkap</label>
            <textarea class="form-control" name="alamat"><?= htmlspecialchars($pelanggan['alamat']) ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Pelanggan</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </form>
</div>

<?php include '../../includes/admin_footer.php'; ?>
