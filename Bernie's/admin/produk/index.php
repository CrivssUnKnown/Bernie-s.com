<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Data Produk';
$current_page = 'produk';

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

// Search & Filter
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

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_query = $conn->query("SELECT COUNT(*) as total FROM produk p $where_sql");
$total_rows = $count_query->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

$query = "
    SELECT p.*, k.nama_kategori 
    FROM produk p 
    JOIN kategori k ON p.id_kategori = k.id_kategori 
    $where_sql 
    ORDER BY p.id_produk DESC 
    LIMIT $limit OFFSET $offset
";
$result = $conn->query($query);

// Get all categories for filter dropdown
$categories = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

include '../../includes/admin_header.php';
?>

<?php if ($msg == 'success'): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> Data berhasil disimpan!</div>
<?php elseif ($msg == 'deleted'): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> Data berhasil dihapus!</div>
<?php elseif ($msg == 'error'): ?>
<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Gagal menghapus! Produk ada di dalam pesanan.</div>
<?php endif; ?>

<div class="content-card fade-in">
    <div class="toolbar">
        <form method="GET" class="toolbar-left" style="flex-wrap: wrap;">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" class="search-input" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div>
                <select name="kategori" class="form-control" onchange="this.form.submit()" style="min-width: 150px;">
                    <option value="">Semua Kategori</option>
                    <?php while($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id_kategori'] ?>" <?= $kategori_filter == $cat['id_kategori'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php if($search || $kategori_filter): ?>
                <a href="index.php" class="btn btn-outline btn-sm">Clear</a>
            <?php endif; ?>
        </form>
        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Produk</a>
    </div>

    <div class="content-card-body">
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID</th>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Rating</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php 
                        $no = $offset + 1;
                        while($row = $result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= $row['id_produk'] ?></strong></td>
                            <td>
                                <?php if($row['gambar'] && file_exists('../../uploads/'.$row['gambar'])): ?>
                                    <img src="../../uploads/<?= $row['gambar'] ?>" class="product-thumb" alt="thumb">
                                <?php else: ?>
                                    <div class="product-thumb">🧁</div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                            <td><?= formatRupiah($row['harga']) ?></td>
                            <td>
                                <span class="badge <?= $row['stok'] > 10 ? 'badge-info' : ($row['stok'] > 0 ? 'badge-warning' : 'badge-danger') ?>">
                                    <?= $row['stok'] ?>
                                </span>
                            </td>
                            <td><i class="fas fa-star" style="color:#FFB800;"></i> <?= $row['rating'] ?></td>
                            <td>
                                <a href="edit.php?id=<?= $row['id_produk'] ?>" class="btn btn-sm btn-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?= $row['id_produk'] ?>" class="btn btn-sm btn-danger btn-delete" title="Hapus"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center">Tidak ada data produk</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if($total_pages > 1): ?>
<div class="pagination fade-in">
    <?php for($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?><?= $search ? '&search='.$search : '' ?><?= $kategori_filter ? '&kategori='.$kategori_filter : '' ?>" 
           class="page-link <?= $i == $page ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include '../../includes/admin_footer.php'; ?>
