<?= $this->extend('layouts/template') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/unduhan-publik.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Pusat Unduhan</h2>
        </div>

        <?php if (!empty($kategoriList)): ?>
        <div class="unduhan-filter">
            <a href="<?= base_url('unduhan') ?>" class="<?= !$kategoriFilter ? 'active' : '' ?>">Semua</a>
            <?php foreach ($kategoriList as $k): ?>
            <a href="<?= base_url('unduhan?kategori=' . urlencode($k['kategori'])) ?>"
               class="<?= $kategoriFilter === $k['kategori'] ? 'active' : '' ?>"><?= esc($k['kategori']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php
        function ikonFile(string $ekstensi): string
        {
            $ekstensi = strtolower($ekstensi);
            if (in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp', 'svg'], true)) return 'bi-file-image';
            if ($ekstensi === 'pdf') return 'bi-file-pdf';
            if (in_array($ekstensi, ['doc', 'docx'], true)) return 'bi-file-word';
            if (in_array($ekstensi, ['ppt', 'pptx'], true)) return 'bi-file-ppt';
            if (in_array($ekstensi, ['xls', 'xlsx'], true)) return 'bi-file-excel';
            if ($ekstensi === 'zip') return 'bi-file-zip';
            return 'bi-file-earmark';
        }
        ?>

        <?php if (!empty($unduhanList)): ?>
        <div class="unduhan-list">
            <?php foreach ($unduhanList as $u): ?>
            <div class="unduhan-item">
                <div class="unduhan-item-icon">
                    <i class="bi <?= ikonFile($u['ekstensi']) ?>"></i>
                </div>
                <div class="unduhan-item-body">
                    <h4><?= esc($u['judul']) ?></h4>
                    <?php if (!empty($u['deskripsi'])): ?>
                    <p><?= esc($u['deskripsi']) ?></p>
                    <?php endif; ?>
                    <span class="unduhan-item-meta">
                        <?= esc(strtoupper($u['ekstensi'])) ?>
                        <?= $u['ukuran_file'] ? ' · ' . round($u['ukuran_file'] / 1024, 1) . ' KB' : '' ?>
                        · <?= (int) $u['jumlah_unduh'] ?>x diunduh
                    </span>
                </div>
                <a href="<?= base_url('unduhan/download/' . $u['id_unduhan']) ?>" class="unduhan-item-btn">
                    <i class="bi bi-download"></i> Unduh
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="text-align:center;color:#64748b;padding:60px 0;">Belum ada file untuk diunduh.</p>
        <?php endif; ?>

    </div>
</main>

<?= $this->endSection() ?>