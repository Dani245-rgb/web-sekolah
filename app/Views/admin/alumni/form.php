<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Luluskan Siswa</h4>
    <p class="breadcrumb">Dashboard / Alumni / Luluskan</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-error">
        <?php foreach (session()->getFlashdata('errors') as $error): ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/alumni/proses') ?>" method="post" id="formLuluskan">
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
            <label>Tanggal Lulus</label>
            <input type="date" name="tanggal_lulus" required>
        </div>

        <div class="form-group">
            <label>Daftar Siswa (centang yang mau diluluskan)</label>
            <div style="margin-bottom:8px;">
                <button type="button" class="btn btn-sm btn-secondary" onclick="centangSemua(true)">Centang Semua</button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="centangSemua(false)">Kosongkan</button>
            </div>
            <div id="daftarSiswa">
                <p class="text-muted">Pilih kelas dulu untuk menampilkan daftar siswa.</p>
            </div>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="konfirmasi_yakin" value="1" required>
                Saya yakin dengan siswa yang dipilih dan tanggal kelulusan di atas.
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Luluskan Siswa Terpilih</button>
        <a href="<?= base_url('admin/alumni') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script>
    document.getElementById('pilihKelas').addEventListener('change', function() {
        const idKelas = this.value;
        const container = document.getElementById('daftarSiswa');

        if (!idKelas) {
            container.innerHTML = '<p class="text-muted">Pilih kelas dulu untuk menampilkan daftar siswa.</p>';
            return;
        }

        container.innerHTML = '<p>Memuat...</p>';

        fetch('<?= base_url('admin/alumni/siswa-per-kelas') ?>/' + idKelas)
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    container.innerHTML = '<p class="text-muted">Tidak ada siswa Aktif di kelas ini.</p>';
                    return;
                }
                let html = '';
                data.forEach(s => {
                    html += `<label style="display:block;">
                    <input type="checkbox" name="id_siswa[]" value="${s.id_siswa}"> ${s.nis} - ${s.nama}
                </label>`;
                });
                container.innerHTML = html;
            })
            .catch(() => {
                container.innerHTML = '<p class="text-error">Gagal memuat data siswa.</p>';
            });
    });

    function centangSemua(state) {
        document.querySelectorAll('#daftarSiswa input[type=checkbox]').forEach(cb => cb.checked = state);
    }
</script>

<?= $this->endSection() ?>