<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Tambah Jadwal</h4>
    <p class="breadcrumb">Dashboard / Jadwal Pelajaran / Tambah</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/jadwal/store') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Kelas</label>
            <select name="id_kelas" required>
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($kelas as $k): ?>
                <option value="<?= $k['id_kelas'] ?>"><?= esc($k['nama_kelas']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Mata Pelajaran</label>
            <select name="id_mapel" required>
                <option value="">-- Pilih Mapel --</option>
                <?php foreach ($mapel as $m): ?>
                <option value="<?= $m['id_mapel'] ?>"><?= esc($m['nama_mapel']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Guru Pengajar</label>
            <select name="id_guru" required>
                <option value="">-- Pilih Guru --</option>
                <?php foreach ($guru as $g): ?>
                <option value="<?= $g['id_guru'] ?>"><?= esc($g['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Ruangan</label>
            <select name="id_ruangan" required>
                <option value="">-- Pilih Ruangan --</option>
                <?php foreach ($ruangan as $r): ?>
                <option value="<?= $r['id_ruangan'] ?>"><?= esc($r['nama_ruangan']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Hari</label>
            <select name="hari" required>
                <option value="">-- Pilih Hari --</option>
                <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h): ?>
                <option value="<?= $h ?>"><?= $h ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Jam Mulai</label>
            <input type="time" name="jam_mulai" required>
        </div>

        <div class="form-group">
            <label>Jam Selesai</label>
            <input type="time" name="jam_selesai" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
        <a href="<?= base_url('admin/jadwal') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>