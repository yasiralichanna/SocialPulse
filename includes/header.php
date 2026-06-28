<?php
/**
 * Shared header template
 * Included by all page files
 */
$user = currentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialPulse — <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>
    <meta name="description" content="Schedule, publish, and analyze social media posts across Facebook, Twitter, and Instagram.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-bolt"></i></div>
            <span class="brand-text">SocialPulse</span>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item <?= $currentPage === 'index' ? 'active' : '' ?>" id="nav-dashboard">
                <i class="fas fa-chart-pie"></i><span>Dashboard</span>
            </a>
            <?php if (can('create_post')): ?>
            <a href="compose.php" class="nav-item <?= $currentPage === 'compose' ? 'active' : '' ?>" id="nav-composer">
                <i class="fas fa-pen-fancy"></i><span>Compose</span>
            </a>
            <?php endif; ?>
            <?php if (can('schedule_post')): ?>
            <a href="scheduler.php" class="nav-item <?= $currentPage === 'scheduler' ? 'active' : '' ?>" id="nav-scheduler">
                <i class="fas fa-calendar-alt"></i><span>Scheduler</span>
            </a>
            <?php endif; ?>
            <a href="analytics.php" class="nav-item <?= $currentPage === 'analytics' ? 'active' : '' ?>" id="nav-analytics">
                <i class="fas fa-chart-line"></i><span>Analytics</span>
            </a>
            <?php if (can('manage_users')): ?>
            <a href="users.php" class="nav-item <?= $currentPage === 'users' ? 'active' : '' ?>" id="nav-users">
                <i class="fas fa-users-cog"></i><span>Team</span>
            </a>
            <?php endif; ?>
            <a href="reports.php" class="nav-item <?= $currentPage === 'reports' ? 'active' : '' ?>" id="nav-reports">
                <i class="fas fa-file-export"></i><span>Reports</span>
            </a>
            <?php if (can('manage_settings')): ?>
            <a href="settings.php" class="nav-item <?= $currentPage === 'settings' ? 'active' : '' ?>" id="nav-settings">
                <i class="fas fa-cog"></i><span>Settings</span>
            </a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar"><?= strtoupper(substr($user['name'], 0, 2)) ?></div>
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                    <span class="user-role"><?= ucfirst($user['role']) ?></span>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            <div class="topbar-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search posts, analytics..." id="globalSearch">
            </div>
            <div class="topbar-actions">
                <button class="topbar-btn" id="themeToggle" title="Toggle theme"><i class="fas fa-moon"></i></button>
                <a href="logout.php" class="topbar-btn" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>
