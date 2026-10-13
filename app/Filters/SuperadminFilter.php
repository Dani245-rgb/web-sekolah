<?php

namespace App\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Penjaga halaman khusus superadmin. Satu-satunya tempat aturan ini ditulis.
 *
 * Urutan: login dan role Admin dicek dulu lewat RoleFilter (belum login atau
 * bukan Admin -> dialihkan ke /login, sama seperti sekarang). Baru setelah itu
 * flag is_superadmin dicek: bukan superadmin -> 404, sama seperti cek lama di
 * constructor controller.
 *
 * Flag di session sudah dicocokkan dengan database di setiap request oleh AuthFilter.
 */
class SuperadminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Delegasi ke RoleFilter supaya hasilnya sama persis, apa pun urutan filter-nya.
        $hasil = (new RoleFilter())->before($request, ['Admin']);

        if ($hasil !== null) {
            return $hasil;
        }

        if (!session()->get('is_superadmin')) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak dipakai
    }
}