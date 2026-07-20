<aside class="sidebar">

    <!-- Widget Informasi Sekolah (ganti dari Pengumuman) -->
    <div class="widget-info">
        <div class="widget-info-header">
            <i class="fa-solid fa-bullhorn"></i>
            <h3>Informasi Sekolah</h3>
        </div>

        <ul class="info-list">
            <?php // foreach ($informasi as $i): 
            ?>
            <li>
                <a href="#">
                    <span class="info-dot"></span>
                    PPDB 2026 Dibuka
                </a>
            </li>
            <li>
                <a href="#">
                    <span class="info-dot"></span>
                    Libur Semester
                </a>
            </li>
            <li>
                <a href="#">
                    <span class="info-dot"></span>
                    Jadwal MPLS
                </a>
            </li>
            <li>
                <a href="#">
                    <span class="info-dot"></span>
                    Pengambilan Rapor
                </a>
            </li>
            <li>
                <a href="#">
                    <span class="info-dot"></span>
                    Lomba Barista
                </a>
            </li>
            <li>
                <a href="#">
                    <span class="info-dot"></span>
                    Kelulusan
                </a>
            </li>
            <?php // endforeach; 
            ?>
        </ul>

        <a href="<?= base_url('pengumuman') ?>" class="widget-info-more">
            Lihat Semua <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <!-- Widget Tab Berita: Terbaru | Populer | Hits -->
    <div class="berita-tabs">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="terbaru">Terbaru</button>
            <button class="tab-btn" data-tab="populer">Populer</button>
            <button class="tab-btn" data-tab="hits">Hits</button>
        </div>

        <div class="tab-content active" id="terbaru">
            <?php // foreach ($berita_terbaru as $b): 
            ?>
            <a href="#" class="tab-item">
                <span class="tab-item-title">Judul berita terbaru</span>
                <span class="tab-item-date">10 Juli 2026</span>
            </a>

            <a href="#" class="tab-item">
                <span class="tab-item-title">Workshop Digital Marketing Bersama Praktisi Industri</span>
                <span class="tab-item-date">14 Juli 2026</span>
            </a>

            <a href="#" class="tab-item">
                <span class="tab-item-title">Pelaksanaan MPLS Berjalan Lancar dan Meriah</span>
                <span class="tab-item-date">13 Juli 2026</span>
            </a>

            <a href="#" class="tab-item">
                <span class="tab-item-title">Siswa Tata Boga Mengikuti Pelatihan Barista</span>
                <span class="tab-item-date">12 Juli 2026</span>
            </a>

            <a href="#" class="tab-item">
                <span class="tab-item-title">Jadwal Pembagian Rapor Semester Genap</span>
                <span class="tab-item-date">11 Juli 2026</span>
            </a>
            <?php // endforeach; 
            ?>
        </div>

        <div class="tab-content" id="populer">
            <a href="#" class="tab-item">
                <span class="tab-item-title">SMK Attaufiqiyyah Raih Juara Umum LKS Tingkat Kabupaten</span>
                <span class="tab-item-date">10 Juli 2026</span>
            </a>

            <a href="#" class="tab-item">
                <span class="tab-item-title">Tim Barista Menjadi Juara 1 Kompetisi Nasional</span>
                <span class="tab-item-date">8 Juli 2026</span>
            </a>

            <a href="#" class="tab-item">
                <span class="tab-item-title">10 Alumni Diterima Bekerja Sebelum Lulus</span>
                <span class="tab-item-date">6 Juli 2026</span>
            </a>

            <a href="#" class="tab-item">
                <span class="tab-item-title">Peresmian Laboratorium Komputer Baru</span>
                <span class="tab-item-date">5 Juli 2026</span>
            </a>

            <a href="#" class="tab-item">
                <span class="tab-item-title">Kunjungan Industri ke Perusahaan Teknologi Nasional</span>
                <span class="tab-item-date">3 Juli 2026</span>
            </a>
        </div>

        <div class="tab-content" id="hits">
            <a href="#" class="tab-item">
                <span class="tab-item-title">5 Alasan Memilih Jurusan Rekayasa Perangkat Lunak</span>
                <span class="tab-item-date">9 Juli 2026</span>
            </a>

            <a href="#" class="tab-item">
                <span class="tab-item-title">Mengenal Fasilitas Modern di SMK Attaufiqiyyah</span>
                <span class="tab-item-date">7 Juli 2026</span>
            </a>

            <a href="#" class="tab-item">
                <span class="tab-item-title">Ekstrakurikuler Favorit yang Banyak Diminati Siswa</span>
                <span class="tab-item-date">5 Juli 2026</span>
            </a>

            <a href="#" class="tab-item">
                <span class="tab-item-title">Tips Sukses Mengikuti Seleksi PPDB</span>
                <span class="tab-item-date">4 Juli 2026</span>
            </a>

            <a href="#" class="tab-item">
                <span class="tab-item-title">Suasana Belajar Praktik di Jurusan Tata Boga</span>
                <span class="tab-item-date">2 Juli 2026</span>
            </a>
        </div>
    </div>

</aside>