<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Data Siswa</h4>
    <p class="breadcrumb">Dashboard / Data Siswa</p>
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
    <div class="card-toolbar">
        <a href="<?= base_url('admin/siswa/create') ?>" class="btn btn-primary">+ Tambah Siswa</a>
        <a href="<?= base_url('admin/siswa/import') ?>" class="btn btn-secondary">📊 Import Excel</a>

        <form action="<?= base_url('admin/siswa') ?>" method="get" class="search-form">
            <input type="text" name="cari" placeholder="Cari siswa..." value="<?= esc($keyword) ?>">
            <button type="submit"><i class="icon-search"></i></button>
        </form>
    </div>

    <?php if (!$tahunAktif): ?>
    <div class="alert alert-error" style="margin: 0 0 12px;">
        Belum ada Tahun Ajaran Aktif. Kelas siswa tidak bisa ditampilkan sampai kamu mengaktifkan salah satu di menu
        Tahun Ajaran.
    </div>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>JK</th>
                <th>Kelas</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = $pager->getCurrentPage() > 1 ? (($pager->getCurrentPage() - 1) * 10) + 1 : 1; ?>
            <?php foreach ($siswa as $s): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($s['nis']) ?></td>
                <td><strong><?= esc($s['nama']) ?></strong></td>
                <td><?= esc($s['jenis_kelamin']) ?></td>
                <td><?= esc($s['nama_kelas'] ?? '-') ?></td>
                <td>
                    <span class="badge <?= $s['status'] === 'Aktif' ? 'badge-success' : 'badge-danger' ?>">
                        <?= esc($s['status']) ?>
                    </span>
                </td>
                <td>
                    <a href="<?= base_url('admin/siswa/edit/' . $s['id_siswa']) ?>"
                        class="btn btn-sm btn-warning">Edit</a>
                    <a href="<?= base_url('admin/siswa/resetpassword/' . $s['id_siswa']) ?>" class="btn btn-sm btn-info"
                        onclick="return confirm('Reset password siswa ini ke tanggal lahir? Siswa wajib ganti password saat login berikutnya.')">Reset
                        Password</a>
                    <a href="<?= base_url('admin/siswa/delete/' . $s['id_siswa']) ?>" class="btn btn-sm btn-danger"
                        onclick="return confirm('Yakin hapus data siswa ini secara permanen?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($siswa)): ?>
            <tr>
                <td colspan="7" class="text-center">Belum ada data siswa.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <?= $pager->links('siswa', 'default_full') ?>
    </div>
</div>

<?= $this->endSection() ?>