// public/assets/js/siswa.js
// Tempat script khusus dashboard siswa.
// Contoh nanti: render Chart.js untuk Grafik Nilai / Grafik Absensi
// setelah modul Nilai dan Absensi dibuat.

document.addEventListener('DOMContentLoaded', function () {
    // Belum ada logic apapun untuk saat ini.
});

document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
        var btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.classList.add('btn-loading');
            btn.disabled = true;
        }
    });
});