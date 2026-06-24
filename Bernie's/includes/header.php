<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Bernie's Lovely</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/Bernie's/assets/css/style.css">
</head>
<body>
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    ?>
    <header class="site-header">
        <div class="nav-container">
            <a href="/Bernie's/index.php" class="logo">Bernie's Lovely</a>
            <nav class="main-nav">
                <a href="/Bernie's/index.php" class="nav-item <?= (!isset($current_page) || $current_page == 'home') ? 'active' : '' ?>">Home</a>
                <a href="/Bernie's/menu.php" class="nav-item <?= (isset($current_page) && $current_page == 'menu') ? 'active' : '' ?>">Menu</a>
                <a href="/Bernie's/index.php#menu" class="nav-item">Categories</a>
            </nav>
            <div class="nav-actions">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="/Bernie's/profil.php" class="btn btn-outline" style="padding: 8px 15px;"><i class="fas fa-user-circle"></i> Hai, <?= htmlspecialchars(explode(' ', $_SESSION['user_nama'])[0]) ?></a>
                    <a href="/Bernie's/logout.php" class="btn btn-sm btn-secondary" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="/Bernie's/login.php" class="btn btn-outline"><i class="fas fa-sign-in-alt"></i> Login / Register</a>
                <?php endif; ?>
            </div>
            <!-- Mobile Menu Toggle -->
            <button class="mobile-toggle" style="display: none; background: none; border: none; font-size: 1.5rem; color: var(--primary); cursor: pointer;">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>
    <main class="main-content">
