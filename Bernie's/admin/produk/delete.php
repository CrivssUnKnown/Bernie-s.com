<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$id = isset($_GET['id']) ? sanitize($_GET['id']) : '';

if ($id) {
    // Check if product is in pesanan
    $check = $conn->prepare("SELECT COUNT(*) as total FROM detail_pesanan WHERE id_produk = ?");
    $check->bind_param("s", $id);
    $check->execute();
    $used = $check->get_result()->fetch_assoc()['total'];
    
    if ($used > 0) {
        header("Location: index.php?msg=error");
        exit;
    }
    
    // Get image filename to delete file
    $get_img = $conn->prepare("SELECT gambar FROM produk WHERE id_produk = ?");
    $get_img->bind_param("s", $id);
    $get_img->execute();
    $img = $get_img->get_result()->fetch_assoc()['gambar'];
    
    if ($img && file_exists('../../uploads/'.$img)) {
        unlink('../../uploads/'.$img);
    }
    
    $stmt = $conn->prepare("DELETE FROM produk WHERE id_produk = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
}

header("Location: index.php?msg=deleted");
exit;
?>
