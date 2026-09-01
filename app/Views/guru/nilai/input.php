<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/nilai.css') ?>">

<div class="nilai-card">
    <div class="nilai-header">
        <h4>Input Nilai — <?= esc($jadwal['nama_kelas']) ?> / <?= esc($jadwal['nama_mapel']) ?></h4>
    </div>
    <p class="nilai-subinfo">KKM: <strong><?= esc($pengaturan['kkm']) ?></strong></p>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="nilai-alert nilai-alert-error">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="nilai-alert nilai-alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if ($konflikList = session()->getFlashdata('konflikNilai')): ?>
        <div class="nilai-alert nilai-alert-error">
            <strong>Perhatian:</strong> nilai berikut tidak tersimpan karena sudah diubah pengguna lain sejak form ini dibuka:
            <ul style="margin:6px 0 0 18px;">
                <?php foreach ($konflikList as $k): ?>
                    <li>Siswa ID <?= esc($k['id_siswa']) ?>, Komponen ID <?= esc($k['id_komponen']) ?> — nilai Anda (<?= esc($k['nilai_baru_ditolak']) ?>) ditolak, nilai saat ini di server: <?= esc($k['nilai_saat_ini']) ?></li>
                <?php endforeach; ?>
            </ul>
            Silakan periksa ulang dan isi kembali kolom tersebut kalau memang ingin menimpanya.
        </div>
    <?php endif; ?>

    <form action="<?= base_url('guru/nilai/form/' . $jadwal['id_jadwal'] . '/simpan') ?>" method="post" id="formNilai" data-jadwal-id="<?= $jadwal['id_jadwal'] ?>">
        <?= csrf_field() ?>

        <?php if (!empty($kategoriList)): ?>
            <div class="nilai-tab-nav" style="display:flex; gap:8px; margin-bottom:12px; border-bottom:1px solid #ddd;">
                <?php foreach ($kategoriList as $ti => $kat): ?>
                    <button type="button" class="nilai-tab-btn<?= $ti === 0 ? ' active' : '' ?>"
                        data-tab-target="tab-kategori-<?= $kat['id_kategori'] ?>"
                        style="padding:8px 14px; border:none; background:<?= $ti === 0 ? '#eef2ff' : 'transparent' ?>; cursor:pointer; font-weight:600;">
                        <?= esc($kat['nama_kategori']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($kategoriList as $ti => $kat): ?>
                <?php $komponenTab = $komponenPerKategori[$kat['id_kategori']] ?? []; ?>
                <div class="nilai-tab-pane" id="tab-kategori-<?= $kat['id_kategori'] ?>" style="<?= $ti === 0 ? '' : 'display:none;' ?>">
                    <table class="nilai-siswa-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <?php foreach ($komponenTab as $k): ?>
                                    <th>
                                        <?= esc($k['nama_komponen']) ?> (<?= esc($k['bobot']) ?>%)
                                        <?php if (!empty($k['link_referensi'])): ?>
                                            <br><a href="<?= esc($k['link_referensi'], 'url') ?>" target="_blank" rel="noopener noreferrer" style="font-weight:400; font-size:11px;">🔗 Buka link</a>
                                        <?php endif; ?>
                                        <?php if (!empty($k['keterangan'])): ?>
                                            <br><span style="font-weight:400; font-size:11px; color:#888;">📝 <?= esc($k['keterangan']) ?></span>
                                        <?php endif; ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($siswaList as $s): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= esc($s['nama']) ?></td>
                                    <?php foreach ($komponenTab as $k): ?>
                                        <td>
                                            <input type="hidden"
                                                name="updated_at[<?= $s['id_siswa'] ?>][<?= $k['id_komponen'] ?>]"
                                                value="<?= esc($nilaiUpdatedAtMap[$s['id_siswa']][$k['id_komponen']] ?? '') ?>">
                                            <input type="number" step="0.01" min="0" max="100"
                                                class="nilai-input"
                                                data-siswa="<?= $s['id_siswa'] ?>" data-komponen="<?= $k['id_komponen'] ?>"
                                                name="nilai[<?= $s['id_siswa'] ?>][<?= $k['id_komponen'] ?>]"
                                                value="<?= esc($nilaiMap[$s['id_siswa']][$k['id_komponen']] ?? '') ?>">
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($siswaList)): ?>
                                <tr>
                                    <td colspan="<?= 2 + count($komponenTab) ?>" class="nilai-empty">Tidak ada siswa di kelas ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>

            <script>
                document.querySelectorAll('.nilai-tab-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.nilai-tab-btn').forEach(function(b) {
                            b.classList.remove('active');
                            b.style.background = 'transparent';
                        });
                        document.querySelectorAll('.nilai-tab-pane').forEach(function(p) {
                            p.style.display = 'none';
                        });
                        btn.classList.add('active');
                        btn.style.background = '#eef2ff';
                        document.getElementById(btn.dataset.tabTarget).style.display = '';
                    });
                });
            </script>
        <?php else: ?>
            <p class="nilai-empty">Belum ada kategori nilai. Silakan atur dulu lewat "Atur Ulang Komponen Nilai".</p>
        <?php endif; ?>

        <div class="nilai-actions">
            <button type="submit" class="nilai-btn nilai-btn-primary">Simpan Nilai</button>
            <a href="<?= base_url('guru/nilai/rekap/' . $jadwal['id_jadwal']) ?>" class="nilai-btn nilai-btn-secondary">Lihat Rekap</a>
            <a href="<?= base_url('guru/nilai/form/' . $jadwal['id_jadwal'] . '/pengaturan') ?>" class="nilai-btn nilai-btn-secondary">Atur Ulang Komponen Nilai</a>
            <a href="<?= base_url('guru/nilai/form/' . $jadwal['id_jadwal'] . '/riwayat') ?>" class="nilai-btn nilai-btn-secondary">Riwayat Perubahan</a>
            <a href="<?= base_url('guru/dashboard') ?>" class="nilai-btn nilai-btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<script>
    (function() {
        var form = document.getElementById('formNilai');
        var jadwalId = form?.dataset.jadwalId;
        if (!jadwalId || !form) return;
        var draftKey = 'draft_nilai_' + jadwalId;
        var csrfUrl = '<?= base_url('guru/nilai/csrf-token') ?>';

        // --- Auto-save draft ke localStorage tiap kali guru mengubah nilai ---
        function ambilSemuaNilai() {
            var data = {};
            document.querySelectorAll('.nilai-input').forEach(function(input) {
                if (input.value !== '') {
                    data[input.dataset.siswa + '_' + input.dataset.komponen] = input.value;
                }
            });
            return data;
        }

        document.querySelectorAll('.nilai-input').forEach(function(input) {
            input.addEventListener('input', function() {
                localStorage.setItem(draftKey, JSON.stringify({
                    waktu: new Date().toISOString(),
                    data: ambilSemuaNilai()
                }));
            });
        });

        // --- Tawarkan pulihkan draft kalau ada draft tersimpan sebelumnya (maks umur 24 jam) ---
        var draftTersimpan = localStorage.getItem(draftKey);
        if (draftTersimpan) {
            try {
                var draft = JSON.parse(draftTersimpan);
                var umurJam = (new Date() - new Date(draft.waktu)) / 3600000;

                if (umurJam > 24) {
                    // Draft basi (device dipakai bareng, guru lain login setelahnya) — buang tanpa tawaran
                    localStorage.removeItem(draftKey);
                } else {
                    var waktuStr = new Date(draft.waktu).toLocaleString('id-ID');
                    var konfirmasi = confirm(
                        'Ditemukan draft nilai yang belum tersimpan dari ' + waktuStr + '.\n' +
                        'Pulihkan draft tersebut ke form ini?'
                    );
                    if (konfirmasi) {
                        Object.keys(draft.data).forEach(function(key) {
                            var parts = key.split('_');
                            var input = document.querySelector('.nilai-input[data-siswa="' + parts[0] + '"][data-komponen="' + parts[1] + '"]');
                            if (input) input.value = draft.data[key];
                        });
                    } else {
                        localStorage.removeItem(draftKey);
                    }
                }
            } catch (e) {
                localStorage.removeItem(draftKey);
            }
        }
        // --- Cari nama field CSRF hidden input yang sudah ada di form (dari csrf_field()) ---
        function getCsrfInput() {
            // csrf_field() render <input type="hidden" name="csrf_test_name" value="...">
            // nama fieldnya bisa beda2 tergantung config, jadi cari input hidden pertama yg BUKAN updated_at/id_jadwal/dst
            return form.querySelector('input[type="hidden"][name]:not([name^="updated_at"]):not([name="id_jadwal"]):not([name="tanggal"])');
        }

        // --- Keep-alive: setiap 4 menit, ping server supaya session tidak expired + ambil token terbaru ---
        var csrfInput = getCsrfInput();
        var keepAliveTimer = setInterval(refreshCsrfToken, 4 * 60 * 1000);

        function refreshCsrfToken(callback) {
            fetch(csrfUrl, {
                    credentials: 'same-origin'
                })
                .then(function(res) {
                    if (res.status === 401) {
                        clearInterval(keepAliveTimer);
                        tampilkanPeringatanSessionHabis();
                        if (callback) callback(false);
                        return;
                    }
                    return res.json();
                })
                .then(function(data) {
                    if (data && data.valid && csrfInput) {
                        csrfInput.setAttribute('name', data.csrfName);
                        csrfInput.value = data.csrfHash;
                    }
                    if (callback) callback(true);
                })
                .catch(function() {
                    // network sempat putus — bukan berarti session habis, jangan panik, coba lagi nanti
                    if (callback) callback(true);
                });
        }

        function tampilkanPeringatanSessionHabis() {
            if (document.getElementById('peringatanSessionHabis')) return;
            var div = document.createElement('div');
            div.id = 'peringatanSessionHabis';
            div.className = 'nilai-alert nilai-alert-error';
            div.innerHTML = '<strong>Sesi Anda sudah habis.</strong> Nilai yang sudah diketik tetap aman tersimpan di browser ini. ' +
                'Silakan <a href="<?= base_url('login') ?>" style="text-decoration:underline;">login ulang</a>, lalu buka kembali halaman ini untuk memulihkan draft.';
            form.parentNode.insertBefore(div, form);
        }

        // --- Sebelum submit, pastikan token sudah paling baru dulu ---
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            refreshCsrfToken(function(masihValid) {
                if (masihValid) {
                    form.submit(); // lanjut submit normal (bukan AJAX), sekarang dgn token yg sudah pasti segar
                }
                // kalau tidak valid, peringatan sudah ditampilkan oleh refreshCsrfToken(), submit dibatalkan
            });
        });

        // --- Hapus draft otomatis kalau submit sukses (halaman ini dimuat ulang dengan flashdata success) ---
        <?php if (session()->getFlashdata('success')): ?>
            localStorage.removeItem(draftKey);
        <?php endif; ?>
    })();
</script>

<?= $this->include('guru/nilai/_import_section', [
    'komponenList' => $komponenList,
    'riwayatImportPerKomponen' => $riwayatImportPerKomponen ?? [],
]) ?>

<?= $this->endSection() ?>