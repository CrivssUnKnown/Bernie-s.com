<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$id = isset($_GET['id']) ? sanitize($_GET['id']) : '';

if ($id) {
    // Check if category is used by products
    $check = $conn->prepare("SELECT COUNT(*) as total FROM produk WHERE id_kategori = ?");
    $check->bind_param("s", $id);
    $check->execute();
    $used = $check->get_result()->fetch_assoc()['total'];
    
    if ($used > 0) {
        header("Location: index.php?msg=error");
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM kategori WHERE id_kategori = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
}

header("Location: index.php?msg=deleted");
exit;
?>
