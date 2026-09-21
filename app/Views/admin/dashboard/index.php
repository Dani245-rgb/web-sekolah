<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Dashboard</h4>
    <p class="breadcrumb">Selamat datang, <?= esc(session()->get('username')) ?></p>
</div>

<!-- STAT CARDS -->
<div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:20px;">
    <div class="card" style="flex:1;min-width:180px;display:flex;gap:12px;align-items:center;">
        <div style="width:44px;height:44px;border-radius:10px;background:#3b82f6;color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
            <i class="bi bi-people-fill"></i>
        </div>
        <div>
            <strong style="color:#7c8a9c;font-size:12px;">Total Siswa</strong>
            <h3 style="margin-top:2px;font-size:24px;"><?= $totalSiswa ?></h3>
        </div>
    </div>
    <div class="card" style="flex:1;min-width:180px;display:flex;gap:12px;align-items:center;">
        <div style="width:44px;height:44px;border-radius:10px;background:#8b5cf6;color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div>
            <strong style="color:#7c8a9c;font-size:12px;">Total Guru</strong>
            <h3 style="margin-top:2px;font-size:24px;"><?= $totalGuru ?></h3>
        </div>
    </div>
</div>

<!-- ROW: Gender + Pengumuman + Aktivitas -->
<div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:24px;align-items:stretch;">

    <!-- Siswa per Gender -->
    <div class="card" style="flex:1;min-width:230px;">
        <strong>Siswa per Gender</strong>
        <div style="margin-top:16px;height:200px;">
            <canvas id="chartGender"></canvas>
        </div>
        <div style="margin-top:16px;font-size:13px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#3b82f6;margin-right:6px;"></span>Laki-laki</span>
                <strong><?= $perGender['L'] ?></strong>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#ec4899;margin-right:6px;"></span>Perempuan</span>
                <strong><?= $perGender['P'] ?></strong>
            </div>
        </div>
    </div>

    <!-- Pengumuman Terbaru -->
    <div class="card" style="flex:1;min-width:230px;">
        <strong>Pengumuman Terbaru</strong>
        <div style="margin-top:12px;">
            <?php if (empty($pengumumanTerbaru)): ?>
                <div class="empty-state-dash">
                    <i class="bi bi-megaphone"></i>
                    <span>Belum ada pengumuman.</span>
                </div>
            <?php else: ?>
                <?php foreach ($pengumumanTerbaru as $p): ?>
                    <div style="display:flex;gap:8px;padding:8px 0;border-bottom:1px solid #eef1f5;">
                        <div style="width:7px;height:7px;border-radius:50%;background:#3b82f6;margin-top:6px;flex-shrink:0;"></div>
                        <div>
                            <div style="font-size:13px;font-weight:600;"><?= esc($p['judul']) ?></div>
                            <div style="font-size:11px;color:#b7c0cc;margin-top:2px;"><?= date('d M Y', strtotime($p['tanggal_publish'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <a href="<?= base_url('admin/pengumuman') ?>" style="font-size:12px;color:#3b82f6;text-decoration:none;display:inline-block;margin-top:10px;">Lihat Semua Pengumuman →</a>
    </div>

    <!-- Aktivitas Terbaru -->
    <div class="card" style="flex:1;min-width:230px;">
        <strong>Aktivitas Terbaru</strong>
        <div style="margin-top:12px;">
            <?php if (empty($aktivitasTerbaru)): ?>
                <div class="empty-state-dash">
                    <i class="bi bi-clock-history"></i>
                    <span>Belum ada aktivitas.</span>
                </div>
            <?php else: ?>
                <?php foreach ($aktivitasTerbaru as $a): ?>
                    <div style="display:flex;gap:8px;padding:8px 0;border-bottom:1px solid #eef1f5;">
                        <div style="width:7px;height:7px;border-radius:50%;background:#22c55e;margin-top:6px;flex-shrink:0;"></div>
                        <div>
                            <div style="font-size:13px;"><?= esc($a['username'] ?? 'Sistem') ?>: <?= esc($a['keterangan'] ?? $a['aksi']) ?></div>
                            <div style="font-size:11px;color:#b7c0cc;margin-top:2px;"><?= date('d M Y H:i', strtotime($a['created_at'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <a href="<?= base_url('admin/audit-log') ?>" style="font-size:12px;color:#3b82f6;text-decoration:none;display:inline-block;margin-top:10px;">Lihat Semua Aktivitas →</a>
    </div>

</div>

<!-- QUICK ACTIONS -->
<div class="card">
    <strong>Quick Action</strong>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:14px;">
        <a href="<?= base_url('admin/siswa/create') ?>" class="btn btn-accent" style="flex:1;min-width:140px;text-align:center;"><i class="bi bi-person-plus"></i> Tambah Siswa</a>
        <a href="<?= base_url('admin/guru/create') ?>" class="btn btn-secondary" style="flex:1;min-width:140px;text-align:center;"><i class="bi bi-person-plus"></i> Tambah Guru</a>
        <a href="<?= base_url('admin/pengumuman/create') ?>" class="btn btn-secondary" style="flex:1;min-width:140px;text-align:center;"><i class="bi bi-megaphone"></i> Buat Pengumuman</a>
        <a href="<?= base_url('admin/laporan-siswa') ?>" class="btn btn-secondary" style="flex:1;min-width:140px;text-align:center;"><i class="bi bi-file-earmark-text"></i> Lihat Laporan</a>
        <a href="<?= base_url('admin/backup-database') ?>" class="btn btn-secondary" style="flex:1;min-width:140px;text-align:center;"><i class="bi bi-hdd-stack"></i> Backup Data</a>
    </div>
</div>

<!-- Notifikasi + Akses Role -->
<div style="display:flex;gap:24px;flex-wrap:wrap;margin-top:24px;">
    <div class="card" style="flex:2;min-width:300px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <strong>Notifikasi Terbaru</strong>
            <a href="<?= base_url('admin/notifikasi') ?>" style="font-size:13px;color:#3b82f6;text-decoration:none;">Lihat Semua →</a>
        </div>
        <?php if (empty($notifikasiTerbaru)): ?>
            <div class="empty-state-dash">
                <i class="bi bi-bell"></i>
                <span>Belum ada notifikasi.</span>
            </div>
        <?php else: ?>
            <?php foreach ($notifikasiTerbaru as $n): ?>
                <div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid #eef1f5;">
                    <div style="width:8px;height:8px;border-radius:50%;background:<?= $n['is_read'] ? '#d7dce3' : '#3b82f6' ?>;margin-top:6px;flex-shrink:0;"></div>
                    <div>
                        <div style="font-size:13px;<?= $n['is_read'] ? '' : 'font-weight:600;' ?>"><?= esc($n['judul']) ?></div>
                        <div style="font-size:12px;color:#7c8a9c;margin-top:2px;"><?= esc($n['pesan']) ?></div>
                        <div style="font-size:11px;color:#b7c0cc;margin-top:2px;"><?= date('d M Y H:i', strtotime($n['created_at'])) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card" style="flex:1;min-width:260px;">
        <strong>Akses Role</strong>
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:14px;">
            <div style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid #eef1f5;border-radius:8px;">
                <div style="width:36px;height:36px;border-radius:8px;background:#3b82f6;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;"><i class="bi bi-shield-check"></i></div>
                <div><strong style="font-size:13px;">Admin</strong>
                    <div style="font-size:12px;color:#7c8a9c;">Akses Penuh</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid #eef1f5;border-radius:8px;">
                <div style="width:36px;height:36px;border-radius:8px;background:#22c55e;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;"><i class="bi bi-mortarboard"></i></div>
                <div><strong style="font-size:13px;">Guru</strong>
                    <div style="font-size:12px;color:#7c8a9c;">Akses Terbatas</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid #eef1f5;border-radius:8px;">
                <div style="width:36px;height:36px;border-radius:8px;background:#ec4899;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;"><i class="bi bi-person-badge"></i></div>
                <div><strong style="font-size:13px;">Siswa</strong>
                    <div style="font-size:12px;color:#7c8a9c;">Akses Terbatas</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    (function() {
        new Chart(document.getElementById('chartGender'), {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [<?= $perGender['L'] ?>, <?= $perGender['P'] ?>],
                    backgroundColor: ['#3b82f6', '#ec4899'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '65%'
            }
        });
    })();
</script>

<?= $this->endSection() ?>