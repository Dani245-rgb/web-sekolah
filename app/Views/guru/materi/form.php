<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <h4><?= $materi ? 'Edit' : 'Unggah' ?> Materi — <?= esc($jadwal['nama_kelas']) ?> / <?= esc($jadwal['nama_mapel']) ?></h4>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-error" style="margin-top:12px;">
            <?php foreach (session()->getFlashdata('errors') as $e): ?>
                <div><?= esc($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $materi ? base_url('guru/materi/update/' . $materi['id_materi']) : base_url('guru/materi/store') ?>" enctype="multipart/form-data" style="max-width:600px; margin-top:16px;">
        <?= csrf_field() ?>
        <?php if (!$materi): ?>
            <input type="hidden" name="id_jadwal" value="<?= $jadwal['id_jadwal'] ?>">
        <?php endif; ?>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">Judul Materi</label>
            <input type="text" name="judul" value="<?= old('judul', $materi['judul'] ?? '') ?>" required class="absensi-keterangan" style="width:100%;">
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">Deskripsi (opsional)</label>
            <textarea name="deskripsi" rows="4" class="absensi-keterangan" style="width:100%;"><?= old('deskripsi', $materi['deskripsi'] ?? '') ?></textarea>
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">File Materi</label>
            <?php if (!empty($materi['file_materi'])): ?>
                <div style="margin-bottom:6px; font-size:13px;">
                    File saat ini: <a href="<?= base_url('assets/uploads/materi/' . $materi['file_materi']) ?>" target="_blank"><?= esc($materi['file_materi']) ?></a>
                </div>
            <?php endif; ?>
            <input type="file" name="file_materi" <?= $materi ? '' : 'required' ?>>
            <small style="color:#888;">PDF, Word, PowerPoint, Excel, gambar, atau ZIP. Maks 10MB.</small>
        </div>

        <div class="absensi-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?= base_url('guru/materi') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>