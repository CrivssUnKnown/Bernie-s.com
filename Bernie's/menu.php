<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$kategori_filter = isset($_GET['kategori']) ? sanitize($_GET['kategori']) : '';

$where_clauses = [];
if ($search) {
    $where_clauses[] = "p.nama_produk LIKE '%" . $conn->real_escape_string($search) . "%'";
}
if ($kategori_filter) {
    $where_clauses[] = "p.id_kategori = '" . $conn->real_escape_string($kategori_filter) . "'";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : "";

// Count total
$count_query = $conn->query("SELECT COUNT(*) as total FROM produk p $where_sql");
$total_rows = $count_query->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch products
$query = "
    SELECT p.*, k.nama_kategori 
    FROM produk p 
    JOIN kategori k ON p.id_kategori = k.id_kategori 
    $where_sql 
    ORDER BY p.id_produk DESC 
    LIMIT $limit OFFSET $offset
";
$products = $conn->query($query);

// Fetch all categories for filter
$categories = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

include 'includes/header.php';
?>

<div style="background: linear-gradient(135deg, var(--bg-main), white); padding: 40px 0;">
    <div class="nav-container text-center" style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h1 style="font-size: 3rem; margin-bottom: 10px; font-family: 'Playfair Display', serif;">Our Menu</h1>
        <p style="color: var(--text-muted);">Discover our delightful collection of treats</p>
    </div>
</div>

<section class="section">
    <!-- Filter Bar -->
    <div class="toolbar form-card" style="margin-bottom: 40px; padding: 20px;">
        <form action="menu.php" method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; width: 100%;">
            <div class="search-box" style="flex: 1;">
                <i class="fas fa-search"></i>
                <input type="text" name="search" class="search-input" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
            </div>
            
            <div style="min-width: 200px;">
                <select name="kategori" class="form-control" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php while($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id_kategori'] ?>" <?= $kategori_filter == $cat['id_kategori'] ? 'selected' : '' ?>>
                            <?= $cat['icon'] ?> <?= htmlspecialchars($cat['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if($search || $kategori_filter): ?>
                <a href="menu.php" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Products Grid -->
    <?php if($products->num_rows > 0): ?>
        <div class="products-grid">
            <?php while($prod = $products->fetch_assoc()): ?>
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

        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?><?= $search ? '&search='.$search : '' ?><?= $kategori_filter ? '&kategori='.$kategori_filter : '' ?>" 
                       class="page-link <?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No products found</h3>
            <p>Try adjusting your search or filter criteria.</p>
            <a href="menu.php" class="btn btn-primary" style="margin-top: 20px;">Clear Filters</a>
        </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
