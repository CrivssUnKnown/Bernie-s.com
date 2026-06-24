<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Protect admin pages
$current_uri = $_SERVER['REQUEST_URI'];
if (!isset($_SESSION['admin_id']) && strpos($current_uri, 'login.php') === false) {
    header("Location: /Bernie's/admin/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Admin | Bernie's Lovely</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/Bernie's/assets/css/style.css">
</head>
<body class="admin-body">

<?php if(isset($_SESSION['admin_id']) && strpos($current_uri, 'login.php') === false): ?>
<div class="admin-wrapper">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-logo">🧁 Bernie's Lovely</h2>
            <span class="sidebar-subtitle">Admin Panel</span>
        </div>
        <nav class="sidebar-nav">
            <a href="/Bernie's/admin/" class="nav-link <?= (isset($current_page) && $current_page == 'dashboard') ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="/Bernie's/admin/kategori/" class="nav-link <?= (isset($current_page) && $current_page == 'kategori') ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Kategori
            </a>
            <a href="/Bernie's/admin/produk/" class="nav-link <?= (isset($current_page) && $current_page == 'produk') ? 'active' : '' ?>">
                <i class="fas fa-birthday-cake"></i> Produk
            </a>
            <a href="/Bernie's/admin/pelanggan/" class="nav-link <?= (isset($current_page) && $current_page == 'pelanggan') ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Pelanggan
            </a>
            <a href="/Bernie's/admin/pesanan/" class="nav-link <?= (isset($current_page) && $current_page == 'pesanan') ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> Pesanan
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="/Bernie's/admin/logout.php" class="nav-link text-danger" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <a href="/Bernie's/" class="nav-link" target="_blank"><i class="fas fa-globe"></i> Lihat Website</a>
        </div>
    </aside>
    
    <div class="admin-main">
        <header class="admin-topbar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="topbar-title"><?= isset($page_title) ? $page_title : 'Dashboard' ?></h1>
            <div class="topbar-right">
                <span class="admin-user"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_nama']) ?></span>
            </div>
        </header>
        
        <div class="admin-content fade-in">
<?php endif; ?>
