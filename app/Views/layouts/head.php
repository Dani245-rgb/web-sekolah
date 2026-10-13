<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $seoHead        = service('seoService');
    $seo            = $seo ?? $seoHead->halaman($title ?? '', '', ['path' => uri_string()]);
    $pengaturanHead = $seoHead->pengaturan();
    ?>
    <title><?= esc($seo['title']) ?></title>
    <meta name="description" content="<?= esc($seo['description'], 'attr') ?>">
    <meta name="robots" content="<?= esc($seo['robots'], 'attr') ?>">
    <link rel="canonical" href="<?= esc($seo['canonical'], 'attr') ?>">

    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="<?= esc($seo['type'], 'attr') ?>">
    <meta property="og:site_name" content="<?= esc($seo['siteName'], 'attr') ?>">
    <meta property="og:title" content="<?= esc($seo['title'], 'attr') ?>">
    <meta property="og:description" content="<?= esc($seo['description'], 'attr') ?>">
    <meta property="og:url" content="<?= esc($seo['canonical'], 'attr') ?>">
    <?php if (!empty($seo['image'])): ?>
        <meta property="og:image" content="<?= esc($seo['image'], 'attr') ?>">
    <?php endif; ?>
    <?php if (!empty($seo['terbit'])): ?>
        <meta property="article:published_time" content="<?= esc($seo['terbit'], 'attr') ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($seo['title'], 'attr') ?>">
    <meta name="twitter:description" content="<?= esc($seo['description'], 'attr') ?>">
    <?php if (!empty($seo['image'])): ?>
        <meta name="twitter:image" content="<?= esc($seo['image'], 'attr') ?>">
    <?php endif; ?>

    <?php foreach ($seo['jsonld'] as $blok): ?>
        <script type="application/ld+json">
            <?= $seoHead->jsonLdKeScript($blok) ?>
        </script>
    <?php endforeach; ?>

    <?php if (!empty($pengaturanHead['favicon'])): ?>
        <link rel="icon" type="image/png" href="<?= base_url('assets/uploads/sekolah/' . $pengaturanHead['favicon']) ?>">
    <?php else: ?>
        <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- 1. Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- 2. Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- 3. CSS custom kamu, urutan besar -> kecil -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive/desktop.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive/tablet.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive/mobile.css') ?>">

    <?= $this->renderSection('styles') ?>
</head>