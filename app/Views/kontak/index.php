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
                <form action="<?= base_url('kontak/kirim') ?>" method="post" class="form-kontak">
                    <?= csrf_field() ?>

                    <div class="form-kontak-group">
                        <label>Nama</label>
                        <input type="text" name="nama" value="<?= old('nama') ?>" required>
                    </div>

                    <div class="form-kontak-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= old('email') ?>" required>
                    </div>

                    <div class="form-kontak-group">
                        <label>Subjek</label>
                        <input type="text" name="subjek" value="<?= old('subjek') ?>">
                    </div>

                    <div class="form-kontak-group">
                        <label>Pesan</label>
                        <textarea name="pesan" rows="5" required><?= old('pesan') ?></textarea>
                    </div>

                    <button type="submit" class="btn-wa-cepat" style="background:var(--emas);">
                        <i class="bi bi-send"></i> Kirim Pesan
                    </button>
                </form>
            </div>
        </div>

    </div>
</main>

<?= $this->endSection() ?>