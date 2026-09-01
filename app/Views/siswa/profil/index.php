<?= $this->extend('layouts/siswa/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/dashboard_siswa/profil.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4>Profil Saya</h4>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert" style="background:#dcfce7;color:#166534;padding:10px 14px;border-radius:8px;margin-top:16px;">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <div class="profil-header">
        <?php if (!empty($siswa['foto'])): ?>
            <img src="<?= base_url('assets/uploads/siswa/' . $siswa['foto']) ?>" alt="Foto Profil" class="profil-avatar">
        <?php else: ?>
            <div class="profil-avatar-fallback"><?= esc(strtoupper(substr($siswa['nama'], 0, 1))) ?></div>
        <?php endif; ?>

        <div class="profil-info">
            <div class="profil-name"><?= esc($siswa['nama']) ?></div>
            <div class="profil-meta">Kelas <?= esc($siswa['nama_kelas'] ?? '-') ?> · NIS <?= esc($siswa['nis']) ?></div>
            <span class="profil-badge <?= $siswa['status'] === 'Aktif' ? 'status-aktif' : 'status-nonaktif' ?>">
                <?= esc($siswa['status']) ?>
            </span>
        </div>
    </div>

    <div class="profil-section">
        <div class="profil-section-title">Data Akademik</div>
        <div class="profil-grid">
            <div class="profil-field">
                <div class="profil-field-label">NIS</div>
                <div class="profil-field-value"><?= esc($siswa['nis'] ?: '-') ?></div>
            </div>
            <div class="profil-field">
                <div class="profil-field-label">NISN</div>
                <div class="profil-field-value"><?= esc($siswa['nisn'] ?: '-') ?></div>
            </div>
            <div class="profil-field">
                <div class="profil-field-label">Kelas</div>
                <div class="profil-field-value"><?= esc($siswa['nama_kelas'] ?? '-') ?></div>
            </div>
        </div>
    </div>

    <div class="profil-section">
        <div class="profil-section-title">Data Pribadi</div>
        <div class="profil-grid">
            <div class="profil-field">
                <div class="profil-field-label">Tempat, Tanggal Lahir</div>
                <div class="profil-field-value">
                    <?= esc($siswa['tempat_lahir'] ?: '-') ?><?= $siswa['tanggal_lahir'] ? ', ' . date('d F Y', strtotime($siswa['tanggal_lahir'])) : '' ?>
                </div>
            </div>
            <div class="profil-field">
                <div class="profil-field-label">Jenis Kelamin</div>
                <div class="profil-field-value">
                    <?= $siswa['jenis_kelamin'] === 'L' ? 'Laki-laki' : ($siswa['jenis_kelamin'] === 'P' ? 'Perempuan' : '-') ?>
                </div>
            </div>
            <div class="profil-field">
                <div class="profil-field-label">Agama</div>
                <div class="profil-field-value"><?= esc($siswa['agama'] ?: '-') ?></div>
            </div>
            <div class="profil-field full">
                <div class="profil-field-label">Alamat</div>
                <div class="profil-field-value"><?= esc($siswa['alamat'] ?: '-') ?></div>
            </div>
        </div>
    </div>

    <div class="profil-section">
        <div class="profil-section-title">Data Orang Tua / Wali</div>
        <div class="profil-grid">
            <div class="profil-field">
                <div class="profil-field-label">Nama Ayah</div>
                <div class="profil-field-value"><?= esc($siswa['nama_ayah'] ?: '-') ?></div>
            </div>
            <div class="profil-field">
                <div class="profil-field-label">Nama Ibu</div>
                <div class="profil-field-value"><?= esc($siswa['nama_ibu'] ?: '-') ?></div>
            </div>
            <div class="profil-field">
                <div class="profil-field-label">Pekerjaan Orang Tua</div>
                <div class="profil-field-value"><?= esc($siswa['pekerjaan_ortu'] ?: '-') ?></div>
            </div>
            <div class="profil-field">
                <div class="profil-field-label">No. HP Orang Tua</div>
                <div class="profil-field-value"><?= esc($siswa['no_hp_ortu'] ?: '-') ?></div>
            </div>
        </div>
    </div>

    <div class="profil-section">
        <div class="profil-section-title">Kontak</div>
        <div class="profil-grid">
            <div class="profil-field">
                <div class="profil-field-label">Email</div>
                <div class="profil-field-value wrap-anywhere"><?= esc($siswa['email'] ?: '-') ?></div>
            </div>
        </div>
    </div>

    <div class="absensi-actions" style="margin-top:28px;">
        <a href="<?= base_url('siswa/profil/edit') ?>" class="btn btn-primary">Edit Kontak</a>
        <a href="<?= base_url('siswa/dashboard') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>