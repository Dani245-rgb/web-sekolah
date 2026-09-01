<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <h4>Materi Saya</h4>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert" style="background:#dcfce7;color:#166534;padding:10px 14px;border-radius:8px;margin-top:16px;">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($daftar)): ?>
        <div style="text-align:center; padding:40px 0; color:#999;">
            Anda belum mengunggah materi. Unggah dari halaman <a href="<?= base_url('guru/jadwal') ?>">Jadwal</a> Anda.
        </div>
    <?php else: ?>
        <table style="width:100%; border-collapse:collapse; margin-top:16px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left;">
                    <th style="padding:10px 12px;">Judul</th>
                    <th style="padding:10px 12px;">Kelas / Mapel</th>
                    <th style="padding:10px 12px;">Diunggah</th>
                    <th style="padding:10px 12px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftar as $m): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:10px 12px; font-weight:600;"><?= esc($m['judul']) ?></td>
                        <td style="padding:10px 12px;"><?= esc($m['nama_kelas']) ?> / <?= esc($m['nama_mapel']) ?></td>
                        <td style="padding:10px 12px;"><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
                        <td style="padding:10px 12px;">
                            <a href="<?= base_url('materi/unduh/' . $m['id_materi']) ?>">Lihat File</a> |
                            <a href="<?= base_url('guru/materi/edit/' . $m['id_materi']) ?>">Edit</a> |
                            <form method="post" action="<?= base_url('guru/materi/delete/' . $m['id_materi']) ?>" style="display:inline;"
                                onsubmit="return confirm('Yakin hapus materi ini?')">
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