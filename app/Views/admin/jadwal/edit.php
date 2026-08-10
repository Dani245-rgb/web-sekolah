<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Edit Jadwal</h4>
    <p class="breadcrumb">Dashboard / Jadwal Pelajaran / Edit</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/jadwal/update/' . $jadwal['id_jadwal']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Kelas</label>
            <select name="id_kelas" required>
                <?php foreach ($kelas as $k): ?>
                <option value="<?= $k['id_kelas'] ?>" <?= $k['id_kelas'] == $jadwal['id_kelas'] ? 'selected' : '' ?>>
                    <?= esc($k['nama_kelas']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Mata Pelajaran</label>
            <select name="id_mapel" required>
                <?php foreach ($mapel as $m): ?>
                <option value="<?= $m['id_mapel'] ?>" <?= $m['id_mapel'] == $jadwal['id_mapel'] ? 'selected' : '' ?>>
                    <?= esc($m['nama_mapel']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Guru Pengajar</label>
            <select name="id_guru" required>
                <?php foreach ($guru as $g): ?>
                <option value="<?= $g['id_guru'] ?>" <?= $g['id_guru'] == $jadwal['id_guru'] ? 'selected' : '' ?>>
                    <?= esc($g['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Ruangan</label>
            <select name="id_ruangan" required>
                <?php foreach ($ruangan as $r): ?>
                <option value="<?= $r['id_ruangan'] ?>"
                    <?= $r['id_ruangan'] == $jadwal['id_ruangan'] ? 'selected' : '' ?>><?= esc($r['nama_ruangan']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Hari</label>
            <select name="hari" required>
                <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h): ?>
                <option value="<?= $h ?>" <?= $h == $jadwal['hari'] ? 'selected' : '' ?>><?= $h ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Jam Mulai</label>
            <input type="time" name="jam_mulai" value="<?= substr($jadwal['jam_mulai'], 0, 5) ?>" required>
        </div>

        <div class="form-group">
            <label>Jam Selesai</label>
            <input type="time" name="jam_selesai" value="<?= substr($jadwal['jam_selesai'], 0, 5) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="<?= base_url('admin/jadwal') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>