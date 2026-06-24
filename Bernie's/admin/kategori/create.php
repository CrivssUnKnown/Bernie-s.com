<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Tambah Kategori';
$current_page = 'kategori';

// Generate ID Kategori (Custom without Auto Increment)
$new_id = generateId($conn, 'kategori', 'id_kategori', 'KAT');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize($_POST['nama_kategori']);
    $icon = sanitize($_POST['icon']);
    $deskripsi = sanitize($_POST['deskripsi']);
    
    $stmt = $conn->prepare("INSERT INTO kategori (id_kategori, nama_kategori, icon, deskripsi) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $new_id, $nama, $icon, $deskripsi);
    
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
            <label class="form-label">ID Kategori</label>
            <input type="text" class="form-control" name="id_kategori" value="<?= $new_id ?>" readonly>
            <small style="color: var(--text-muted);">*ID di-generate otomatis tanpa Auto Increment</small>
        </div>
        
        <div class="form-group">
            <label class="form-label">Nama Kategori</label>
            <input type="text" class="form-control" name="nama_kategori" required placeholder="Contoh: Classic Cakes">
        </div>
        
        <div class="form-group">
            <label class="form-label">Icon (Emoji)</label>
            <input type="text" class="form-control" name="icon" placeholder="Contoh: 🎂">
        </div>
        
        <div class="form-group">
            <label class="form-label">Deskripsi</label>
            <textarea class="form-control" name="deskripsi" placeholder="Deskripsi kategori..."></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Kategori</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </form>
</div>

<?php include '../../includes/admin_footer.php'; ?>
