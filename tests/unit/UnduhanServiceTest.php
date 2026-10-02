<?php

namespace Tests\Unit;

use App\Models\UnduhanModel;
use App\Services\FileUploadService;
use App\Services\UnduhanService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;
use RuntimeException;

/**
 * @internal
 */
final class UnduhanServiceTest extends CIUnitTestCase
{
    private $model;
    private $uploader;
    private $koneksi;
    private UnduhanService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model    = $this->createMock(UnduhanModel::class);
        $this->uploader = $this->createMock(FileUploadService::class);
        $this->koneksi       = $this->createMock(BaseConnection::class);

        $this->koneksi->method('transStatus')->willReturn(true);

        $this->service = new UnduhanService($this->model, $this->uploader, $this->koneksi);
    }

    private function fileUpload(string $ekstensi, string $namaAsli): UploadedFile
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('isValid')->willReturn(true);
        $file->method('hasMoved')->willReturn(false);
        $file->method('getClientExtension')->willReturn($ekstensi);
        $file->method('getClientName')->willReturn($namaAsli);
        $file->method('getSize')->willReturn(1234);

        return $file;
    }

    public function testEkstensiTidakDiizinkanDitolak(): void
    {
        $this->uploader->expects($this->never())->method('simpan');
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->service->tambah(
            ['judul' => 'Virus', 'kategori' => 'Umum'],
            $this->fileUpload('exe', 'virus.exe')
        );
    }

    public function testTambahBerhasilMenyimpanInfoFile(): void
    {
        $this->uploader->method('simpan')->willReturn('abc123.pdf');
        $this->model->expects($this->once())
            ->method('insert')
            ->with($this->callback(
                static fn (array $d) => $d['file'] === 'abc123.pdf'
                    && $d['ekstensi'] === 'pdf'
                    && $d['nama_file_asli'] === 'surat.pdf'
                    && $d['ukuran_file'] === 1234
                    && $d['status'] === 'Draft'
            ))
            ->willReturn(1);

        $this->service->tambah(
            ['judul' => 'Surat Edaran', 'kategori' => 'Umum'],
            $this->fileUpload('pdf', 'surat.pdf')
        );
    }

    public function testInsertGagalFileDibuangDanRollback(): void
    {
        $this->uploader->method('simpan')->willReturn('abc123.pdf');
        $this->model->method('insert')->willReturn(false);

        $this->koneksi->expects($this->once())->method('transRollback');
        $this->uploader->expects($this->once())->method('hapus')->with('abc123.pdf', 'unduhan');

        $this->expectException(RuntimeException::class);
        $this->service->tambah(
            ['judul' => 'Surat Edaran', 'kategori' => 'Umum'],
            $this->fileUpload('pdf', 'surat.pdf')
        );
    }
}