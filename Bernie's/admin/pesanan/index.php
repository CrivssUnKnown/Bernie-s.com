<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Data Pesanan';
$current_page = 'pesanan';

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

// Search & Filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$where_clauses = [];
if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $where_clauses[] = "(p.id_pesanan LIKE '%$search_esc%' OR pl.nama LIKE '%$search_esc%')";
}
if ($status_filter) {
    $where_clauses[] = "p.status = '" . $conn->real_escape_string($status_filter) . "'";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : "";

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_query = $conn->query("
    SELECT COUNT(*) as total 
    FROM pesanan p 
    JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan 
    $where_sql
");
$total_rows = $count_query->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

$query = "
    SELECT p.*, pl.nama as nama_pelanggan 
    FROM pesanan p 
    JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan 
    $where_sql 
    ORDER BY p.tanggal DESC 
    LIMIT $limit OFFSET $offset
";
$result = $conn->query($query);

include '../../includes/admin_header.php';
?>

<?php if ($msg == 'success'): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> Data berhasil disimpan!</div>
<?php elseif ($msg == 'deleted'): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> Data berhasil dihapus!</div>
<?php endif; ?>

<div class="content-card fade-in">
    <div class="toolbar">
        <form method="GET" class="toolbar-left" style="flex-wrap: wrap;">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" class="search-input" placeholder="Cari ID/Nama pelanggan..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div>
                <select name="status" class="form-control" onchange="this.form.submit()" style="min-width: 150px;">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="proses" <?= $status_filter == 'proses' ? 'selected' : '' ?>>Proses</option>
                    <option value="selesai" <?= $status_filter == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="batal" <?= $status_filter == 'batal' ? 'selected' : '' ?>>Batal</option>
                </select>
            </div>
            <?php if($search || $status_filter): ?>
                <a href="index.php" class="btn btn-outline btn-sm">Clear</a>
            <?php endif; ?>
        </form>
        <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Pesanan</a>
    </div>

    <div class="content-card-body">
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Pesanan</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
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
                            <td><strong><?= $row['id_pesanan'] ?></strong></td>
                            <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                            <td><strong><?= formatRupiah($row['total']) ?></strong></td>
                            <td><?= getStatusBadge($row['status']) ?></td>
                            <td>
                                <a href="detail.php?id=<?= $row['id_pesanan'] ?>" class="btn btn-sm btn-outline" title="Detail"><i class="fas fa-eye"></i></a>
                                <a href="edit.php?id=<?= $row['id_pesanan'] ?>" class="btn btn-sm btn-secondary" title="Edit Status"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?= $row['id_pesanan'] ?>" class="btn btn-sm btn-danger btn-delete" title="Hapus"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">Tidak ada data pesanan</td></tr>
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
        <a href="?page=<?= $i ?><?= $search ? '&search='.$search : '' ?><?= $status_filter ? '&status='.$status_filter : '' ?>" 
           class="page-link <?= $i == $page ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include '../../includes/admin_footer.php'; ?>
