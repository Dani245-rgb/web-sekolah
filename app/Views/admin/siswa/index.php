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
        <div class="toolbar-actions">
            <a href="<?= base_url('admin/siswa/create') ?>" class="btn btn-primary">+ Tambah Siswa</a>
            <a href="<?= base_url('admin/siswa/import') ?>" class="btn btn-secondary">Import Excel</a>
            <a href="<?= base_url('admin/siswa/trash') ?>" class="btn btn-secondary">Tong Sampah</a>
        </div>

        <form action="<?= base_url('admin/siswa') ?>" method="get" class="search-form">
            <?php if (!empty($jurusanFilter)): ?>
                <input type="hidden" name="jurusan" value="<?= esc($jurusanFilter) ?>">
            <?php endif; ?>
            <input type="text" name="cari" placeholder="Cari siswa..." value="<?= esc($keyword) ?>">
            <button type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <?php if (!$tahunAktif): ?>
        <div class="alert alert-error" style="margin: 0 0 12px;">
            Belum ada Tahun Ajaran Aktif. Kelas siswa tidak bisa ditampilkan sampai kamu mengaktifkan salah satu di menu
            Tahun Ajaran.
        </div>
    <?php endif; ?>

    <?php if (!empty($daftarJurusan)): ?>
        <div class="filter-chips">
            <a href="<?= base_url('admin/siswa' . (!empty($keyword) ? '?cari=' . urlencode($keyword) : '')) ?>"
                class="btn btn-sm <?= empty($jurusanFilter) ? 'btn-primary' : 'btn-secondary' ?>">Semua Jurusan</a>
            <?php foreach ($daftarJurusan as $j): ?>
                <a href="<?= base_url('admin/siswa?jurusan=' . urlencode($j['jurusan']) . (!empty($keyword) ? '&cari=' . urlencode($keyword) : '')) ?>"
                    class="btn btn-sm <?= $jurusanFilter === $j['jurusan'] ? 'btn-primary' : 'btn-secondary' ?>"><?= esc($j['jurusan']) ?></a>
            <?php endforeach; ?>
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
                <th>Jurusan</th>
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
                    <td>
                        <?php if (!empty($s['nama_kelas'])): ?>
                            <?= esc($s['nama_kelas']) ?>
                        <?php elseif ($s['status'] === 'Aktif'): ?>
                            <span class="badge badge-warning" title="Siswa aktif tapi belum di-assign ke kelas manapun">⚠️ Belum ada kelas</span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?= esc($s['jurusan'] ?? '-') ?></td>
                    <td>
                        <span class="badge <?= $s['status'] === 'Aktif' ? 'badge-success' : 'badge-danger' ?>">
                            <?= esc($s['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= base_url('admin/siswa/edit/' . $s['id_siswa']) ?>"
                            class="btn btn-sm btn-warning">Edit</a>
                        <form method="post" action="<?= base_url('admin/siswa/delete/' . $s['id_siswa']) ?>" style="display:inline;"
                            onsubmit="return confirm('Hapus data siswa ini? Data akan dipindahkan ke Tong Sampah dan bisa dipulihkan kapan saja.')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($siswa)): ?>
                <tr>
                    <td colspan="8" class="text-center">
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