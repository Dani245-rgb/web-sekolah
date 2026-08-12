<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/berita.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4 class="berita-form-title"><?= $berita ? 'Edit Berita' : 'Tambah Berita' ?></h4>

<?php if (session('errors')): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach (session('errors') as $e): ?>
                <li><?= is_array($e) ? esc(implode(', ', $e)) : esc($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post"
    action="<?= $berita ? base_url('admin/berita/update/' . $berita['id_berita']) : base_url('admin/berita/store') ?>"
    enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Judul</label>
        <input type="text" name="judul" class="form-control" value="<?= esc(old('judul', $berita['judul'] ?? '')) ?>" required>
    </div>

    <div class="mb-3">
        <label>Kategori</label>
        <select name="kategori" class="form-control" required>
            <?php foreach (['Akademik', 'Prestasi', 'Kegiatan', 'Umum'] as $k): ?>
                <option value="<?= $k ?>" <?= ($berita['kategori'] ?? '') === $k ? 'selected' : '' ?>><?= $k ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Gambar <?= $berita ? '(kosongkan kalau tidak diganti)' : '' ?></label>
        <?php if ($berita && $berita['gambar']): ?>
            <div class="berita-form-img-preview">
                <img src="<?= base_url('assets/images/berita/' . $berita['gambar']) ?>" alt="">
            </div>
        <?php endif; ?>
        <input type="file" name="gambar" class="form-control" accept="image/*">
    </div>

    <div class="mb-3">
        <label>Ringkasan (opsional, tampil di listing)</label>
        <textarea name="ringkasan" class="form-control" rows="2"><?= esc(old('ringkasan', $berita['ringkasan'] ?? '')) ?></textarea>
    </div>

    <div class="mb-3">
        <label>Konten</label>
        <textarea name="konten" class="form-control" rows="10" required><?= esc(old('konten', $berita['konten'] ?? '')) ?></textarea>
    </div>

    <div class="mb-3">
        <label>Tanggal Publish</label>
        <input type="date" name="tanggal_publish" class="form-control"
            value="<?= esc($berita['tanggal_publish'] ?? date('Y-m-d')) ?>">
    </div>

    <div class="mb-3">
        <label>Tampilkan di Homepage</label>
        <select name="posisi" class="form-control">
            <option value="biasa" <?= ($berita['posisi'] ?? 'biasa') === 'biasa' ? 'selected' : '' ?>>Biasa (Berita Terbaru)</option>
            <option value="utama" <?= ($berita['posisi'] ?? '') === 'utama' ? 'selected' : '' ?>>Berita Utama</option>
            <option value="hero" <?= ($berita['posisi'] ?? '') === 'hero' ? 'selected' : '' ?>>Hero (Banner Utama)</option>
            <option value="populer" <?= ($berita['posisi'] ?? '') === 'populer' ? 'selected' : '' ?>>Widget: (Populer)</option>
            <option value="hits" <?= ($berita['posisi'] ?? '') === 'hits' ? 'selected' : '' ?>>Widget: (Hits)
                
            </option>
        </select>
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-control" required>
            <option value="Draft" <?= ($berita['status'] ?? 'Draft') === 'Draft' ? 'selected' : '' ?>>Draft</option>
            <option value="Published" <?= ($berita['status'] ?? '') === 'Published' ? 'selected' : '' ?>>Published</option>
        </select>
    </div>

    <button class="btn btn-primary">Simpan</button>
    <a href="<?= base_url('admin/berita') ?>" class="btn btn-outline-secondary">Batal</a>
</form>

<?= $this->endSection() ?>