SET NAMES utf8mb4 COLLATE utf8mb4_0900_ai_ci;

INSERT INTO Alergi (id_alergi, nama_alergi, kategori_alergi, keterangan_alergi) VALUES
('A0001', 'Parasetamol', 'Obat', 'Alergen pada obat yang mengandung parasetamol'),
('A0002', 'Kacang Tanah', 'Makanan', 'Alergen makanan berbahan kacang tanah'),
('A0003', 'Amoxicillin', 'Obat', 'Alergi antibiotik golongan penisilin'),
('A0004', 'Seafood / Udang', 'Makanan', 'Menyebabkan gatal-gatal dan kemerahan pada kulit'),
('A0005', 'Debu dan Tungau', 'Lingkungan', 'Memicu bersin dan asma ringan'),
('A0006', 'Udara Dingin', 'Lingkungan', 'Menyebabkan biduran (urtikaria) saat suhu turun');

INSERT INTO Poliklinik (id_poliklinik, nama_poliklinik, lokasi_poliklinik) VALUES
('P0001', 'Poli Penyakit Dalam', 'Gedung A Lantai 2'),
('P0002', 'Poli Anak', 'Gedung A Lantai 1'),
('P0003', 'Poli Kandungan & Kebidanan', 'Gedung B Lantai 2'),
('P0004', 'Poli Gigi & Mulut', 'Gedung C Lantai 1'),
('P0005', 'Poli Mata', 'Gedung C Lantai 2'),
('P0006', 'Poli Saraf', 'Gedung A Lantai 3');

INSERT INTO Dokter (id_dokter, nama_dokter, nomor_telepon_dokter, spesialisasi_dokter) VALUES
('D0001', 'Dr. Budi Utomo', '08123456789', 'Spesialis Penyakit Dalam'),
('D0002', 'Dr. Andi Saputra, Sp.A', '08122334455', 'Spesialis Anak'),
('D0003', 'Dr. Maria Lestari, Sp.OG', '08133445566', 'Spesialis Kandungan (Obgyn)'),
('D0004', 'Drg. Hendra Wijaya', '08144556677', 'Dokter Gigi'),
('D0005', 'Dr. Ratna Sari, Sp.M', '08155667788', 'Spesialis Mata'),
('D0006', 'Dr. Lukman Hakim, Sp.N', '08166778899', 'Spesialis Saraf');

INSERT INTO Perawat (id_perawat, nama_perawat, nomor_telepon_perawat) VALUES
('N0001', 'Suster Siti Aminah', '08987654321'),
('N0002', 'Br. Ahmad Fauzi', '08911223344'),
('N0003', 'Suster Dewi Sartika', '08922334455'),
('N0004', 'Suster Rina Maharani', '08933445566'),
('N0005', 'Br. Doni Setiawan', '08944556677');

INSERT INTO Kamar (id_kamar, nomor_kamar, tipe_kamar, status_kamar) VALUES
('K0001', 101, 'VIP', 'Kosong'),
('K0002', 102, 'VIP', 'Kosong'),
('K0003', 201, 'Kelas 1', 'Kosong'),
('K0004', 202, 'Kelas 1', 'Kosong'),
('K0005', 301, 'Kelas 2', 'Kosong'),
('K0006', 302, 'Kelas 3', 'Kosong');

INSERT INTO Obat (id_obat, nama_obat, stok_obat, harga_obat) VALUES
('O0001', 'Parasetamol 500mg', 100, 5000.00),
('O0002', 'Amoxicillin 500mg', 200, 15000.00),
('O0003', 'Ibuprofen 400mg', 150, 8000.00),
('O0004', 'Omeprazole 20mg', 120, 12000.00),
('O0005', 'Cetirizine 10mg', 80, 6000.00),
('O0006', 'Vitamin C 500mg', 300, 3000.00),
('O0007', 'Asam Mefenamat 500mg', 250, 7500.00);

INSERT INTO Asuransi (nomor_asuransi, nama_lembaga_asuransi, jenis_asuransi) VALUES
('ASR0000000001', 'BPJS Kesehatan', 'JKN'),
('ASR0000000002', 'Mandiri Inhealth', 'Asuransi Swasta / Karyawan'),
('ASR0000000003', 'Prudential', 'Asuransi Kesehatan Swasta'),
('ASR0000000004', 'Allianz', 'Asuransi Kesehatan Swasta');

INSERT INTO Jenis_Pembayaran (id_jenis_pembayaran, nama_jenis_pembayaran) VALUES
('JP001', 'Non-Tunai (Asuransi)'),
('JP002', 'Tunai / Cash'),
('JP003', 'Kartu Debit'),
('JP004', 'Kartu Kredit'),
('JP005', 'QRIS / E-Wallet');

INSERT INTO Shift (id_shift, Jenis_Shift, Jam_Masuk, Jam_Selesai) VALUES
(1, 'Pagi', '07:00:00', '14:00:00'),
(2, 'Siang', '14:00:00', '21:00:00'),
(3, 'Malam', '21:00:00', '07:00:00');

