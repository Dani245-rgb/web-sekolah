<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h4>Tugas Saya</h4>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert" style="background:#dcfce7;color:#166534;padding:10px 14px;border-radius:8px;margin-top:16px;">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($daftar)): ?>
        <div class="tugas-empty-cta">
            <i class="bi bi-folder-plus"></i>
            <h5>Anda belum membuat tugas</h5>
            <p>Tugas dibuat dari halaman Jadwal — pilih salah satu jadwal mengajar, lalu klik tombol "Tugas" di baris itu.</p>
            <a href="<?= base_url('guru/jadwal') ?>" class="btn btn-primary">
                <i class="bi bi-calendar3"></i> Buka Halaman Jadwal
            </a>
        </div>
    <?php else: ?>
        <table style="width:100%; border-collapse:collapse; margin-top:16px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left;">
                    <th style="padding:10px 12px;">Judul</th>
                    <th style="padding:10px 12px;">Kelas / Mapel</th>
                    <th style="padding:10px 12px;">Tenggat</th>
                    <th style="padding:10px 12px;">Submisi</th>
                    <th style="padding:10px 12px;">Status</th>
                    <th style="padding:10px 12px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftar as $t): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:10px 12px; font-weight:600;"><?= esc($t['judul']) ?></td>
                        <td style="padding:10px 12px;"><?= esc($t['nama_kelas']) ?> / <?= esc($t['nama_mapel']) ?></td>
                        <td style="padding:10px 12px;"><?= date('d/m/Y H:i', strtotime($t['tenggat'])) ?></td>
                        <td style="padding:10px 12px;"><?= $t['jumlah_submisi'] ?> siswa</td>
                        <td style="padding:10px 12px;"><?= esc($t['status']) ?></td>
                        <td style="padding:10px 12px;">
                            <a href="<?= base_url('guru/tugas/submisi/' . $t['id_tugas']) ?>">Lihat Submisi</a> |
                            <a href="<?= base_url('guru/tugas/edit/' . $t['id_tugas']) ?>">Edit</a> |
                            <form method="post" action="<?= base_url('guru/tugas/delete/' . $t['id_tugas']) ?>" style="display:inline;" onsubmit="return confirm('Yakin hapus tugas ini? Semua submisi siswa juga akan terhapus.')">
                                <?= csrf_field() ?>
                                <button type="submit" style="background:none; border:none; padding:0; color:#dc2626; text-decoration:underline; cursor:pointer; font:inherit;">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:20px;">
        <a href="<?= base_url('guru/dashboard') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>