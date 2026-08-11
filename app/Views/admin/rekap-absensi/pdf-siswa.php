<style>
    body { font-family: sans-serif; font-size: 12px; }
    h2 { margin-bottom: 2px; }
    .sub { color: #666; margin-bottom: 14px; font-size: 11px; }
    .rekap-box { display: flex; gap: 10px; margin-bottom: 16px; }
    .rekap-item { border: 1px solid #ccc; border-radius: 6px; padding: 8px 14px; text-align: center; }
    .rekap-item .angka { font-size: 18px; font-weight: bold; }
    .rekap-item .label { font-size: 10px; color: #666; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
    th { background: #eef1ff; }
    .status-hadir { color: #1a7a3e; font-weight: bold; }
    .status-izin  { color: #8a6400; font-weight: bold; }
    .status-sakit { color: #1a56ad; font-weight: bold; }
    .status-alfa  { color: #b3261e; font-weight: bold; }
</style>

<h2>Riwayat Absensi Siswa</h2>
<div class="sub">
    Nama: <?= esc($siswa['nama'] ?? '-') ?> &middot;
    NIS: <?= esc($siswa['nis'] ?? '-') ?> &middot;
    Periode: <?= esc(date('d M Y', strtotime($tglMulai))) ?> s/d <?= esc(date('d M Y', strtotime($tglSelesai))) ?> &middot;
    Dicetak: <?= esc(date('d M Y, H:i')) ?>
</div>

<div class="rekap-box">
    <div class="rekap-item"><div class="angka"><?= esc($rekap['Hadir']) ?></div><div class="label">HADIR</div></div>
    <div class="rekap-item"><div class="angka"><?= esc($rekap['Izin']) ?></div><div class="label">IZIN</div></div>
    <div class="rekap-item"><div class="angka"><?= esc($rekap['Sakit']) ?></div><div class="label">SAKIT</div></div>
    <div class="rekap-item"><div class="angka"><?= esc($rekap['Alfa']) ?></div><div class="label">ALFA</div></div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:30px;">No</th>
            <th style="width:100px;">Tanggal</th>
            <th>Mapel</th>
            <th style="width:70px;">Status</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($detail as $i => $d): ?>
        <?php $statusClass = 'status-' . strtolower($d['status'] === 'Alfa' ? 'alfa' : $d['status']); ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc(date('d M Y', strtotime($d['tanggal']))) ?></td>
            <td><?= esc($d['nama_mapel']) ?></td>
            <td class="<?= $statusClass ?>"><?= esc($d['status']) ?></td>
            <td><?= esc($d['keterangan'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($detail)): ?>
        <tr><td colspan="5" style="text-align:center;">Tidak ada data pada periode ini.</td></tr>
        <?php endif; ?>
    </tbody>
</table>