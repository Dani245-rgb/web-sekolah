<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/nilai.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/riwayat-nilai.css') ?>">

<div class="nilai-card">
    <div class="nilai-header">
        <h4>Riwayat Perubahan Nilai — <?= esc($jadwal['nama_kelas']) ?> / <?= esc($jadwal['nama_mapel']) ?></h4>
    </div>

    <?php if (empty($riwayatList)): ?>
        <p class="nilai-empty">Belum ada riwayat perubahan untuk kelas/mapel ini.</p>
    <?php else: ?>
        <?php foreach ($riwayatList as $r): ?>
            <?php
                // Parse keterangan format: Key:Value|Key:Value|...
                $parts = [];
                foreach (explode('|', $r['keterangan']) as $segment) {
                    [$k, $v] = array_pad(explode(':', $segment, 2), 2, '');
                    $parts[$k] = $v;
                }
                $kategoriRaw = $parts['Kategori'] ?? '';
                $daftarKategori = $kategoriRaw === '' ? [] : explode(';;', $kategoriRaw);
            ?>
            <div class="riwayat-item">
                <div class="riwayat-waktu">
                    Disimpan <?= esc(date('d M Y, H:i', strtotime($r['created_at']))) ?>
                </div>

                <div class="riwayat-info-row">
                    <?php if (!empty($parts['KKM'])): ?>
                        <span class="badge-info badge-kkm">KKM: <?= esc($parts['KKM']) ?></span>
                    <?php endif; ?>
                    <span class="badge-info"><?= count($daftarKategori) ?> Kategori</span>
                </div>

                <div class="riwayat-kategori-label">Kategori & Komponen Nilai</div>
                <?php if (empty($daftarKategori)): ?>
                    <div class="riwayat-kategori-list"><em>Tidak ada data kategori.</em></div>
                <?php else: ?>
                    <div class="riwayat-kategori-list">
                        <?php foreach ($daftarKategori as $kat): ?>
                            <?php
                                // Format per kategori: "Nama (bobot X) [Komp1, Komp2]"
                                preg_match('/^(.*?)\s*\(bobot\s*(.*?)\)\s*\[(.*?)\]$/', trim($kat), $m);
                                $namaKat  = $m[1] ?? trim($kat);
                                $bobotKat = $m[2] ?? '';
                                $komponen = isset($m[3]) ? array_map('trim', explode(',', $m[3])) : [];
                            ?>
                            <div class="kategori-card">
                                <span class="kategori-nama"><?= esc($namaKat) ?></span>
                                <?php if ($bobotKat !== ''): ?>
                                    <span class="kategori-bobot">bobot <?= esc($bobotKat) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($komponen)): ?>
                                    <div class="kategori-komponen">
                                        <?php foreach ($komponen as $k): ?>
                                            <span class="chip-komponen"><?= esc($k) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="nilai-actions" style="margin-top:8px;">
        <a href="<?= base_url('guru/nilai/form/' . $jadwal['id_jadwal']) ?>" class="nilai-btn nilai-btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>