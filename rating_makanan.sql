create database rating_makanan;
use rating_makanan;

-- Tabel users (untuk login/admin)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel foods (data makanan)
CREATE TABLE foods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  origin VARCHAR(150) DEFAULT '',
  description TEXT,
  image VARCHAR(255) DEFAULT '',
  tasteatlas_rating DECIMAL(3,2) DEFAULT NULL,
  world_rank VARCHAR(50) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel reviews (ulasan / rating pengguna)
CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  food_id INT NOT NULL,
  user_id INT DEFAULT NULL,
  guest_name VARCHAR(120) DEFAULT NULL,
  email VARCHAR(150) DEFAULT NULL,
  rating TINYINT NOT NULL,
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT IGNORE INTO users (username, password_hash, role)
VALUES ('cuyy', '<REPLACE_WITH_HASH>', 'admin');
UPDATE users SET password_hash = '$2y$10$....hasil_hash....' WHERE username = 'cuyy';

