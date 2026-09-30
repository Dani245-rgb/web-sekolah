<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AuditLog extends BaseController
{
    protected AuditLogModel $auditLogModel;

    public function __construct()
    {
        if (!session()->get('is_superadmin')) {
            throw new PageNotFoundException('Halaman tidak ditemukan.');
        }

        $this->auditLogModel = new AuditLogModel();
    }

    public function index()
    {
        $filter = $this->ambilFilter();

        $data['logs']       = $this->auditLogModel->filterQuery($filter)->paginate(30);
        $data['pager']      = $this->auditLogModel->pager;
        $data['filter']     = $filter;
        $data['daftarAksi'] = $this->auditLogModel->daftarAksi();

        return view('admin/audit_log/index', $data);
    }

    public function exportPdf()
    {
        $filter = $this->ambilFilter();
        $logs   = $this->auditLogModel->filterQuery($filter)->findAll();

        $html = view('admin/audit_log/pdf', [
            'logs'   => $logs,
            'filter' => $filter,
        ]);

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L', // landscape, kolomnya lumayan banyak
            'margin_top'    => 12,
            'margin_bottom' => 12,
        ]);
        $mpdf->SetTitle('Audit Log');
        $mpdf->WriteHTML($html);

        $isi = $mpdf->Output('', Destination::STRING_RETURN);

        return $this->response->download('audit-log-' . date('Y-m-d_His') . '.pdf', $isi, true);
    }

    public function exportExcel()
    {
        $filter = $this->ambilFilter();
        $logs   = $this->auditLogModel->filterQuery($filter)->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Audit Log');

        $headers = ['No', 'Waktu', 'User', 'Aksi', 'Keterangan', 'IP Address'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EEF1FF');

        helper('excel');

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
        $sheet->getStyle("B2:B{$row}")->getAlignment()->setWrapText(false);
        $sheet->getStyle("E2:E{$row}")->getAlignment()->setWrapText(true);

        ob_start();
        (new Xlsx($spreadsheet))->save('php://output');
        $isi = ob_get_clean();

        return $this->response->download('audit-log-' . date('Y-m-d_His') . '.xlsx', $isi, true);
    }

    /**
     * Ambil parameter filter dari query string (dipakai bareng oleh index & export).
     * Nilai yang tidak valid dianggap tidak ada, bukan error.
     */
    private function ambilFilter(): array
    {
        return [
            'aksi'     => $this->teksAtauNull($this->request->getGet('aksi')),
            'username' => $this->teksAtauNull($this->request->getGet('username')),
            'dari'     => $this->tanggalAtauNull($this->request->getGet('dari')),
            'sampai'   => $this->tanggalAtauNull($this->request->getGet('sampai')),
        ];
    }

    private function teksAtauNull($nilai): ?string
    {
        if (!is_string($nilai)) {
            return null;
        }

        $nilai = trim($nilai);

        return $nilai === '' ? null : $nilai;
    }

    private function tanggalAtauNull($nilai): ?string
    {
        $nilai = $this->teksAtauNull($nilai);

        if ($nilai === null) {
            return null;
        }

        $tanggal = \DateTime::createFromFormat('Y-m-d', $nilai);

        return ($tanggal && $tanggal->format('Y-m-d') === $nilai) ? $nilai : null;
    }
}