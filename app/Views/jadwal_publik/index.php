<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Jadwal Pelajaran</h2>
        </div>

        <?php if (!$semesterAda): ?>
            <p>Belum ada semester aktif. Jadwal belum bisa ditampilkan.</p>
        <?php else: ?>

        <form method="get" style="margin-bottom:20px;display:flex;gap:10px;flex-wrap:wrap;">
            <select name="id_kelas" class="form-control" style="max-width:200px;" onchange="this.form.submit()">
                <option value="">Semua Kelas</option>
                <?php foreach ($kelasList as $k): ?>
                    <option value="<?= $k['id_kelas'] ?>" <?= ($filter['id_kelas'] ?? '') == $k['id_kelas'] ? 'selected' : '' ?>>
                        <?= esc($k['nama_kelas']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="hari" class="form-control" style="max-width:160px;" onchange="this.form.submit()">
                <option value="">Semua Hari</option>
                <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h): ?>
                    <option value="<?= $h ?>" <?= ($filter['hari'] ?? '') === $h ? 'selected' : '' ?>><?= $h ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <table class="table-admin">
            <thead>
                <tr>
                    <th>Hari</th>
                    <th>Jam</th>
                    <th>Kelas</th>
                    <th>Mata Pelajaran</th>
                    <th>Guru</th>
                    <th>Ruangan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jadwal as $j): ?>
                <tr>
                    <td><?= esc($j['hari']) ?></td>
                    <td><?= esc($j['jam_mulai']) ?> - <?= esc($j['jam_selesai']) ?></td>
                    <td><?= esc($j['nama_kelas']) ?></td>
                    <td><?= esc($j['nama_mapel']) ?></td>
                    <td><?= esc($j['nama_guru']) ?></td>
                    <td><?= esc($j['nama_ruangan']) ?></td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($jadwal)): ?>
                <tr><td colspan="6" class="text-muted">Tidak ada jadwal untuk filter ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php endif; ?>

    </div>
</main>

<?= $this->endSection() ?>