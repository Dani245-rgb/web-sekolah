<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('content') ?>

<h4 style="margin-bottom:16px;">Laporan Akademik</h4>

<div style="margin-bottom:16px;display:flex;gap:8px;">
    <a href="<?= base_url('admin/laporan-akademik?tab=ringkasan') ?>" class="btn btn-sm <?= $tab === 'ringkasan' ? 'btn-primary' : 'btn-secondary' ?>">Ringkasan Per Kelas</a>
    <a href="<?= base_url('admin/laporan-akademik?tab=siswa') ?>" class="btn btn-sm <?= $tab === 'siswa' ? 'btn-primary' : 'btn-secondary' ?>">Detail Per Siswa</a>
</div>

<?php if ($tab === 'ringkasan'): ?>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Kelas</th>
                    <th>Wali Kelas</th>
                    <th>Jumlah Siswa</th>
                    <th>Rata-rata Nilai</th>
                    <th>Persentase Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftarKelas as $k): ?>
                <tr>
                    <td><?= esc($k['nama_kelas']) ?></td>
                    <td><?= esc($k['nama_wali'] ?? '-') ?></td>
                    <td><?= $k['jumlah_siswa'] ?></td>
                    <td><?= $k['rata_nilai'] !== null ? $k['rata_nilai'] : '-' ?></td>
                    <td><?= $k['persen_hadir'] !== null ? $k['persen_hadir'] . '%' : '-' ?></td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($daftarKelas)): ?>
                <tr><td colspan="5" style="color:#7c8a9c;">Belum ada data kelas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>

    <div class="card" style="margin-bottom:16px;">
        <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <input type="hidden" name="tab" value="siswa">
            <div class="form-group" style="margin-bottom:0;min-width:240px;">
                <label>Siswa</label>
                <select name="id_siswa" onchange="this.form.submit()">
                    <option value="">-- Pilih Siswa --</option>
                    <?php foreach ($daftarSiswa as $s): ?>
                        <option value="<?= $s['id_siswa'] ?>" <?= (string) $idSiswaAktif === (string) $s['id_siswa'] ? 'selected' : '' ?>>
                            <?= esc($s['nama']) ?> (<?= esc($s['nis']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>Dari</label>
                <input type="date" name="tgl_mulai" value="<?= esc($tglMulai) ?>">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>Sampai</label>
                <input type="date" name="tgl_selesai" value="<?= esc($tglSelesai) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Terapkan</button>
        </form>
    </div>

    <?php if ($detail): ?>
        <div class="card" style="margin-bottom:16px;">
            <h5><?= esc($detail['siswa']['nama']) ?></h5>
            <p style="color:#7c8a9c;font-size:13px;margin-top:4px;">NIS: <?= esc($detail['siswa']['nis']) ?> | NISN: <?= esc($detail['siswa']['nisn']) ?></p>
        </div>

        <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <div class="card" style="flex:1;min-width:280px;">
                <strong>Rata-rata Nilai per Mapel</strong>
                <table class="table" style="margin-top:10px;">
                    <thead><tr><th>Mapel</th><th>Rata-rata</th></tr></thead>
                    <tbody>
                        <?php foreach ($detail['nilai_per_mapel'] as $n): ?>
                        <tr>
                            <td><?= esc($n['nama_mapel']) ?></td>
                            <td><?= round((float) $n['rata'], 1) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($detail['nilai_per_mapel'])): ?>
                        <tr><td colspan="2" style="color:#7c8a9c;">Belum ada nilai.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card" style="flex:1;min-width:280px;">
                <strong>Rekap Kehadiran (<?= esc($tglMulai) ?> s/d <?= esc($tglSelesai) ?>)</strong>
                <table class="table" style="margin-top:10px;">
                    <tbody>
                        <?php foreach ($detail['rekap_absensi'] as $status => $jumlah): ?>
                        <tr><td><?= esc($status) ?></td><td><?= $jumlah ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <p style="color:#7c8a9c;">Pilih siswa untuk melihat detail.</p>
    <?php endif; ?>

<?php endif; ?>

<?= $this->endSection() ?>