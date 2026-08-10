<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Preview Import Jadwal</h4>
    <p class="breadcrumb">Dashboard / Jadwal Pelajaran / Import / Preview</p>
</div>

<div class="alert alert-info">
    <strong><?= $jumlahOk ?></strong> baris siap diimpor, <strong><?= $jumlahGagal ?></strong> baris gagal (akan
    dilewati).
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Baris</th>
                <th>Hari</th>
                <th>Jam</th>
                <th>Kelas</th>
                <th>Mapel</th>
                <th>Guru</th>
                <th>Ruangan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($baris as $b): ?>
            <tr class="<?= $b['valid'] ? '' : 'row-error' ?>">
                <td><?= $b['baris'] ?></td>
                <td><?= esc($b['raw']['hari']) ?></td>
                <td><?= esc($b['raw']['jamMulai']) ?> - <?= esc($b['raw']['jamSelesai']) ?></td>
                <td><?= esc($b['raw']['namaKelas']) ?></td>
                <td><?= esc($b['raw']['kodeMapel']) ?></td>
                <td><?= esc($b['raw']['nip']) ?></td>
                <td><?= esc($b['raw']['namaRuangan']) ?></td>
                <td>
                    <?php if ($b['valid']): ?>
                    <span class="badge badge-success">✔ Valid</span>
                    <?php else: ?>
                    <span class="badge badge-danger">❌ <?= esc($b['error']) ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <form action="<?= base_url('admin/jadwal/import/confirm') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="temp_file" value="<?= esc($tempFile) ?>">
        <button type="submit" class="btn btn-primary" <?= $jumlahOk === 0 ? 'disabled' : '' ?>>
            Import <?= $jumlahOk ?> Jadwal Valid
        </button>
        <a href="<?= base_url('admin/jadwal/import') ?>" class="btn btn-secondary">Upload Ulang</a>
    </form>
</div>

<?= $this->endSection() ?>