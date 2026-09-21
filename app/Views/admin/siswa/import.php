<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Import Siswa Massal</h4>
    <p class="breadcrumb">Dashboard / Data Siswa / Import</p>
</div>

<div class="card">

    <a href="<?= base_url('admin/siswa/import/template') ?>" class="btn btn-secondary" style="margin-bottom:16px;">
        📥 Download Template Excel
    </a>

    <?php if ($jobBelumSelesai): ?>
    <div class="alert alert-warning" id="resume-box">
        <strong>Ada proses import yang belum selesai:</strong>
        <?= esc($jobBelumSelesai['nama_file']) ?>
        (<?= $jobBelumSelesai['baris_selesai'] ?>/<?= $jobBelumSelesai['total_baris'] ?> baris)
        <br>
        <button class="btn btn-primary btn-sm" style="margin-top:8px;"
            onclick="lanjutkanImport(<?= $jobBelumSelesai['id_import_log'] ?>, <?= $jobBelumSelesai['baris_selesai'] ?>, <?= $jobBelumSelesai['total_baris'] ?>)">
            Lanjutkan Import
        </button>
    </div>
    <?php endif; ?>

    <div id="upload-box">
        <div class="form-group">
            <label>File Excel (.xlsx)</label>
            <input type="file" id="file_excel" accept=".xlsx,.xls">
        </div>
        <button class="btn btn-primary" onclick="mulaiUpload()">Upload & Mulai Import</button>
    </div>

    <div id="progress-box" style="display:none; margin-top:20px;">
        <p id="progress-text">Memproses 0/0...</p>
        <div style="background:#e5e7eb; border-radius:8px; height:20px; overflow:hidden;">
            <div id="progress-bar" style="background:#4f46e5; height:100%; width:0%; transition:width .3s;"></div>
        </div>
        <p style="margin-top:8px;">
            Sukses: <span id="text-sukses">0</span> |
            Gagal: <span id="text-gagal">0</span>
        </p>
    </div>

    <div id="hasil-box" style="display:none; margin-top:20px;">
        <div class="alert alert-success" id="hasil-ringkasan"></div>
        <div id="hasil-error"
            style="max-height:300px; overflow-y:auto; background:#fef2f2; padding:12px; border-radius:8px; display:none;">
        </div>
    </div>

</div>

<script>
let idImportLog = null;
let totalBaris = 0;
let csrfName = '<?= csrf_token() ?>';
let csrfHash = '<?= csrf_hash() ?>';

function mulaiUpload() {
    const fileInput = document.getElementById('file_excel');
    if (!fileInput.files.length) {
        alert('Pilih file Excel dulu.');
        return;
    }

    const formData = new FormData();
    formData.append('file_excel', fileInput.files[0]);
    formData.append(csrfName, csrfHash);

    document.querySelector('#upload-box button').disabled = true;
    document.querySelector('#upload-box button').innerText = 'Mengupload...';

    fetch('<?= base_url("admin/siswa/import/upload") ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                document.querySelector('#upload-box button').disabled = false;
                document.querySelector('#upload-box button').innerText = 'Upload & Mulai Import';
                return;
            }

            idImportLog = data.id_import_log;
            totalBaris = data.total_baris;
            csrfHash = data.csrf_hash; // update token

            document.getElementById('upload-box').style.display = 'none';
            document.getElementById('progress-box').style.display = 'block';

            prosesBatch(0);
        });
}

function lanjutkanImport(id, barisSelesai, total) {
    idImportLog = id;
    totalBaris = total;

    document.getElementById('resume-box').style.display = 'none';
    document.getElementById('upload-box').style.display = 'none';
    document.getElementById('progress-box').style.display = 'block';

    updateProgressUI(barisSelesai, total, null, null);
    prosesBatch(barisSelesai);
}

function prosesBatch(offset) {
    fetch(`<?= base_url("admin/siswa/import/proses") ?>/${idImportLog}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                offset: offset,
                [csrfName]: csrfHash
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }

            csrfHash = data.csrf_hash; // update token tiap batch

            updateProgressUI(data.baris_selesai, data.total_baris, data.total_sukses, data.total_gagal);

            if (data.selesai) {
                tampilkanHasilAkhir(data);
            } else {
                prosesBatch(data.baris_selesai);
            }
        })
        .catch(err => {
            document.getElementById('progress-text').innerText =
                'Koneksi terputus. Muat ulang halaman ini untuk melanjutkan (progress tersimpan aman).';
        });
}

function updateProgressUI(barisSelesai, total, sukses, gagal) {
    const persen = total > 0 ? Math.round((barisSelesai / total) * 100) : 0;
    document.getElementById('progress-text').innerText = `Memproses ${barisSelesai}/${total}...`;
    document.getElementById('progress-bar').style.width = persen + '%';
    if (sukses !== null) document.getElementById('text-sukses').innerText = sukses;
    if (gagal !== null) document.getElementById('text-gagal').innerText = gagal;
}

function tampilkanHasilAkhir(data) {
    document.getElementById('progress-box').style.display = 'none';
    document.getElementById('hasil-box').style.display = 'block';
    document.getElementById('hasil-ringkasan').innerText =
        `Import selesai. Total: ${data.total_baris} baris — Sukses: ${data.total_sukses}, Gagal: ${data.total_gagal}.`;

    if (data.total_gagal > 0) {
        fetch(`<?= base_url("admin/siswa/import/status") ?>/${idImportLog}`)
            .then(res => res.json())
            .then(status => {
                const box = document.getElementById('hasil-error');
                box.style.display = 'block';
                const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                }[c]));

                box.innerHTML = '<strong>Detail baris gagal:</strong><br>' +
                    status.detail_gagal.map(e => `• ${esc(e)}`).join('<br>');   
            });
    }
}
</script>

<?= $this->endSection() ?>