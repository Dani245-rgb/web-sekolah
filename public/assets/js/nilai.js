document.addEventListener("DOMContentLoaded", function () {
  const formPengaturan = document.getElementById("form-pengaturan");

  // Halaman ini cuma ada di form pengaturan kategori & komponen —
  // kalau elemennya gak ketemu (misal lagi di halaman input/rekap), skip aja.
  if (!formPengaturan) return;

  const daftarKategori = document.getElementById("daftar-kategori");
  const btnTambahKategori = document.getElementById("btn-tambah-kategori");
  const totalBobotKategoriInfo = document.getElementById(
    "total-bobot-kategori-info",
  );

  let kategoriCounter = daftarKategori.querySelectorAll(
    ".nilai-kategori-block",
  ).length;

  // Tambah blok kategori baru (clone blok pertama, kosongkan isinya, sisakan 1 baris komponen)
  btnTambahKategori.addEventListener("click", function () {
    const blokPertama = daftarKategori.querySelector(".nilai-kategori-block");
    const blokBaru = blokPertama.cloneNode(true);
    const indexBaru = kategoriCounter++;

    blokBaru.dataset.kategoriIndex = indexBaru;
    blokBaru.querySelectorAll("input").forEach((input) => (input.value = ""));

    const tbody = blokBaru.querySelector("tbody");
    while (tbody.rows.length > 1) tbody.deleteRow(1);

    gantiNamaKategori(blokBaru, indexBaru, 0);
    blokBaru.querySelector(".total-bobot-komponen-info").textContent = "";

    daftarKategori.appendChild(blokBaru);
    hitungTotalBobotKategori();
  });

  // Event delegation untuk semua tombol di dalam daftar kategori
  daftarKategori.addEventListener("click", function (e) {
    // Hapus kategori (minimal harus tetap ada 1 kategori)
    if (e.target.classList.contains("btn-hapus-kategori")) {
      if (daftarKategori.querySelectorAll(".nilai-kategori-block").length > 1) {
        e.target.closest(".nilai-kategori-block").remove();
        hitungTotalBobotKategori();
      }
      return;
    }

    // Tambah baris komponen di dalam 1 kategori
    if (e.target.classList.contains("btn-tambah-komponen")) {
      const blok = e.target.closest(".nilai-kategori-block");
      const tbody = blok.querySelector("tbody");
      const barisBaru = tbody.rows[0].cloneNode(true);
      barisBaru
        .querySelectorAll("input")
        .forEach((input) => (input.value = ""));

      const komponenIndexBaru = tbody.rows.length;
      gantiNamaKomponenBaris(
        barisBaru,
        blok.dataset.kategoriIndex,
        komponenIndexBaru,
      );

      tbody.appendChild(barisBaru);
      return;
    }

    // Hapus baris komponen (minimal harus tetap ada 1 baris per kategori)
    if (e.target.classList.contains("btn-hapus-komponen")) {
      const tbody = e.target.closest("tbody");
      if (tbody.rows.length > 1) {
        e.target.closest("tr").remove();
        hitungTotalBobotKomponen(tbody.closest(".nilai-kategori-block"));
      }
    }
  });

  // Hitung ulang total bobot tiap kali ada input berubah
  formPengaturan.addEventListener("input", function (e) {
    if (e.target.classList.contains("input-bobot-kategori")) {
      hitungTotalBobotKategori();
    }
    if (e.target.name && e.target.name.includes("[komponen]")) {
      hitungTotalBobotKomponen(e.target.closest(".nilai-kategori-block"));
    }
  });

  function gantiNamaKategori(blok, kategoriIndex, komponenIndexAwal) {
    blok.querySelector(".input-nama-kategori").name =
      `kategori[${kategoriIndex}][nama]`;
    blok.querySelector(".input-bobot-kategori").name =
      `kategori[${kategoriIndex}][bobot]`;

    blok.querySelectorAll("tbody tr").forEach((tr, i) => {
      gantiNamaKomponenBaris(tr, kategoriIndex, komponenIndexAwal + i);
    });
  }

  function gantiNamaKomponenBaris(tr, kategoriIndex, komponenIndex) {
    tr.querySelector('input[type="text"]').name =
      `kategori[${kategoriIndex}][komponen][${komponenIndex}][nama]`;
    tr.querySelector('input[type="url"]').name =
      `kategori[${kategoriIndex}][komponen][${komponenIndex}][link]`;
    tr.querySelector('input[type="number"]').name =
      `kategori[${kategoriIndex}][komponen][${komponenIndex}][bobot]`;
  }

  function hitungTotalBobotKategori() {
    const bobotInputs = document.querySelectorAll(".input-bobot-kategori");
    let total = 0;
    bobotInputs.forEach((input) => (total += Number(input.value || 0)));

    totalBobotKategoriInfo.textContent =
      "Total bobot kategori: " +
      total +
      "%" +
      (total !== 100 ? " (harus 100%)" : " ✓");
    totalBobotKategoriInfo.classList.toggle("valid", total === 100);
  }

  function hitungTotalBobotKomponen(blok) {
    const info = blok.querySelector(".total-bobot-komponen-info");
    const bobotInputs = blok.querySelectorAll('tbody input[type="number"]');
    let total = 0;
    bobotInputs.forEach((input) => (total += Number(input.value || 0)));

    info.textContent =
      "Total bobot komponen: " +
      total +
      "%" +
      (total !== 100 ? " (harus 100%)" : " ✓");
    info.classList.toggle("valid", total === 100);
  }

  hitungTotalBobotKategori();
  daftarKategori
    .querySelectorAll(".nilai-kategori-block")
    .forEach(hitungTotalBobotKomponen);
});
