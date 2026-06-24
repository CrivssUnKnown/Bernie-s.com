<?php
function generateId($conn, $table, $column, $prefix) {
    $sql = "SELECT MAX($column) AS max_id FROM $table";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    if ($row['max_id']) {
        $num = (int) substr($row['max_id'], 3);
        $newNum = $num + 1;
    } else {
        $newNum = 1;
    }
    return $prefix . str_pad($newNum, 3, '0', STR_PAD_LEFT);
}

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function uploadImage($file, $target_dir = '') {
    if (empty($target_dir)) {
        $target_dir = __DIR__ . '/../uploads/';
    }
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;
    $filename = uniqid('img_') . '.' . $ext;
    $target = $target_dir . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return $filename;
    }
    return false;
}

function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'proses' => '<span class="badge badge-info">Proses</span>',
        'selesai' => '<span class="badge badge-success">Selesai</span>',
        'batal' => '<span class="badge badge-danger">Batal</span>'
    ];
    return $badges[$status] ?? '<span class="badge">Unknown</span>';
}

function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host . "/Bernie's/";
}
?>
