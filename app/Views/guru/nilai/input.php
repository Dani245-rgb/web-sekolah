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

        <?php if (!empty($kategoriList)): ?>
            <div class="nilai-tab-nav" style="display:flex; gap:8px; margin-bottom:12px; border-bottom:1px solid #ddd;">
                <?php foreach ($kategoriList as $ti => $kat): ?>
                    <button type="button" class="nilai-tab-btn<?= $ti === 0 ? ' active' : '' ?>"
                        data-tab-target="tab-kategori-<?= $kat['id_kategori'] ?>"
                        style="padding:8px 14px; border:none; background:<?= $ti === 0 ? '#eef2ff' : 'transparent' ?>; cursor:pointer; font-weight:600;">
                        <?= esc($kat['nama_kategori']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($kategoriList as $ti => $kat): ?>
                <?php $komponenTab = $komponenPerKategori[$kat['id_kategori']] ?? []; ?>
                <div class="nilai-tab-pane" id="tab-kategori-<?= $kat['id_kategori'] ?>" style="<?= $ti === 0 ? '' : 'display:none;' ?>">
                    <table class="nilai-siswa-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <?php foreach ($komponenTab as $k): ?>
                                    <th>
                                        <?= esc($k['nama_komponen']) ?> (<?= esc($k['bobot']) ?>%)
                                        <?php if (!empty($k['link_referensi'])): ?>
                                            <br><a href="<?= esc($k['link_referensi']) ?>" target="_blank" style="font-weight:400; font-size:11px;">🔗 Buka link</a>
                                        <?php endif; ?>
                                        <?php if (!empty($k['keterangan'])): ?>
                                            <br><span style="font-weight:400; font-size:11px; color:#888;">📝 <?= esc($k['keterangan']) ?></span>
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
                                    <?php foreach ($komponenTab as $k): ?>
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
                                    <td colspan="<?= 2 + count($komponenTab) ?>" class="nilai-empty">Tidak ada siswa di kelas ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>

            <script>
                document.querySelectorAll('.nilai-tab-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.nilai-tab-btn').forEach(function(b) {
                            b.classList.remove('active');
                            b.style.background = 'transparent';
                        });
                        document.querySelectorAll('.nilai-tab-pane').forEach(function(p) {
                            p.style.display = 'none';
                        });
                        btn.classList.add('active');
                        btn.style.background = '#eef2ff';
                        document.getElementById(btn.dataset.tabTarget).style.display = '';
                    });
                });
            </script>
        <?php else: ?>
            <p class="nilai-empty">Belum ada kategori nilai. Silakan atur dulu lewat "Atur Ulang Komponen Nilai".</p>
        <?php endif; ?>

        <div class="nilai-actions">
            <button type="submit" class="nilai-btn nilai-btn-primary">Simpan Nilai</button>
            <a href="<?= base_url('guru/nilai/rekap/' . $jadwal['id_jadwal']) ?>" class="nilai-btn nilai-btn-secondary">Lihat Rekap</a>
            <a href="<?= base_url('guru/nilai/form/' . $jadwal['id_jadwal'] . '/pengaturan') ?>" class="nilai-btn nilai-btn-secondary">Atur Ulang Komponen Nilai</a>
            <a href="<?= base_url('guru/nilai/form/' . $jadwal['id_jadwal'] . '/riwayat') ?>" class="nilai-btn nilai-btn-secondary">Riwayat Perubahan</a>
            <a href="<?= base_url('guru/dashboard') ?>" class="nilai-btn nilai-btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<?= $this->include('guru/nilai/_import_section', [
    'komponenList' => $komponenList,
    'riwayatImportPerKomponen' => $riwayatImportPerKomponen ?? [],
]) ?>

<?= $this->endSection() ?>