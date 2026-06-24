CREATE DATABASE IF NOT EXISTS bernies_lovely;
USE bernies_lovely;

CREATE TABLE kategori (
    id_kategori VARCHAR(6) NOT NULL,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    icon VARCHAR(50),
    PRIMARY KEY (id_kategori)
) ENGINE=InnoDB;

CREATE TABLE produk (
    id_produk VARCHAR(6) NOT NULL,
    id_kategori VARCHAR(6) NOT NULL,
    nama_produk VARCHAR(150) NOT NULL,
    harga DECIMAL(12,2) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    gambar VARCHAR(255) DEFAULT NULL,
    deskripsi TEXT,
    rating DECIMAL(2,1) DEFAULT 0.0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_produk),
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pelanggan (
    id_pelanggan VARCHAR(6) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telepon VARCHAR(20),
    alamat TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_pelanggan)
) ENGINE=InnoDB;

CREATE TABLE pesanan (
    id_pesanan VARCHAR(6) NOT NULL,
    id_pelanggan VARCHAR(6) NOT NULL,
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(12,2) DEFAULT 0,
    status ENUM('pending','proses','selesai','batal') DEFAULT 'pending',
    PRIMARY KEY (id_pesanan),
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE detail_pesanan (
    id_detail VARCHAR(6) NOT NULL,
    id_pesanan VARCHAR(6) NOT NULL,
    id_produk VARCHAR(6) NOT NULL,
    jumlah INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (id_detail),
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES produk(id_produk) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Sample Data --
INSERT INTO kategori (id_kategori, nama_kategori, deskripsi, icon) VALUES
('KAT001', 'Classic Cakes', 'Kue klasik favorit sepanjang masa', '🎂'),
('KAT002', 'Gourmet Cakes', 'Kreasi premium dengan bahan pilihan', '🍰'),
('KAT003', 'Desserts', 'Pencuci mulut manis dan lezat', '🍮'),
('KAT004', 'Cookies & Pastry', 'Kue kering dan pastry renyah', '🍪');

INSERT INTO produk (id_produk, id_kategori, nama_produk, harga, stok, deskripsi, rating) VALUES
('PRD001', 'KAT001', 'Rich Chocolate Truffle Cake', 250000.00, 20, 'Kue cokelat truffle kaya rasa', 5.0),
('PRD002', 'KAT001', 'Choco Chip Truffle Cake', 200000.00, 15, 'Kue truffle dengan taburan choco chip', 4.8),
('PRD003', 'KAT002', 'Tropical Fruit N Almond Cake', 220000.00, 10, 'Paduan buah tropis dan kacang almond', 4.7),
('PRD004', 'KAT002', 'Rich Butterscotch Crunch', 180000.00, 25, 'Kue butterscotch renyah dan gurih', 4.9),
('PRD005', 'KAT003', 'Lava Cake', 50000.00, 30, 'Lava cake cokelat lumer di mulut', 4.9),
('PRD006', 'KAT003', 'Tiramisu Dessert Box', 75000.00, 40, 'Dessert box tiramisu klasik', 4.8),
('PRD007', 'KAT004', 'Nastar Keju Premium', 120000.00, 50, 'Kue nastar isi nanas manis dengan keju', 4.9),
('PRD008', 'KAT004', 'Kastengel Keju Edam', 130000.00, 45, 'Kastengel renyah dengan keju edam asli', 4.8);

INSERT INTO pelanggan (id_pelanggan, nama, email, telepon, alamat) VALUES
('PLG001', 'Budi Santoso', 'budi@example.com', '081234567890', 'Jl. Merdeka No. 1, Jakarta'),
('PLG002', 'Siti Aminah', 'siti@example.com', '081298765432', 'Jl. Sudirman No. 10, Bandung'),
('PLG003', 'Andi Pratama', 'andi@example.com', '085612345678', 'Jl. Malioboro No. 5, Yogyakarta'),
('PLG004', 'Rina Wati', 'rina@example.com', '085798765432', 'Jl. Pahlawan No. 8, Surabaya');

INSERT INTO pesanan (id_pesanan, id_pelanggan, total, status) VALUES
('PSN001', 'PLG001', 300000.00, 'selesai'),
('PSN002', 'PLG002', 200000.00, 'proses'),
('PSN003', 'PLG003', 180000.00, 'pending');

INSERT INTO detail_pesanan (id_detail, id_pesanan, id_produk, jumlah, subtotal) VALUES
('DTL001', 'PSN001', 'PRD001', 1, 250000.00),
('DTL002', 'PSN001', 'PRD005', 1, 50000.00),
('DTL003', 'PSN002', 'PRD002', 1, 200000.00),
('DTL004', 'PSN003', 'PRD004', 1, 180000.00);
