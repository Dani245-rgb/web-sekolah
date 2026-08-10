<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/nilai.css') ?>">

<div class="nilai-card">
    <div class="nilai-header">
        <h4>Input Nilai — <?= esc($jadwal['nama_kelas']) ?> / <?= esc($jadwal['nama_mapel']) ?></h4>
    </div>
    <p class="nilai-subinfo">KKM: <strong><?= esc($pengaturan['kkm']) ?></strong></p>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="nilai-alert nilai-alert-error">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="nilai-alert nilai-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <form action="<?= base_url('guru/nilai/form/' . $jadwal['id_jadwal'] . '/simpan') ?>" method="post">
        <?= csrf_field() ?>
        <table class="nilai-siswa-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <?php foreach ($komponenList as $k): ?>
                        <th>
                            <?= esc($k['nama_komponen']) ?> (<?= esc($k['bobot']) ?>%)
                            <?php if (!empty($k['link_referensi'])): ?>
                                <br><a href="<?= esc($k['link_referensi']) ?>" target="_blank" style="font-weight:400; font-size:11px;">🔗 Buka link</a>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($siswaList as $s): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($s['nama']) ?></td>
                        <?php foreach ($komponenList as $k): ?>
                            <td>
                                <input type="number" step="0.01" min="0" max="100"
                                    name="nilai[<?= $s['id_siswa'] ?>][<?= $k['id_komponen'] ?>]"
                                    value="<?= esc($nilaiMap[$s['id_siswa']][$k['id_komponen']] ?? '') ?>">
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($siswaList)): ?>
                    <tr>
                        <td colspan="<?= 2 + count($komponenList) ?>" class="nilai-empty">Tidak ada siswa di kelas ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="nilai-actions">
            <button type="submit" class="nilai-btn nilai-btn-primary">Simpan Nilai</button>
            <a href="<?= base_url('guru/nilai/rekap/' . $jadwal['id_jadwal']) ?>" class="nilai-btn nilai-btn-secondary">Lihat Rekap</a>
            <a href="<?= base_url('guru/nilai/form/' . $jadwal['id_jadwal'] . '/pengaturan') ?>" class="nilai-btn nilai-btn-secondary">Atur Ulang Komponen Nilai</a>
            <a href="<?= base_url('guru/dashboard') ?>" class="nilai-btn nilai-btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<?= $this->include('guru/nilai/_import_section', [
    'komponenList' => $komponenList,
    'riwayatImportPerKomponen' => $riwayatImportPerKomponen ?? [],
]) ?>

<?= $this->endSection() ?>