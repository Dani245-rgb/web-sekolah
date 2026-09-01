<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4>Isi Absensi — <?= esc($jadwal['nama_kelas']) ?> / <?= esc($jadwal['nama_mapel']) ?></h4>
    </div>
    <p class="absensi-tanggal">Tanggal: <strong><?= esc($tanggal) ?></strong></p>

    <?php if ($hariKhususHariIni): ?>
        <div class="alert" style="background:#dbeafe;color:#1e40af;padding:10px 14px;border-radius:8px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;">
            <span>📌 Hari ini ditandai: <strong><?= esc($hariKhususHariIni['keterangan']) ?></strong></span>
            <?php if ($hariKhususHariIni['id_jadwal'] == $jadwal['id_jadwal']): ?>
                <form action="<?= base_url('guru/absensi/hapus-khusus') ?>" method="post" style="margin:0;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_jadwal" value="<?= $jadwal['id_jadwal'] ?>">
                    <input type="hidden" name="tanggal" value="<?= esc($tanggal) ?>">
                    <button type="submit" class="btn btn-secondary" style="padding:4px 10px;font-size:12px;">Batalkan</button>
                </form>
            <?php else: ?>
                <span style="font-size:12px;opacity:.75;">(ditandai Admin)</span>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <details style="margin-bottom:14px;">
            <summary style="cursor:pointer;color:#4a6cf7;font-size:13.5px;">+ Tandai tanggal ini sebagai hari khusus (rapat/kendala lain)</summary>
            <form action="<?= base_url('guru/absensi/tandai-khusus') ?>" method="post" style="margin-top:8px;display:flex;gap:8px;">
                <?= csrf_field() ?>
                <input type="hidden" name="id_jadwal" value="<?= $jadwal['id_jadwal'] ?>">
                <input type="hidden" name="tanggal" value="<?= esc($tanggal) ?>">
                <input type="text" name="keterangan" placeholder="mis. Rapat Dadakan" class="absensi-keterangan" style="max-width:260px;" required>
                <button type="submit" class="btn btn-secondary" style="font-size:13px;">Tandai</button>
            </form>
        </details>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-error">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('guru/absensi/simpan') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="id_jadwal" value="<?= $jadwal['id_jadwal'] ?>">
        <input type="hidden" name="tanggal" value="<?= esc($tanggal) ?>">

        <table class="absensi-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($siswaList as $s): ?>
                    <?php $statusSaved = $statusTersimpan[$s['id_siswa']]['status'] ?? 'Hadir'; ?>
                    <?php $ketSaved = $statusTersimpan[$s['id_siswa']]['keterangan'] ?? ''; ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($s['nama']) ?></td>
                        <td>
                            <select
                                name="status[<?= $s['id_siswa'] ?>]"
                                id="status-<?= $s['id_siswa'] ?>"
                                class="absensi-select"
                                data-status="<?= $statusSaved ?>"
                                data-nama="<?= esc($s['nama']) ?>"
                                onchange="this.setAttribute('data-status', this.value); updateMapAbsensi(<?= $s['id_siswa'] ?>, this.value)">
                                <option value="Hadir" <?= $statusSaved === 'Hadir' ? 'selected' : '' ?>>Hadir</option>
                                <option value="Izin" <?= $statusSaved === 'Izin' ? 'selected' : '' ?>>Izin</option>
                                <option value="Sakit" <?= $statusSaved === 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                                <option value="Alfa" <?= $statusSaved === 'Alfa' ? 'selected' : '' ?>>Alfa</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="keterangan[<?= $s['id_siswa'] ?>]" value="<?= esc($ketSaved) ?>" placeholder="Opsional" class="absensi-keterangan">
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($siswaList)): ?>
                    <tr>
                        <td colspan="4" class="absensi-empty">Tidak ada siswa di kelas ini untuk tahun ajaran aktif.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="absensi-actions">
            <button type="submit" class="btn btn-primary">Simpan Absensi</button>
            <a href="<?= base_url('guru/absensi/riwayat/' . $jadwal['id_jadwal']) ?>" class="btn btn-secondary">Riwayat</a>
            <a href="<?= base_url('guru/dashboard') ?>" class="btn btn-secondary">Kembali</a>
        </div>
    </form>

    <?= $this->include('guru/absensi/_peta_partial') ?>
</div>
<style>
    .peta-absensi-wrapper {
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .peta-absensi-title {
        margin-bottom: 12px;
        font-weight: 600;
    }

    .peta-absensi-hint {
        font-weight: 400;
        font-size: 12px;
        color: #9aa0ac;
        margin-left: 6px;
    }

    .peta-absensi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 10px;
    }

    .peta-item {
        border-radius: 8px;
        padding: 10px 8px;
        text-align: center;
        border: 2px solid transparent;
        background: #f1f5f9;
        color: #334155;
        transition: background-color .25s ease, border-color .25s ease, transform .15s ease;
    }

    .peta-item-nama {
        font-size: 12.5px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .peta-item-status {
        font-size: 11px;
        margin-top: 3px;
        opacity: .85;
    }

    /* status: Hadir */
    .peta-item[data-status="Hadir"] {
        background: #dcfce7;
        border-color: #22c55e;
        color: #166534;
    }

    /* status: Izin / Sakit */
    .peta-item[data-status="Izin"],
    .peta-item[data-status="Sakit"] {
        background: #fef9c3;
        border-color: #eab308;
        color: #854d0e;
    }

    /* status: Alfa */
    .peta-item[data-status="Alfa"] {
        background: #fee2e2;
        border-color: #ef4444;
        color: #991b1b;
    }

    .peta-item.just-changed {
        transform: scale(1.06);
    }

    .peta-absensi-legend {
        margin-top: 14px;
        display: flex;
        gap: 18px;
        font-size: 12.5px;
        color: #475569;
    }

    .legend-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
        vertical-align: middle;
    }

    .legend-hijau {
        background: #22c55e;
    }

    .legend-kuning {
        background: #eab308;
    }

    .legend-merah {
        background: #ef4444;
    }
</style>

<script>
    function updateMapAbsensi(idSiswa, status) {
        const item = document.getElementById('peta-item-' + idSiswa);
        if (!item) return;

        item.setAttribute('data-status', status);
        const statusLabel = item.querySelector('.peta-item-status');
        if (statusLabel) statusLabel.textContent = status;

        // efek kecil biar kelihatan "hidup" saat berubah
        item.classList.add('just-changed');
        setTimeout(() => item.classList.remove('just-changed'), 200);
    }

    // Sinkronkan peta absensi dengan nilai select saat halaman pertama kali dimuat
    // (jaga-jaga kalau browser autofill/restore nilai select yang beda dari data-status awal)
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.absensi-select').forEach(function(select) {
            const idSiswa = select.name.match(/\[(\d+)\]/)[1];
            updateMapAbsensi(idSiswa, select.value);
        });
    });
</script>

<?= $this->endSection() ?>