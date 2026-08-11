<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Rekap Nilai — <?= esc($pengaturan['nama_kelas'] ?? '') ?></h4>
    <p class="breadcrumb">Dashboard / Rekap Nilai / Detail</p>
</div>

<div class="card">
    <div class="card-toolbar">
        <a href="<?= base_url('admin/nilai/rekap') ?>" class="btn btn-secondary">← Kembali</a>
        <a href="<?= base_url('admin/nilai/rekap/' . $pengaturan['id_pengaturan'] . '/export/pdf') ?>" class="btn btn-outline-danger">📄 Export PDF</a>
        <a href="<?= base_url('admin/nilai/rekap/' . $pengaturan['id_pengaturan'] . '/export/excel') ?>" class="btn btn-outline-success">📊 Export Excel</a>
    </div>

    <p>KKM: <strong><?= esc($pengaturan['kkm']) ?></strong></p>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Nilai Akhir</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($hasil as $h): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc($h['nama_siswa']) ?></td>
                    <td><?= esc($h['nilai_akhir']) ?></td>
                    <td>
                        <span class="badge-info <?= $h['status'] === 'Tuntas' ? 'badge-tuntas' : 'badge-belum' ?>">
                            <?= esc($h['status']) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($hasil)): ?>
                <tr>
                    <td colspan="4" class="text-center">Belum ada nilai siswa untuk kelas/mapel ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>