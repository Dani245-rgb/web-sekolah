<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4><?= $item ? 'Edit Anggota' : 'Tambah Anggota' ?> — <?= esc($organisasi['nama']) ?></h4>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= $item ? base_url('admin/organisasi/' . $organisasi['id'] . '/anggota/update/' . $item['id']) : base_url('admin/organisasi/' . $organisasi['id'] . '/anggota/store') ?>"
      method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Nama</label>
        <input type="text" name="nama" class="form-control" value="<?= old('nama', $item['nama'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label>Jabatan</label>
        <input type="text" name="jabatan" class="form-control" value="<?= old('jabatan', $item['jabatan'] ?? '') ?>" placeholder="Contoh: Ketua OSIS" required>
    </div>

    <div class="mb-3">
        <label>Urutan Tampil</label>
        <input type="number" name="urutan" class="form-control" value="<?= old('urutan', $item['urutan'] ?? 0) ?>">
    </div>

    <div class="mb-3">
        <label>Foto (opsional)</label>
        <input type="file" name="foto" class="form-control">
        <?php if (!empty($item['foto'])): ?>
            <img src="<?= base_url('uploads/organisasi/' . $item['foto']) ?>" width="100" class="mt-2 rounded">
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= base_url('admin/organisasi/' . $organisasi['id'] . '/anggota') ?>" class="btn btn-secondary">Batal</a>
</form>

<?= $this->endSection() ?>