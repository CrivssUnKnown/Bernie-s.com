<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$id = isset($_GET['id']) ? sanitize($_GET['id']) : '';

if ($id) {
    // Check if pelanggan is in pesanan
    $check = $conn->prepare("SELECT COUNT(*) as total FROM pesanan WHERE id_pelanggan = ?");
    $check->bind_param("s", $id);
    $check->execute();
    $used = $check->get_result()->fetch_assoc()['total'];
    
    if ($used > 0) {
        header("Location: index.php?msg=error");
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM pelanggan WHERE id_pelanggan = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
}

header("Location: index.php?msg=deleted");
exit;
?>
