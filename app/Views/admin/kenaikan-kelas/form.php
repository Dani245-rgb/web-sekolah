<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Kenaikan Kelas</h4>
    <p class="breadcrumb">Dashboard / Kenaikan Kelas</p>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/kenaikan-kelas/proses') ?>" method="post" id="formKenaikanKelas">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Tahun Ajaran Asal</label>
            <select id="tahunAjaranAsal" required>
                <option value="">-- Pilih Tahun Ajaran Asal --</option>
                <?php foreach ($tahunAjaran as $t): ?>
                <option value="<?= $t['id_tahun_ajaran'] ?>" <?= ($tahunAktif && $t['id_tahun_ajaran'] == $tahunAktif['id_tahun_ajaran']) ? 'selected' : '' ?>>
                    <?= esc($t['tahun_ajaran']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Kelas Asal</label>
            <select id="kelasAsal" required>
                <option value="">-- Pilih Kelas Asal --</option>
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
                <p class="text-muted">Pilih tahun ajaran asal dan kelas asal dulu.</p>
            </div>
        </div>

        <hr>

        <div class="form-group">
            <label>Tahun Ajaran Tujuan</label>
            <select name="id_tahun_ajaran_tujuan" required>
                <option value="">-- Pilih Tahun Ajaran Tujuan --</option>
                <?php foreach ($tahunAjaran as $t): ?>
                <option value="<?= $t['id_tahun_ajaran'] ?>"><?= esc($t['tahun_ajaran']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Kelas Tujuan</label>
            <select name="id_kelas_tujuan" required>
                <option value="">-- Pilih Kelas Tujuan --</option>
                <?php foreach ($kelas as $k): ?>
                <option value="<?= $k['id_kelas'] ?>"><?= esc($k['nama_kelas']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Proses Kenaikan Kelas</button>
        <a href="<?= base_url('admin/kenaikan-kelas') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script>
function muatSiswa() {
    const idKelas = document.getElementById('kelasAsal').value;
    const idTahun = document.getElementById('tahunAjaranAsal').value;
    const container = document.getElementById('daftarSiswa');

    if (!idKelas || !idTahun) {
        container.innerHTML = '<p class="text-muted">Pilih tahun ajaran asal dan kelas asal dulu.</p>';
        return;
    }

    container.innerHTML = '<p>Memuat...</p>';

    fetch('<?= base_url('admin/kenaikan-kelas/siswa-by-kelas') ?>/' + idKelas + '/' + idTahun)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                container.innerHTML = '<p class="text-muted">Tidak ada siswa Aktif di kelas dan tahun ajaran ini.</p>';
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
            container.innerHTML = '<p class="text-error">Gagal memuat data siswa.</p>';
        });
}

document.getElementById('tahunAjaranAsal').addEventListener('change', muatSiswa);
document.getElementById('kelasAsal').addEventListener('change', muatSiswa);

document.getElementById('checkAllSiswa').addEventListener('change', function () {
    document.querySelectorAll('.checkSiswa').forEach(cb => cb.checked = this.checked);
});
</script>

<?= $this->endSection() ?>