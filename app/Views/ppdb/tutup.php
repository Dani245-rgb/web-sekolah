<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <nav class="breadcrumb" aria-label="breadcrumb">
            <a href="<?= base_url('/') ?>">Beranda</a>
            <span class="sep">/</span>
            <span class="current">PPDB</span>
        </nav>

        <div class="section-heading">
            <h2>Pendaftaran Siswa Baru (PPDB)</h2>
        </div>

        <div class="ppdb-tutup" style="text-align:center; padding: 60px 20px;">
            <p style="font-size:16px; color:#555;">
                <?= esc($setting['pesan_tutup'] ?? 'Pendaftaran belum dibuka.') ?>
            </p>
            <?php if (!empty($setting['tahun_ajaran'])): ?>
                <p style="color:#777; margin-top:8px;">
                    Tahun Ajaran: <?= esc($setting['tahun_ajaran']) ?>
                </p>
            <?php endif; ?>
        </div>

    </div>
</main>

<?= $this->endSection() ?>