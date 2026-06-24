<?php
// AKTIFKAN WAF DI BARIS PALING ATAS
require_once 'waf.php';

require_once 'config/database.php';
require_once 'includes/functions.php';

// Fetch Categories
$categories = $conn->query("SELECT * FROM kategori LIMIT 4");

// Fetch Bestsellers (top 8 by rating)
$bestsellers = $conn->query("
    SELECT p.*, k.nama_kategori 
    FROM produk p 
    JOIN kategori k ON p.id_kategori = k.id_kategori 
    ORDER BY p.rating DESC 
    LIMIT 8
");

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-container">
        <div class="hero-content fade-in">
            <span class="promo-badge">Special Offer SAVE 20%</span>
            <h1>Decadent<br>Chocolate Bliss!</h1>
            <p>Satisfy Your Sweetest Cravings with our handcrafted cakes and pastries. Baked fresh daily with premium ingredients.</p>
            <div class="hero-btns">
                <a href="menu.php" class="btn btn-primary">Order Now</a>
                <a href="#menu" class="btn btn-outline">Explore Menu</a>
            </div>
        </div>
        <div class="hero-image fade-in" style="animation-delay: 0.2s;">
            <div class="hero-image-placeholder">
                🎂
            </div>
        </div>
    </div>
</section>

<!-- Menu / Categories -->
<section id="menu" class="section">
    <h2 class="section-title">Menu</h2>
    <p class="section-subtitle">What will you wish for?</p>
    
    <div class="category-grid">
        <?php while($cat = $categories->fetch_assoc()): ?>
        <a href="menu.php?kategori=<?= $cat['id_kategori'] ?>" class="category-card fade-in">
            <div class="category-icon">
                <?= htmlspecialchars($cat['icon']) ?>
            </div>
            <h3><?= htmlspecialchars($cat['nama_kategori']) ?></h3>
        </a>
        <?php endwhile; ?>
    </div>
</section>

<!-- Bestsellers -->
<section class="section">
    <h2 class="section-title">Bestsellers</h2>
    <p class="section-subtitle">Most loved by our customers across the country</p>
    
    <div class="products-grid">
        <?php while($prod = $bestsellers->fetch_assoc()): ?>
        <div class="product-card fade-in">
            <a href="detail_produk.php?id=<?= $prod['id_produk'] ?>" class="product-image">
                <?php if($prod['gambar'] && file_exists('uploads/'.$prod['gambar'])): ?>
                    <img src="uploads/<?= $prod['gambar'] ?>" alt="<?= htmlspecialchars($prod['nama_produk']) ?>">
                <?php else: ?>
                    <div class="no-image">🧁</div>
                <?php endif; ?>
            </a>
            <div class="product-body">
                <a href="detail_produk.php?id=<?= $prod['id_produk'] ?>">
                    <h3 class="product-name"><?= htmlspecialchars($prod['nama_produk']) ?></h3>
                </a>
                <div class="product-meta">
                    <div class="product-rating">
                        <?php 
                        $rating = floatval($prod['rating']);
                        for($i=1; $i<=5; $i++) {
                            if($i <= $rating) echo '<i class="fas fa-star"></i>';
                            elseif($i - 0.5 <= $rating) echo '<i class="fas fa-star-half-alt"></i>';
                            else echo '<i class="far fa-star"></i>';
                        }
                        ?>
                        <span><?= number_format($rating, 1) ?></span>
                    </div>
                    <span class="product-stock"><?= $prod['stok'] > 0 ? $prod['stok'].' left' : 'Sold Out' ?></span>
                </div>
                <div class="product-footer">
                    <div class="product-price"><?= formatRupiah($prod['harga']) ?></div>
                    <a href="detail_produk.php?id=<?= $prod['id_produk'] ?>" class="btn-icon" title="View Details">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <div style="text-align: center; margin-top: 40px;">
        <a href="menu.php" class="btn btn-secondary">View All Products</a>
    </div>
</section>

<!-- Promise Section -->
<section class="section">
    <div class="promise-section fade-in">
        <h2 class="section-title" style="font-size: 2rem;">Our Promise</h2>
        <p class="section-subtitle" style="margin-bottom: 20px;">There's no secret spell – only honest work!</p>
        
        <div class="promise-grid">
            <div class="promise-card">
                <div class="promise-icon"><i class="fas fa-truck"></i></div>
                <h4>ON-TIME DELIVERY</h4>
                <p>We ensure your sweet treats arrive right when you need them.</p>
            </div>
            <div class="promise-card">
                <div class="promise-icon"><i class="fas fa-birthday-cake"></i></div>
                <h4 style="text-transform: uppercase;">500+ Designs</h4>
                <p>A cake for every theme and celebration imaginable.</p>
            </div>
            <div class="promise-card">
                <div class="promise-icon"><i class="fas fa-box-open"></i></div>
                <h4>2 CR+ ORDERS</h4>
                <p>Trusted by millions of happy customers nationwide.</p>
            </div>
            <div class="promise-card">
                <div class="promise-icon"><i class="fas fa-leaf"></i></div>
                <h4>BAKED FRESH</h4>
                <p>Always made with the freshest ingredients daily.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Banner -->
<section class="section">
    <div class="cta-banner fade-in">
        <h2>The Magical Ticket</h2>
        <p>Add 3 reminders in your account. Win offers worth Rp 75.000!</p>
        <a href="menu.php" class="btn btn-secondary">Unlock Now</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
