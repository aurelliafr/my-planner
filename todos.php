<?php
require_once 'config/koneksi.php';
require_once 'functions/helpers.php';
cekLogin();

$user_id = $_SESSION['user_id'];
$alert = '';

// PROSES DELETE TODO
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM todos WHERE id = $id AND user_id = $user_id");
    header("Location: todos.php");
    exit();
}

// PROSES TOGGLE STATUS (CHECKLIST CEPAT)
if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $cek = mysqli_query($conn, "SELECT status FROM todos WHERE id = $id AND user_id = $user_id");
    $data = mysqli_fetch_assoc($cek);
    $statusBaru = ($data['status'] == 'selesai') ? 'belum' : 'selesai';
    mysqli_query($conn, "UPDATE todos SET status = '$statusBaru' WHERE id = $id AND user_id = $user_id");
    header("Location: todos.php");
    exit();
}

// PROSES TAMBAH / EDIT TODO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $kategori = $_POST['kategori'];
    $prioritas = $_POST['prioritas'];
    $status = $_POST['status'];
    $tanggal_deadline = $_POST['tanggal_deadline'];

    // Upload file jika ada
    $file_lampiran = '';
    if (isset($_FILES['file_lampiran']) && $_FILES['file_lampiran']['error'] === 0) {
        $namaFile = time() . '_' . basename($_FILES['file_lampiran']['name']);
        $tujuan = 'assets/uploads/' . $namaFile;
        if (move_uploaded_file($_FILES['file_lampiran']['tmp_name'], $tujuan)) {
            $file_lampiran = $namaFile;
        }
    }

    if (isset($_POST['todo_id']) && $_POST['todo_id'] != '') {
        // UPDATE
        $todo_id = (int) $_POST['todo_id'];
        $fileQuery = $file_lampiran ? "file_lampiran = '$file_lampiran'," : "";

        $query = "UPDATE todos SET 
                    judul = '$judul',
                    deskripsi = '$deskripsi',
                    kategori = '$kategori',
                    prioritas = '$prioritas',
                    status = '$status',
                    $fileQuery
                    tanggal_deadline = '$tanggal_deadline'
                  WHERE id = $todo_id AND user_id = $user_id";
        mysqli_query($conn, $query);
        $alert = "To Do berhasil diperbarui!";
    } else {
        // INSERT BARU
        $query = "INSERT INTO todos 
                    (judul, deskripsi, kategori, prioritas, status, file_lampiran, tanggal_deadline, user_id) 
                  VALUES 
                    ('$judul', '$deskripsi', '$kategori', '$prioritas', '$status', '$file_lampiran', '$tanggal_deadline', $user_id)";
        mysqli_query($conn, $query);
        $alert = "To Do berhasil ditambahkan!";
    }

    header("Location: todos.php?msg=" . urlencode($alert));
    exit();
}

if (isset($_GET['msg'])) {
    $alert = $_GET['msg'];
}

// AMBIL DATA EDIT (jika ada)
$editTodo = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM todos WHERE id = $id AND user_id = $user_id");
    $editTodo = mysqli_fetch_assoc($result);
}

$showModal = (isset($_GET['action']) && $_GET['action'] == 'add') || $editTodo;

// FILTER TAB (semua / belum / proses / selesai)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'semua';
$whereFilter = "user_id = $user_id";
if ($filter != 'semua') {
    $whereFilter .= " AND status = '$filter'";
}

$todos = mysqli_query($conn, "SELECT * FROM todos WHERE $whereFilter ORDER BY tanggal_deadline ASC, created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do List - My Planner</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .modal-overlay { display: <?= $showModal ? 'flex' : 'none' ?>; }
    </style>
</head>
<body>
<div class="app-wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="breadcrumb">To Do List</div>
                <div class="page-title">Daftar Tugas</div>
            </div>
            <a href="todos.php?action=add" class="btn-primary">+ Tambah To Do</a>
        </div>

        <?php if ($alert): ?>
            <div class="alert alert-success"><?= htmlspecialchars($alert) ?></div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="todo-tabs">
            <a href="todos.php?filter=semua" class="todo-tab <?= $filter == 'semua' ? 'active' : '' ?>">Semua</a>
            <a href="todos.php?filter=belum" class="todo-tab <?= $filter == 'belum' ? 'active' : '' ?>">Belum</a>
            <a href="todos.php?filter=proses" class="todo-tab <?= $filter == 'proses' ? 'active' : '' ?>">Proses</a>
            <a href="todos.php?filter=selesai" class="todo-tab <?= $filter == 'selesai' ? 'active' : '' ?>">Selesai</a>
        </div>

        <!-- List To Do -->
        <?php if (mysqli_num_rows($todos) > 0): ?>
            <?php while ($t = mysqli_fetch_assoc($todos)): ?>
                <div class="todo-item <?= $t['status'] == 'selesai' ? 'selesai' : '' ?>">
                    <a href="todos.php?toggle=<?= $t['id'] ?>" class="todo-checkbox <?= $t['status'] == 'selesai' ? 'checked' : '' ?>">
                        <?= $t['status'] == 'selesai' ? '✓' : '' ?>
                    </a>
                    <div class="todo-content">
                        <div class="todo-title"><?= htmlspecialchars($t['judul']) ?></div>
                        <div class="todo-meta">
                            <span class="badge" style="background: <?= warnaPrioritas($t['prioritas']) ?>;">
                                <?= ucfirst($t['prioritas']) ?>
                            </span>
                            <span><?= labelKategori($t['kategori']) ?></span>
                            <span>📅 <?= formatTanggal($t['tanggal_deadline']) ?></span>
                            <?php if ($t['file_lampiran']): ?>
                                <a href="assets/uploads/<?= $t['file_lampiran'] ?>" target="_blank" style="color:#9580D4;">📎 File</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="todo-actions">
                        <a href="todos.php?edit=<?= $t['id'] ?>" class="action-edit">Edit</a>
                        <a href="todos.php?delete=<?= $t['id'] ?>" class="action-delete" onclick="return confirm('Yakin hapus to do ini?')">Hapus</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card-box" style="text-align:center; color:#a89bb5;">
                Belum ada to do disini. Yuk tambahkan yang pertama! 🌸
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL TAMBAH / EDIT TODO -->
<div class="modal-overlay">
    <div class="modal-box">
        <span class="modal-close" onclick="window.location.href='todos.php'">&times;</span>
        <div class="modal-title"><?= $editTodo ? 'Edit To Do' : 'Tambah To Do Baru' ?></div>

        <form method="POST" action="todos.php" enctype="multipart/form-data">
            <?php if ($editTodo): ?>
                <input type="hidden" name="todo_id" value="<?= $editTodo['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Judul</label>
                <input type="text" name="judul" class="form-control" required
                    value="<?= $editTodo ? htmlspecialchars($editTodo['judul']) : '' ?>">
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control"><?= $editTodo ? htmlspecialchars($editTodo['deskripsi']) : '' ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" class="form-control">
                        <?php
                        $kategoriOptions = ['task' => 'Tugas', 'project' => 'Project', 'self_care' => 'Self Care', 'academic' => 'Akademik'];
                        foreach ($kategoriOptions as $key => $val):
                        ?>
                            <option value="<?= $key ?>" <?= ($editTodo && $editTodo['kategori'] == $key) ? 'selected' : '' ?>>
                                <?= $val ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Prioritas</label>
                    <select name="prioritas" class="form-control">
                        <option value="low" <?= ($editTodo && $editTodo['prioritas'] == 'low') ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= ($editTodo && $editTodo['prioritas'] == 'medium') ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= ($editTodo && $editTodo['prioritas'] == 'high') ? 'selected' : '' ?>>High</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="belum" <?= ($editTodo && $editTodo['status'] == 'belum') ? 'selected' : '' ?>>Belum Dikerjakan</option>
                        <option value="proses" <?= ($editTodo && $editTodo['status'] == 'proses') ? 'selected' : '' ?>>Sedang Dikerjakan</option>
                        <option value="selesai" <?= ($editTodo && $editTodo['status'] == 'selesai') ? 'selected' : '' ?>>Selesai</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deadline</label>
                    <input type="date" name="tanggal_deadline" class="form-control"
                        value="<?= $editTodo ? $editTodo['tanggal_deadline'] : '' ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Upload File Pendukung</label>
                <input type="file" name="file_lampiran" class="form-control">
                <?php if ($editTodo && $editTodo['file_lampiran']): ?>
                    <small style="color:#a89bb5;">File saat ini: <?= htmlspecialchars($editTodo['file_lampiran']) ?></small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; margin-top:10px;">
                <?= $editTodo ? 'Update To Do' : 'Simpan To Do' ?>
            </button>
        </form>
    </div>
</div>
</body>
</html>