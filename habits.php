<?php
require_once 'config/koneksi.php';
require_once 'functions/helpers.php';
cekLogin();

$user_id = $_SESSION['user_id'];
$alert = '';
$today = date('Y-m-d');

// ================================================
// PROSES DELETE HABIT
// ================================================
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM habits WHERE id = $id AND user_id = $user_id");
    header("Location: habits.php");
    exit();
}

// ================================================
// PROSES CHECKLIST HABIT HARI INI
// ================================================
if (isset($_GET['check'])) {
    $habit_id = (int) $_GET['check'];

    if (sudahDicentangHariIni($conn, $habit_id)) {
        // Sudah dicentang, batalkan
        mysqli_query($conn, "DELETE FROM habit_logs WHERE habit_id = $habit_id AND tanggal = '$today'");
    } else {
        // Belum dicentang, tambahkan
        mysqli_query($conn, "INSERT INTO habit_logs (habit_id, tanggal, selesai) VALUES ($habit_id, '$today', 1)");
    }

    header("Location: habits.php");
    exit();
}

// ================================================
// PROSES TAMBAH / EDIT HABIT
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_habit = mysqli_real_escape_string($conn, $_POST['nama_habit']);
    $icon = $_POST['icon'];
    $warna = $_POST['warna'];
    $target_per_minggu = (int) $_POST['target_per_minggu'];

    if (isset($_POST['habit_id']) && $_POST['habit_id'] != '') {
        $habit_id = (int) $_POST['habit_id'];
        $query = "UPDATE habits SET 
                    nama_habit = '$nama_habit',
                    icon = '$icon',
                    warna = '$warna',
                    target_per_minggu = $target_per_minggu
                  WHERE id = $habit_id AND user_id = $user_id";
        mysqli_query($conn, $query);
        $alert = "Habit berhasil diperbarui!";
    } else {
        $query = "INSERT INTO habits (nama_habit, icon, warna, target_per_minggu, user_id) 
                   VALUES ('$nama_habit', '$icon', '$warna', $target_per_minggu, $user_id)";
        mysqli_query($conn, $query);
        $alert = "Habit berhasil ditambahkan!";
    }

    header("Location: habits.php?msg=" . urlencode($alert));
    exit();
}

if (isset($_GET['msg'])) {
    $alert = $_GET['msg'];
}

// ================================================
// AMBIL DATA EDIT (jika ada)
// ================================================
$editHabit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM habits WHERE id = $id AND user_id = $user_id");
    $editHabit = mysqli_fetch_assoc($result);
}

$showModal = (isset($_GET['action']) && $_GET['action'] == 'add') || $editHabit;

// ================================================
// AMBIL SEMUA HABIT
// ================================================
$habits = mysqli_query($conn, "SELECT * FROM habits WHERE user_id = $user_id ORDER BY created_at DESC");

// Pilihan icon untuk dropdown
$iconOptions = ['✨', '💧', '📚', '🏃', '🧘', '🥗', '😴', '✍️', '🎨', '🎵', '🌱', '☕'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habit Tracker - My Planner</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .modal-overlay { display: <?= $showModal ? 'flex' : 'none' ?>; }
        .icon-picker { display:flex; flex-wrap:wrap; gap:8px; margin-top:6px; }
        .icon-option { width:36px; height:36px; border-radius:10px; border:1px solid #E2D9E2; display:flex; align-items:center; justify-content:center; font-size:18px; cursor:pointer; background:#fff; }
        .icon-option.selected { background:#9580D4; border-color:#9580D4; }
        .color-picker { display:flex; gap:8px; margin-top:6px; }
        .color-option { width:32px; height:32px; border-radius:50%; cursor:pointer; border:2px solid transparent; }
        .color-option.selected { border-color:#4a4458; }
    </style>
</head>
<body>
<div class="app-wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="breadcrumb">Habit Tracker</div>
                <div class="page-title">Kebiasaan Harian</div>
            </div>
            <a href="habits.php?action=add" class="btn-primary">+ Tambah Habit</a>
        </div>

        <?php if ($alert): ?>
            <div class="alert alert-success"><?= htmlspecialchars($alert) ?></div>
        <?php endif; ?>

        <div class="habit-grid">
            <?php if (mysqli_num_rows($habits) > 0): ?>
                <?php while ($h = mysqli_fetch_assoc($habits)): ?>
                    <?php
                        $sudahCentang = sudahDicentangHariIni($conn, $h['id']);
                        $streak = hitungStreak($conn, $h['id']);
                    ?>
                    <div class="habit-card">
                        <div class="habit-icon-box" style="background: <?= $h['warna'] ?>22; color: <?= $h['warna'] ?>;">
                            <?= $h['icon'] ?>
                        </div>
                        <div class="habit-name"><?= htmlspecialchars($h['nama_habit']) ?></div>
                        <div class="habit-streak">🔥 <?= $streak ?> hari streak</div>

                        <a href="habits.php?check=<?= $h['id'] ?>" class="habit-check-btn <?= $sudahCentang ? 'checked' : '' ?>">
                            <?= $sudahCentang ? '✓ Selesai Hari Ini' : 'Tandai Selesai' ?>
                        </a>

                        <div class="habit-actions">
                            <a href="habits.php?edit=<?= $h['id'] ?>" class="action-edit">Edit</a>
                            <a href="habits.php?delete=<?= $h['id'] ?>" class="action-delete" onclick="return confirm('Yakin hapus habit ini?')">Hapus</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card-box" style="text-align:center; color:#a89bb5; grid-column: 1/-1;">
                    Belum ada habit. Yuk mulai bangun kebiasaan baik! 🌱
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH / EDIT HABIT -->
<div class="modal-overlay">
    <div class="modal-box">
        <span class="modal-close" onclick="window.location.href='habits.php'">&times;</span>
        <div class="modal-title"><?= $editHabit ? 'Edit Habit' : 'Tambah Habit Baru' ?></div>

        <form method="POST" action="habits.php">
            <?php if ($editHabit): ?>
                <input type="hidden" name="habit_id" value="<?= $editHabit['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Nama Habit</label>
                <input type="text" name="nama_habit" class="form-control" required
                    placeholder="Contoh: Minum air 8 gelas"
                    value="<?= $editHabit ? htmlspecialchars($editHabit['nama_habit']) : '' ?>">
            </div>

            <div class="form-group">
                <label>Pilih Icon</label>
                <input type="hidden" name="icon" id="selectedIcon" value="<?= $editHabit ? $editHabit['icon'] : '✨' ?>">
                <div class="icon-picker">
                    <?php foreach ($iconOptions as $ic): ?>
                        <div class="icon-option <?= ($editHabit && $editHabit['icon'] == $ic) || (!$editHabit && $ic == '✨') ? 'selected' : '' ?>"
                             onclick="pilihIcon('<?= $ic ?>', this)">
                            <?= $ic ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Pilih Warna</label>
                <input type="hidden" name="warna" id="selectedWarna" value="<?= $editHabit ? $editHabit['warna'] : '#9580D4' ?>">
                <div class="color-picker">
                    <?php
                    $warnaOptions = ['#9580D4', '#75ADC9', '#CDB9DD', '#c97a7a', '#6a9c5a', '#c9985a'];
                    foreach ($warnaOptions as $w):
                    ?>
                        <div class="color-option <?= ($editHabit && $editHabit['warna'] == $w) || (!$editHabit && $w == '#9580D4') ? 'selected' : '' ?>"
                             style="background: <?= $w ?>;" onclick="pilihWarna('<?= $w ?>', this)">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Target per Minggu (hari)</label>
                <input type="number" name="target_per_minggu" class="form-control" min="1" max="7"
                    value="<?= $editHabit ? $editHabit['target_per_minggu'] : 7 ?>">
            </div>

            <button type="submit" class="btn-primary" style="width:100%; margin-top:10px;">
                <?= $editHabit ? 'Update Habit' : 'Simpan Habit' ?>
            </button>
        </form>
    </div>
</div>

<script>
    function pilihIcon(icon, el) {
        document.getElementById('selectedIcon').value = icon;
        document.querySelectorAll('.icon-option').forEach(e => e.classList.remove('selected'));
        el.classList.add('selected');
    }

    function pilihWarna(warna, el) {
        document.getElementById('selectedWarna').value = warna;
        document.querySelectorAll('.color-option').forEach(e => e.classList.remove('selected'));
        el.classList.add('selected');
    }
</script>
</body>
</html>