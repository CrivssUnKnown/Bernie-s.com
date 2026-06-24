<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Tambah Produk';
$current_page = 'produk';

$new_id = generateId($conn, 'produk', 'id_produk', 'PRD');
$categories = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kategori = sanitize($_POST['id_kategori']);
    $nama = sanitize($_POST['nama_produk']);
    $harga = str_replace(['Rp', '.', ' '], '', sanitize($_POST['harga'])); // clean format if any
    $stok = (int) sanitize($_POST['stok']);
    $rating = (float) sanitize($_POST['rating']);
    $deskripsi = sanitize($_POST['deskripsi']);
    
    // Handle Upload Gambar
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload = uploadImage($_FILES['gambar'], '../../uploads/');
        if ($upload) {
            $gambar = $upload;
        } else {
            $error = "Gagal upload gambar. Pastikan format JPG/PNG dan ukuran maksimal 5MB.";
        }
    }
    
    if (!isset($error)) {
        $stmt = $conn->prepare("INSERT INTO produk (id_produk, id_kategori, nama_produk, harga, stok, gambar, deskripsi, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssissd", $new_id, $id_kategori, $nama, $harga, $stok, $gambar, $deskripsi, $rating);
        
        if ($stmt->execute()) {
            header("Location: index.php?msg=success");
            exit;
        } else {
            $error = "Gagal menyimpan data: " . $conn->error;
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
                <input type="text" class="form-control" name="id_produk" value="<?= $new_id ?>" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Produk *</label>
                <input type="text" class="form-control" name="nama_produk" required placeholder="Contoh: Choco Lava Cake">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Kategori *</label>
                <select name="id_kategori" class="form-control" required>
                    <option value="">Pilih Kategori...</option>
                    <?php while($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id_kategori'] ?>"><?= $cat['icon'] ?> <?= htmlspecialchars($cat['nama_kategori']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Rating (0-5)</label>
                <input type="number" class="form-control" name="rating" min="0" max="5" step="0.1" value="0.0">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Harga (Rp) *</label>
                <input type="number" class="form-control" name="harga" required min="0" placeholder="Contoh: 150000">
            </div>
            <div class="form-group">
                <label class="form-label">Stok *</label>
                <input type="number" class="form-control" name="stok" required min="0" value="0">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Gambar Produk</label>
            <div class="image-upload-area" onclick="document.getElementById('gambar').click()">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Klik untuk upload gambar</p>
                <p style="font-size: 0.8rem; color: var(--text-muted);">Format JPG, PNG. Maks 5MB</p>
                <input type="file" id="gambar" name="gambar" accept="image/*">
            </div>
            <img id="imagePreview" class="image-preview" src="">
        </div>
        
        <div class="form-group">
            <label class="form-label">Deskripsi Lengkap</label>
            <textarea class="form-control" name="deskripsi" placeholder="Deskripsi menarik tentang produk ini..."></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Produk</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </form>
</div>

<?php include '../../includes/admin_footer.php'; ?>
