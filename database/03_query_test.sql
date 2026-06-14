-- Uji Coba Stored Procedure Registrasi Pasien Baru
CALL registrasi_pasien_baru(
    'PS001', 'Andi Pratama', '081122334455', 'Jl. Merdeka No. 45', '1995-08-17', 'L',
    '[{"id_alergi":"A0001","reaksi":"Gatal-gatal pada kulit","keparahan":"Sedang","tanggal_diketahui":"2024-02-10","status":"Aktif","catatan":"Dikonfirmasi setelah konsumsi obat"}]',
    'R0001', 'P0001', 'Rawat Inap'
);

-- Contoh registrasi Rawat Jalan tanpa data Rawat_Inap.
CALL registrasi_pasien_baru(
    'PS002', 'Sari Lestari', '081133445566', 'Jl. Melati No. 12', '1998-04-21', 'P',
    '[]',
    'R0002', 'P0001', 'Rawat Jalan'
);

-- Checker
SELECT * FROM Pasien;
SELECT * FROM Riwayat_Alergi;
SELECT * FROM Registrasi;


-- Uji Coba Function Menghitung Umur Pasien & Menampilkan Riwayat Alergi
SELECT hitung_umur_pasien('PS001') AS Umur_Pasien, riwayat_alergi_pasien('PS001') AS Alergi_Pasien;


-- Uji Coba Stored Procedure Penjadwalan Jaga Dokter & Perawat (dan validasi bentrok)
CALL tambah_jadwal_jaga('J0001', '2026-06-08', 'N0001', 'D0001', 1);

-- Percobaan jadwal bentrok (Akan menghasilkan error SIGNAL SQLSTATE)
-- CALL tambah_jadwal_jaga('J0002', '2026-06-08', 'N0001', 'D0001', 1);


-- Uji Coba Stored Procedure Rawat Inap Pasien (dan validasi ketersediaan kamar)
CALL proses_rawat_inap('RI001', '2026-06-07 10:00:00', 'K0001', 'R0001');

-- Cek status kamar sekarang (Seharusnya terisi oleh trigger/SP)
SELECT cek_ketersediaan_kamar('K0001') AS Status_Kamar;

-- Percobaan rawat inap di kamar yang sama (Akan menghasilkan error)
-- CALL proses_rawat_inap('RI002', '2026-06-07 11:00:00', 'K0001', 'R0001');


-- Uji Coba Stored Procedure Pembuatan Rekam Medis (Dan Trigger Generate ID Rekam Medis)
-- ID Rekam Medis diset 'RM000' agar di-generate otomatis oleh trigger menjadi 'RM001'
CALL buat_rekam_medis(
    'RM000', 'Demam tinggi dan pusing kepala', 'R0001', 'D0001', 'N0001', 'RI001',
    'DG001', 'Demam Dengue', 'Gejala awal demam berdarah',
    'T0001', 'Pemeriksaan Darah Lengkap', 150000.00, 'Trombosit menurun',
    TRUE, 'RS001'
);

SELECT * FROM Rekam_Medis;
SELECT * FROM Diagnosa;
SELECT * FROM Tindakan_Medis;
SELECT * FROM Resep;

-- Rawat Jalan tetap dapat memiliki rekam medis, diagnosa, tindakan, dan resep.
CALL buat_rekam_medis(
    'RM000', 'Batuk ringan selama tiga hari', 'R0002', 'D0001', 'N0001', NULL,
    'DG002', 'Infeksi Saluran Pernapasan Atas', 'Gejala ringan tanpa rawat inap',
    'T0002', 'Pemeriksaan Umum', 75000.00, 'Kondisi stabil',
    TRUE, 'RS002'
);


-- Uji Coba Trigger Mengurangi Stok Obat & Validasi Stok Obat
SELECT stok_obat FROM Obat WHERE id_obat = 'O0001'; -- Stok awal 100

-- Kaitkan obat dengan resep melalui Detail_Resep (Trigger validation & reduction berjalan)
INSERT INTO Detail_Resep (Resep_id_resep, Obat_id_obat, jumlah_obat, dosis_obat) VALUES ('RS001', 'O0001', 2, '3x1 tablet');
INSERT INTO Detail_Resep (Resep_id_resep, Obat_id_obat, jumlah_obat, dosis_obat) VALUES ('RS002', 'O0001', 3, '2x1 tablet');

SELECT stok_obat FROM Obat WHERE id_obat = 'O0001'; -- Seharusnya berkurang 5 menjadi 95


-- Uji Coba Function hitung_total_obat
SELECT hitung_total_obat('RS001') AS Total_Obat_Resep;


-- Uji Coba Stored Procedure Pembayaran & Trigger Sinkronisasi Total
-- Proses Pembayaran terlebih dahulu
CALL proses_pembayaran('PY001', 'R0001', 'JP001', 'ASR0000000001');
CALL proses_pembayaran('PY002', 'R0002', 'JP001', NULL);

-- Buat detail pembayaran (Trigger akan mensinkronisasi total)
INSERT INTO Detail_Pembayaran (Pembayaran_id_pembayaran, keterangan_biaya, sub_total, Tindakan_Medis_id_tindakan_medis, Rawat_Inap_id_rawat_inap, Resep_id_resep) VALUES
('PY001', 'Biaya Tindakan, Rawat Inap & Obat', 250000.00, 'T0001', 'RI001', 'RS001'),
('PY002', 'Biaya Rawat Jalan & Obat', 100000.00, 'T0002', NULL, 'RS002');


-- Cek pembayaran (total_biaya seharusnya otomatis sinkron dengan sub_total)
SELECT * FROM Pembayaran;

-- Update sub_total pada Detail_Pembayaran (Seharusnya mengupdate total_biaya di Pembayaran lewat trigger)
-- Asumsi ID Detail_Pembayaran auto_increment dimulai dari 1
UPDATE Detail_Pembayaran SET sub_total = 275000.00 WHERE id_detail_pembayaran = 1;
SELECT * FROM Pembayaran; -- Seharusnya total_biaya berubah menjadi 275000.00


-- Uji Coba Trigger Audit Rekam Medis
-- Update keluhan rekam medis
UPDATE Rekam_Medis SET keluhan_pasien = 'Demam tinggi disertai mual' WHERE id_rekam_medis = 'RM001';

-- Cek Log Audit
SELECT * FROM Log_Audit_Rekam_Medis;


-- Selesai Rawat Inap & Uji Coba Trigger Mengubah Status Kamar Menjadi Kosong
UPDATE Rawat_Inap SET tanggal_keluar = '2026-06-12 12:00:00' WHERE id_rawat_inap = 'RI001';

-- Cek status kamar sekarang (Seharusnya kembali 'Kosong' karena trigger)
SELECT cek_ketersediaan_kamar('K0001') AS Status_Kamar;

SELECT 
    pol.nama_poliklinik, jp.nama_jenis_pembayaran, COUNT(pem.id_pembayaran) AS jumlah_transaksi, SUM(pem.total_biaya) AS total_pendapatan
FROM Pembayaran pem
JOIN Registrasi reg ON pem.Registrasi_id_registrasi = reg.id_registrasi
JOIN Poliklinik pol ON reg.Poliklinik_id_poliklinik = pol.id_poliklinik
JOIN Jenis_Pembayaran jp ON pem.Jenis_Pembayaran_id_jenis_pembayaran = jp.id_jenis_pembayaran
WHERE MONTH(pem.tanggal_pembayaran) = MONTH(CURDATE()) AND YEAR(pem.tanggal_pembayaran) = YEAR(CURDATE())
GROUP BY pol.nama_poliklinik, jp.nama_jenis_pembayaran
ORDER BY total_pendapatan DESC;


