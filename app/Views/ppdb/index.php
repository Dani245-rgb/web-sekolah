<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Pendaftaran Siswa Baru (PPDB)</h2>
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

        <form action="<?= base_url('ppdb/daftar') ?>" method="post" class="ppdb-form">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control" value="<?= old('nama_lengkap') ?>" required>
            </div>

            <div class="mb-3">
                <label>Tempat Lahir</label>
                <input type="text" name="tempat_lahir" class="form-control" value="<?= old('tempat_lahir') ?>">
            </div>

            <div class="mb-3">
                <label>Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="form-control" value="<?= old('tanggal_lahir') ?>">
            </div>

            <div class="mb-3">
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-control" required>
                    <option value="">-- Pilih --</option>
                    <option value="Laki-laki" <?= old('jenis_kelamin') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="Perempuan" <?= old('jenis_kelamin') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Asal Sekolah</label>
                <input type="text" name="asal_sekolah" class="form-control" value="<?= old('asal_sekolah') ?>">
            </div>

            <div class="mb-3">
                <label>Jurusan Pilihan</label>
                <select name="jurusan_pilihan" class="form-control" required>
                    <option value="">-- Pilih Jurusan --</option>
                    <option value="Rekayasa Perangkat Lunak" <?= old('jurusan_pilihan') === 'Rekayasa Perangkat Lunak' ? 'selected' : '' ?>>Rekayasa Perangkat Lunak</option>
                    <option value="Tata Boga" <?= old('jurusan_pilihan') === 'Tata Boga' ? 'selected' : '' ?>>Tata Boga</option>
                    <option value="Teknik Kendaraan" <?= old('jurusan_pilihan') === 'Teknik Kendaraan' ? 'selected' : '' ?>>Teknik Kendaraan</option>
                </select>
            </div>

            <div class="mb-3">
                <label>No. HP / WhatsApp</label>
                <input type="text" name="no_hp" class="form-control" value="<?= old('no_hp') ?>" required>
            </div>

            <div class="mb-3">
                <label>Email (opsional)</label>
                <input type="email" name="email" class="form-control" value="<?= old('email') ?>">
            </div>

            <div class="mb-3">
                <label>Alamat</label>
                <textarea name="alamat" class="form-control" rows="3"><?= old('alamat') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Kirim Pendaftaran</button>
        </form>

    </div>
</main>

<?= $this->endSection() ?>