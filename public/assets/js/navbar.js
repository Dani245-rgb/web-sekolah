document.addEventListener('DOMContentLoaded', function () {
    var toggleBtn = document.getElementById('navbar-toggle');
    var navMenu = document.getElementById('nav-menu');

    if (toggleBtn && navMenu) {
        toggleBtn.addEventListener('click', function () {
            var isOpen = navMenu.classList.toggle('open');
            toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    // Di mobile, dropdown dibuka via tap (klik), bukan hover
    document.querySelectorAll('.dropdown > a').forEach(function (link) {
        link.addEventListener('click', function (e) {
            // Cuma override perilaku default di layar sempit (mobile)
            if (window.innerWidth <= 768) {
                e.preventDefault();
                var parentLi = link.closest('.dropdown');
                var sedangTerbuka = parentLi.classList.contains('open');

                // Tutup dropdown lain yang mungkin masih terbuka
                document.querySelectorAll('.dropdown.open').forEach(function (d) {
                    d.classList.remove('open');
                });

                if (!sedangTerbuka) {
                    parentLi.classList.add('open');
                }
            }
        });
    });
});

// Scroll-reveal (Intersection Observer) — elemen muncul halus saat discroll
(function () {
  const targets = document.querySelectorAll("[data-reveal]");
  if (!targets.length) return;

  if (!("IntersectionObserver" in window)) {
    targets.forEach((el) => el.classList.add("is-visible"));
    return;
  }

  const observer = new IntersectionObserver(
    (entries, obs) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          obs.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.15, rootMargin: "0px 0px -40px 0px" }
  );

  targets.forEach((el) => observer.observe(el));
})();