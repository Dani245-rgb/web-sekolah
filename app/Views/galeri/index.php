<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <nav class="breadcrumb" aria-label="breadcrumb">
            <a href="<?= base_url('/') ?>">Beranda</a>
            <span class="sep">/</span>
            <span class="current">Galeri</span>
        </nav>

        <div class="section-heading">
            <h2>Galeri Sekolah</h2>
        </div>

        <!-- Filter kategori -->
        <div class="galeri-filter">
            <a href="<?= base_url('galeri') ?>"
                class="galeri-filter-btn <?= $kategori === null ? 'active' : '' ?>">Semua</a>
            <?php $kategoriList = ['Kegiatan', 'Fasilitas', 'Prestasi', 'Lainnya']; ?>
            <?php foreach ($kategoriList as $k): ?>
                <a href="<?= base_url('galeri?kategori=' . $k) ?>"
                    class="galeri-filter-btn <?= $kategori === $k ? 'active' : '' ?>"><?= $k ?></a>
            <?php endforeach; ?>
        </div>

        <div class="galeri-grid">
            <?php foreach ($galeri as $g): ?>
                <button type="button" class="galeri-item" title="<?= esc($g['judul']) ?>"
                    data-img="<?= base_url('uploads/galeri/' . $g['foto']) ?>"
                    data-title="<?= esc($g['judul']) ?>">
                    <img src="<?= base_url('uploads/galeri/' . $g['foto']) ?>" alt="<?= esc($g['judul']) ?>">
                </button>
            <?php endforeach; ?>

            <?php if (empty($galeri)): ?>
                <div class="empty-state">
                    <i class="bi bi-images"></i>
                    <p>Belum ada foto <?= $kategori ? 'untuk kategori ' . esc($kategori) : '' ?>.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Lightbox Modal -->
        <div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Pratinjau foto" hidden>
            <button type="button" class="lightbox-close" id="lightbox-close" aria-label="Tutup pratinjau">
                <i class="bi bi-x-lg"></i>
            </button>
            <figure class="lightbox-content">
                <img src="" alt="" id="lightbox-img">
                <figcaption id="lightbox-caption"></figcaption>
            </figure>
        </div>

    </div>
</main>

<script>
    (function() {
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');
        const lightboxCaption = document.getElementById('lightbox-caption');
        const closeBtn = document.getElementById('lightbox-close');
        let lastFocused = null;

        function openLightbox(imgSrc, title) {
            lastFocused = document.activeElement;
            lightboxImg.src = imgSrc;
            lightboxImg.alt = title;
            lightboxCaption.textContent = title;
            lightbox.hidden = false;
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
        }

        function closeLightbox() {
            lightbox.hidden = true;
            lightboxImg.src = '';
            document.body.style.overflow = '';
            if (lastFocused) lastFocused.focus();
        }

        document.querySelectorAll('.galeri-item').forEach(function(item) {
            item.addEventListener('click', function() {
                openLightbox(item.dataset.img, item.dataset.title);
            });
        });

        closeBtn.addEventListener('click', closeLightbox);

        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) closeLightbox();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !lightbox.hidden) closeLightbox();
        });
    })();
</script>

<?= $this->endSection() ?>