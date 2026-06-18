<?php
require_once 'config/koneksi.php';
require_once 'functions/helpers.php';
cekLogin();

$user_id = $_SESSION['user_id'];
$alert = '';

// ================================================
// PROSES DELETE DIARY
// ================================================
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM diary_entries WHERE id = $id AND user_id = $user_id");
    header("Location: diary.php");
    exit();
}

// ================================================
// PROSES TAMBAH / EDIT DIARY
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $isi = mysqli_real_escape_string($conn, $_POST['isi']);
    $mood = $_POST['mood'];
    $tanggal_catatan = $_POST['tanggal_catatan'];

    // Upload foto jika ada
    $foto_lampiran = '';
    if (isset($_FILES['foto_lampiran']) && $_FILES['foto_lampiran']['error'] === 0) {
        $namaFile = time() . '_' . basename($_FILES['foto_lampiran']['name']);
        $tujuan = 'assets/uploads/' . $namaFile;
        if (move_uploaded_file($_FILES['foto_lampiran']['tmp_name'], $tujuan)) {
            $foto_lampiran = $namaFile;
        }
    }

    if (isset($_POST['diary_id']) && $_POST['diary_id'] != '') {
        $diary_id = (int) $_POST['diary_id'];
        $fotoQuery = $foto_lampiran ? "foto_lampiran = '$foto_lampiran'," : "";

        $query = "UPDATE diary_entries SET 
                    judul = '$judul',
                    isi = '$isi',
                    mood = '$mood',
                    $fotoQuery
                    tanggal_catatan = '$tanggal_catatan'
                  WHERE id = $diary_id AND user_id = $user_id";
        mysqli_query($conn, $query);
        $alert = "Catatan diary berhasil diperbarui!";
    } else {
        $query = "INSERT INTO diary_entries (judul, isi, mood, foto_lampiran, tanggal_catatan, user_id) 
                   VALUES ('$judul', '$isi', '$mood', '$foto_lampiran', '$tanggal_catatan', $user_id)";
        mysqli_query($conn, $query);
        $alert = "Catatan diary berhasil ditambahkan!";
    }

    header("Location: diary.php?msg=" . urlencode($alert));
    exit();
}

if (isset($_GET['msg'])) {
    $alert = $_GET['msg'];
}

// ================================================
// AMBIL DATA EDIT (jika ada)
// ================================================
$editDiary = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM diary_entries WHERE id = $id AND user_id = $user_id");
    $editDiary = mysqli_fetch_assoc($result);
}

$showModal = (isset($_GET['action']) && $_GET['action'] == 'add') || $editDiary;

// ================================================
// AMBIL SEMUA DIARY
// ================================================
$diaryEntries = mysqli_query($conn, "SELECT * FROM diary_entries WHERE user_id = $user_id ORDER BY tanggal_catatan DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diary - My Planner</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .modal-overlay { display: <?= $showModal ? 'flex' : 'none' ?>; }
        .mood-picker { display:flex; gap:8px; margin-top:6px; }
        .mood-option { flex:1; padding:10px; border-radius:12px; border:1px solid #E2D9E2; text-align:center; cursor:pointer; font-size:22px; background:#fff; }
        .mood-option.selected { background:#9580D4; border-color:#9580D4; }
    </style>
</head>
<body>
<div class="app-wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="breadcrumb">Diary</div>
                <div class="page-title">Catatan Harian</div>
            </div>
            <a href="diary.php?action=add" class="btn-primary">+ Tulis Catatan</a>
        </div>

        <?php if ($alert): ?>
            <div class="alert alert-success"><?= htmlspecialchars($alert) ?></div>
        <?php endif; ?>

        <div class="diary-grid">
            <?php if (mysqli_num_rows($diaryEntries) > 0): ?>
                <?php while ($d = mysqli_fetch_assoc($diaryEntries)): ?>
                    <div class="diary-card">
                        <div class="diary-card-top">
                            <div class="diary-mood"><?= emojiMood($d['mood']) ?></div>
                            <div class="diary-date"><?= formatTanggal($d['tanggal_catatan']) ?></div>
                        </div>

                        <?php if ($d['foto_lampiran']): ?>
                            <img src="assets/uploads/<?= $d['foto_lampiran'] ?>" class="diary-photo" alt="Foto diary">
                        <?php endif; ?>

                        <div class="diary-title"><?= htmlspecialchars($d['judul']) ?></div>
                        <div class="diary-text"><?= nl2br(htmlspecialchars(substr($d['isi'], 0, 150))) ?><?= strlen($d['isi']) > 150 ? '...' : '' ?></div>

                        <div class="diary-actions">
                            <a href="diary.php?edit=<?= $d['id'] ?>" class="action-edit">Edit</a>
                            <a href="diary.php?delete=<?= $d['id'] ?>" class="action-delete" onclick="return confirm('Yakin hapus catatan ini?')">Hapus</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card-box" style="text-align:center; color:#a89bb5; grid-column: 1/-1;">
                    Belum ada catatan diary. Yuk mulai menulis hari ini! 📝
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH / EDIT DIARY -->
<div class="modal-overlay">
    <div class="modal-box">
        <span class="modal-close" onclick="window.location.href='diary.php'">&times;</span>
        <div class="modal-title"><?= $editDiary ? 'Edit Catatan' : 'Tulis Catatan Baru' ?></div>

        <form method="POST" action="diary.php" enctype="multipart/form-data">
            <?php if ($editDiary): ?>
                <input type="hidden" name="diary_id" value="<?= $editDiary['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Judul</label>
                <input type="text" name="judul" class="form-control" required
                    placeholder="Apa yang terjadi hari ini?"
                    value="<?= $editDiary ? htmlspecialchars($editDiary['judul']) : '' ?>">
            </div>

            <div class="form-group">
                <label>Bagaimana Perasaanmu?</label>
                <input type="hidden" name="mood" id="selectedMood" value="<?= $editDiary ? $editDiary['mood'] : 'biasa' ?>">
                <div class="mood-picker">
                    <?php
                    $moodOptions = ['senang' => '😊', 'semangat' => '🔥', 'biasa' => '😐', 'lelah' => '😴', 'sedih' => '😢'];
                    foreach ($moodOptions as $key => $emoji):
                    ?>
                        <div class="mood-option <?= ($editDiary && $editDiary['mood'] == $key) || (!$editDiary && $key == 'biasa') ? 'selected' : '' ?>"
                             onclick="pilihMood('<?= $key ?>', this)">
                            <?= $emoji ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Isi Catatan</label>
                <textarea name="isi" class="form-control" rows="5" required
                    placeholder="Ceritakan harimu disini..."><?= $editDiary ? htmlspecialchars($editDiary['isi']) : '' ?></textarea>
            </div>

            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal_catatan" class="form-control" required
                    value="<?= $editDiary ? $editDiary['tanggal_catatan'] : date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label>Upload Foto (Opsional)</label>
                <input type="file" name="foto_lampiran" class="form-control" accept="image/*">
                <?php if ($editDiary && $editDiary['foto_lampiran']): ?>
                    <small style="color:#a89bb5;">Foto saat ini: <?= htmlspecialchars($editDiary['foto_lampiran']) ?></small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; margin-top:10px;">
                <?= $editDiary ? 'Update Catatan' : 'Simpan Catatan' ?>
            </button>
        </form>
    </div>
</div>

<script>
    function pilihMood(mood, el) {
        document.getElementById('selectedMood').value = mood;
        document.querySelectorAll('.mood-option').forEach(e => e.classList.remove('selected'));
        el.classList.add('selected');
    }
</script>
</body>
</html>