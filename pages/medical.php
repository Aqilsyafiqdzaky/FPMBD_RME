<?php
$action = $_GET['action'] ?? 'index';

if ($action === 'anamnesis_store') {
    postOnly();
    try {
        execute('CALL sp_anamnesis_perawat(?, ?, ?, ?, ?)', [
            $_POST['id_rekam_medis'],
            $_POST['keluhan_pasien'],
            $_POST['id_registrasi'],
            $_POST['id_perawat'],
            $_POST['id_rawat_inap'] !== '' ? $_POST['id_rawat_inap'] : null,
        ]);
        flash('Anamnesis awal berhasil dibuat.');
        redirect('medical');
    } catch (Throwable $e) {
        flash($e->getMessage(), 'danger');
        redirect('medical');
    }
}

if ($action === 'pemeriksaan_store') {
    postOnly();
    try {
        execute('CALL sp_pemeriksaan_dokter(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $_POST['id_rekam_medis'],
            $_POST['id_dokter'],
            $_POST['id_diagnosa'],
            $_POST['nama_diagnosa'],
            $_POST['keterangan_diagnosa'],
            $_POST['id_tindakan_medis'],
            $_POST['nama_tindakan'],
            $_POST['biaya_tindakan'],
            $_POST['hasil_tindakan'],
        ]);
        flash('Data pemeriksaan dan tindakan berhasil disimpan.');
        redirect('medical', ['action' => 'detail', 'id' => $_POST['id_rekam_medis']]);
    } catch (Throwable $e) {
        flash($e->getMessage(), 'danger');
        redirect('medical', ['action' => 'detail', 'id' => $_POST['id_rekam_medis']]);
    }
}

if ($action === 'resep_store') {
    postOnly();
    try {
        execute('CALL sp_order_farmasi(?, ?, ?, ?, ?)', [
            $_POST['id_rekam_medis'],
            $_POST['id_resep'],
            $_POST['id_obat'],
            $_POST['jumlah_obat'],
            $_POST['dosis_obat'],
        ]);
        flash('Obat berhasil ditambahkan ke resep.');
        redirect('medical', ['action' => 'detail', 'id' => $_POST['id_rekam_medis']]);
    } catch (Throwable $e) {
        flash($e->getMessage(), 'danger');
        redirect('medical', ['action' => 'detail', 'id' => $_POST['id_rekam_medis']]);
    }
}

if ($action === 'anamnesis_form') {
    $id_registrasi = $_GET['id_reg'] ?? '';
    $reg = fetchOne('SELECT r.*, p.nama_pasien, pl.nama_poliklinik FROM Registrasi r JOIN Pasien p ON p.id_pasien = r.Pasien_id_pasien JOIN Poliklinik pl ON pl.id_poliklinik = r.Poliklinik_id_poliklinik WHERE id_registrasi = ?', [$id_registrasi]);
    if (!$reg) redirect('medical');

    $perawat = fetchAll('SELECT * FROM Perawat ORDER BY nama_perawat');
    $rawatInap = fetchAll('SELECT ri.id_rawat_inap, p.nama_pasien, k.nomor_kamar FROM Rawat_Inap ri JOIN Registrasi r ON r.id_registrasi = ri.Registrasi_id_registrasi JOIN Pasien p ON p.id_pasien = r.Pasien_id_pasien JOIN Kamar k ON k.id_kamar = ri.Kamar_id_kamar WHERE ri.tanggal_keluar IS NULL ORDER BY ri.tanggal_masuk DESC');
    ?>
    <section class="header">
        <div>
            <h1>Anamnesis Perawat</h1>
            <p>Proses awal rekam medis untuk pasien <?= e($reg['nama_pasien']) ?></p>
        </div>
        <a class="btn secondary" href="<?= e(url('medical')) ?>">Kembali</a>
    </section>
    
    <div class="card">
        <form class="form" method="post" action="<?= e(url('medical', ['action' => 'anamnesis_store'])) ?>">
            <input type="hidden" name="id_registrasi" value="<?= e($reg['id_registrasi']) ?>">
            <div class="form-row-3">
                <label>ID Rekam Medis <input name="id_rekam_medis" value="RM000" readonly></label>
                <label>Perawat Pemeriksa
                    <select name="id_perawat" required>
                        <option value="">Pilih Perawat</option>
                        <?php foreach ($perawat as $row): ?><option value="<?= e($row['id_perawat']) ?>"><?= e($row['nama_perawat']) ?></option><?php endforeach; ?>
                    </select>
                </label>
                <label>Kamar Rawat Inap (Jika ada)
                    <select name="id_rawat_inap">
                        <option value="">Tidak ada</option>
                        <?php foreach ($rawatInap as $row): ?><option value="<?= e($row['id_rawat_inap']) ?>"><?= e($row['id_rawat_inap'] . ' - ' . $row['nama_pasien'] . ' (Kamar ' . $row['nomor_kamar'] . ')') ?></option><?php endforeach; ?>
                    </select>
                </label>
            </div>
            <label>Keluhan Pasien <textarea name="keluhan_pasien" required></textarea></label>
            <button type="submit" class="btn">Simpan Anamnesis</button>
        </form>
    </div>
    <?php
    return;
}

if ($action === 'detail') {
    $id_rekam_medis = $_GET['id'] ?? '';
    $rm = fetchOne('SELECT rm.*, p.nama_pasien, r.jenis_layanan, d.nama_dokter, pr.nama_perawat FROM Rekam_Medis rm JOIN Registrasi r ON r.id_registrasi = rm.Registrasi_id_registrasi JOIN Pasien p ON p.id_pasien = r.Pasien_id_pasien LEFT JOIN Dokter d ON d.id_dokter = rm.Dokter_id_dokter JOIN Perawat pr ON pr.id_perawat = rm.Perawat_id_perawat WHERE rm.id_rekam_medis = ?', [$id_rekam_medis]);
    if (!$rm) redirect('medical');

    $diagnosa = fetchAll('SELECT * FROM Diagnosa WHERE Rekam_Medis_id_rekam_medis = ?', [$id_rekam_medis]);
    $tindakan = fetchAll('SELECT * FROM Tindakan_Medis WHERE Rekam_Medis_id_rekam_medis = ?', [$id_rekam_medis]);
    $resep = fetchOne('SELECT * FROM Resep WHERE Rekam_Medis_id_rekam_medis = ?', [$id_rekam_medis]);
    $detail_resep = [];
    if ($resep) {
        $detail_resep = fetchAll('SELECT dr.*, o.nama_obat FROM Detail_Resep dr JOIN Obat o ON o.id_obat = dr.Obat_id_obat WHERE dr.Resep_id_resep = ?', [$resep['id_resep']]);
    }

    $dokter = fetchAll('SELECT * FROM Dokter ORDER BY nama_dokter');
    $obat = fetchAll('SELECT * FROM Obat ORDER BY nama_obat');
    ?>
    <section class="header">
        <div>
            <h1>Detail Rekam Medis: <?= e($rm['id_rekam_medis']) ?></h1>
            <p>Pasien: <?= e($rm['nama_pasien']) ?> | Perawat: <?= e($rm['nama_perawat']) ?></p>
        </div>
        <a class="btn secondary" href="<?= e(url('medical')) ?>">Kembali</a>
    </section>

    <div class="card mb-4">
        <h2>Data Anamnesis (Perawat)</h2>
        <p><strong>Keluhan Pasien:</strong><br><?= nl2br(e($rm['keluhan_pasien'])) ?></p>
        <p><strong>Dokter Pemeriksa:</strong> <?= $rm['nama_dokter'] ? e($rm['nama_dokter']) : '<span class="muted">Belum diperiksa dokter</span>' ?></p>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h2>Pemeriksaan & Tindakan (Dokter)</h2>
            <?php if (!$rm['nama_dokter']): ?>
                <form class="form" method="post" action="<?= e(url('medical', ['action' => 'pemeriksaan_store'])) ?>">
                    <input type="hidden" name="id_rekam_medis" value="<?= e($id_rekam_medis) ?>">
                    <label>Pilih Dokter Pemeriksa
                        <select name="id_dokter" required>
                            <option value="">Pilih Dokter</option>
                            <?php foreach ($dokter as $row): ?><option value="<?= e($row['id_dokter']) ?>"><?= e($row['nama_dokter']) ?></option><?php endforeach; ?>
                        </select>
                    </label>
                    <hr>
                    <h3>Diagnosa</h3>
                    <div class="form-row">
                        <label>ID Diagnosa <input name="id_diagnosa" value="<?= e(nextId('Diagnosa', 'id_diagnosa', 'DG', 3)) ?>" readonly></label>
                        <label>Nama Diagnosa <input name="nama_diagnosa" required></label>
                    </div>
                    <label>Keterangan <textarea name="keterangan_diagnosa" required></textarea></label>
                    <hr>
                    <h3>Tindakan Medis</h3>
                    <div class="form-row">
                        <label>ID Tindakan <input name="id_tindakan_medis" value="<?= e(nextId('Tindakan_Medis', 'id_tindakan_medis', 'T', 4)) ?>" readonly></label>
                        <label>Nama Tindakan <input name="nama_tindakan" required></label>
                    </div>
                    <div class="form-row">
                        <label>Biaya (Rp) <input type="number" name="biaya_tindakan" min="0" step="0.01" required></label>
                        <label>Hasil <input name="hasil_tindakan" required></label>
                    </div>
                    <button type="submit" class="btn">Simpan Pemeriksaan</button>
                </form>
            <?php else: ?>
                <p class="muted">Pemeriksaan telah diselesaikan oleh <?= e($rm['nama_dokter']) ?>.</p>
                <hr>
                <h4>Riwayat Diagnosa</h4>
                <ul>
                    <?php foreach($diagnosa as $d): ?>
                        <li><strong><?= e($d['nama_diagnosa']) ?>:</strong> <?= e($d['keterangan_diagnosa']) ?></li>
                    <?php endforeach; ?>
                    <?php if(!$diagnosa) echo '<li>Tidak ada</li>'; ?>
                </ul>
                <h4>Riwayat Tindakan</h4>
                <ul>
                    <?php foreach($tindakan as $t): ?>
                        <li><strong><?= e($t['nama_tindakan']) ?></strong> (<?= e(rupiah($t['biaya_tindakan'])) ?>) - Hasil: <?= e($t['hasil_tindakan']) ?></li>
                    <?php endforeach; ?>
                    <?php if(!$tindakan) echo '<li>Tidak ada</li>'; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Resep Farmasi</h2>
            <?php if ($rm['nama_dokter']): ?>
                <form class="form mb-4" method="post" action="<?= e(url('medical', ['action' => 'resep_store'])) ?>">
                    <input type="hidden" name="id_rekam_medis" value="<?= e($id_rekam_medis) ?>">
                    <input type="hidden" name="id_resep" value="<?= $resep ? e($resep['id_resep']) : e(nextId('Resep', 'id_resep', 'RS', 3)) ?>">
                    <div class="form-row">
                        <label>Obat
                            <select name="id_obat" required>
                                <option value="">Pilih Obat</option>
                                <?php foreach ($obat as $row): ?><option value="<?= e($row['id_obat']) ?>"><?= e($row['nama_obat']) ?> (Stok: <?= e($row['stok_obat']) ?>)</option><?php endforeach; ?>
                            </select>
                        </label>
                        <label>Jumlah <input type="number" name="jumlah_obat" min="1" required></label>
                    </div>
                    <label>Dosis <input name="dosis_obat" placeholder="3x1 sesudah makan" required></label>
                    <button type="submit" class="btn">Tambah Obat</button>
                </form>
            <?php else: ?>
                <p class="muted">Tunggu dokter menyelesaikan pemeriksaan untuk menambah resep.</p>
            <?php endif; ?>

            <?php if ($resep): ?>
                <h4>Daftar Obat (<?= e($resep['id_resep']) ?>)</h4>
                <table class="table-wrap">
                    <thead><tr><th>Obat</th><th>Jml</th><th>Dosis</th></tr></thead>
                    <tbody>
                        <?php foreach($detail_resep as $dr): ?>
                            <tr><td><?= e($dr['nama_obat']) ?></td><td><?= e($dr['jumlah_obat']) ?></td><td><?= e($dr['dosis_obat']) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return;
}

// index
$q1 = $_GET['q1'] ?? '';
$searchParam1 = "%$q1%";

$q2 = $_GET['q2'] ?? '';
$searchParam2 = "%$q2%";

$limit = 10;
$pageAntrean = max(1, (int)($_GET['p1'] ?? 1));
$offsetAntrean = ($pageAntrean - 1) * $limit;

$pageRiwayat = max(1, (int)($_GET['p2'] ?? 1));
$offsetRiwayat = ($pageRiwayat - 1) * $limit;

// Total count for Antrean
$totalAntrean = fetchOne(
    'SELECT COUNT(*) as cnt
     FROM Registrasi r
     JOIN Pasien p ON p.id_pasien = r.Pasien_id_pasien
     LEFT JOIN Rekam_Medis rm ON rm.Registrasi_id_registrasi = r.id_registrasi
     WHERE rm.id_rekam_medis IS NULL 
       AND (p.nama_pasien LIKE ? OR r.id_registrasi LIKE ?)',
    [$searchParam1, $searchParam1]
)['cnt'];
$totalPagesAntrean = ceil($totalAntrean / $limit);

$menunggu = fetchAll(
    'SELECT r.id_registrasi, r.jenis_layanan, p.nama_pasien, pl.nama_poliklinik
     FROM Registrasi r
     JOIN Pasien p ON p.id_pasien = r.Pasien_id_pasien
     JOIN Poliklinik pl ON pl.id_poliklinik = r.Poliklinik_id_poliklinik
     LEFT JOIN Rekam_Medis rm ON rm.Registrasi_id_registrasi = r.id_registrasi
     WHERE rm.id_rekam_medis IS NULL 
       AND (p.nama_pasien LIKE ? OR r.id_registrasi LIKE ?)
     ORDER BY r.tanggal_registrasi DESC
     LIMIT ? OFFSET ?',
    [$searchParam1, $searchParam1, $limit, $offsetAntrean]
);

// Total count for Riwayat
$totalRiwayat = fetchOne(
    'SELECT COUNT(*) as cnt
     FROM Rekam_Medis rm
     JOIN Registrasi r ON r.id_registrasi = rm.Registrasi_id_registrasi
     JOIN Pasien p ON p.id_pasien = r.Pasien_id_pasien
     WHERE p.nama_pasien LIKE ? OR rm.id_rekam_medis LIKE ? OR r.id_registrasi LIKE ?',
    [$searchParam2, $searchParam2, $searchParam2]
)['cnt'];
$totalPagesRiwayat = ceil($totalRiwayat / $limit);

$berjalan = fetchAll(
    'SELECT rm.*, r.jenis_layanan, p.nama_pasien, d.nama_dokter, pr.nama_perawat
     FROM Rekam_Medis rm
     JOIN Registrasi r ON r.id_registrasi = rm.Registrasi_id_registrasi
     JOIN Pasien p ON p.id_pasien = r.Pasien_id_pasien
     LEFT JOIN Dokter d ON d.id_dokter = rm.Dokter_id_dokter
     JOIN Perawat pr ON pr.id_perawat = rm.Perawat_id_perawat
     WHERE p.nama_pasien LIKE ? OR rm.id_rekam_medis LIKE ? OR r.id_registrasi LIKE ?
     ORDER BY rm.tanggal_pemeriksaan DESC
     LIMIT ? OFFSET ?',
    [$searchParam2, $searchParam2, $searchParam2, $limit, $offsetRiwayat]
);
?>
<section class="header">
    <div>
        <h1>Rekam Medis</h1>
        <p>Anamnesis Perawat - Pemeriksaan Dokter.</p>
    </div>
</section>

<div class="card mb-4">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
        <div>
            <h2>Antrean Registrasi (Menunggu Anamnesis)</h2>
            <?php if ($totalPagesAntrean > 1): ?>
                <div style="display: flex; gap: 5px; margin-top: 0.5rem;">
                    <?php for ($i = 1; $i <= $totalPagesAntrean; $i++): ?>
                        <a href="<?= e(url('medical', ['q1' => $q1, 'q2' => $q2, 'p1' => $i, 'p2' => $pageRiwayat])) ?>" class="btn <?= $i === $pageAntrean ? 'primary' : 'secondary' ?> btn-sm"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
        <form method="get" action="index.php" style="display: flex; gap: 0.5rem; align-items: center;">
            <input type="hidden" name="page" value="medical">
            <input type="hidden" name="q2" value="<?= e($q2) ?>">
            <input type="hidden" name="p2" value="<?= e($pageRiwayat) ?>">
            <input type="text" name="q1" value="<?= e($q1) ?>" placeholder="Cari Pasien / Reg..." style="padding: 0.4rem; border: 1px solid #ccc; border-radius: 4px;">
            <button class="btn btn-sm" type="submit">Cari</button>
            <?php if ($q1): ?>
                <a class="btn secondary btn-sm" href="<?= e(url('medical', ['q2' => $q2, 'p2' => $pageRiwayat])) ?>">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="table-wrap" style="margin-top: 1rem;">
        <table>
            <thead><tr><th>ID Registrasi</th><th>Pasien</th><th>Layanan</th><th>Poli</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($menunggu as $row): ?>
                <tr>
                    <td><?= e($row['id_registrasi']) ?></td>
                    <td><?= e($row['nama_pasien']) ?></td>
                    <td><?= e($row['jenis_layanan']) ?></td>
                    <td><?= e($row['nama_poliklinik']) ?></td>
                    <td><a class="btn" href="<?= e(url('medical', ['action' => 'anamnesis_form', 'id_reg' => $row['id_registrasi']])) ?>">Proses Anamnesis</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$menunggu): ?><tr><td colspan="5" class="muted">Tidak ada antrean baru.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
        <div>
            <h2>Daftar Rekam Medis (Sedang / Sudah Berjalan)</h2>
            <?php if ($totalPagesRiwayat > 1): ?>
                <div style="display: flex; gap: 5px; margin-top: 0.5rem;">
                    <?php for ($i = 1; $i <= $totalPagesRiwayat; $i++): ?>
                        <a href="<?= e(url('medical', ['q1' => $q1, 'q2' => $q2, 'p1' => $pageAntrean, 'p2' => $i])) ?>" class="btn <?= $i === $pageRiwayat ? 'primary' : 'secondary' ?> btn-sm"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
        <form method="get" action="index.php" style="display: flex; gap: 0.5rem; align-items: center;">
            <input type="hidden" name="page" value="medical">
            <input type="hidden" name="q1" value="<?= e($q1) ?>">
            <input type="hidden" name="p1" value="<?= e($pageAntrean) ?>">
            <input type="text" name="q2" value="<?= e($q2) ?>" placeholder="Cari Pasien / RM..." style="padding: 0.4rem; border: 1px solid #ccc; border-radius: 4px;">
            <button class="btn btn-sm" type="submit">Cari</button>
            <?php if ($q2): ?>
                <a class="btn secondary btn-sm" href="<?= e(url('medical', ['q1' => $q1, 'p1' => $pageAntrean])) ?>">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="table-wrap" style="margin-top: 1rem;">
        <table>
            <thead><tr><th>ID RM</th><th>Tanggal</th><th>Pasien</th><th>Perawat</th><th>Dokter</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($berjalan as $row): ?>
                <tr>
                    <td><?= e($row['id_rekam_medis']) ?></td>
                    <td><?= e($row['tanggal_pemeriksaan']) ?></td>
                    <td><?= e($row['nama_pasien']) ?></td>
                    <td><?= e($row['nama_perawat']) ?></td>
                    <td><?= $row['nama_dokter'] ? e($row['nama_dokter']) : '<span class="muted small">Menunggu Dokter</span>' ?></td>
                    <td><?= $row['nama_dokter'] ? '<span style="color:green">Selesai Diperiksa</span>' : '<span style="color:orange">Tahap Anamnesis</span>' ?></td>
                    <td><a class="btn secondary" href="<?= e(url('medical', ['action' => 'detail', 'id' => $row['id_rekam_medis']])) ?>">Buka Detail</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$berjalan): ?><tr><td colspan="7" class="muted">Belum ada data rekam medis.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
