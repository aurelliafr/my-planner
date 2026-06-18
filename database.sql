CREATE DATABASE IF NOT EXISTS my_planner;
USE my_planner;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    foto_profil VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE todos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    kategori ENUM('task', 'project', 'self_care', 'academic') DEFAULT 'task',
    prioritas ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('belum', 'proses', 'selesai') DEFAULT 'belum',
    file_lampiran VARCHAR(255),
    tanggal_deadline DATE,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE habits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_habit VARCHAR(150) NOT NULL,
    icon VARCHAR(10) DEFAULT '✨',
    warna VARCHAR(20) DEFAULT '#9580D4',
    target_per_minggu INT DEFAULT 7,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE habit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habit_id INT NOT NULL,
    tanggal DATE NOT NULL,
    selesai BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (habit_id) REFERENCES habits(id) ON DELETE CASCADE,
    UNIQUE KEY unique_log (habit_id, tanggal)
);

CREATE TABLE diary_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    isi TEXT NOT NULL,
    mood ENUM('senang', 'biasa', 'sedih', 'semangat', 'lelah') DEFAULT 'biasa',
    foto_lampiran VARCHAR(255),
    user_id INT NOT NULL,
    tanggal_catatan DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (nama, email, password) VALUES
('Aurellia', 'aurellia@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- password default: "password"