<?php
require_once 'config/koneksi.php';
require_once 'functions/helpers.php';
cekLogin();

$user_id = $_SESSION['user_id'];

// ================================================
// STATISTIK TO DO
// ================================================
$totalTodo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM todos WHERE user_id = $user_id"))['total'];
$todoSelesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM todos WHERE user_id = $user_id AND status = 'selesai'"))['total'];
$todoProses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM todos WHERE user_id = $user_id AND status = 'proses'"))['total'];

// ================================================
// STATISTIK HABIT
// ================================================
$totalHabit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM habits WHERE user_id = $user_id"))['total'];

// ================================================
// TODO TERDEKAT (BELUM SELESAI, URUT DEADLINE)
// ================================================
$todoTerdekat = mysqli_query($conn, "
    SELECT * FROM todos 
    WHERE user_id = $user_id AND status != 'selesai'
    ORDER BY tanggal_deadline ASC
    LIMIT 5
");

// ================================================
// HABIT HARI INI
// ================================================
$habitHariIni = mysqli_query($conn, "SELECT * FROM habits WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 4");

// ================================================
// DIARY TERBARU
// ================================================
$diaryTerbaru = mysqli_query($conn, "SELECT * FROM diary_entries WHERE user_id = $user_id ORDER BY tanggal_catatan DESC LIMIT 1");
$diaryData = mysqli_fetch_assoc($diaryTerbaru);

date_default_timezone_set('Asia/Jakarta');
$jamSekarang = date('H:i');
$hariIni = date('l');
$hariIndo = [
    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - My Planner</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="breadcrumb">Dashboard</div>
                <div class="page-title">Hai, <?= htmlspecialchars($_SESSION['nama']) ?> 🌿</div>
            </div>
            <div style="text-align:right; color:#a89bb5; font-size:13px;">
                <?= $jamSekarang ?> WIB · <?= $hariIndo[$hariIni] ?>
            </div>
        </div>

        <!-- Statistik Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon-box" style="background:#E2D9E2; color:#9580D4;">📋</div>
                <div class="label">Total To Do</div>
                <div class="value"><?= $totalTodo ?></div>
            </div>
            <div class="stat-card">
                <div class="icon-box" style="background:#dcebf0; color:#75ADC9;">⏳</div>
                <div class="label">Sedang Dikerjakan</div>
                <div class="value"><?= $todoProses ?></div>
            </div>
            <div class="stat-card">
                <div class="icon-box" style="background:#e8f0e0; color:#6a9c5a;">✅</div>
                <div class="label">Selesai</div>
                <div class="value"><?= $todoSelesai ?></div>
            </div>
            <div class="stat-card">
                <div class="icon-box" style="background:#f0e6d8; color:#c9985a;">🔥</div>
                <div class="label">Total Habit</div>
                <div class="value"><?= $totalHabit ?></div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1.4fr 1fr; gap:20px;">
            <!-- To Do Terdekat -->
            <div class="card-box">
                <h3 style="margin-bottom:16px; font-size:16px;">To Do Terdekat</h3>
                <?php if (mysqli_num_rows($todoTerdekat) > 0): ?>
                    <?php while ($t = mysqli_fetch_assoc($todoTerdekat)): ?>
                        <div class="todo-item">
                            <div class="todo-checkbox <?= $t['status'] == 'selesai' ? 'checked' : '' ?>">
                                <?= $t['status'] == 'selesai' ? '✓' : '' ?>
                            </div>
                            <div class="todo-content">
                                <div class="todo-title"><?= htmlspecialchars($t['judul']) ?></div>
                                <div class="todo-meta">
                                    <span class="badge" style="background: <?= warnaPrioritas($t['prioritas']) ?>;">
                                        <?= ucfirst($t['prioritas']) ?>
                                    </span>
                                    <span>📅 <?= formatTanggal($t['tanggal_deadline']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color:#a89bb5; font-size:13px;">Tidak ada to do yang mendekati deadline. Yay! 🎉</p>
                <?php endif; ?>
            </div>

            <!-- Habit Hari Ini -->
            <div class="card-box">
                <h3 style="margin-bottom:16px; font-size:16px;">Habit Tracker</h3>
                <?php if (mysqli_num_rows($habitHariIni) > 0): ?>
                    <?php while ($h = mysqli_fetch_assoc($habitHariIni)): ?>
                        <?php $sudahCentang = sudahDicentangHariIni($conn, $h['id']); ?>
                        <div style="display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #f1f0f4;">
                            <div style="font-size:20px;"><?= $h['icon'] ?></div>
                            <div style="flex:1;">
                                <div style="font-size:13px; font-weight:600; color:#4a4458;"><?= htmlspecialchars($h['nama_habit']) ?></div>
                                <div style="font-size:11px; color:#a89bb5;">🔥 <?= hitungStreak($conn, $h['id']) ?> hari streak</div>
                            </div>
                            <div style="font-size:18px;"><?= $sudahCentang ? '✅' : '⬜' ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color:#a89bb5; font-size:13px;">Belum ada habit. Yuk mulai bikin kebiasaan baik!</p>
                <?php endif; ?>

                <?php if ($diaryData): ?>
                    <div style="margin-top:20px; padding-top:16px; border-top:1px solid #f1f0f4;">
                        <div style="font-size:13px; font-weight:700; margin-bottom:8px; color:#4a4458;">Diary Terakhir</div>
                        <div style="font-size:12px; color:#a89bb5;">
                            <?= emojiMood($diaryData['mood']) ?> "<?= htmlspecialchars(substr($diaryData['judul'], 0, 30)) ?>" — <?= formatTanggal($diaryData['tanggal_catatan']) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>