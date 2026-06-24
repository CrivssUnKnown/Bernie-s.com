<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Edit Produk';
$current_page = 'produk';

$id = isset($_GET['id']) ? sanitize($_GET['id']) : '';

// Get existing data
$stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$produk = $result->fetch_assoc();
$categories = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kategori = sanitize($_POST['id_kategori']);
    $nama = sanitize($_POST['nama_produk']);
    $harga = str_replace(['Rp', '.', ' '], '', sanitize($_POST['harga']));
    $stok = (int) sanitize($_POST['stok']);
    $rating = (float) sanitize($_POST['rating']);
    $deskripsi = sanitize($_POST['deskripsi']);
    
    // Handle Upload Gambar
    $gambar = $produk['gambar']; // keep old by default
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload = uploadImage($_FILES['gambar'], '../../uploads/');
        if ($upload) {
            // Delete old file if exists
            if ($gambar && file_exists('../../uploads/'.$gambar)) {
                unlink('../../uploads/'.$gambar);
            }
            $gambar = $upload;
        } else {
            $error = "Gagal upload gambar. Pastikan format JPG/PNG dan ukuran maksimal 5MB.";
        }
    }
    
    if (!isset($error)) {
        $upd_stmt = $conn->prepare("UPDATE produk SET id_kategori=?, nama_produk=?, harga=?, stok=?, gambar=?, deskripsi=?, rating=? WHERE id_produk=?");
        $upd_stmt->bind_param("ssssssds", $id_kategori, $nama, $harga, $stok, $gambar, $deskripsi, $rating, $id);
        
        if ($upd_stmt->execute()) {
            header("Location: index.php?msg=success");
            exit;
        } else {
            $error = "Gagal mengupdate data: " . $conn->error;
        }
    }
}

include '../../includes/admin_header.php';
?>

<div class="form-card fade-in" style="max-width: 900px;">
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">ID Produk</label>
                <input type="text" class="form-control" name="id_produk" value="<?= $produk['id_produk'] ?>" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Produk *</label>
                <input type="text" class="form-control" name="nama_produk" required value="<?= htmlspecialchars($produk['nama_produk']) ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Kategori *</label>
                <select name="id_kategori" class="form-control" required>
                    <?php while($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id_kategori'] ?>" <?= $produk['id_kategori'] == $cat['id_kategori'] ? 'selected' : '' ?>>
                            <?= $cat['icon'] ?> <?= htmlspecialchars($cat['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Rating (0-5)</label>
                <input type="number" class="form-control" name="rating" min="0" max="5" step="0.1" value="<?= $produk['rating'] ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Harga (Rp) *</label>
                <input type="number" class="form-control" name="harga" required min="0" value="<?= (int)$produk['harga'] ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Stok *</label>
                <input type="number" class="form-control" name="stok" required min="0" value="<?= $produk['stok'] ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Gambar Produk</label>
            <div class="image-upload-area" onclick="document.getElementById('gambar').click()">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Klik untuk ganti gambar (Biarkan kosong jika tidak ingin ganti)</p>
                <input type="file" id="gambar" name="gambar" accept="image/*">
            </div>
            <img id="imagePreview" class="image-preview" src="<?= $produk['gambar'] ? '../../uploads/'.$produk['gambar'] : '' ?>" style="<?= $produk['gambar'] ? 'display:inline-block;' : '' ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Deskripsi Lengkap</label>
            <textarea class="form-control" name="deskripsi"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Produk</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </form>
</div>

<?php include '../../includes/admin_footer.php'; ?>
