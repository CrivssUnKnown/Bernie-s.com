<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Tambah Pelanggan';
$current_page = 'pelanggan';

$new_id = generateId($conn, 'pelanggan', 'id_pelanggan', 'PLG');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $telepon = sanitize($_POST['telepon']);
    $alamat = sanitize($_POST['alamat']);
    
    $stmt = $conn->prepare("INSERT INTO pelanggan (id_pelanggan, nama, email, telepon, alamat) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $new_id, $nama, $email, $telepon, $alamat);
    
    if ($stmt->execute()) {
        header("Location: index.php?msg=success");
        exit;
    } else {
        $error = "Gagal menyimpan data: " . $conn->error;
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
            <input type="text" class="form-control" name="id_pelanggan" value="<?= $new_id ?>" readonly>
        </div>
        
        <div class="form-group">
            <label class="form-label">Nama Lengkap *</label>
            <input type="text" class="form-control" name="nama" required placeholder="Contoh: Budi Santoso">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" placeholder="contoh@email.com">
            </div>
            <div class="form-group">
                <label class="form-label">No. Telepon / WhatsApp</label>
                <input type="text" class="form-control" name="telepon" placeholder="0812xxxx">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Alamat Lengkap</label>
            <textarea class="form-control" name="alamat" placeholder="Alamat pengiriman..."></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Pelanggan</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </form>
</div>

<?php include '../../includes/admin_footer.php'; ?>
