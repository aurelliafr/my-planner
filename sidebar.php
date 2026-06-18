<?php
$current_page = basename($_SERVER['PHP_SELF']);
$inisial = strtoupper(substr($_SESSION['nama'], 0, 1));
?>
<!-- Hubungkan font estetik dari Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600&display=swap" rel="stylesheet">

<div class="sidebar">
    <!-- Bagian Logo Brand Baru -->
    <div class="sidebar-brand">
        <span class="brand-icon">✓</span> My Planner
    </div>

    <!-- Profile User -->
    <div class="sidebar-profile">
        <div class="avatar-large"><?= $inisial ?></div>
        <div class="profile-info">
            <p class="profile-name"><?= htmlspecialchars($_SESSION['nama']) ?></p>
            <span class="profile-email"><?= htmlspecialchars($_SESSION['email']) ?></span>
        </div>
    </div>

    <!-- Menu Navigasi -->
    <ul class="sidebar-menu">
        <li>
            <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">
                <span class="icon">📊</span> Dashboard
            </a>
        </li>
        <li>
            <a href="todos.php" class="<?= $current_page == 'todos.php' ? 'active' : '' ?>">
                <span class="icon">✅</span> To Do List
            </a>
        </li>
        <li>
            <a href="habits.php" class="<?= $current_page == 'habits.php' ? 'active' : '' ?>">
                <span class="icon">🔥</span> Habit Tracker
            </a>
        </li>
        <li>
            <a href="diary.php" class="<?= $current_page == 'diary.php' ? 'active' : '' ?>">
                <span class="icon">📔</span> Diary
            </a>
        </li>
    </ul>

    <!-- Logout di paling bawah -->
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-link">
            <span class="icon">🚪</span> Logout
        </a>
    </div>
</div>