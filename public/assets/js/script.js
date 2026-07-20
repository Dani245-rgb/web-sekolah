// bagian header
const headerImages = [
    `${BASE_URL}/assets/images/hero_header/header1.jpg`,
    `${BASE_URL}/assets/images/hero_header/header2.jpg`,
    `${BASE_URL}/assets/images/hero_header/header3.jpg`,
];

const headerBg = document.getElementById("header-bg");

if (headerBg) {
    const randomImage = headerImages[Math.floor(Math.random() * headerImages.length)];
    headerBg.src = randomImage;
}

// unutk tombol src
document.querySelector('.search-box button').addEventListener('click', () => {
  document.querySelector('.search-box').classList.toggle('active');
});


// Widget tab berita: Terbaru / Populer / Trending
document.addEventListener("DOMContentLoaded", function () {
  const tabButtons = document.querySelectorAll(".berita-tabs .tab-btn");

  tabButtons.forEach(function (btn) {
    btn.addEventListener("click", function () {
      const target = btn.dataset.tab;

      document
        .querySelectorAll(".berita-tabs .tab-btn")
        .forEach((b) => b.classList.remove("active"));
      document
        .querySelectorAll(".berita-tabs .tab-content")
        .forEach((c) => c.classList.remove("active"));

      btn.classList.add("active");
      document.getElementById(target).classList.add("active");
    });
  });
});


// tombol scroll up
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const group = btn.closest('.berita-tabs');
        group.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        group.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        group.querySelector('#' + btn.dataset.tab).classList.add('active');
    });
});

const btnTop = document.getElementById('btnTop');

window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
        btnTop.classList.add('show');
    } else {
        btnTop.classList.remove('show');
    }
});

btnTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
