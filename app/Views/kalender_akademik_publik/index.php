<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Kalender Akademik</h2>
        </div>

        <table class="table-admin">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kegiatan</th>
                    <th>Semester</th>
                    <th>Tahun Ajaran</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kalender as $k): ?>
                <tr>
                    <td>
                        <?= date('d M Y', strtotime($k['tanggal_mulai'])) ?>
                        <?= $k['tanggal_selesai'] ? ' - ' . date('d M Y', strtotime($k['tanggal_selesai'])) : '' ?>
                    </td>
                    <td>
                        <?= esc($k['kegiatan']) ?>
                        <?php if ($k['keterangan']): ?>
                            <br><small style="color:#64748b;"><?= esc($k['keterangan']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($k['semester']) ?></td>
                    <td><?= esc($k['tahun_ajaran']) ?></td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($kalender)): ?>
                <tr><td colspan="4" class="text-muted">Belum ada kegiatan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</main>

<?= $this->endSection() ?>