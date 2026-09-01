<?= view('layouts/head') ?>

<body>

<?= view('layouts/ticker') ?>

<?= view('layouts/topbar') ?>

<?= view('layouts/header') ?>

<?= view('layouts/navbar') ?>

<?= $this->renderSection('content') ?>

<?= view('layouts/footer') ?>

<!-- Variabel PHP untuk JavaScript -->
<script>
    const BASE_URL = "<?= base_url() ?>";
</script>

<!-- Bootstrap JS (bundle sudah termasuk Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- File JavaScript kamu -->
<script src="<?= base_url('assets/js/script.js') ?>"></script>
<script src="<?= base_url('assets/js/navbar.js') ?>"></script>

</body>
</html>