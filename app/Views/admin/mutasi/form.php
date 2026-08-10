<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Catat Mutasi Siswa</h4>
    <p class="breadcrumb">Dashboard / Mutasi / Catat</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/mutasi/proses') ?>" method="post" id="formMutasi">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Kelas</label>
            <select id="pilihKelas">
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($kelas as $k): ?>
                <option value="<?= $k['id_kelas'] ?>"><?= esc($k['nama_kelas']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Siswa</label>
            <div id="daftarSiswa">
                <p class="text-muted">Pilih kelas dulu untuk menampilkan daftar siswa.</p>
            </div>
        </div>

        <div class="form-group">
            <label>Jenis Mutasi</label>
            <select name="jenis_mutasi" id="jenisMutasi" required>
                <option value="">-- Pilih Jenis --</option>
                <option value="Pindah">Pindah (ke sekolah lain)</option>
                <option value="Keluar">Keluar</option>
            </select>
        </div>

        <div class="form-group" id="groupSekolahTujuan" style="display:none;">
            <label>Sekolah Tujuan</label>
            <input type="text" name="sekolah_tujuan" placeholder="Nama sekolah tujuan (opsional)">
        </div>

        <div class="form-group">
            <label>Tanggal Mutasi</label>
            <input type="date" name="tanggal_mutasi" required>
        </div>

        <div class="form-group">
            <label>Alasan / Keterangan</label>
            <textarea name="alasan" rows="3" placeholder="Opsional"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="<?= base_url('admin/mutasi') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script>
document.getElementById('pilihKelas').addEventListener('change', function () {
    const idKelas = this.value;
    const container = document.getElementById('daftarSiswa');

    if (!idKelas) {
        container.innerHTML = '<p class="text-muted">Pilih kelas dulu untuk menampilkan daftar siswa.</p>';
        return;
    }

    container.innerHTML = '<p>Memuat...</p>';

    fetch('<?= base_url('admin/mutasi/siswa-per-kelas') ?>/' + idKelas)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                container.innerHTML = '<p class="text-muted">Tidak ada siswa Aktif di kelas ini.</p>';
                return;
            }
            let html = '';
            data.forEach(s => {
                html += `<label style="display:block;">
                    <input type="radio" name="id_siswa" value="${s.id_siswa}" required> ${s.nis} - ${s.nama}
                </label>`;
            });
            container.innerHTML = html;
        })
        .catch(() => {
            container.innerHTML = '<p class="text-error">Gagal memuat data siswa.</p>';
        });
});

document.getElementById('jenisMutasi').addEventListener('change', function () {
    document.getElementById('groupSekolahTujuan').style.display = this.value === 'Pindah' ? 'block' : 'none';
});
</script>

<?= $this->endSection() ?>