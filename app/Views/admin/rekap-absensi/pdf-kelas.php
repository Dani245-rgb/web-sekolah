<style>
    body { font-family: sans-serif; font-size: 12px; }
    h2 { margin-bottom: 2px; }
    .sub { color: #666; margin-bottom: 14px; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
    th { background: #eef1ff; }
    .status-hadir { color: #1a7a3e; font-weight: bold; }
    .status-izin  { color: #8a6400; font-weight: bold; }
    .status-sakit { color: #1a56ad; font-weight: bold; }
    .status-alfa  { color: #b3261e; font-weight: bold; }
</style>

<h2>Rekap Absensi Kelas</h2>
<div class="sub">
    Kelas: <?= esc($kelas['nama_kelas'] ?? '-') ?> &middot;
    Tanggal: <?= esc(date('d M Y', strtotime($tanggal))) ?> &middot;
    Dicetak: <?= esc(date('d M Y, H:i')) ?>
</div>

<table>
    <thead>
        <tr>
            <th style="width:30px;">No</th>
            <th style="width:80px;">NIS</th>
            <th>Nama Siswa</th>
            <th>Mapel</th>
            <th style="width:90px;">Jam</th>
            <th style="width:70px;">Status</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($hasil as $i => $h): ?>
        <?php $statusClass = 'status-' . strtolower($h['status'] === 'Alfa' ? 'alfa' : $h['status']); ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($h['nis']) ?></td>
            <td><?= esc($h['nama']) ?></td>
            <td><?= esc($h['nama_mapel']) ?></td>
            <td><?= esc(substr($h['jam_mulai'], 0, 5)) ?>-<?= esc(substr($h['jam_selesai'], 0, 5)) ?></td>
            <td class="<?= $statusClass ?>"><?= esc($h['status']) ?></td>
            <td><?= esc($h['keterangan'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($hasil)): ?>
        <tr><td colspan="7" style="text-align:center;">Tidak ada data absensi pada tanggal ini.</td></tr>
        <?php endif; ?>
    </tbody>
</table>