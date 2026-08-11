<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/berita.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="berita-page-header">
    <h4>Kelola Berita</h4>
    <a href="<?= base_url('admin/berita/create') ?>" class="btn btn-primary">+ Tambah Berita</a>
</div>

<form method="get" class="berita-search-form">
    <input type="text" name="q" value="<?= esc($keyword) ?>" placeholder="Cari judul berita...">
    <button class="btn btn-outline-secondary">Cari</button>
</form>

<table class="table table-bordered">
    <thead>
        <tr><th>Judul</th><th>Kategori</th><th>Status</th><th>Tanggal Publish</th><th>Aksi</th></tr>
    </thead>
    <tbody>
        <?php foreach ($beritaList as $b): ?>
        <tr>
            <td><?= esc($b['judul']) ?></td>
            <td><?= esc($b['kategori']) ?></td>
            <td>
                <span class="badge <?= $b['status'] === 'Published' ? 'bg-success' : 'bg-secondary' ?>">
                    <?= esc($b['status']) ?>
                </span>
            </td>
            <td><?= esc($b['tanggal_publish'] ? date('d M Y', strtotime($b['tanggal_publish'])) : '-') ?></td>
            <td>
                <a href="<?= base_url('admin/berita/edit/' . $b['id_berita']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                <a href="<?= base_url('admin/berita/delete/' . $b['id_berita']) ?>" class="btn btn-sm btn-outline-danger"
                    onclick="return confirm('Hapus berita ini?')">Hapus</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($beritaList)): ?>
        <tr><td colspan="5" style="text-align:center;">Belum ada berita.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= $pager->links() ?>

<?= $this->endSection() ?>