<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <h4><?= $tugas ? 'Edit' : 'Buat' ?> Tugas — <?= esc($jadwal['nama_kelas']) ?> / <?= esc($jadwal['nama_mapel']) ?></h4>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-error" style="margin-top:12px;">
            <?php foreach (session()->getFlashdata('errors') as $e): ?>
                <div><?= esc($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $tugas ? base_url('guru/tugas/update/' . $tugas['id_tugas']) : base_url('guru/tugas/store') ?>" enctype="multipart/form-data" style="max-width:600px; margin-top:16px;">
        <?= csrf_field() ?>
        <?php if (!$tugas): ?>
            <input type="hidden" name="id_jadwal" value="<?= $jadwal['id_jadwal'] ?>">
        <?php endif; ?>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">Judul Tugas</label>
            <input type="text" name="judul" value="<?= old('judul', $tugas['judul'] ?? '') ?>" required class="absensi-keterangan" style="width:100%;">
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">Deskripsi</label>
            <textarea name="deskripsi" rows="4" class="absensi-keterangan" style="width:100%;"><?= old('deskripsi', $tugas['deskripsi'] ?? '') ?></textarea>
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">File Lampiran Soal (opsional)</label>
            <?php if (!empty($tugas['file_lampiran'])): ?>
                <div style="margin-bottom:6px; font-size:13px;">
                    File saat ini: <a href="<?= base_url('assets/uploads/tugas/' . $tugas['file_lampiran']) ?>" target="_blank"><?= esc($tugas['file_lampiran']) ?></a>
                </div>
            <?php endif; ?>
            <input type="file" name="file_lampiran">
            <small style="color:#888;">PDF, Word, PowerPoint, Excel, gambar, atau ZIP. Maks 10MB.</small>
        </div>

        <div style="display:flex; gap:14px; margin-bottom:14px;">
            <div style="flex:1;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">Tanggal Mulai</label>
                <input type="datetime-local" name="tanggal_mulai" value="<?= old('tanggal_mulai', isset($tugas['tanggal_mulai']) ? date('Y-m-d\TH:i', strtotime($tugas['tanggal_mulai'])) : date('Y-m-d\TH:i')) ?>" required class="absensi-keterangan" style="width:100%;">
            </div>
            <div style="flex:1;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">Tenggat</label>
                <input type="datetime-local" name="tenggat" value="<?= old('tenggat', isset($tugas['tenggat']) ? date('Y-m-d\TH:i', strtotime($tugas['tenggat'])) : '') ?>" required class="absensi-keterangan" style="width:100%;">
            </div>
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">Hubungkan ke Komponen Nilai (opsional)</label>
            <?php if (empty($komponenList)): ?>
                <div style="font-size:13px; color:#888; background:#f8fafc; padding:10px 12px; border-radius:8px;">
                    Anda belum mengatur kategori & komponen nilai untuk mapel ini. Nilai tugas akan berdiri sendiri (tidak masuk rekap Nilai resmi).
                    <a href="<?= base_url('guru/nilai/form/' . $jadwal['id_jadwal'] . '/pengaturan') ?>">Atur sekarang</a>.
                </div>
            <?php else: ?>
                <select name="id_komponen" class="absensi-select" style="width:100%;">
                    <option value="">-- Berdiri sendiri, tidak masuk rekap Nilai --</option>
                    <?php foreach ($komponenList as $k): ?>
                        <option value="<?= $k['id_komponen'] ?>" <?= old('id_komponen', $tugas['id_komponen'] ?? '') == $k['id_komponen'] ? 'selected' : '' ?>>
                            <?= esc($k['nama_komponen']) ?> (bobot <?= esc($k['bobot']) ?>%)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color:#888;">Kalau dipilih, nilai yang Anda berikan ke submisi siswa otomatis masuk ke komponen ini di rekap Nilai.</small>
            <?php endif; ?>
        </div>

        <?php if ($tugas): ?>
            <div style="margin-bottom:14px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">Status</label>
                <select name="status" class="absensi-select" style="width:100%;">
                    <option value="Aktif" <?= old('status', $tugas['status']) === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="Ditutup" <?= old('status', $tugas['status']) === 'Ditutup' ? 'selected' : '' ?>>Ditutup</option>
                </select>
            </div>
        <?php endif; ?>

        <div class="absensi-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?= base_url('guru/tugas') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>