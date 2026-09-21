document.addEventListener('DOMContentLoaded', function () {
    var toggleBtn = document.getElementById('navbar-toggle');
    var navMenu = document.getElementById('nav-menu');

    if (toggleBtn && navMenu) {
        toggleBtn.addEventListener('click', function () {
            var isOpen = navMenu.classList.toggle('open');
            toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    // Link dropdown pakai href="#" — selalu dicegah biar gak nge-jump/nambah "#" ke URL,
    // di ukuran layar manapun.
    document.querySelectorAll('.dropdown > a').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            // Breakpoint 1200px HARUS sama persis dengan breakpoint hamburger
            // di desktop.css (.navbar-toggle { display: block } di situ juga 1200px).
            // Di bawah breakpoint ini, dropdown-menu di CSS mode "display:none,
            // butuh class .open" — jadi harus di-toggle manual via JS.
            // Di atas breakpoint ini, dropdown-menu tetap pakai :hover dari style.css.
            if (window.innerWidth <= 1200) {
                var parentLi = link.closest('.dropdown');
                var sedangTerbuka = parentLi.classList.contains('open');

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

// Contextual navbar — sembunyi saat scroll ke bawah, muncul saat scroll ke atas
(function () {
  const navbar = document.querySelector('.navbar');
  if (!navbar) return;

  let lastScrollY = window.scrollY;
  let ticking = false;
  const threshold = 80;

  function onScroll() {
    const currentScrollY = window.scrollY;
    const menu = document.getElementById('nav-menu');
    const menuOpen = menu && menu.classList.contains('open');

    if (menuOpen) {
      navbar.classList.remove('nav-hidden');
      lastScrollY = currentScrollY;
      ticking = false;
      return;
    }

    if (currentScrollY < threshold) {
      navbar.classList.remove('nav-hidden');
    } else if (currentScrollY > lastScrollY) {
      navbar.classList.add('nav-hidden');
    } else {
      navbar.classList.remove('nav-hidden');
    }

    lastScrollY = currentScrollY;
    ticking = false;
  }

  window.addEventListener('scroll', () => {
    if (!ticking) {
      requestAnimationFrame(onScroll);
      ticking = true;
    }
  });
})();