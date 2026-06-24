<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$page_title = 'Dashboard';
$current_page = 'dashboard';

// Get counts
$q_produk = $conn->query("SELECT COUNT(*) as total FROM produk");
$total_produk = $q_produk->fetch_assoc()['total'];

$q_pelanggan = $conn->query("SELECT COUNT(*) as total FROM pelanggan");
$total_pelanggan = $q_pelanggan->fetch_assoc()['total'];

$q_pesanan = $conn->query("SELECT COUNT(*) as total FROM pesanan");
$total_pesanan = $q_pesanan->fetch_assoc()['total'];

$q_pendapatan = $conn->query("SELECT SUM(total) as total FROM pesanan WHERE status='selesai'");
$total_pendapatan = $q_pendapatan->fetch_assoc()['total'] ?? 0;

// Get recent orders
$recent_orders = $conn->query("
    SELECT p.*, pl.nama as nama_pelanggan 
    FROM pesanan p 
    JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan 
    ORDER BY p.tanggal DESC 
    LIMIT 5
");

// Get low stock products
$low_stock = $conn->query("
    SELECT p.id_produk, p.nama_produk, p.stok, k.nama_kategori 
    FROM produk p 
    JOIN kategori k ON p.id_kategori = k.id_kategori 
    WHERE p.stok < 10 
    ORDER BY p.stok ASC 
    LIMIT 5
");

include '../includes/admin_header.php';
?>

<div class="stats-grid fade-in">
    <div class="stat-card">
        <div class="stat-icon products"><i class="fas fa-birthday-cake"></i></div>
        <div class="stat-info">
            <div class="stat-number"><?= $total_produk ?></div>
            <div class="stat-label">Total Produk</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon customers"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-number"><?= $total_pelanggan ?></div>
            <div class="stat-label">Total Pelanggan</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orders"><i class="fas fa-shopping-cart"></i></div>
        <div class="stat-info">
            <div class="stat-number"><?= $total_pesanan ?></div>
            <div class="stat-label">Total Pesanan</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon revenue"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-info">
            <div class="stat-number" style="font-size: 1.5rem;"><?= formatRupiah($total_pendapatan) ?></div>
            <div class="stat-label">Total Pendapatan</div>
        </div>
    </div>
</div>

<div class="form-row">
    <div class="content-card fade-in" style="animation-delay: 0.1s;">
        <div class="content-card-header">
            <h3>Pesanan Terbaru</h3>
            <a href="pesanan/" class="btn btn-sm btn-outline">Lihat Semua</a>
        </div>
        <div class="content-card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($recent_orders->num_rows > 0): ?>
                        <?php while($order = $recent_orders->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= $order['id_pesanan'] ?></strong></td>
                            <td><?= htmlspecialchars($order['nama_pelanggan']) ?></td>
                            <td><?= formatRupiah($order['total']) ?></td>
                            <td><?= getStatusBadge($order['status']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Belum ada pesanan</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="content-card fade-in" style="animation-delay: 0.2s;">
        <div class="content-card-header">
            <h3>Produk Stok Rendah</h3>
            <a href="produk/" class="btn btn-sm btn-outline">Cek Produk</a>
        </div>
        <div class="content-card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($low_stock->num_rows > 0): ?>
                        <?php while($prod = $low_stock->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($prod['nama_produk']) ?></td>
                            <td><?= htmlspecialchars($prod['nama_kategori']) ?></td>
                            <td>
                                <span class="badge <?= $prod['stok'] == 0 ? 'badge-danger' : 'badge-warning' ?>">
                                    <?= $prod['stok'] ?> Tersisa
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center">Stok produk aman</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
