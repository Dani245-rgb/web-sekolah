<style>
    body { font-family: sans-serif; font-size: 12px; }
    h2 { margin-bottom: 2px; }
    .sub { color: #666; margin-bottom: 14px; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
    th { background: #eef1ff; }
    .tuntas { color: #1a7a3e; font-weight: bold; }
    .belum { color: #b3261e; font-weight: bold; }
</style>

<h2>Rekap Nilai Akhir</h2>
<div class="sub">
    Kelas: <?= esc($jadwal['nama_kelas'] ?? '-') ?> &middot;
    Mapel: <?= esc($jadwal['nama_mapel'] ?? '-') ?> &middot;
    KKM: <?= esc($pengaturan['kkm']) ?> &middot;
    Dicetak: <?= esc(date('d M Y, H:i')) ?>
</div>

<table>
    <thead>
        <tr><th style="width:40px;">No</th><th>Nama Siswa</th><th style="width:100px;">Nilai Akhir</th><th style="width:100px;">Status</th></tr>
    </thead>
    <tbody>
        <?php foreach ($rekap as $i => $r): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($r['nama_siswa']) ?></td>
            <td><?= esc($r['nilai_akhir']) ?></td>
            <td class="<?= $r['status'] === 'Tuntas' ? 'tuntas' : 'belum' ?>"><?= esc($r['status']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($rekap)): ?>
        <tr><td colspan="4" style="text-align:center;">Tidak ada data.</td></tr>
        <?php endif; ?>
    </tbody>
</table>