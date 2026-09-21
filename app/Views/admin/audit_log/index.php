<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/audit-log.css') ?>">

<div class="page-header">
    <h4>Audit Log</h4>
    <p class="breadcrumb">Dashboard / Audit Log</p>
</div>

<div class="card">
    <form method="get" action="<?= base_url('admin/audit-log') ?>" class="audit-filter-form">
        <div class="audit-filter-row">
            <div class="audit-filter-group">
                <label>Aksi</label>
                <select name="aksi">
                    <option value="">Semua Aksi</option>
                    <?php foreach ($daftarAksi as $a): ?>
                        <option value="<?= esc($a) ?>" <?= ($filter['aksi'] ?? '') === $a ? 'selected' : '' ?>>
                            <?= esc($a) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="audit-filter-group">
                <label>User</label>
                <input type="text" name="username" value="<?= esc($filter['username'] ?? '') ?>" placeholder="Cari nama user...">
            </div>

            <div class="audit-filter-group">
                <label>Dari Tanggal</label>
                <input type="date" name="dari" value="<?= esc($filter['dari'] ?? '') ?>">
            </div>

            <div class="audit-filter-group">
                <label>Sampai Tanggal</label>
                <input type="date" name="sampai" value="<?= esc($filter['sampai'] ?? '') ?>">
            </div>

            <div class="audit-filter-group audit-filter-actions">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?= base_url('admin/audit-log') ?>" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="audit-export-row">
        <?php
        $queryString = http_build_query(array_filter($filter));
        $qs = $queryString ? '?' . $queryString : '';
        ?>
        <a href="<?= base_url('admin/audit-log/export/pdf' . $qs) ?>" class="btn btn-outline-danger">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
        <a href="<?= base_url('admin/audit-log/export/excel' . $qs) ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
        </a>
    </div>

    <div class="table-responsive-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Waktu</th>
                    <th>User</th>
                    <th>Aksi</th>
                    <th>Keterangan</th>
                    <th>IP Address</th>
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
                    <tr>
                        <td colspan="6" class="text-center">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                <i class="bi bi-inbox" style="font-size:24px;color:#d7dce3;"></i>
                                <span>Belum ada log yang cocok dengan filter.</span>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <?= $pager->links() ?>
    </div>
</div>

<?= $this->endSection() ?>