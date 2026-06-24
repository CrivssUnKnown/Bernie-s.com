<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
if(!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit;
}

$id_produk = isset($_GET['id']) ? sanitize($_GET['id']) : '';
$qty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;

if(!$id_produk || $qty < 1) {
    header("Location: menu.php");
    exit;
}

// Check product
$stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");
$stmt->bind_param("s", $id_produk);
$stmt->execute();
$produk = $stmt->get_result()->fetch_assoc();

if(!$produk || $produk['stok'] < $qty) {
    echo "<script>alert('Produk tidak ditemukan atau stok tidak mencukupi!'); window.location.href='menu.php';</script>";
    exit;
}

$id_pelanggan = $_SESSION['user_id'];
$total = $produk['harga'] * $qty;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        $new_id = generateId($conn, 'pesanan', 'id_pesanan', 'PSN');
        $status = 'pending';
        
        // 1. Insert Pesanan
        $ins_psn = $conn->prepare("INSERT INTO pesanan (id_pesanan, id_pelanggan, total, status) VALUES (?, ?, ?, ?)");
        $ins_psn->bind_param("ssds", $new_id, $id_pelanggan, $total, $status);
        $ins_psn->execute();
        
        // 2. Insert Detail
        $dtl_id = generateId($conn, 'detail_pesanan', 'id_detail', 'DTL');
        $ins_dtl = $conn->prepare("INSERT INTO detail_pesanan (id_detail, id_pesanan, id_produk, jumlah, subtotal) VALUES (?, ?, ?, ?, ?)");
        $ins_dtl->bind_param("sssid", $dtl_id, $new_id, $id_produk, $qty, $total);
        $ins_dtl->execute();
        
        // 3. Update Stok
        $upd_stok = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE id_produk = ?");
        $upd_stok->bind_param("is", $qty, $id_produk);
        $upd_stok->execute();
        
        $conn->commit();
        echo "<script>alert('Pesanan berhasil dibuat!'); window.location.href='profil.php';</script>";
        exit;
    } catch(Exception $e) {
        $conn->rollback();
        $error = "Terjadi kesalahan sistem. Silahkan coba lagi.";
    }
}

$page_title = 'Checkout';
include 'includes/header.php';
?>

<div class="section" style="padding-top: 40px; min-height: 60vh;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-family: 'Playfair Display', serif; color: var(--primary); margin-bottom: 30px; text-align: center;">Ringkasan Pesanan</h2>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="form-card fade-in" style="padding: 30px;">
            <div style="display: flex; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px;">
                <?php if($produk['gambar'] && file_exists('uploads/'.$produk['gambar'])): ?>
                    <img src="uploads/<?= $produk['gambar'] ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px; margin-right: 20px;">
                <?php else: ?>
                    <div style="width: 100px; height: 100px; background: #eee; border-radius: 10px; margin-right: 20px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">🧁</div>
                <?php endif; ?>
                
                <div style="flex-grow: 1;">
                    <h3 style="margin-bottom: 5px;"><?= htmlspecialchars($produk['nama_produk']) ?></h3>
                    <p style="color: var(--text-muted);"><?= formatRupiah($produk['harga']) ?> x <?= $qty ?> pcs</p>
                </div>
                <div style="text-align: right;">
                    <strong style="font-size: 1.2rem; color: var(--primary);"><?= formatRupiah($total) ?></strong>
                </div>
            </div>
            
            <div style="background: var(--bg-main); padding: 15px; border-radius: var(--radius-sm); margin-bottom: 30px;">
                <strong><i class="fas fa-info-circle"></i> Catatan:</strong> Pesanan akan segera diproses setelah checkout. Pembayaran dilakukan di tempat (COD) atau transfer setelah konfirmasi.
            </div>
            
            <form action="" method="POST">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.1rem;"><i class="fas fa-check-circle"></i> Konfirmasi & Pesan Sekarang</button>
            </form>
            <div style="text-align: center; margin-top: 15px;">
                <a href="detail_produk.php?id=<?= $id_produk ?>" style="color: var(--text-muted);">Batal</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
