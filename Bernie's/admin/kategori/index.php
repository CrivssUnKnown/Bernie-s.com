<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Data Kategori';
$current_page = 'kategori';

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

// Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$where = '';
if ($search) {
    $where = "WHERE nama_kategori LIKE '%" . $conn->real_escape_string($search) . "%'";
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_query = $conn->query("SELECT COUNT(*) as total FROM kategori $where");
$total_rows = $count_query->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

$query = "
    SELECT k.*, (SELECT COUNT(*) FROM produk WHERE id_kategori = k.id_kategori) as jml_produk 
    FROM kategori k 
    $where 
    ORDER BY k.id_kategori DESC 
    LIMIT $limit OFFSET $offset
";
$result = $conn->query($query);

include '../../includes/admin_header.php';
?>

<?php if ($msg == 'success'): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> Data berhasil disimpan!</div>
<?php elseif ($msg == 'deleted'): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> Data berhasil dihapus!</div>
<?php elseif ($msg == 'error'): ?>
<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Gagal menghapus! Kategori masih digunakan oleh produk.</div>
<?php endif; ?>

<div class="content-card fade-in">
    <div class="toolbar">
        <form method="GET" class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="search-input" placeholder="Cari kategori..." value="<?= htmlspecialchars($search) ?>">
        </form>
        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Kategori</a>
    </div>

    <div class="content-card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID</th>
                    <th>Icon</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Produk</th>
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
                        <td><strong><?= $row['id_kategori'] ?></strong></td>
                        <td style="font-size: 1.5rem;"><?= htmlspecialchars($row['icon']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars(substr($row['deskripsi'], 0, 50)) ?><?= strlen($row['deskripsi']) > 50 ? '...' : '' ?></td>
                        <td><span class="badge badge-info"><?= $row['jml_produk'] ?> Produk</span></td>
                        <td>
                            <a href="edit.php?id=<?= $row['id_kategori'] ?>" class="btn btn-sm btn-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="delete.php?id=<?= $row['id_kategori'] ?>" class="btn btn-sm btn-danger btn-delete" title="Hapus"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">Tidak ada data kategori</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if($total_pages > 1): ?>
<div class="pagination fade-in">
    <?php for($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?><?= $search ? '&search='.$search : '' ?>" 
           class="page-link <?= $i == $page ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include '../../includes/admin_footer.php'; ?>
