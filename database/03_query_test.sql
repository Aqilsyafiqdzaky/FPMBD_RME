
SELECT 
    pol.nama_poliklinik, jp.nama_jenis_pembayaran, COUNT(pem.id_pembayaran) AS jumlah_transaksi, SUM(pem.total_biaya) AS total_pendapatan
FROM Pembayaran pem
JOIN Registrasi reg ON pem.Registrasi_id_registrasi = reg.id_registrasi
JOIN Poliklinik pol ON reg.Poliklinik_id_poliklinik = pol.id_poliklinik
JOIN Jenis_Pembayaran jp ON pem.Jenis_Pembayaran_id_jenis_pembayaran = jp.id_jenis_pembayaran
WHERE MONTH(pem.tanggal_pembayaran) = MONTH(CURDATE()) AND YEAR(pem.tanggal_pembayaran) = YEAR(CURDATE())
GROUP BY pol.nama_poliklinik, jp.nama_jenis_pembayaran
ORDER BY total_pendapatan DESC;


