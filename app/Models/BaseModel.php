<?php

namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    protected bool $auditLog = true;

    protected $afterInsert = ['logInsert'];
    protected $afterUpdate = ['logUpdate'];
    protected $afterDelete = ['logDelete'];

    public function skipAudit(bool $skip = true): static
    {
        $this->auditLog = !$skip;
        return $this;
    }

    protected function logInsert(array $eventData): array
    {
        $this->logAksi('tambah', $eventData['id'] ?? null);
        return $eventData;
    }

    protected function logUpdate(array $eventData): array
    {
        $this->logAksi('ubah', $eventData['id'] ?? null);
        return $eventData;
    }

    protected function logDelete(array $eventData): array
    {
        $this->logAksi('hapus', $eventData['id'] ?? null);
        return $eventData;
    }

    private function logAksi(string $tipe, $id): void
    {
        if (!$this->auditLog) {
            return;
        }

        $idText = is_array($id) ? implode(',', $id) : (string) $id;

        (new AuditLogModel())->catat(
            session('id_user'),
            session('username') ?? 'system',
            $tipe . '_' . $this->table,
            "ID: {$idText}"
        );
    }
}
