<?php

namespace App\Services;

use App\Models\AuditLogModel;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

/**
 * Logika audit log sisi admin: normalisasi filter, ambil data (halaman / semua),
 * dan pembuatan file export PDF & Excel.
 *
 * Penjagaan akses (hanya superadmin) dan respons download tetap di controller.
 */
class AuditLogService
{
    public function __construct(
        private AuditLogModel $auditLogModel,
    ) {
    }

    /**
     * Ubah input mentah dari query string jadi filter bersih.
     * Nilai yang tidak valid dianggap tidak ada, bukan error.
     *
     * @param array{aksi?: mixed, username?: mixed, dari?: mixed, sampai?: mixed} $input
     */
    public function filterDariInput(array $input): array
    {
        return [
            'aksi'     => $this->teksAtauNull($input['aksi'] ?? null),
            'username' => $this->teksAtauNull($input['username'] ?? null),
            'dari'     => $this->tanggalAtauNull($input['dari'] ?? null),
            'sampai'   => $this->tanggalAtauNull($input['sampai'] ?? null),
        ];
    }

    /**
     * Data satu halaman untuk tampilan: log, pager, dan daftar aksi untuk dropdown filter.
     *
     * @return array{logs: array, pager: mixed, daftarAksi: array}
     */
    public function halaman(array $filter, int $perHalaman = 30): array
    {
        $logs = $this->auditLogModel->filterQuery($filter)->paginate($perHalaman);

        return [
            'logs'       => $logs,
            'pager'      => $this->auditLogModel->pager,
            'daftarAksi' => $this->auditLogModel->daftarAksi(),
        ];
    }

    /**
     * Semua log sesuai filter (untuk export), urutan sama dengan tampilan.
     */
    public function semua(array $filter): array
    {
        return $this->auditLogModel->filterQuery($filter)->findAll();
    }

    /**
     * Render HTML jadi isi file PDF (landscape, kolomnya lumayan banyak).
     */
    public function buatPdf(string $html): string
    {
        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',
            'margin_top'    => 12,
            'margin_bottom' => 12,
        ]);
        $mpdf->SetTitle('Audit Log');
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    /**
     * Susun log jadi isi file Excel (.xlsx). Sel teks disanitasi supaya tidak dibaca sebagai rumus.
     */
    public function buatExcel(array $logs): string
    {
        helper('excel');

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Audit Log');

        $sheet->fromArray(['No', 'Waktu', 'User', 'Aksi', 'Keterangan', 'IP Address'], null, 'A1');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EEF1FF');

        $row = 2;
        foreach ($logs as $i => $log) {
            $sheet->fromArray([
                $i + 1,
                $log['created_at'],
                sanitize_excel_cell($log['username'] ?? '-'),
                $log['aksi'],
                sanitize_excel_cell($log['keterangan'] ?? '-'),
                $log['ip_address'] ?? '-',
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $barisAkhir = max(2, $row - 1);
        $sheet->getStyle("B2:B{$barisAkhir}")->getAlignment()->setWrapText(false);
        $sheet->getStyle("E2:E{$barisAkhir}")->getAlignment()->setWrapText(true);

        ob_start();
        (new Xlsx($spreadsheet))->save('php://output');
        $isi = ob_get_clean();

        if ($isi === false || $isi === '') {
            throw new RuntimeException('File Excel audit log kosong atau gagal dibuat.');
        }

        return $isi;
    }

    private function teksAtauNull(mixed $nilai): ?string
    {
        if (!is_string($nilai)) {
            return null;
        }

        $nilai = trim($nilai);

        return $nilai === '' ? null : $nilai;
    }

    private function tanggalAtauNull(mixed $nilai): ?string
    {
        $nilai = $this->teksAtauNull($nilai);

        if ($nilai === null) {
            return null;
        }

        $tanggal = \DateTime::createFromFormat('Y-m-d', $nilai);

        return ($tanggal && $tanggal->format('Y-m-d') === $nilai) ? $nilai : null;
    }
}