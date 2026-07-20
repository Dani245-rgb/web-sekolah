<?= view('layouts/head') ?>

<body>

<?= view('layouts/topbar') ?>

<?= view('layouts/header') ?>

<?= view('layouts/navbar') ?>

<?= $this->renderSection('content') ?>

<?= view('layouts/footer') ?>

<!-- Variabel PHP untuk JavaScript -->
<script>
    const BASE_URL = "<?= base_url() ?>";
</script>

<!-- File JavaScript -->
<script src="<?= base_url('assets/js/script.js') ?>"></script>

</body>
</html>