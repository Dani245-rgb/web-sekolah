<?= $this->extend('layouts/siswa/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <h4><?= esc($tugas['judul']) ?></h4>
    <span style="color:#888; font-size:14px;"><?= esc($tugas['nama_mapel']) ?> · Tenggat: <?= date('d/m/Y H:i', strtotime($tugas['tenggat'])) ?></span>

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

    <div style="margin-top:18px; padding:14px; background:#f8fafc; border-radius:10px; font-size:14px;">
        <?= nl2br(esc($tugas['deskripsi'])) ?>
    </div>

    <?php if (!empty($tugas['file_lampiran'])): ?>
        <div style="margin-top:10px;">
            <a href="<?= base_url('tugas/lampiran/' . $tugas['id_tugas']) ?>">📎 Lihat file soal</a>
        </div>
    <?php endif; ?>

    <hr style="margin:24px 0; border-color:#eee;">

    <?php if ($submisi && $submisi['status'] === 'Dinilai'): ?>
        <h5>Jawaban Anda (Sudah Dinilai)</h5>
        <div style="margin-top:8px; font-size:14px;"><?= nl2br(esc($submisi['isi_jawaban'])) ?></div>
        <?php if (!empty($submisi['file_jawaban'])): ?>
            <div style="margin-top:8px;">
                <a href="<?= base_url('tugas/jawaban/' . $submisi['id_submisi']) ?>">📎 File jawaban Anda</a>
            </div>
        <?php endif; ?>
        <div style="margin-top:14px; padding:14px; background:#dcfce7; border-radius:10px;">
            <div style="font-weight:600; color:#166534;">Nilai: <?= esc($submisi['nilai']) ?></div>
            <?php if (!empty($submisi['catatan_guru'])): ?>
                <div style="margin-top:6px; font-size:13.5px; color:#166534;">Catatan guru: <?= esc($submisi['catatan_guru']) ?></div>
            <?php endif; ?>
        </div>

    <?php elseif ($tugas['status'] !== 'Aktif'): ?>
        <div style="text-align:center; padding:20px 0; color:#991b1b;">Tugas ini sudah ditutup oleh guru.</div>

    <?php else: ?>
        <h5><?= $submisi ? 'Perbarui Jawaban' : 'Kumpulkan Jawaban' ?></h5>
        <?php if ($sudahLewatTenggat): ?>
            <div style="color:#991b1b; font-size:13px; margin-bottom:10px;">⚠️ Sudah lewat tenggat, submisi akan ditandai Terlambat.</div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('siswa/tugas/kumpulkan/' . $tugas['id_tugas']) ?>" enctype="multipart/form-data" style="margin-top:10px;">
            <?= csrf_field() ?>
            <div style="margin-bottom:14px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">Jawaban (teks)</label>
                <textarea name="isi_jawaban" rows="5" class="absensi-keterangan" style="width:100%;"><?= old('isi_jawaban', $submisi['isi_jawaban'] ?? '') ?></textarea>
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">File Jawaban (opsional)</label>
                <?php if ($submisi && !empty($submisi['file_jawaban'])): ?>
                    <div style="margin-bottom:6px; font-size:13px;">
                        File saat ini: <a href="<?= base_url('tugas/jawaban/' . $submisi['id_submisi']) ?>"><?= esc($submisi['file_jawaban']) ?></a>
                    </div>
                <?php endif; ?>
                <input type="file" name="file_jawaban">
                <small style="color:#888;">Upload file baru akan menggantikan file lama.</small>
            </div>
            <button type="submit" class="btn btn-primary"><?= $submisi ? 'Perbarui Jawaban' : 'Kumpulkan' ?></button>
        </form>
    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:20px;">
        <a href="<?= base_url('siswa/tugas') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>