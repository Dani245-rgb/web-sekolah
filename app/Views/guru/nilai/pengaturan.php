<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/nilai.css') ?>">

<div class="nilai-card">
    <div class="nilai-header">
        <h4>Atur Kategori, Komponen & Bobot Nilai — <?= esc($jadwal['nama_kelas']) ?> / <?= esc($jadwal['nama_mapel']) ?></h4>
    </div>
    <p class="nilai-subinfo">Semester Aktif: <strong><?= esc($semesterAktif['nama_semester']) ?></strong></p>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="nilai-alert nilai-alert-error">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('guru/nilai/form/' . $jadwal['id_jadwal'] . '/pengaturan') ?>" method="post" id="form-pengaturan">
        <?= csrf_field() ?>

        <div class="nilai-kkm-group">
            <label>KKM</label>
            <input type="number" name="kkm" value="<?= esc($pengaturan['kkm'] ?? 75) ?>" min="0" max="100" required>
        </div>

        <div id="daftar-kategori">
            <?php $kategoriData = $kategoriList ?? []; ?>
            <?php if (empty($kategoriData)): ?>
                <div class="nilai-kategori-block" data-kategori-index="0">
                    <div class="nilai-kategori-header">
                        <input type="text" name="kategori[0][nama]" class="input-nama-kategori" placeholder="misal: Tugas Harian" required>
                        <input type="number" name="kategori[0][bobot]" class="input-bobot-kategori" placeholder="Bobot kategori (%)" min="1" max="100" required>
                        <button type="button" class="nilai-btn nilai-btn-danger btn-hapus-kategori">Hapus Kategori</button>
                    </div>

                   <table class="nilai-komponen-table">
                        <thead>
                            <tr>
                                <th>Nama Komponen</th>
                                <th>Link (opsional)</th>
                                <th>Keterangan (opsional)</th>
                                <th>Bobot dalam kategori (%)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" name="kategori[0][komponen][0][nama]" placeholder="misal: Kuis Bab 3" required></td>
                                <td><input type="url" name="kategori[0][komponen][0][link]" placeholder="https://forms.google.com/..."></td>
                                <td><input type="text" name="kategori[0][komponen][0][keterangan]" placeholder="misal: Dikumpulkan lewat WA grup"></td>
                                <td><input type="number" name="kategori[0][komponen][0][bobot]" min="1" max="100" required></td>
                                <td><button type="button" class="nilai-btn nilai-btn-danger btn-hapus-komponen">Hapus</button></td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="button" class="nilai-btn nilai-btn-add btn-tambah-komponen">+ Tambah Komponen</button>
                    <p class="total-bobot-komponen-info"></p>
                </div>
            <?php else: ?>
                <?php foreach ($kategoriData as $ki => $kat): ?>
                    <div class="nilai-kategori-block" data-kategori-index="<?= $ki ?>">
                        <div class="nilai-kategori-header">
                            <input type="text" name="kategori[<?= $ki ?>][nama]" class="input-nama-kategori" value="<?= esc($kat['nama_kategori']) ?>" placeholder="misal: Tugas Harian" required>
                            <input type="number" name="kategori[<?= $ki ?>][bobot]" class="input-bobot-kategori" value="<?= esc($kat['bobot']) ?>" placeholder="Bobot kategori (%)" min="1" max="100" required>
                            <button type="button" class="nilai-btn nilai-btn-danger btn-hapus-kategori">Hapus Kategori</button>
                        </div>

                       <table class="nilai-komponen-table">
                            <thead>
                                <tr>
                                    <th>Nama Komponen</th>
                                    <th>Link (opsional)</th>
                                    <th>Keterangan (opsional)</th>
                                    <th>Bobot dalam kategori (%)</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kat['komponen'] as $kompi => $komp): ?>
                                    <tr>
                                        <td><input type="text" name="kategori[<?= $ki ?>][komponen][<?= $kompi ?>][nama]" value="<?= esc($komp['nama_komponen']) ?>" placeholder="misal: Kuis Bab 3" required></td>
                                        <td><input type="url" name="kategori[<?= $ki ?>][komponen][<?= $kompi ?>][link]" value="<?= esc($komp['link_referensi'] ?? '') ?>" placeholder="https://forms.google.com/..."></td>
                                        <td><input type="text" name="kategori[<?= $ki ?>][komponen][<?= $kompi ?>][keterangan]" value="<?= esc($komp['keterangan'] ?? '') ?>" placeholder="misal: Dikumpulkan lewat WA grup"></td>
                                        <td><input type="number" name="kategori[<?= $ki ?>][komponen][<?= $kompi ?>][bobot]" value="<?= esc($komp['bobot']) ?>" min="1" max="100" required></td>
                                        <td><button type="button" class="nilai-btn nilai-btn-danger btn-hapus-komponen">Hapus</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <button type="button" class="nilai-btn nilai-btn-add btn-tambah-komponen">+ Tambah Komponen</button>
                        <p class="total-bobot-komponen-info"></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <button type="button" id="btn-tambah-kategori" class="nilai-btn nilai-btn-add">+ Tambah Kategori</button>
        <p id="total-bobot-kategori-info"></p>

        <div class="nilai-actions">
            <button type="submit" class="nilai-btn nilai-btn-primary">Simpan Pengaturan</button>
            <a href="<?= base_url('guru/dashboard') ?>" class="nilai-btn nilai-btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<script src="<?= base_url('assets/js/nilai.js') ?>"></script>

<?= $this->endSection() ?>