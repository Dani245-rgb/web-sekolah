<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;

class AuditLog extends BaseController
{
    /**
     * Ambil parameter filter dari query string (dipakai bareng oleh index & export)
     */
    private function ambilFilter(): array
    {
        return [
            'aksi'     => $this->request->getGet('aksi'),
            'username' => $this->request->getGet('username'),
            'dari'     => $this->request->getGet('dari'),
            'sampai'   => $this->request->getGet('sampai'),
        ];
    }

    public function index()
    {
        $auditLogModel = new AuditLogModel();
        $filter        = $this->ambilFilter();

        $data['logs']       = $auditLogModel->filterQuery($filter)->paginate(30);
        $data['pager']      = $auditLogModel->pager;
        $data['filter']     = $filter;
        $data['daftarAksi'] = $auditLogModel->daftarAksi();

        return view('admin/audit_log/index', $data);
    }

    public function exportPdf()
    {
        $auditLogModel = new AuditLogModel();
        $filter        = $this->ambilFilter();
        $logs          = $auditLogModel->filterQuery($filter)->findAll();

        $html = view('admin/audit_log/pdf', [
            'logs'   => $logs,
            'filter' => $filter,
        ]);

        $mpdf = new \Mpdf\Mpdf([
            'mode'         => 'utf-8',
            'format'       => 'A4-L', // landscape, kolomnya lumayan banyak
            'margin_top'   => 12,
            'margin_bottom' => 12,
        ]);
        $mpdf->SetTitle('Audit Log');
        $mpdf->WriteHTML($html);
        $mpdf->Output('audit-log-' . date('Y-m-d_His') . '.pdf', 'D');
    }

    public function exportExcel()
    {
        $auditLogModel = new AuditLogModel();
        $filter        = $this->ambilFilter();
        $logs          = $auditLogModel->filterQuery($filter)->findAll();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Audit Log');

        $headers = ['No', 'Waktu', 'User', 'Aksi', 'Keterangan', 'IP Address'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EEF1FF');

        $row = 2;
        foreach ($logs as $i => $log) {
            $sheet->fromArray([
                $i + 1,
                $log['created_at'],
                $log['username'] ?? '-',
                $log['aksi'],
                $log['keterangan'] ?? '-',
                $log['ip_address'] ?? '-',
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle("B2:B{$row}")->getAlignment()
            ->setWrapText(false);
        $sheet->getStyle("E2:E{$row}")->getAlignment()
            ->setWrapText(true);

        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'audit-log-' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}