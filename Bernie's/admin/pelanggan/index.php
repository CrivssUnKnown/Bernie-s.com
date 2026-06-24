<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Data Pelanggan';
$current_page = 'pelanggan';

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

// Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$where = '';
if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $where = "WHERE nama LIKE '%$search_esc%' OR email LIKE '%$search_esc%' OR telepon LIKE '%$search_esc%'";
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_query = $conn->query("SELECT COUNT(*) as total FROM pelanggan $where");
$total_rows = $count_query->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

$query = "
    SELECT pl.*, 
           (SELECT COUNT(*) FROM pesanan WHERE id_pelanggan = pl.id_pelanggan) as jml_pesanan 
    FROM pelanggan pl 
    $where 
    ORDER BY pl.created_at DESC 
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
<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Gagal menghapus! Pelanggan ini memiliki riwayat pesanan.</div>
<?php endif; ?>

<div class="content-card fade-in">
    <div class="toolbar">
        <form method="GET" class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="search-input" placeholder="Cari nama/email/telepon..." value="<?= htmlspecialchars($search) ?>">
        </form>
        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Pelanggan</a>
    </div>

    <div class="content-card-body">
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Pesanan</th>
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
                            <td><strong><?= $row['id_pelanggan'] ?></strong></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['telepon']) ?></td>
                            <td><?= htmlspecialchars(substr($row['alamat'], 0, 30)) ?><?= strlen($row['alamat']) > 30 ? '...' : '' ?></td>
                            <td><span class="badge badge-info"><?= $row['jml_pesanan'] ?> Kali</span></td>
                            <td>
                                <a href="edit.php?id=<?= $row['id_pelanggan'] ?>" class="btn btn-sm btn-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?= $row['id_pelanggan'] ?>" class="btn btn-sm btn-danger btn-delete" title="Hapus"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center">Tidak ada data pelanggan</td></tr>
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
        <a href="?page=<?= $i ?><?= $search ? '&search='.$search : '' ?>" 
           class="page-link <?= $i == $page ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include '../../includes/admin_footer.php'; ?>
