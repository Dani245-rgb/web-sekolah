<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/dashboard_guru/profil.css') ?>">

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
        <?php if (!empty($guru['foto'])): ?>
            <img src="<?= base_url('assets/uploads/guru/' . $guru['foto']) ?>" alt="Foto Profil" class="profil-avatar">
        <?php else: ?>
            <div class="profil-avatar-fallback"><?= esc(strtoupper(substr($guru['nama'], 0, 1))) ?></div>
        <?php endif; ?>

        <div class="profil-info">
            <div class="profil-name"><?= esc($guru['nama']) ?></div>
            <div class="profil-meta"><?= esc(ucwords($guru['jabatan'] ?? '-')) ?> · @<?= esc($guru['username']) ?></div>
            <span class="profil-badge <?= $guru['status'] === 'Aktif' ? 'status-aktif' : 'status-nonaktif' ?>">
                <?= esc($guru['status']) ?>
            </span>
        </div>
    </div>

    <div class="profil-section">
        <div class="profil-section-title">Data Kepegawaian</div>
        <div class="profil-grid">
            <div class="profil-field">
                <div class="profil-field-label">NIP</div>
                <div class="profil-field-value"><?= esc($guru['nip'] ?: '-') ?></div>
            </div>
            <div class="profil-field">
                <div class="profil-field-label">NUPTK</div>
                <div class="profil-field-value"><?= esc($guru['nuptk'] ?: '-') ?></div>
            </div>
            <div class="profil-field">
                <div class="profil-field-label">Jabatan/Bidang</div>
                <div class="profil-field-value"><?= esc(ucwords($guru['jabatan'] ?? '-')) ?></div>
            </div>
        </div>
    </div>

    <div class="profil-section">
        <div class="profil-section-title">Data Pribadi</div>
        <div class="profil-grid">
            <div class="profil-field">
                <div class="profil-field-label">Tempat, Tanggal Lahir</div>
                <div class="profil-field-value">
                    <?= esc($guru['tempat_lahir'] ?: '-') ?><?= $guru['tanggal_lahir'] ? ', ' . date('d F Y', strtotime($guru['tanggal_lahir'])) : '' ?>
                </div>
            </div>
            <div class="profil-field">
                <div class="profil-field-label">Jenis Kelamin</div>
                <div class="profil-field-value">
                    <?= $guru['jenis_kelamin'] === 'L' ? 'Laki-laki' : ($guru['jenis_kelamin'] === 'P' ? 'Perempuan' : '-') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="profil-section">
        <div class="profil-section-title">Kontak</div>
        <div class="profil-grid">
            <div class="profil-field">
                <div class="profil-field-label">No. HP</div>
                <div class="profil-field-value"><?= esc($guru['no_hp'] ?: '-') ?></div>
            </div>
            <div class="profil-field">
                <div class="profil-field-label">Email</div>
                <div class="profil-field-value wrap-anywhere"><?= esc($guru['email'] ?: '-') ?></div>
            </div>
        </div>
    </div>

    <div class="absensi-actions" style="margin-top:28px;">
        <a href="<?= base_url('guru/profil/edit') ?>" class="btn btn-primary">Edit Profil</a>
        <a href="<?= base_url('guru/dashboard') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>