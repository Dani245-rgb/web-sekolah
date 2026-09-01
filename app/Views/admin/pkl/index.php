<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="card-panel">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h4>Penempatan PKL</h4>
        <a href="<?= base_url('admin/pkl/create') ?>" class="btn btn-primary">+ Tambah Penempatan</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert" style="background:#dcfce7;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:16px;">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert" style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;margin-bottom:16px;">
            <?php foreach (session()->getFlashdata('errors') as $e): ?>
                <div><?= esc($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="get" style="display:flex; gap:10px; margin-bottom:16px;">
        <input type="text" name="keyword" placeholder="Cari siswa/perusahaan..." value="<?= esc($filter['keyword'] ?? '') ?>" style="flex:1; padding:8px 12px; border:1px solid #ddd; border-radius:8px;">
        <select name="status" style="padding:8px 12px; border:1px solid #ddd; border-radius:8px;">
            <option value="">Semua Status</option>
            <option value="Aktif" <?= ($filter['status'] ?? '') === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="Selesai" <?= ($filter['status'] ?? '') === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
            <option value="Dibatalkan" <?= ($filter['status'] ?? '') === 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
    </form>

    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f8fafc; text-align:left;">
                <th style="padding:10px 12px;">Siswa</th>
                <th style="padding:10px 12px;">Perusahaan</th>
                <th style="padding:10px 12px;">Pembimbing Sekolah</th>
                <th style="padding:10px 12px;">Periode</th>
                <th style="padding:10px 12px;">Status</th>
                <th style="padding:10px 12px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($daftar)): ?>
                <tr><td colspan="6" style="text-align:center; padding:24px; color:#999;">Belum ada data penempatan PKL.</td></tr>
            <?php endif; ?>
            <?php foreach ($daftar as $d): ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:10px 12px;"><?= esc($d['nama_siswa']) ?> <br><small style="color:#888;"><?= esc($d['nis']) ?></small></td>
                    <td style="padding:10px 12px;"><?= esc($d['nama_perusahaan']) ?></td>
                    <td style="padding:10px 12px;"><?= esc($d['nama_guru_pembimbing'] ?? '-') ?></td>
                    <td style="padding:10px 12px;"><?= date('d/m/Y', strtotime($d['tanggal_mulai'])) ?> - <?= $d['tanggal_selesai'] ? date('d/m/Y', strtotime($d['tanggal_selesai'])) : '-' ?></td>
                    <td style="padding:10px 12px;"><?= esc($d['status']) ?></td>
                    <td style="padding:10px 12px;">
                        <a href="<?= base_url('admin/pkl/edit/' . $d['id_penempatan']) ?>">Edit</a> |
                        <form method="post" action="<?= base_url('admin/pkl/delete/' . $d['id_penempatan']) ?>" style="display:inline;"
                            onsubmit="return confirm('Yakin hapus data ini? Jurnal terkait juga akan ikut terhapus.')">
                            <?= csrf_field() ?>
                            <button type="submit" style="background:none; border:none; padding:0; color:#dc2626; text-decoration:underline; cursor:pointer; font:inherit;">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>