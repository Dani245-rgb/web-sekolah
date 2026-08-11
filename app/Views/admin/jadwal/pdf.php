<style>
    body { font-family: sans-serif; font-size: 11px; }
    h2 { margin-bottom: 2px; }
    .sub { color: #666; margin-bottom: 14px; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 5px 7px; text-align: left; }
    th { background: #eef1ff; }
</style>

<h2>Jadwal Pelajaran</h2>
<div class="sub">
    Semester: <?= esc($semesterAktif['nama_semester'] ?? '-') ?> &middot;
    Dicetak: <?= esc(date('d M Y, H:i')) ?>
</div>

<table>
    <thead>
        <tr>
            <th>Hari</th><th>Jam</th><th>Kelas</th><th>Jurusan</th>
            <th>Mapel</th><th>Guru</th><th>Ruangan</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($jadwal as $j): ?>
        <tr>
            <td><?= esc($j['hari']) ?></td>
            <td><?= esc(substr($j['jam_mulai'], 0, 5)) ?> - <?= esc(substr($j['jam_selesai'], 0, 5)) ?></td>
            <td><?= esc($j['nama_kelas']) ?></td>
            <td><?= esc($j['nama_jurusan'] ?? '-') ?></td>
            <td><?= esc($j['nama_mapel']) ?></td>
            <td><?= esc($j['nama_guru']) ?></td>
            <td><?= esc($j['nama_ruangan']) ?></td>
            <td><?= esc($j['status']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($jadwal)): ?>
        <tr><td colspan="8" style="text-align:center;">Tidak ada data.</td></tr>
        <?php endif; ?>
    </tbody>
</table>


