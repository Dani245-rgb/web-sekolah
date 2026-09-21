<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('content') ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h4>Notifikasi</h4>
    <form action="<?= base_url('admin/notifikasi/baca-semua') ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-secondary btn-sm">Tandai Semua Dibaca</button>
    </form>
</div>

<div class="card">
    <div class="table-responsive-wrap">
<table class="table">
        <thead>
            <tr>
                <th>Status</th>
                <th>Judul</th>
                <th>Pesan</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($notifikasi as $n): ?>
            <tr style="<?= $n['is_read'] ? 'opacity:0.6;' : 'font-weight:600;' ?>">
                <td><?= $n['is_read'] ? '-' : '<span class="badge badge-warning">Baru</span>' ?></td>
                <td>
                    <a href="<?= base_url('admin/notifikasi/baca/' . $n['id_notif']) ?>" style="color:#3b82f6;text-decoration:none;" onclick="event.preventDefault(); document.getElementById('form-baca-<?= $n['id_notif'] ?>').submit();">
                        <?= esc($n['judul']) ?>
                    </a>
                    <form id="form-baca-<?= $n['id_notif'] ?>" action="<?= base_url('admin/notifikasi/baca/' . $n['id_notif']) ?>" method="post" style="display:none;">
                        <?= csrf_field() ?>
                    </form>
                </td>
                <td><?= esc($n['pesan']) ?></td>
                <td><?= date('d M Y H:i', strtotime($n['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($notifikasi)): ?>
            <tr><td colspan="4" style="color:#7c8a9c;">Belum ada notifikasi.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?= $this->endSection() ?>