<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="berita-detail">
            <h1><?= esc($pengumuman['judul']) ?></h1>
            <span class="berita-meta"><?= date('d F Y', strtotime($pengumuman['tanggal_publish'])) ?></span>

            <div class="berita-detail-body" style="margin-top: 20px; line-height: 1.7;">
                <?= nl2br(esc($pengumuman['isi'])) ?>
            </div>

            <a href="<?= base_url('pengumuman') ?>" class="lihat-semua" style="margin-top: 24px; display: inline-block;">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Pengumuman
            </a>
        </div>

    </div>
</main>

<?= $this->endSection() ?>