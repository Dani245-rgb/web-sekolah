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
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="<?= base_url('admin/siswa/create') ?>" class="btn btn-primary">+ Tambah Siswa</a>
            <a href="<?= base_url('admin/siswa/import') ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel"></i> Import Excel</a>
            <a href="<?= base_url('admin/siswa/trash') ?>" class="btn btn-secondary">Tong Sampah</a>
        </div>

        <form action="<?= base_url('admin/siswa') ?>" method="get" class="search-form">
            <?php if (!empty($jurusanAktif)): ?>
                <input type="hidden" name="jurusan" value="<?= (int) $jurusanAktif ?>">
            <?php endif; ?>
            <input type="text" name="cari" placeholder="Cari siswa..." value="<?= esc($keyword) ?>">
            <button type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin:4px 0 16px;">
        <a href="<?= base_url('admin/siswa') ?>"
            class="btn <?= empty($jurusanAktif) ? 'btn-primary' : 'btn-secondary' ?>">Semua</a>
        <?php foreach ($jurusanList as $j): ?>
            <a href="<?= base_url('admin/siswa?jurusan=' . $j['id_jurusan']) ?>"
                class="btn <?= (int) $jurusanAktif === (int) $j['id_jurusan'] ? 'btn-primary' : 'btn-secondary' ?>"
                title="<?= esc($j['nama_jurusan']) ?>"><?= esc($j['kode_jurusan']) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="table-responsive-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>Jurusan</th>
                    <th>JK</th>
                    <th>Tempat, Tgl Lahir</th>
                    <th>Agama</th>
                    <th>No HP Ortu</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = $pager->getCurrentPage() > 1 ? (($pager->getCurrentPage() - 1) * 50) + 1 : 1; ?>
                <?php foreach ($siswa as $s): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($s['nis']) ?></td>
                        <td><?= esc($s['nisn']) ?></td>
                        <td><strong><?= esc($s['nama']) ?></strong></td>
                        <td title="<?= esc($s['nama_jurusan'] ?? '') ?>"><?= esc($s['kode_jurusan'] ?? '-') ?></td>
                        <td><?= esc($s['jenis_kelamin']) ?></td>
                        <td>
                            <?= esc($s['tempat_lahir'] ?? '-') ?><?= !empty($s['tanggal_lahir']) ? ', ' . date('d-m-Y', strtotime($s['tanggal_lahir'])) : '' ?>
                        </td>
                        <td><?= esc($s['agama'] ?? '-') ?></td>
                        <td><?= esc($s['no_hp_ortu'] ?? '-') ?></td>
                        <td><?= esc($s['status']) ?></td>
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
                        <td colspan="11" class="text-center">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                <i class="bi bi-inbox" style="font-size:24px;color:#d7dce3;"></i>
                                <span>Belum ada data siswa.</span>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <?= $pager->links('siswa', 'default_full') ?>
    </div>
</div>

<?= $this->endSection() ?>