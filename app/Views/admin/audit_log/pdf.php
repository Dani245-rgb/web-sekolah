<style>
    body { font-family: sans-serif; font-size: 11px; }
    h2 { margin-bottom: 2px; }
    .sub { color: #666; margin-bottom: 14px; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 5px 7px; text-align: left; vertical-align: top; }
    th { background: #eef1ff; }
    .col-no { width: 30px; }
    .col-waktu { width: 110px; }
    .col-user { width: 100px; }
    .col-aksi { width: 90px; }
    .col-ip { width: 80px; }
</style>

<h2>Audit Log</h2>
<div class="sub">
    Dicetak: <?= esc(date('d M Y, H:i')) ?>
    <?php if (!empty($filter['aksi']) || !empty($filter['username']) || !empty($filter['dari']) || !empty($filter['sampai'])): ?>
        <br>Filter:
        <?= !empty($filter['aksi']) ? 'Aksi=' . esc($filter['aksi']) . ' ' : '' ?>
        <?= !empty($filter['username']) ? 'User=' . esc($filter['username']) . ' ' : '' ?>
        <?= !empty($filter['dari']) ? 'Dari=' . esc($filter['dari']) . ' ' : '' ?>
        <?= !empty($filter['sampai']) ? 'Sampai=' . esc($filter['sampai']) : '' ?>
    <?php endif; ?>
</div>

<table>
    <thead>
        <tr>
            <th class="col-no">No</th>
            <th class="col-waktu">Waktu</th>
            <th class="col-user">User</th>
            <th class="col-aksi">Aksi</th>
            <th>Keterangan</th>
            <th class="col-ip">IP Address</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logs as $i => $log): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($log['created_at']) ?></td>
            <td><?= esc($log['username'] ?? '-') ?></td>
            <td><?= esc($log['aksi']) ?></td>
            <td><?= esc($log['keterangan'] ?? '-') ?></td>
            <td><?= esc($log['ip_address'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($logs)): ?>
        <tr><td colspan="6" style="text-align:center;">Tidak ada data.</td></tr>
        <?php endif; ?>
    </tbody>
</table>