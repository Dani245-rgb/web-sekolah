<div class="peta-absensi-wrapper">
    <div class="absensi-header" style="margin-top:8px;">
        <h5 style="margin:0;">Peta Absensi — Bulan Ini</h5>
    </div>

    <form method="get" class="peta-filter" style="margin-top:8px;">
        <input type="hidden" name="tanggal" value="<?= esc($tanggal) ?>">
        <select name="bulan">
            <?php $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']; ?>
            <?php for ($b = 1; $b <= 12; $b++): ?>
                <option value="<?= $b ?>" <?= $b == $bulanPeta ? 'selected' : '' ?>><?= $namaBulan[$b] ?></option>
            <?php endfor; ?>
        </select>
        <select name="tahun">
            <?php for ($t = date('Y') - 1; $t <= date('Y') + 1; $t++): ?>
                <option value="<?= $t ?>" <?= $t == $tahunPeta ? 'selected' : '' ?>><?= $t ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Tampilkan</button>
        <a href="<?= base_url('guru/absensi/peta/' . $jadwal['id_jadwal'] . '/export/pdf?bulan=' . $bulanPeta . '&tahun=' . $tahunPeta) ?>" class="btn btn-outline-danger" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
    </form>

    <?php if (empty($tanggalMingguan)): ?>
        <div class="empty-state" style="margin-top:16px;">
            <p>Tidak ada jadwal <?= esc($jadwal['nama_mapel']) ?> (hari <?= esc($jadwal['hari']) ?>) pada bulan ini.</p>
        </div>
    <?php else: ?>

        <?php
        $rekapBulanIni = [];
        foreach ($siswaList as $s) {
            $rekapBulanIni[$s['id_siswa']] = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alfa' => 0];
            foreach ($tanggalMingguan as $tgl) {
                if (isset($mapHariKhusus[$tgl])) continue; // skip hari khusus dari rekap
                $status = $matrix[$s['id_siswa']][$tgl] ?? null;
                if ($status && isset($rekapBulanIni[$s['id_siswa']][$status])) {
                    $rekapBulanIni[$s['id_siswa']][$status]++;
                }
            }
        }
        ?>

        <table class="absensi-table peta-table" style="margin-top:16px;">
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Nama Siswa</th>
                    <th colspan="<?= count($tanggalMingguan) ?>" style="text-align:center;">Minggu Ke</th>
                    <th colspan="4" style="text-align:center;">Rekap Bulan Ini</th>
                </tr>
                <tr>
                    <?php foreach ($tanggalMingguan as $i => $tgl): ?>
                        <th style="text-align:center;" title="<?= date('d M Y', strtotime($tgl)) ?>"><?= $i + 1 ?></th>
                    <?php endforeach; ?>
                    <th>H</th>
                    <th>I</th>
                    <th>S</th>
                    <th>A</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($siswaList as $s): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td style="text-align:left;"><?= esc($s['nama']) ?></td>
                        <?php foreach ($tanggalMingguan as $tgl): ?>
                            <?php $keteranganKhusus = $mapHariKhusus[$tgl] ?? null; ?>
                            <?php $status = $matrix[$s['id_siswa']][$tgl] ?? null; ?>
                            <?php if ($keteranganKhusus): ?>
                                <td class="peta-cell peta-cell-khusus" title="<?= esc($keteranganKhusus) . ' - ' . date('d M Y', strtotime($tgl)) ?>">•</td>
                            <?php else: ?>
                                <td class="peta-cell peta-cell-<?= $status ? strtolower($status) : 'kosong' ?>"
                                    title="<?= $status ? esc($status) . ' - ' . date('d M Y', strtotime($tgl)) : 'Belum diabsen' ?>">
                                    <?php if ($status === 'Hadir'): ?>&#10003;
                                    <?php elseif ($status === 'Alfa'): ?>&#10007;
                                    <?php elseif ($status): ?><?= substr($status, 0, 1) ?>
                                    <?php else: ?>-
                                <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <td><?= $rekapBulanIni[$s['id_siswa']]['Hadir'] ?></td>
                        <td><?= $rekapBulanIni[$s['id_siswa']]['Izin'] ?></td>
                        <td><?= $rekapBulanIni[$s['id_siswa']]['Sakit'] ?></td>
                        <td><?= $rekapBulanIni[$s['id_siswa']]['Alfa'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($siswaList)): ?>
                    <tr>
                        <td colspan="<?= 6 + count($tanggalMingguan) ?>" class="absensi-empty">Tidak ada siswa di kelas ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="peta-absensi-legend" style="margin-top:14px;">
            <span><i class="legend-dot legend-hijau"></i> Hadir</span>
            <span><i class="legend-dot legend-kuning"></i> Izin/Sakit</span>
            <span><i class="legend-dot legend-merah"></i> Alfa</span>
            <span><i class="legend-dot" style="background:#e5e7eb;"></i> Belum diabsen</span>
            <span><i class="legend-dot" style="background:#3b82f6;"></i> Hari Khusus (Libur/Rapat/dll)</span>
        </div>

    <?php endif; ?>
</div>

<style>
    .peta-absensi-wrapper {
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .peta-filter {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .peta-filter select {
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
    }

    .peta-table td,
    .peta-table th {
        padding: 6px 8px;
        text-align: center;
        font-size: 13px;
    }

    .peta-cell {
        font-weight: 600;
        border-radius: 4px;
    }

    .peta-cell-hadir {
        background: #dcfce7;
        color: #166534;
    }

    .peta-cell-izin {
        background: #fef9c3;
        color: #854d0e;
    }

    .peta-cell-sakit {
        background: #fef9c3;
        color: #854d0e;
    }

    .peta-cell-alfa {
        background: #fee2e2;
        color: #991b1b;
    }

    .peta-cell-kosong {
        background: #f1f5f9;
        color: #94a3b8;
    }

    .peta-cell-khusus {
        background: #dbeafe;
        color: #1e40af;
    }

    .peta-absensi-legend {
        display: flex;
        gap: 18px;
        font-size: 12.5px;
        color: #475569;
    }

    .legend-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
        vertical-align: middle;
    }

    .legend-hijau {
        background: #22c55e;
    }

    .legend-kuning {
        background: #eab308;
    }

    .legend-merah {
        background: #ef4444;
    }
</style>