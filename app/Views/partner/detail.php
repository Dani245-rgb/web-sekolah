<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">
        <div class="berita-detail">
            <img src="<?= base_url('uploads/partner/' . $partner['foto']) ?>" alt="<?= esc($partner['nama']) ?>"
                 style="max-width:100%;border-radius:10px;margin-bottom:20px;">
            <h1><?= esc($partner['nama']) ?></h1>
            <div class="berita-detail-body" style="margin-top: 16px; line-height: 1.7;">
                <?= nl2br(esc($partner['deskripsi'])) ?>
            </div>

            <a href="<?= base_url('partner') ?>" class="lihat-semua" style="margin-top: 24px; display: inline-block;">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Industri Mitra
            </a>
        </div>
    </div>
</main>

<?= $this->endSection() ?>