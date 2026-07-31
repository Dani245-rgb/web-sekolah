<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Admin' ?> - Sistem Sekolah</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>

<body>

    <div class="admin-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <span>SISTEM SEKOLAH</span>
            </div>

            <div class="sidebar-profile">
                <div class="profile-avatar">A</div>
                <div>
                    <strong>Admin</strong>
                    <small><?= esc(session()->get('username')) ?></small>
                </div>
            </div>

            <nav class="sidebar-menu">
                <a href="<?= base_url('admin/dashboard') ?>"
                    class="<?= uri_string() === 'admin/dashboard' ? 'active' : '' ?>">
                    Dashboard
                </a>

                <p class="menu-label">DATA SEKOLAH</p>
                <a href="<?= base_url('admin/tahun-ajaran') ?>"
                    class="<?= strpos(uri_string(), 'admin/tahun-ajaran') === 0 ? 'active' : '' ?>">Tahun Ajaran</a>
                <a href="<?= base_url('admin/siswa') ?>"
                    class="<?= strpos(uri_string(), 'admin/siswa') === 0 ? 'active' : '' ?>">Siswa</a>
                <a href="<?= base_url('admin/guru') ?>"
                    class="<?= strpos(uri_string(), 'admin/guru') === 0 ? 'active' : '' ?>">Guru</a>
                <a href="<?= base_url('admin/kelas') ?>"
                    class="<?= strpos(uri_string(), 'admin/kelas') === 0 ? 'active' : '' ?>">Kelas</a>
                <a href="<?= base_url('admin/mapel') ?>"
                    class="<?= strpos(uri_string(), 'admin/mapel') === 0 ? 'active' : '' ?>">Mata Pelajaran</a>
                <a href="<?= base_url('admin/jadwal') ?>"
                    class="<?= strpos(uri_string(), 'admin/jadwal') === 0 ? 'active' : '' ?>">Jadwal Manager</a>

                <p class="menu-label">USER MANAGEMENT</p>
                <a href="<?= base_url('admin/user') ?>"
                    class="<?= strpos(uri_string(), 'admin/user') === 0 ? 'active' : '' ?>">User</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <header class="topbar">
                <div></div>
                <div class="topbar-right">
                    <span><?= esc(session()->get('username')) ?> (<?= esc(session()->get('role')) ?>)</span>
                    <a href="<?= base_url('logout') ?>" class="btn-logout">Logout</a>
                </div>
            </header>

            <div class="content-body">
                <?= $this->renderSection('content') ?>
            </div>
        </main>

    </div>

</body>

</html>