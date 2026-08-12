<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Agenda Sekolah</h2>
        </div>

        <ul class="agenda-list agenda-list-full">
            <?php foreach ($agenda as $a): ?>
            <li>
                <a href="#">
                    <div class="agenda-tanggal">
                        <span class="tgl"><?= date('d', strtotime($a['tanggal'])) ?></span>
                        <span class="bln"><?= date('M', strtotime($a['tanggal'])) ?></span>
                    </div>
                    <div class="agenda-info">
                        <h4><?= esc($a['judul']) ?></h4>
                        <span><?= esc($a['waktu']) ?><?= $a['lokasi'] ? ' - ' . esc($a['lokasi']) : '' ?></span>
                    </div>
                </a>
            </li>
            <?php endforeach; ?>

            <?php if (empty($agenda)): ?>
            <li><span class="text-muted">Belum ada agenda.</span></li>
            <?php endif; ?>
        </ul>

    </div>
</main>

<?= $this->endSection() ?>