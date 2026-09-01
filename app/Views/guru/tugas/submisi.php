<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4>Submisi — <?= esc($tugas['judul']) ?></h4>
        <span style="color:#888; font-size:14px;"><?= esc($tugas['nama_kelas']) ?> / <?= esc($tugas['nama_mapel']) ?> · Tenggat: <?= date('d/m/Y H:i', strtotime($tugas['tenggat'])) ?></span>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert" style="background:#dcfce7;color:#166534;padding:10px 14px;border-radius:8px;margin-top:16px;">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert" style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;margin-top:16px;">
            <?php foreach (session()->getFlashdata('errors') as $e): ?>
                <div><?= esc($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($daftarSubmisi)): ?>
        <div style="text-align:center; padding:24px 0; color:#999;">Belum ada siswa yang mengumpulkan.</div>
    <?php else: ?>
        <?php foreach ($daftarSubmisi as $s): ?>
            <div style="border:1px solid #eee; border-radius:10px; padding:16px; margin-top:14px;">
                <div style="display:flex; justify-content:space-between;">
                    <div>
                        <div style="font-weight:600;"><?= esc($s['nama_siswa']) ?></div>
                        <div style="font-size:12.5px; color:#888;"><?= esc($s['nis']) ?> · Dikumpulkan: <?= date('d/m/Y H:i', strtotime($s['waktu_kumpul'])) ?></div>
                    </div>
                    <span style="align-self:start; background:<?= $s['status'] === 'Dinilai' ? '#dcfce7' : ($s['status'] === 'Terlambat' ? '#fee2e2' : '#fef9c3') ?>; color:<?= $s['status'] === 'Dinilai' ? '#166534' : ($s['status'] === 'Terlambat' ? '#991b1b' : '#854d0e') ?>; padding:2px 10px; border-radius:12px; font-size:12px;">
                        <?= esc($s['status']) ?>
                    </span>
                </div>

                <?php if (!empty($s['isi_jawaban'])): ?>
                    <div style="margin-top:8px; font-size:13.5px;"><?= nl2br(esc($s['isi_jawaban'])) ?></div>
                <?php endif; ?>
                <?php if (!empty($s['file_jawaban'])): ?>
                    <div style="margin-top:6px;">
                        <a href="<?= base_url('tugas/jawaban/' . $s['id_submisi']) ?>">📎 Lihat file jawaban</a>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= base_url('guru/tugas/nilai/' . $s['id_submisi']) ?>" style="margin-top:12px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <?= csrf_field() ?>
                    <input type="number" step="0.01" min="0" max="100" name="nilai" value="<?= $s['nilai'] ?? '' ?>" placeholder="Nilai" required style="width:90px; padding:6px 10px; border:1px solid #ddd; border-radius:8px;">
                    <input type="text" name="catatan_guru" value="<?= esc($s['catatan_guru'] ?? '') ?>" placeholder="Catatan (opsional)" style="flex:1; min-width:180px; padding:6px 10px; border:1px solid #ddd; border-radius:8px;">
                    <button type="submit" class="btn btn-primary" style="padding:6px 16px; font-size:13px;">Simpan Nilai</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($siswaBelumSubmit)): ?>
        <h5 style="margin-top:24px; margin-bottom:10px; color:#991b1b;">Belum Mengumpulkan (<?= count($siswaBelumSubmit) ?>)</h5>
        <div style="display:flex; flex-wrap:wrap; gap:8px;">
            <?php foreach ($siswaBelumSubmit as $s): ?>
                <span style="background:#fee2e2; color:#991b1b; padding:4px 12px; border-radius:8px; font-size:13px;"><?= esc($s['nama']) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:20px;">
        <a href="<?= base_url('guru/tugas') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>