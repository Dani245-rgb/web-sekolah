<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Edit Kelas</h4>
    <p class="breadcrumb">Dashboard / Data Kelas / Edit</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/kelas/update/' . $kelasData['id_kelas']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label>Tingkat</label>
                <select name="tingkat" required>
                    <option value="X" <?= $kelasData['tingkat'] === 'X' ? 'selected' : '' ?>>X</option>
                    <option value="XI" <?= $kelasData['tingkat'] === 'XI' ? 'selected' : '' ?>>XI</option>
                    <option value="XII" <?= $kelasData['tingkat'] === 'XII' ? 'selected' : '' ?>>XII</option>
                </select>
            </div>

            <div class="form-group">
                <label>Jurusan</label>
                <input type="text" name="jurusan" value="<?= old('jurusan', $kelasData['jurusan']) ?>" required>
            </div>

            <div class="form-group">
                <label>Rombel (No. Urut)</label>
                <input type="number" name="rombel" min="1" value="<?= old('rombel', $kelasData['rombel']) ?>" required>
            </div>
        </div>
        <small style="display:block;margin-bottom:14px;color:#7C8A9C;">Nama kelas saat ini:
            <strong><?= esc($kelasData['nama_kelas']) ?></strong> (akan diperbarui otomatis kalau tingkat/jurusan/rombel
            diubah)</small>

        <div class="form-group">
            <label>Wali Kelas</label>
            <select name="wali_kelas_id">
                <option value="">-- Belum ditentukan --</option>
                <?php foreach ($guru as $g): ?>
                <option value="<?= $g['id_guru'] ?>"
                    <?= $kelasData['wali_kelas_id'] == $g['id_guru'] ? 'selected' : '' ?>>
                    <?= esc($g['nama']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Ruangan</label>
                <input type="text" name="ruangan" value="<?= old('ruangan', $kelasData['ruangan']) ?>">
            </div>

            <div class="form-group">
                <label>Kapasitas</label>
                <input type="number" name="kapasitas" min="1" value="<?= old('kapasitas', $kelasData['kapasitas']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Tahun Ajaran</label>
            <select name="id_tahun_ajaran" required>
                <?php foreach ($tahunAjaran as $t): ?>
                <option value="<?= $t['id_tahun_ajaran'] ?>"
                    <?= $kelasData['id_tahun_ajaran'] == $t['id_tahun_ajaran'] ? 'selected' : '' ?>>
                    <?= esc($t['tahun_ajaran']) ?> (<?= esc($t['status']) ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="Aktif" <?= $kelasData['status'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="Nonaktif" <?= $kelasData['status'] === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="<?= base_url('admin/kelas') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>