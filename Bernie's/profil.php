<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id_pelanggan = $_SESSION['user_id'];

// Get user profile
$stmt = $conn->prepare("SELECT * FROM pelanggan WHERE id_pelanggan = ?");
$stmt->bind_param("s", $id_pelanggan);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get order history
$pesanan_stmt = $conn->prepare("
    SELECT * FROM pesanan 
    WHERE id_pelanggan = ? 
    ORDER BY tanggal DESC
");
$pesanan_stmt->bind_param("s", $id_pelanggan);
$pesanan_stmt->execute();
$pesanan_history = $pesanan_stmt->get_result();

$page_title = 'Profil Saya';
include 'includes/header.php';
?>

<div class="section" style="padding-top: 40px; min-height: 60vh;">
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; max-width: 1200px; margin: 0 auto;">
        
        <!-- Sidebar Profile -->
        <div class="form-card fade-in" style="padding: 30px; height: fit-content;">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="width: 80px; height: 80px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 15px;">
                    <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                </div>
                <h3 style="font-family: 'Playfair Display', serif;"><?= htmlspecialchars($user['nama']) ?></h3>
                <p style="color: var(--text-muted);"><?= htmlspecialchars($user['email']) ?></p>
            </div>
            
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
            
            <div style="margin-bottom: 15px;">
                <strong><i class="fab fa-whatsapp" style="color: var(--primary); width: 25px;"></i> Telepon</strong><br>
                <span style="color: var(--text-body);"><?= htmlspecialchars($user['telepon']) ?></span>
            </div>
            <div style="margin-bottom: 15px;">
                <strong><i class="fas fa-map-marker-alt" style="color: var(--primary); width: 25px;"></i> Alamat</strong><br>
                <span style="color: var(--text-body);"><?= htmlspecialchars($user['alamat']) ?></span>
            </div>
            
            <a href="logout.php" class="btn btn-outline" style="width: 100%; margin-top: 20px;"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
        
        <!-- Order History -->
        <div class="content-card fade-in" style="animation-delay: 0.1s;">
            <div class="content-card-header">
                <h3>Riwayat Pesanan</h3>
            </div>
            <div class="content-card-body">
                <?php if($pesanan_history->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = $pesanan_history->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= $order['id_pesanan'] ?></strong></td>
                                <td><?= date('d M Y', strtotime($order['tanggal'])) ?></td>
                                <td><?= formatRupiah($order['total']) ?></td>
                                <td><?= getStatusBadge($order['status']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline" onclick="alert('Fitur detail invoice menyusul!')"><i class="fas fa-eye"></i> Detail</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px 20px;">
                        <i class="fas fa-box-open" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                        <p style="color: var(--text-muted); font-size: 1.1rem;">Belum ada riwayat pesanan.</p>
                        <a href="menu.php" class="btn btn-primary" style="margin-top: 15px;">Mulai Belanja</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<?php include 'includes/footer.php'; ?>
