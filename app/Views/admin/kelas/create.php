<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Tambah Kelas</h4>
    <p class="breadcrumb">Dashboard / Data Kelas / Tambah</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/kelas/store') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label>Tingkat</label>
                <select name="tingkat" required>
                    <option value="">-- Pilih --</option>
                    <option value="X" <?= old('tingkat') === 'X' ? 'selected' : '' ?>>X</option>
                    <option value="XI" <?= old('tingkat') === 'XI' ? 'selected' : '' ?>>XI</option>
                    <option value="XII" <?= old('tingkat') === 'XII' ? 'selected' : '' ?>>XII</option>
                </select>
            </div>

            <div class="form-group">
                <label>Jurusan</label>
                <input type="text" name="jurusan" placeholder="RPL / TKJ / dll" value="<?= old('jurusan') ?>" required>
            </div>

            <div class="form-group">
                <label>Rombel (No. Urut)</label>
                <input type="number" name="rombel" min="1" value="<?= old('rombel', 1) ?>" required>
            </div>
        </div>
        <small style="display:block;margin-bottom:14px;color:#7C8A9C;">Nama kelas akan dibuat otomatis, contoh: X RPL
            1</small>

        <div class="form-group">
            <label>Wali Kelas</label>
            <select name="wali_kelas_id">
                <option value="">-- Belum ditentukan --</option>
                <?php foreach ($guru as $g): ?>
                <option value="<?= $g['id_guru'] ?>" <?= old('wali_kelas_id') == $g['id_guru'] ? 'selected' : '' ?>>
                    <?= esc($g['nama']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Ruangan</label>
                <input type="text" name="ruangan" value="<?= old('ruangan') ?>">
            </div>

            <div class="form-group">
                <label>Kapasitas</label>
                <input type="number" name="kapasitas" min="1" value="<?= old('kapasitas', 36) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Tahun Ajaran</label>
            <select name="id_tahun_ajaran" required>
                <option value="">-- Pilih --</option>
                <?php foreach ($tahunAjaran as $t): ?>
                <option value="<?= $t['id_tahun_ajaran'] ?>"
                    <?= old('id_tahun_ajaran') == $t['id_tahun_ajaran'] ? 'selected' : '' ?>>
                    <?= esc($t['tahun_ajaran']) ?> (<?= esc($t['status']) ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?= base_url('admin/kelas') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>