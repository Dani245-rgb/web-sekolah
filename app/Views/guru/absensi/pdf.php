<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        h3 { text-align: center; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px; }
        p.subinfo { text-align: center; margin-top: 0; margin-bottom: 4px; color: #333; font-size: 11px; }
        .info-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
            font-size: 10.5px;
            border-bottom: 1.5px solid #000;
            padding-bottom: 8px;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            border: 1.2px solid #000;
            padding: 5px 4px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background: #e5e7eb;
            font-weight: bold;
            font-size: 10px;
        }
        td.nama { text-align: left; padding-left: 8px; font-weight: 500; }
        td.no { width: 26px; }
        .cell-pertemuan {
            width: 34px;
            font-size: 13px;
            font-weight: bold;
        }
        .status-hadir { background: #dcfce7; color: #166534; }
        .status-izin, .status-sakit { background: #fef9c3; color: #854d0e; }
        .status-alfa { background: #fee2e2; color: #991b1b; }
        .status-kosong { background: #f9fafb; color: #cbd5e1; }
        .rekap-col { width: 28px; font-weight: bold; background: #f8fafc; }
        .footer-tanda-tangan {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
            font-size: 10.5px;
        }
        .footer-tanda-tangan .kotak-ttd {
            text-align: center;
            width: 200px;
        }
        .footer-tanda-tangan .garis-ttd {
            margin-top: 50px;
            border-top: 1px solid #000;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <h3>Rekap Absensi Bulanan</h3>
    <p class="subinfo">
        <?= esc($jadwal['nama_kelas']) ?> / <?= esc($jadwal['nama_mapel']) ?> —
        <?= esc($namaBulanTeks) ?> <?= esc($tahun) ?>
    </p>

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="no">No</th>
                <th rowspan="2">Nama Siswa</th>
                <?php foreach ($kelompokMinggu as $mingguKe => $tanggalDalamMinggu): ?>
                    <th colspan="<?= count($tanggalDalamMinggu) ?>">Minggu <?= $mingguKe ?></th>
                <?php endforeach; ?>
                <th colspan="4">Rekap</th>
            </tr>
            <tr>
                <?php foreach ($kelompokMinggu as $tanggalDalamMinggu): ?>
                    <?php foreach ($tanggalDalamMinggu as $tgl): ?>
                        <th class="cell-pertemuan" style="font-size:10px;"><?= date('d/m', strtotime($tgl)) ?></th>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <th class="rekap-col">H</th>
                <th class="rekap-col">I</th>
                <th class="rekap-col">S</th>
                <th class="rekap-col">A</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($siswaList as $s): ?>
                <tr>
                    <td class="no"><?= $no++ ?></td>
                    <td class="nama"><?= esc($s['nama']) ?></td>
                    <?php foreach ($kelompokMinggu as $tanggalDalamMinggu): ?>
                        <?php foreach ($tanggalDalamMinggu as $tgl): ?>
                            <?php $status = $matrix[$s['id_siswa']][$tgl] ?? null; ?>
                            <td class="cell-pertemuan <?= $status ? 'status-' . strtolower($status) : 'status-kosong' ?>">
                                <?php if ($status === 'Hadir'): ?>&#10003;
                                <?php elseif ($status === 'Alfa'): ?>&#10007;
                                <?php elseif ($status): ?><?= esc(substr($status, 0, 1)) ?>
                                <?php else: ?>&nbsp;
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    <td class="rekap-col"><?= esc($rekapBulanIni[$s['id_siswa']]['Hadir']) ?></td>
                    <td class="rekap-col"><?= esc($rekapBulanIni[$s['id_siswa']]['Izin']) ?></td>
                    <td class="rekap-col"><?= esc($rekapBulanIni[$s['id_siswa']]['Sakit']) ?></td>
                    <td class="rekap-col"><?= esc($rekapBulanIni[$s['id_siswa']]['Alfa']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($siswaList)): ?>
                <tr><td colspan="<?= 6 + count($tanggalMingguan) ?>">Tidak ada siswa di kelas ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer-tanda-tangan">
        <div class="kotak-ttd">
            <div>Mengetahui,</div>
            <div class="garis-ttd">Guru Mapel</div>
        </div>
    </div>
</body>
</html>