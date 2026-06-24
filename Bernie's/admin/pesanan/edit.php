<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Edit Status Pesanan';
$current_page = 'pesanan';

$id = isset($_GET['id']) ? sanitize($_GET['id']) : '';

// Get pesanan
$stmt = $conn->prepare("SELECT p.*, pl.nama FROM pesanan p JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan WHERE p.id_pesanan = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$pesanan = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = sanitize($_POST['status']);
    
    $conn->begin_transaction();
    try {
        $upd = $conn->prepare("UPDATE pesanan SET status = ? WHERE id_pesanan = ?");
        $upd->bind_param("ss", $new_status, $id);
        $upd->execute();
        
        // If status changed to 'batal', we should restore stock
        // For a more complete system we would check if it was previously NOT batal, 
        // but for simplicity we assume it's cancelling from pending/proses.
        if ($new_status == 'batal' && $pesanan['status'] != 'batal') {
            $dtl = $conn->prepare("SELECT id_produk, jumlah FROM detail_pesanan WHERE id_pesanan = ?");
            $dtl->bind_param("s", $id);
            $dtl->execute();
            $items = $dtl->get_result();
            
            while($item = $items->fetch_assoc()) {
                $restore = $conn->prepare("UPDATE produk SET stok = stok + ? WHERE id_produk = ?");
                $restore->bind_param("is", $item['jumlah'], $item['id_produk']);
                $restore->execute();
            }
        }
        
        $conn->commit();
        header("Location: index.php?msg=success");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Gagal mengupdate status: " . $e->getMessage();
    }
}

include '../../includes/admin_header.php';
?>

<div class="form-card fade-in">
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div style="background: var(--bg-main); padding: 20px; border-radius: var(--radius-sm); margin-bottom: 25px;">
        <h4 style="margin-bottom: 10px; color: var(--primary);">Info Pesanan</h4>
        <p><strong>ID:</strong> <?= $pesanan['id_pesanan'] ?></p>
        <p><strong>Pelanggan:</strong> <?= htmlspecialchars($pesanan['nama']) ?></p>
        <p><strong>Total:</strong> <?= formatRupiah($pesanan['total']) ?></p>
    </div>

    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Ubah Status Pesanan</label>
            <select name="status" class="form-control" required>
                <option value="pending" <?= $pesanan['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="proses" <?= $pesanan['status'] == 'proses' ? 'selected' : '' ?>>Proses</option>
                <option value="selesai" <?= $pesanan['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                <option value="batal" <?= $pesanan['status'] == 'batal' ? 'selected' : '' ?>>Batal</option>
            </select>
            <small style="color: var(--warning); display: block; margin-top: 10px;">
                *Jika diubah ke Batal, stok produk akan dikembalikan secara otomatis.
            </small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Status</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </form>
</div>

<?php include '../../includes/admin_footer.php'; ?>
