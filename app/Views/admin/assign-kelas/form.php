<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Assign Siswa ke Kelas</h4>
    <p class="breadcrumb">Dashboard / Kelas / Assign Kelas</p>
</div>

<?php if (!$tahunAktif): ?>
<div class="alert alert-error">Belum ada Tahun Ajaran aktif. Set dulu Tahun Ajaran aktif sebelum assign kelas.</div>
<?php else: ?>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <p class="text-muted">Tahun Ajaran aktif: <strong><?= esc($tahunAktif['tahun_ajaran']) ?></strong></p>

    <form action="<?= base_url('admin/assign-kelas/proses') ?>" method="post" id="formAssignKelas">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Kelas Tujuan</label>
            <select name="id_kelas" required>
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($kelas as $k): ?>
                <option value="<?= $k['id_kelas'] ?>"><?= esc($k['nama_kelas']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" id="checkAllSiswa"> Pilih Semua
            </label>
            <div id="daftarSiswa" style="max-height: 400px; overflow-y: auto; margin-top: 8px;">
                <p class="text-muted">Memuat daftar siswa...</p>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Assign Siswa Terpilih</button>
        <a href="<?= base_url('admin/assign-kelas') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script>
fetch('<?= base_url('admin/assign-kelas/siswa-belum-assign') ?>')
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('daftarSiswa');
        if (data.length === 0) {
            container.innerHTML = '<p class="text-muted">Semua siswa Aktif sudah punya kelas di tahun ajaran ini.</p>';
            return;
        }
        let html = '';
        data.forEach(s => {
            html += `<label style="display:block; padding:4px 0;">
                <input type="checkbox" name="id_siswa[]" value="${s.id_siswa}" class="checkSiswa"> ${s.nis} - ${s.nama}
            </label>`;
        });
        container.innerHTML = html;
    })
    .catch(() => {
        document.getElementById('daftarSiswa').innerHTML = '<p class="text-error">Gagal memuat data siswa.</p>';
    });

document.getElementById('checkAllSiswa').addEventListener('change', function () {
    document.querySelectorAll('.checkSiswa').forEach(cb => cb.checked = this.checked);
});
</script>

<?php endif; ?>
<?= $this->endSection() ?>