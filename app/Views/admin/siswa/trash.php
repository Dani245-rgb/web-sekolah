<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Tong Sampah - Data Siswa</h4>
    <p class="breadcrumb">Dashboard / Data Siswa / Tong Sampah</p>
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
        <a href="<?= base_url('admin/siswa') ?>" class="btn btn-secondary">Kembali ke Data Siswa</a>
    </div>

    <p style="color:#666; margin-bottom:16px;">
        Siswa yang dihapus akan muncul di sini. Bisa dipulihkan kapan saja, atau dihapus permanen jika sudah tidak dibutuhkan.
    </p>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Dihapus Pada</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($siswaTerhapus as $s): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc($s['nis']) ?></td>
                    <td><strong><?= esc($s['nama']) ?></strong></td>
                    <td><?= esc($s['deleted_at']) ?></td>
                    <td>
                        <form action="<?= base_url('admin/siswa/restore/' . $s['id_siswa']) ?>" method="post" style="display:inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-primary"
                                onclick="return confirm('Pulihkan siswa &quot;<?= esc($s['nama']) ?>&quot;?')">
                                Pulihkan
                            </button>
                        </form>

                        <form action="<?= base_url('admin/siswa/force-delete/' . $s['id_siswa']) ?>" method="post" style="display:inline;"
                            onsubmit="return konfirmasiHapusPermanen(this, '<?= esc($s['nama'], 'js') ?>')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger">
                                Hapus Permanen
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($siswaTerhapus)): ?>
                <tr>
                    <td colspan="5" class="text-center">Tong sampah kosong.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function konfirmasiHapusPermanen(form, namaSiswa) {
    const ketik = prompt(
        `PERINGATAN: Data "${namaSiswa}" akan dihapus PERMANEN dan tidak bisa dikembalikan.\n\nKetik ulang nama siswa persis "${namaSiswa}" untuk melanjutkan:`
    );

    if (ketik === null) {
        return false; // Admin klik Cancel
    }

    if (ketik.trim() !== namaSiswa) {
        alert('Nama yang diketik tidak cocok. Penghapusan dibatalkan.');
        return false;
    }

    return true; // cocok, lanjutkan submit form
}
</script>

<?= $this->endSection() ?>