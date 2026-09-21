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

        <form action="<?= base_url('admin/siswa') ?>" method="get" class="search-form">
            <input type="text" name="cari" placeholder="Cari siswa..." value="<?= esc($keyword) ?>">
            <button type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>JK</th>
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
                    <td>
                        <div class="aksi-actions">
                            <a href="<?= base_url('admin/siswa/edit/' . $s['id_siswa']) ?>"
                                class="btn btn-sm btn-warning">Edit</a>
                            <form method="post" action="<?= base_url('admin/siswa/delete/' . $s['id_siswa']) ?>"
                                onsubmit="return confirm('Hapus data siswa ini? Data akan dipindahkan ke Tong Sampah dan bisa dipulihkan kapan saja.')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($siswa)): ?>
                <tr>
                    <td colspan="5" class="text-center">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <i class="bi bi-inbox" style="font-size:24px;color:#d7dce3;"></i>
                            <span>Belum ada data siswa.</span>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <?= $pager->links('siswa', 'default_full') ?>
    </div>
</div>

<?= $this->endSection() ?>