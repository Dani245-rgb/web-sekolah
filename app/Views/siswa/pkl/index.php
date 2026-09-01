<?= $this->extend('layouts/siswa/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4>PKL Saya</h4>
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

    <?php if (!$penempatanAktif): ?>
        <div style="text-align:center; padding:40px 0; color:#999;">
            Anda belum memiliki penempatan PKL yang aktif. Hubungi Admin/Guru pembimbing.
        </div>
    <?php else: ?>

        <!-- Info Penempatan -->
        <div style="border:1px solid #eee; border-radius:10px; padding:16px; margin-top:16px;">
            <div style="font-weight:700; font-size:16px;"><?= esc($penempatanAktif['nama_perusahaan']) ?></div>
            <div style="color:#888; font-size:13.5px; margin-top:4px;"><?= esc($penempatanAktif['alamat_perusahaan'] ?: '-') ?></div>

            <div style="display:flex; gap:24px; margin-top:12px; flex-wrap:wrap;">
                <div>
                    <div style="font-size:12px; color:#888;">Pembimbing Industri</div>
                    <div style="font-weight:600;"><?= esc($penempatanAktif['nama_pembimbing_industri'] ?: '-') ?></div>
                    <div style="font-size:12.5px; color:#888;"><?= esc($penempatanAktif['no_hp_pembimbing_industri'] ?: '') ?></div>
                </div>
                <div>
                    <div style="font-size:12px; color:#888;">Guru Pembimbing Sekolah</div>
                    <div style="font-weight:600;"><?= esc($penempatanAktif['nama_guru_pembimbing'] ?: '-') ?></div>
                </div>
                <div>
                    <div style="font-size:12px; color:#888;">Periode</div>
                    <div style="font-weight:600;">
                        <?= date('d/m/Y', strtotime($penempatanAktif['tanggal_mulai'])) ?> -
                        <?= $penempatanAktif['tanggal_selesai'] ? date('d/m/Y', strtotime($penempatanAktif['tanggal_selesai'])) : 'sekarang' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form isi jurnal -->
        <div style="margin-top:20px;">
            <h5 style="margin-bottom:10px;">Isi Jurnal Hari Ini</h5>
            <form method="post" action="<?= base_url('siswa/pkl/jurnal/simpan') ?>">
                <?= csrf_field() ?>
                <div style="display:flex; gap:14px; margin-bottom:12px;">
                    <div style="flex:1;">
                        <label style="display:block; font-weight:600; margin-bottom:6px; font-size:13.5px;">Tanggal</label>
                        <input type="date" name="tanggal" value="<?= old('tanggal', date('Y-m-d')) ?>" max="<?= date('Y-m-d') ?>" required class="absensi-keterangan" style="width:100%;">
                    </div>
                </div>
                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px; font-size:13.5px;">Kegiatan</label>
                    <textarea name="kegiatan" rows="3" required class="absensi-keterangan" style="width:100%;" placeholder="Ceritakan kegiatan yang dilakukan hari ini..."><?= old('kegiatan') ?></textarea>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px; font-size:13.5px;">Kendala (opsional)</label>
                    <textarea name="kendala" rows="2" class="absensi-keterangan" style="width:100%;" placeholder="Kalau ada kendala, tulis di sini..."><?= old('kendala') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Jurnal</button>
            </form>
        </div>

        <!-- Riwayat jurnal -->
        <div style="margin-top:24px;">
            <h5 style="margin-bottom:10px;">Riwayat Jurnal</h5>
            <?php if (empty($daftarJurnal)): ?>
                <div style="text-align:center; padding:24px 0; color:#999;">Belum ada jurnal yang diisi.</div>
            <?php else: ?>
                <?php foreach ($daftarJurnal as $j): ?>
                    <?php
                        $warna = match ($j['status_validasi']) {
                            'Disetujui' => ['#dcfce7', '#166534'],
                            'Ditolak'   => ['#fee2e2', '#991b1b'],
                            default     => ['#fef9c3', '#854d0e'],
                        };
                    ?>
                    <div style="border:1px solid #eee; border-radius:10px; padding:14px 16px; margin-bottom:10px;">
                        <div style="display:flex; justify-content:space-between; align-items:start;">
                            <div style="font-weight:600;"><?= date('d F Y', strtotime($j['tanggal'])) ?></div>
                            <span style="background:<?= $warna[0] ?>; color:<?= $warna[1] ?>; padding:2px 10px; border-radius:12px; font-size:12px;">
                                <?= esc($j['status_validasi']) ?>
                            </span>
                        </div>
                        <div style="margin-top:6px; font-size:13.5px;"><?= nl2br(esc($j['kegiatan'])) ?></div>
                        <?php if (!empty($j['kendala'])): ?>
                            <div style="margin-top:4px; font-size:13px; color:#888;">Kendala: <?= nl2br(esc($j['kendala'])) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($j['catatan_pembimbing'])): ?>
                            <div style="margin-top:6px; font-size:12.5px; color:#4a6cf7;">Catatan pembimbing: <?= esc($j['catatan_pembimbing']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:20px;">
        <a href="<?= base_url('siswa/dashboard') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>