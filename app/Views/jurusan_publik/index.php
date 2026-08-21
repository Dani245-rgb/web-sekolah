<?= $this->extend('layouts/template') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/jurusan-publik.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Jurusan / Program Keahlian</h2>
        </div>

        <?php if (!empty($jurusanList)): ?>
        <div class="jurusan-grid">
            <?php foreach ($jurusanList as $j): ?>
            <?php
                $deskripsiSingkat = !empty($j['deskripsi'])
                    ? esc(mb_strimwidth(strip_tags($j['deskripsi']), 0, 130, '...'))
                    : 'Pelajari lebih lanjut tentang program keahlian ini dan prospek kariernya.';
            ?>
            <div class="jurusan-flip">
                <div class="jurusan-flip-inner">

                    <div class="jurusan-flip-front">
                        <?php if (!empty($j['singkatan'])): ?>
                            <p class="jurusan-flip-singkatan"><?= esc($j['singkatan']) ?></p>
                        <?php else: ?>
                            <p class="jurusan-flip-nama-fallback"><?= esc($j['nama_jurusan']) ?></p>
                        <?php endif; ?>
                        <p class="jurusan-flip-hint">Arahkan kursor untuk detail</p>
                    </div>

                    <div class="jurusan-flip-back">
                        <h3><?= esc($j['nama_jurusan']) ?></h3>
                        <p><?= $deskripsiSingkat ?></p>
                        <a href="<?= base_url('akademik/jurusan/' . $j['slug']) ?>" class="jurusan-flip-btn">
                            Lihat Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="jurusan-empty">
            <p>Data jurusan belum tersedia.</p>
        </div>
        <?php endif; ?>

    </div>
</main>

<?= $this->endSection() ?>