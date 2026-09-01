# Catatan: Fix Opsional — Password DB di `BackupDatabase.php`

## Status
**OPSIONAL** — belum wajib diterapkan sekarang. Simpan catatan ini supaya tidak lupa, terapkan nanti **kalau** project di-deploy ke shared hosting.

---

## Masalahnya apa?

File: `app/Controllers/Admin/BackupDatabase.php`, method `create()`.

Saat proses backup database berjalan, PHP menjalankan command `mysqldump` lewat `exec()`. Password database ikut ditempelkan sebagai argumen command:

```php
$passwordPart = $password !== '' ? '-p' . escapeshellarg($password) : '';
```

Selama command ini berjalan (biasanya cuma beberapa detik), password itu **sempat muncul di daftar proses sistem operasi** — bisa dilihat pakai command seperti `ps aux` di Linux.

## Kapan ini berbahaya?

Tergantung siapa yang punya akses ke server:

| Situasi | Risiko |
|---|---|
| Laragon lokal (laptop sendiri) | **Tidak ada risiko** — cuma kamu yang punya akses |
| VPS / server sendiri (dedicated) | **Tidak ada risiko** — server cuma dipakai untuk web sekolah ini |
| Shared hosting (server dipakai bareng user lain) | **Berisiko** — user lain yang juga punya akses shell (SSH) di server yang sama, secara teori bisa mengintip daftar proses dan menangkap password DB di momen backup berjalan |

## Kapan perlu diterapkan?

✅ Terapkan **sebelum go-live** kalau project akan di-deploy ke:
- Shared hosting (hosting murah yang satu server dipakai banyak website)

❌ **Tidak perlu** kalau tetap di:
- Laragon / XAMPP lokal
- VPS atau dedicated server milik sendiri

---

## Fix — Kode Lama vs Kode Baru

**File:** `app/Controllers/Admin/BackupDatabase.php`
**Method:** `create()`

### Kode lama

```php
$mysqldumpPath = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqldump.exe';
$passwordPart  = $password !== '' ? '-p' . escapeshellarg($password) : '';
$command = escapeshellarg($mysqldumpPath)
    . ' -h ' . escapeshellarg($hostname)
    . ' -P ' . escapeshellarg((string) $port)
    . ' -u ' . escapeshellarg($username)
    . ' ' . $passwordPart
    . ' ' . escapeshellarg($database)
    . ' > ' . escapeshellarg($filepath) . ' 2>&1';

exec($command, $output, $resultCode);
```

### Kode baru

Password dipindah dari argumen command ke environment variable `MYSQL_PWD`, sehingga tidak muncul di daftar proses sistem.

```php
$mysqldumpPath = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqldump.exe';

// Password dikirim lewat environment variable, bukan argumen command,
// supaya tidak muncul di daftar proses sistem (ps aux) selama backup berjalan.
$command = ($password !== '' ? 'set MYSQL_PWD=' . escapeshellarg($password) . ' && ' : '')
    . escapeshellarg($mysqldumpPath)
    . ' -h ' . escapeshellarg($hostname)
    . ' -P ' . escapeshellarg((string) $port)
    . ' -u ' . escapeshellarg($username)
    . ' ' . escapeshellarg($database)
    . ' > ' . escapeshellarg($filepath) . ' 2>&1';

exec($command, $output, $resultCode);
```

> **Catatan:** `set VAR=value && command` adalah sintaks Windows (cocok untuk Laragon). Kalau nanti deploy ke server Linux, sintaksnya beda:
> ```php
> $command = ($password !== '' ? 'MYSQL_PWD=' . escapeshellarg($password) . ' ' : '')
>     . escapeshellarg($mysqldumpPath)
>     . ' -h ' . escapeshellarg($hostname)
>     . ' -P ' . escapeshellarg((string) $port)
>     . ' -u ' . escapeshellarg($username)
>     . ' ' . escapeshellarg($database)
>     . ' > ' . escapeshellarg($filepath) . ' 2>&1';
> ```

---

## Ringkasan untuk dilaporkan ke guru pembimbing

> Ditemukan bahwa proses backup database menyertakan password di argumen command shell, yang secara teori bisa terlihat oleh user lain jika di-deploy ke **shared hosting**. Untuk deployment saat ini (Laragon lokal), risiko ini nol. Sudah didokumentasikan sebagai perbaikan yang akan diterapkan sebelum deployment produksi ke hosting.