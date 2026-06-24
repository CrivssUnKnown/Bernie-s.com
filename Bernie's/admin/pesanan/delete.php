<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$id = isset($_GET['id']) ? sanitize($_GET['id']) : '';

if ($id) {
    $conn->begin_transaction();
    try {
        // First restore stok
        $dtl = $conn->prepare("SELECT id_produk, jumlah FROM detail_pesanan WHERE id_pesanan = ?");
        $dtl->bind_param("s", $id);
        $dtl->execute();
        $items = $dtl->get_result();
        
        while($item = $items->fetch_assoc()) {
            $restore = $conn->prepare("UPDATE produk SET stok = stok + ? WHERE id_produk = ?");
            $restore->bind_param("is", $item['jumlah'], $item['id_produk']);
            $restore->execute();
        }
        
        // Delete details (technically handled by ON DELETE CASCADE if DB is setup right, but doing it manually to be safe)
        $del_dtl = $conn->prepare("DELETE FROM detail_pesanan WHERE id_pesanan = ?");
        $del_dtl->bind_param("s", $id);
        $del_dtl->execute();
        
        // Delete pesanan
        $del_psn = $conn->prepare("DELETE FROM pesanan WHERE id_pesanan = ?");
        $del_psn->bind_param("s", $id);
        $del_psn->execute();
        
        $conn->commit();
        header("Location: index.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        // Redirect with error
        header("Location: index.php?msg=error");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
