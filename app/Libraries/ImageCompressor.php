<?php

namespace App\Libraries;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageCompressor
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = ImageManager::usingDriver(Driver::class);
    }

    /**
     * Compress & resize gambar, lalu simpan ke path tujuan.
     *
     * @param string $sourcePath   Path sementara file upload (misal dari $file->getTempName())
     * @param string $destPath     Path lengkap tujuan penyimpanan (folder + nama file)
     * @param int    $maxWidth     Lebar maksimal (default 800)
     * @param int    $maxHeight    Tinggi maksimal (default 800)
     * @param int    $quality      Kualitas JPEG/WebP (default 75)
     */
    public function compressAndSave(
        string $sourcePath,
        string $destPath,
        int $maxWidth = 800,
        int $maxHeight = 800,
        int $quality = 75
    ): void {
        $image = $this->manager->decodePath($sourcePath);

        // Resize proporsional, tidak melebihi batas, tidak upscale gambar kecil
        $image->scaleDown($maxWidth, $maxHeight);

        $image->save($destPath, quality: $quality);
    }
}