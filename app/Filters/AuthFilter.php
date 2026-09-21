<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')
                ->with('errors', ['login' => 'Silakan login terlebih dahulu.']);
        }

        // Re-check status akun ke DB setiap 5 menit, cegah akun yang baru
        // dinonaktifkan/dihapus/dikunci admin tetap bisa akses sampai session habis
        $lastCheck = session()->get('status_checked_at');
        if (!$lastCheck || (time() - $lastCheck) > 300) {
            $userModel = new UserModel();
            // find() otomatis skip soft-deleted user (useSoftDeletes=true di UserModel),
            // jadi $user akan null kalau akun sudah dihapus — tidak perlu cek deleted_at manual
            $user = $userModel->find(session()->get('id_user'));

            $tidakValid = !$user
                || $user['status'] !== 'Aktif'
                || (int) $user['role_id'] !== (int) session()->get('role_id')
                || (bool) $user['is_superadmin'] !== (bool) session()->get('is_superadmin')
                || ($user['locked_until'] !== null && strtotime($user['locked_until']) > time());

            if ($tidakValid) {
                session()->destroy();
                return redirect()->to('/login')
                    ->with('errors', ['login' => 'Sesi Anda telah berakhir. Silakan login kembali.']);
            }

            session()->set('status_checked_at', time());
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}