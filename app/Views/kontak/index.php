<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Hubungi Kami</h2>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul style="margin:0;">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="lokasi-split">
            <div class="lokasi-map">
                <iframe src="https://www.google.com/maps?q=SMK+Attaufiqiyyah&output=embed" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade" title="Peta lokasi SMK Attaufiqiyyah">
                </iframe>
            </div>

            <div class="kontak-cepat">
                <form action="<?= base_url('kontak/kirim') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label>Nama</label>
                        <input type="text" name="nama" class="form-control" value="<?= old('nama') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Subjek</label>
                        <input type="text" name="subjek" class="form-control" value="<?= old('subjek') ?>">
                    </div>

                    <div class="mb-3">
                        <label>Pesan</label>
                        <textarea name="pesan" class="form-control" rows="5" required><?= old('pesan') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                </form>
            </div>
        </div>

    </div>
</main>

<?= $this->endSection() ?>