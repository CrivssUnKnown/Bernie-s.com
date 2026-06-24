<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Edit Kategori';
$current_page = 'kategori';

$id = isset($_GET['id']) ? sanitize($_GET['id']) : '';

// Get existing data
$stmt = $conn->prepare("SELECT * FROM kategori WHERE id_kategori = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$kategori = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize($_POST['nama_kategori']);
    $icon = sanitize($_POST['icon']);
    $deskripsi = sanitize($_POST['deskripsi']);
    
    $upd_stmt = $conn->prepare("UPDATE kategori SET nama_kategori=?, icon=?, deskripsi=? WHERE id_kategori=?");
    $upd_stmt->bind_param("ssss", $nama, $icon, $deskripsi, $id);
    
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
            <label class="form-label">ID Kategori</label>
            <input type="text" class="form-control" value="<?= $kategori['id_kategori'] ?>" readonly>
        </div>
        
        <div class="form-group">
            <label class="form-label">Nama Kategori</label>
            <input type="text" class="form-control" name="nama_kategori" required value="<?= htmlspecialchars($kategori['nama_kategori']) ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Icon (Emoji)</label>
            <input type="text" class="form-control" name="icon" value="<?= htmlspecialchars($kategori['icon']) ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Deskripsi</label>
            <textarea class="form-control" name="deskripsi"><?= htmlspecialchars($kategori['deskripsi']) ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Kategori</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </form>
</div>

<?php include '../../includes/admin_footer.php'; ?>
