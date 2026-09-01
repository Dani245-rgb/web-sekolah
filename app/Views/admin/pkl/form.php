<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="card-panel">
    <h4><?= $penempatan ? 'Edit' : 'Tambah' ?> Penempatan PKL</h4>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert" style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;margin:12px 0;">
            <?php foreach (session()->getFlashdata('errors') as $e): ?>
                <div><?= esc($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $penempatan ? base_url('admin/pkl/update/' . $penempatan['id_penempatan']) : base_url('admin/pkl/store') ?>" style="max-width:600px; margin-top:16px;">
        <?= csrf_field() ?>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">Siswa</label>
            <select name="id_siswa" required style="width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:8px;">
                <option value="">-- Pilih Siswa --</option>
                <?php foreach ($siswaList as $s): ?>
                    <option value="<?= $s['id_siswa'] ?>" <?= old('id_siswa', $penempatan['id_siswa'] ?? '') == $s['id_siswa'] ? 'selected' : '' ?>>
                        <?= esc($s['nama']) ?> (<?= esc($s['nis']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">Guru Pembimbing</label>
            <select name="id_guru_pembimbing" style="width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:8px;">
                <option value="">-- Belum Ditentukan --</option>
                <?php foreach ($guruList as $g): ?>
                    <option value="<?= $g['id_guru'] ?>" <?= old('id_guru_pembimbing', $penempatan['id_guru_pembimbing'] ?? '') == $g['id_guru'] ? 'selected' : '' ?>>
                        <?= esc($g['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">Nama Perusahaan</label>
            <input type="text" name="nama_perusahaan" value="<?= old('nama_perusahaan', $penempatan['nama_perusahaan'] ?? '') ?>" required style="width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:8px;">
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">Alamat Perusahaan</label>
            <textarea name="alamat_perusahaan" rows="2" style="width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:8px;"><?= old('alamat_perusahaan', $penempatan['alamat_perusahaan'] ?? '') ?></textarea>
        </div>

        <div style="display:flex; gap:14px; margin-bottom:14px;">
            <div style="flex:1;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">Nama Pembimbing Industri</label>
                <input type="text" name="nama_pembimbing_industri" value="<?= old('nama_pembimbing_industri', $penempatan['nama_pembimbing_industri'] ?? '') ?>" style="width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:8px;">
            </div>
            <div style="flex:1;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">No. HP Pembimbing Industri</label>
                <input type="text" name="no_hp_pembimbing_industri" value="<?= old('no_hp_pembimbing_industri', $penempatan['no_hp_pembimbing_industri'] ?? '') ?>" style="width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:8px;">
            </div>
        </div>

        <div style="display:flex; gap:14px; margin-bottom:14px;">
            <div style="flex:1;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" value="<?= old('tanggal_mulai', $penempatan['tanggal_mulai'] ?? '') ?>" required style="width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:8px;">
            </div>
            <div style="flex:1;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" value="<?= old('tanggal_selesai', $penempatan['tanggal_selesai'] ?? '') ?>" style="width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:8px;">
            </div>
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">Status</label>
            <select name="status" required style="width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:8px;">
                <?php foreach (['Aktif', 'Selesai', 'Dibatalkan'] as $st): ?>
                    <option value="<?= $st ?>" <?= old('status', $penempatan['status'] ?? 'Aktif') === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">Catatan</label>
            <textarea name="catatan" rows="2" style="width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:8px;"><?= old('catatan', $penempatan['catatan'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="<?= base_url('admin/pkl') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>