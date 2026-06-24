<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? sanitize($_GET['id']) : '';

if (!$id) {
    header('Location: menu.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT p.*, k.nama_kategori 
    FROM produk p 
    JOIN kategori k ON p.id_kategori = k.id_kategori 
    WHERE p.id_produk = ?
");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: menu.php');
    exit;
}

$product = $result->fetch_assoc();

// Get related products
$rel_stmt = $conn->prepare("
    SELECT * FROM produk 
    WHERE id_kategori = ? AND id_produk != ? 
    ORDER BY RAND() LIMIT 4
");
$rel_stmt->bind_param("ss", $product['id_kategori'], $id);
$rel_stmt->execute();
$related = $rel_stmt->get_result();

include 'includes/header.php';
?>

<section class="section" style="padding-top: 40px;">
    <div style="margin-bottom: 30px;">
        <a href="menu.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back to Menu</a>
    </div>

    <div class="content-card">
        <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 40px; padding: 40px;">
            <!-- Left: Image -->
            <div>
                <?php if($product['gambar'] && file_exists('uploads/'.$product['gambar'])): ?>
                    <img src="uploads/<?= $product['gambar'] ?>" alt="<?= htmlspecialchars($product['nama_produk']) ?>" style="width: 100%; border-radius: var(--radius); box-shadow: var(--shadow);">
                <?php else: ?>
                    <div class="no-image" style="height: 400px; border-radius: var(--radius); box-shadow: var(--shadow);">🧁</div>
                <?php endif; ?>
            </div>

            <!-- Right: Details -->
            <div>
                <span class="badge badge-info" style="margin-bottom: 15px; font-size: 0.85rem;">
                    <?= htmlspecialchars($product['nama_kategori']) ?>
                </span>
                
                <h1 style="font-size: 2.5rem; margin-bottom: 10px; font-family: 'Playfair Display', serif;">
                    <?= htmlspecialchars($product['nama_produk']) ?>
                </h1>
                
                <div class="product-rating" style="font-size: 1.2rem; margin-bottom: 20px; color: #FFB800;">
                    <?php 
                    $rating = floatval($product['rating']);
                    for($i=1; $i<=5; $i++) {
                        if($i <= $rating) echo '<i class="fas fa-star"></i>';
                        elseif($i - 0.5 <= $rating) echo '<i class="fas fa-star-half-alt"></i>';
                        else echo '<i class="far fa-star"></i>';
                    }
                    ?>
                    <span style="color: var(--text-muted); font-size: 1rem; margin-left: 10px; font-family: 'Poppins', sans-serif;">
                        <?= number_format($rating, 1) ?> Rating
                    </span>
                </div>

                <div style="font-size: 2rem; font-weight: 700; color: var(--primary); margin-bottom: 20px; font-family: 'Playfair Display', serif;">
                    <?= formatRupiah($product['harga']) ?>
                </div>

                <p style="color: var(--text-body); line-height: 1.8; margin-bottom: 30px; font-size: 1.05rem;">
                    <?= nl2br(htmlspecialchars($product['deskripsi'])) ?>
                </p>

                <div style="background: var(--bg-main); padding: 15px 20px; border-radius: var(--radius-sm); margin-bottom: 30px; display: inline-block;">
                    <strong><i class="fas fa-box"></i> Stock Availability:</strong> 
                    <?php if($product['stok'] > 10): ?>
                        <span style="color: var(--success);"><?= $product['stok'] ?> units in stock</span>
                    <?php elseif($product['stok'] > 0): ?>
                        <span style="color: var(--warning);">Only <?= $product['stok'] ?> units left!</span>
                    <?php else: ?>
                        <span style="color: var(--danger);">Out of stock</span>
                    <?php endif; ?>
                </div>

                <div style="margin-bottom: 20px; display: flex; gap: 15px; align-items: center;">
                    <label style="font-weight: bold;">Jumlah:</label>
                    <input type="number" id="qty" value="1" min="1" max="<?= $product['stok'] ?>" class="form-control" style="width: 100px;">
                </div>

                <div>
                    <?php if($product['stok'] > 0): ?>
                    <button class="btn btn-primary" style="padding: 15px 40px; font-size: 1.1rem; width: 100%;" onclick="window.location.href='checkout.php?id=<?= $id ?>&qty=' + document.getElementById('qty').value">
                        <i class="fas fa-shopping-cart"></i> Beli Sekarang
                    </button>
                    <?php else: ?>
                    <button class="btn btn-secondary" style="padding: 15px 40px; font-size: 1.1rem; width: 100%;" disabled>
                        Habis Terjual
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if($related->num_rows > 0): ?>
    <div style="margin-top: 80px;">
        <h3 style="font-size: 1.8rem; margin-bottom: 30px; font-family: 'Playfair Display', serif;">You Might Also Like</h3>
        <div class="products-grid">
            <?php while($prod = $related->fetch_assoc()): ?>
            <div class="product-card fade-in">
                <a href="detail_produk.php?id=<?= $prod['id_produk'] ?>" class="product-image" style="height: 180px;">
                    <?php if($prod['gambar'] && file_exists('uploads/'.$prod['gambar'])): ?>
                        <img src="uploads/<?= $prod['gambar'] ?>" alt="<?= htmlspecialchars($prod['nama_produk']) ?>">
                    <?php else: ?>
                        <div class="no-image" style="font-size: 3rem;">🧁</div>
                    <?php endif; ?>
                </a>
                <div class="product-body" style="padding: 15px;">
                    <a href="detail_produk.php?id=<?= $prod['id_produk'] ?>">
                        <h3 class="product-name" style="font-size: 1rem;"><?= htmlspecialchars($prod['nama_produk']) ?></h3>
                    </a>
                    <div class="product-footer">
                        <div class="product-price" style="font-size: 1.1rem;"><?= formatRupiah($prod['harga']) ?></div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

</section>

<?php include 'includes/footer.php'; ?>
