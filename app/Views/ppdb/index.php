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

            <div class="ppdb-grid">
                <div class="ppdb-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="<?= old('nama_lengkap') ?>" required>
                </div>

                <div class="ppdb-group">
                    <label>Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="<?= old('tempat_lahir') ?>">
                </div>

                <div class="ppdb-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="<?= old('tanggal_lahir') ?>">
                </div>

                <div class="ppdb-group">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" required>
                        <option value="">-- Pilih --</option>
                        <option value="Laki-laki" <?= old('jenis_kelamin') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= old('jenis_kelamin') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>

                <div class="ppdb-group">
                    <label>Asal Sekolah</label>
                    <input type="text" name="asal_sekolah" value="<?= old('asal_sekolah') ?>">
                </div>

                <div class="ppdb-group">
                    <label>Jurusan Pilihan</label>
                    <select name="jurusan_pilihan" required>
                        <option value="">-- Pilih Jurusan --</option>
                        <option value="Rekayasa Perangkat Lunak" <?= old('jurusan_pilihan') === 'Rekayasa Perangkat Lunak' ? 'selected' : '' ?>>Rekayasa Perangkat Lunak</option>
                        <option value="Tata Boga" <?= old('jurusan_pilihan') === 'Tata Boga' ? 'selected' : '' ?>>Tata Boga</option>
                        <option value="Teknik Kendaraan" <?= old('jurusan_pilihan') === 'Teknik Kendaraan' ? 'selected' : '' ?>>Teknik Kendaraan</option>
                    </select>
                </div>

                <div class="ppdb-group">
                    <label>No. HP / WhatsApp</label>
                    <input type="text" name="no_hp" value="<?= old('no_hp') ?>" required>
                </div>

                <div class="ppdb-group">
                    <label>Email (opsional)</label>
                    <input type="email" name="email" value="<?= old('email') ?>">
                </div>

                <div class="ppdb-group ppdb-group-full">
                    <label>Alamat</label>
                    <textarea name="alamat" rows="3"><?= old('alamat') ?></textarea>
                </div>
            </div>

            <button type="submit" class="ppdb-submit">
                <i class="bi bi-send"></i> Kirim Pendaftaran
            </button>
        </form>

    </div>
</main>

<?= $this->endSection() ?>