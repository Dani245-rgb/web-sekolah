<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/audit-log.css') ?>">

<div class="page-header">
    <h4>Audit Log</h4>
    <p class="breadcrumb">Dashboard / Audit Log</p>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>No</th><th>Waktu</th><th>User</th><th>Aksi</th><th>Keterangan</th><th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = ($pager->getCurrentPage() - 1) * $pager->getPerPage() + 1; ?>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($log['created_at']) ?></td>
                <td><?= esc($log['username'] ?? '-') ?></td>
                <td><?= esc($log['aksi']) ?></td>
                <td><?= esc($log['keterangan'] ?? '-') ?></td>
                <td><?= esc($log['ip_address'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
            <tr><td colspan="6" class="text-center">Belum ada log.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?= $pager->links() ?>
    </div>
</div>

<?= $this->endSection() ?>