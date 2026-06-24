<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Detail Pesanan';
$current_page = 'pesanan';

$id = isset($_GET['id']) ? sanitize($_GET['id']) : '';

// Get pesanan
$stmt = $conn->prepare("
    SELECT p.*, pl.nama, pl.email, pl.telepon, pl.alamat 
    FROM pesanan p 
    JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan 
    WHERE p.id_pesanan = ?
");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$pesanan = $result->fetch_assoc();

// Get detail items
$dtl_stmt = $conn->prepare("
    SELECT d.*, pr.nama_produk, pr.harga 
    FROM detail_pesanan d 
    JOIN produk pr ON d.id_produk = pr.id_produk 
    WHERE d.id_pesanan = ?
");
$dtl_stmt->bind_param("s", $id);
$dtl_stmt->execute();
$details = $dtl_stmt->get_result();

include '../../includes/admin_header.php';
?>

<div class="toolbar fade-in" style="margin-bottom: 20px;">
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    <div>
        <a href="edit.php?id=<?= $id ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Edit Status</a>
        <button onclick="window.print()" class="btn btn-outline"><i class="fas fa-print"></i> Cetak Invois</button>
    </div>
</div>

<div class="detail-info-grid fade-in">
    <div class="info-card">
        <h4>Informasi Pesanan</h4>
        <div class="info-row">
            <span class="info-label">ID Pesanan</span>
            <span class="info-value">#<?= $pesanan['id_pesanan'] ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal</span>
            <span class="info-value"><?= date('d M Y H:i', strtotime($pesanan['tanggal'])) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Status</span>
            <span class="info-value"><?= getStatusBadge($pesanan['status']) ?></span>
        </div>
    </div>
    
    <div class="info-card">
        <h4>Informasi Pelanggan</h4>
        <div class="info-row">
            <span class="info-label">Nama</span>
            <span class="info-value"><?= htmlspecialchars($pesanan['nama']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Telepon</span>
            <span class="info-value"><?= htmlspecialchars($pesanan['telepon']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Alamat</span>
            <span class="info-value"><?= htmlspecialchars($pesanan['alamat']) ?></span>
        </div>
    </div>
</div>

<div class="content-card fade-in">
    <div class="content-card-header">
        <h3>Detail Item</h3>
    </div>
    <div class="content-card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Produk</th>
                    <th>Harga Satuan</th>
                    <th>Jumlah</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($item = $details->fetch_assoc()): 
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <strong><?= htmlspecialchars($item['nama_produk']) ?></strong>
                        <br><small style="color: var(--text-muted);">ID: <?= $item['id_produk'] ?></small>
                    </td>
                    <td><?= formatRupiah($item['harga']) ?></td>
                    <td><?= $item['jumlah'] ?></td>
                    <td style="text-align: right;"><?= formatRupiah($item['subtotal']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr style="background: #fffbfa;">
                    <td colspan="4" style="text-align: right; font-weight: bold; font-size: 1.1rem; padding: 20px;">TOTAL PESANAN</td>
                    <td style="text-align: right; font-weight: bold; font-size: 1.2rem; color: var(--primary); padding: 20px;"><?= formatRupiah($pesanan['total']) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    .admin-sidebar, .admin-topbar, .toolbar, .btn { display: none !important; }
    .admin-main { margin-left: 0 !important; }
    .admin-content { padding: 0 !important; }
    body { background: white !important; }
    .content-card, .info-card { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>

<?php include '../../includes/admin_footer.php'; ?>
