<?php
/**
 * Partial: section Import Excel per komponen (untuk semua komponen di 1 jadwal).
 * Include di app/Views/guru/nilai/input.php, kirim variable:
 *   - $komponenList (array, sudah ada di input.php)
 *   - $riwayatImportPerKomponen (array, key = id_komponen, value = array riwayat import)
 */
?>

<div class="nilai-card" style="margin-top: 24px;">
    <div class="nilai-header">
        <h4>Import Nilai dari Excel</h4>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="nilai-alert nilai-alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="nilai-alert nilai-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php foreach ($komponenList as $k): ?>
        <details style="margin-bottom: 12px; border: 1px solid #e0e0e0; border-radius: 6px; padding: 10px 14px;">
            <summary style="cursor: pointer; font-weight: 600;">
                <?= esc($k['nama_komponen']) ?> (<?= esc($k['bobot']) ?>%)
            </summary>

            <div style="margin-top: 12px;">
                <p class="nilai-subinfo">
                    1. Download template dulu, isi kolom <strong>Nilai</strong>, lalu upload kembali di sini.
                </p>

                <a href="<?= base_url('guru/nilai/import/template/' . $k['id_komponen']) ?>" class="nilai-btn nilai-btn-secondary">
                    Download Template
                </a>

                <form action="<?= base_url('guru/nilai/import/preview') ?>" method="post" enctype="multipart/form-data" style="margin-top: 10px;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_komponen" value="<?= esc($k['id_komponen']) ?>">
                    <input type="file" name="file_excel" accept=".xlsx,.xls" required>
                    <button type="submit" class="nilai-btn nilai-btn-primary">Lanjut ke Preview</button>
                </form>

                <?php $riwayat = $riwayatImportPerKomponen[$k['id_komponen']] ?? []; ?>
                <?php if (!empty($riwayat)): ?>
                    <div style="margin-top: 14px;">
                        <p class="nilai-subinfo" style="margin-bottom: 6px;"><strong>Riwayat Import</strong></p>
                        <?php foreach ($riwayat as $log): ?>
                            <div style="font-size: 13px; padding: 8px 0; border-top: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between;">
                                    <span><?= esc($log['nama_file']) ?></span>
                                    <?php if ($log['status'] === 'di-rollback'): ?>
                                        <span style="color:#888;">Di-rollback</span>
                                    <?php else: ?>
                                        <span style="color:#2e7d32;">Selesai</span>
                                    <?php endif; ?>
                                </div>
                                <div style="color:#888;">
                                    <?= date('d M Y H:i', strtotime($log['created_at'])) ?>
                                    — <?= $log['total_sukses'] ?> valid, <?= $log['total_ditimpa'] ?> ditimpa, <?= $log['total_gagal'] ?> gagal
                                </div>

                                <?php if ($log['status'] !== 'di-rollback'): ?>
                                    <form action="<?= base_url('guru/nilai/import/rollback/' . $log['id_import_log']) ?>" method="post"
                                          onsubmit="return confirm('Yakin batalkan import ini? Nilai akan dikembalikan ke kondisi sebelum import.');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="nilai-btn nilai-btn-secondary" style="color:#c62828; padding:2px 8px; font-size:12px;">
                                            Rollback import ini
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </details>
    <?php endforeach; ?>
</div>