<?php

session_start();

// Cek apakah user sudah login
function cekLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Format tanggal Indonesia 
function formatTanggal($tanggal) {
    // Jaring pengaman jika tanggal kosong, null, atau format kosong default database
    if (empty($tanggal) || $tanggal == '0000-00-00' || trim($tanggal) == '') {
        return 'Tidak ada deadline';
    }

    $pecah = explode('-', $tanggal);
    
    // Pastikan string tanggal terpecah dengan benar menjadi 3 bagian (YYYY-MM-DD)
    if (count($pecah) !== 3) {
        return $tanggal;
    }

    $bulan = [
        '01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr',
        '05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu',
        '09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'
    ];

    // Antisipasi jika indeks bulan tidak ditemukan di dalam array $bulan
    if (!isset($bulan[$pecah[1]])) {
        return $tanggal;
    }

    return $pecah[2] . ' ' . $bulan[$pecah[1]] . ' ' . $pecah[0];
}

// Label status to-do
function labelStatus($status) {
    $label = [
        'belum' => 'Belum Dikerjakan',
        'proses' => 'Sedang Dikerjakan',
        'selesai' => 'Selesai'
    ];
    return $label[$status] ?? $status;
}

// Label kategori to-do
function labelKategori($kategori) {
    $label = [
        'task' => 'Tugas',
        'project' => 'Project',
        'self_care' => 'Self Care',
        'academic' => 'Akademik'
    ];
    return $label[$kategori] ?? $kategori;
}

// Warna badge prioritas
function warnaPrioritas($prioritas) {
    $warna = [
        'low' => '#75ADC9',
        'medium' => '#CDB9DD',
        'high' => '#9580D4'
    ];
    return $warna[$prioritas] ?? '#94a3b8';
}

// Emoji mood diary
function emojiMood($mood) {
    $emoji = [
        'senang' => '😊',
        'biasa' => '😐',
        'sedih' => '😢',
        'semangat' => '🔥',
        'lelah' => '😴'
    ];
    return $emoji[$mood] ?? '😐';
}

// Cek apakah habit sudah di-checklist hari ini
function sudahDicentangHariIni($conn, $habit_id) {
    $today = date('Y-m-d');
    $result = mysqli_query($conn, "SELECT id FROM habit_logs WHERE habit_id = $habit_id AND tanggal = '$today'");
    return mysqli_num_rows($result) > 0;
}

// Hitung streak habit (berapa hari berturut-turut)
function hitungStreak($conn, $habit_id) {
    $result = mysqli_query($conn, "SELECT tanggal FROM habit_logs WHERE habit_id = $habit_id ORDER BY tanggal DESC");
    $tanggalLog = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tanggalLog[] = $row['tanggal'];
    }

    $streak = 0;
    $cekTanggal = new DateTime();

    foreach ($tanggalLog as $tgl) {
        $tglLog = new DateTime($tgl);
        $selisih = $cekTanggal->diff($tglLog)->days;

        if ($selisih <= 1) {
            $streak++;
            $cekTanggal = $tglLog;
        } else {
            break;
        }
    }

    return $streak;
}
?>