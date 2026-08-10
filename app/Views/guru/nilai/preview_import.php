<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/nilai.css') ?>">

<div class="nilai-card">
    <div class="nilai-header">
        <h4>Preview Import Nilai</h4>
    </div>

    <p class="nilai-subinfo">File: <strong><?= esc($namaFile) ?></strong></p>

    <?php if (!empty($error)): ?>
        <div class="nilai-alert nilai-alert-error">
            <?= esc($error) ?>
        </div>
    <?php endif; ?>

    <div style="display:flex; gap:12px; margin-bottom:16px;">
        <div class="nilai-alert nilai-alert-success" style="flex:1; margin:0;">
            <strong><?= $result->totalValid ?></strong> baris valid (baru)
        </div>
        <div class="nilai-alert" style="flex:1; margin:0; background:#fff8e1; color:#8a6d00;">
            <strong><?= $result->totalDitimpa ?></strong> baris akan ditimpa
        </div>
        <div class="nilai-alert" style="flex:1; margin:0; background:#eeeeee; color:#666;">
            <strong><?= $result->totalTidakBerubah ?></strong> baris tidak berubah
        </div>
        <?php if ($result->totalKonflik > 0): ?>
            <div class="nilai-alert" style="flex:1; margin:0; background:#fdecea; color:#c62828; border:1px solid #c62828;">
                <strong><?= $result->totalKonflik ?></strong> baris konflik
            </div>
        <?php endif; ?>
        <div class="nilai-alert nilai-alert-error" style="flex:1; margin:0;">
            <strong><?= $result->totalGagal ?></strong> baris gagal
        </div>
    </div>

    <table class="nilai-siswa-table">
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Nilai Lama</th>
                <th>Nilai Baru</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result->rows as $row): ?>
                <tr>
                    <td><?= $row->nomorBaris ?></td>
                    <td><?= esc($row->dataBaru['nis'] ?? '-') ?></td>
                    <td><?= esc($row->dataBaru['nama'] ?? '-') ?></td>
                    <td><?= in_array($row->status, ['ditimpa', 'konflik']) ? esc($row->dataLama['nilai'] ?? '-') : '-' ?></td>
                    <td><?= esc($row->dataBaru['nilai'] ?? '-') ?></td>
                    <td>
                        <?php if ($row->status === 'valid'): ?>
                            <span style="color:#2e7d32;">Valid</span>
                        <?php elseif ($row->status === 'ditimpa'): ?>
                            <span style="color:#8a6d00;">Akan Ditimpa</span>
                        <?php elseif ($row->status === 'tidak_berubah'): ?>
                            <span style="color:#888;">Tidak Berubah</span>
                        <?php elseif ($row->status === 'konflik'): ?>
                            <span style="color:#c62828; font-weight:bold;">⚠ Konflik</span>
                        <?php else: ?>
                            <span style="color:#c62828;">Gagal</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px; color:#888;">
                        <?php if ($row->status === 'konflik'): ?>
                            Saat kamu download template, nilai = <?= esc($row->nilaiSnapshot ?? 'kosong') ?>.
                            Sekarang di sistem sudah jadi <?= esc($row->dataLama['nilai'] ?? 'kosong') ?>
                            (diubah pihak lain). Nilai Excel kamu (<?= esc($row->dataBaru['nilai']) ?>) akan menimpa itu kalau dilanjutkan.
                        <?php else: ?>
                            <?= esc($row->alasanGagal ?? '') ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
                <?php if (empty($result->rows)): ?>
                <tr>
                    <td colspan="7" class="nilai-empty">Tidak ada baris data.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <form action="<?= base_url('guru/nilai/import/konfirmasi') ?>" method="post" style="margin-top:16px;">
        <?= csrf_field() ?>

        <?php if ($result->totalGagal > 0): ?>
            <label style="display:block; margin-bottom:8px;">
                <input type="checkbox" name="lewati_baris_gagal" value="1" checked>
                Lewati baris gagal, tetap import baris yang valid/ditimpa
            </label>
            <p class="nilai-subinfo">
                Kalau tidak dicentang dan masih ada baris gagal, proses import akan dibatalkan seluruhnya.
            </p>
        <?php endif; ?>

        <?php if ($result->totalKonflik > 0): ?>
            <label style="display:block; margin-bottom:8px;">
                <input type="checkbox" name="timpa_konflik" value="1" id="timpa_konflik">
                Tetap timpa <?= $result->totalKonflik ?> baris konflik (data yang diubah pihak lain akan hilang, diganti isi Excel ini)
            </label>
            <p class="nilai-subinfo">
                Kalau tidak dicentang dan masih ada baris konflik, proses import akan dibatalkan seluruhnya.
                Disarankan download ulang template terbaru dulu sebelum melanjutkan.
            </p>
        <?php endif; ?>

        <div class="nilai-actions">
            <button type="submit" class="nilai-btn nilai-btn-primary"
                <?= ($result->totalValid + $result->totalDitimpa + $result->totalKonflik === 0) ? 'disabled' : '' ?>>
                Konfirmasi Import
            </button>
            <a href="<?= base_url('guru/nilai/form/' . $idJadwal) ?>" class="nilai-btn nilai-btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>