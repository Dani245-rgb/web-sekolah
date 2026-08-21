<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <?php if ($item): ?>
        <div class="section-heading">
            <h2><?= esc($item['nama_jurusan']) ?></h2>
        </div>

        <div style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;">
            <?php if (!empty($item['foto'])): ?>
            <img src="<?= base_url('uploads/jurusan/' . $item['foto']) ?>" alt="<?= esc($item['nama_jurusan']) ?>"
                 style="width:280px;object-fit:cover;border-radius:12px;">
            <?php endif; ?>
            <div style="flex:1;min-width:250px;">
                <?php if (!empty($item['deskripsi'])): ?>
                <div style="line-height:1.8;margin-bottom:16px;"><?= nl2br(esc($item['deskripsi'])) ?></div>
                <?php endif; ?>

                <?php if (!empty($item['kompetensi'])): ?>
                <h3 style="margin:16px 0 4px;">Kompetensi</h3>
                <div style="line-height:1.8;"><?= nl2br(esc($item['kompetensi'])) ?></div>
                <?php endif; ?>

                <?php if (!empty($item['prospek_kerja'])): ?>
                <h3 style="margin:16px 0 4px;">Prospek Kerja</h3>
                <div style="line-height:1.8;"><?= nl2br(esc($item['prospek_kerja'])) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <p>Data jurusan tidak ditemukan.</p>
        <?php endif; ?>

        <p style="margin-top:24px;">
            <a href="<?= base_url('akademik/jurusan') ?>"><i class="bi bi-arrow-left"></i> Kembali ke daftar jurusan</a>
        </p>

    </div>
</main>

<?= $this->endSection() ?>