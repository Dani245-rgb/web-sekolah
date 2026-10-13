<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * Berkas untuk crawler: sitemap.xml, robots.txt, dan llms.txt.
 */
class BerkasSeo extends BaseController
{
    public function sitemap(): ResponseInterface
    {
        try {
            $isi = service('sitemapService')->xml();
        } catch (Throwable $e) {
            return $this->gagal($e, 'sitemap');
        }

        return $this->response->setContentType('application/xml', 'UTF-8')->setBody($isi);
    }

    public function robots(): ResponseInterface
    {
        try {
            $isi = service('seoService')->robotsTxt();
        } catch (Throwable $e) {
            return $this->gagal($e, 'robots');
        }

        return $this->response->setContentType('text/plain', 'UTF-8')->setBody($isi);
    }

    public function llms(): ResponseInterface
    {
        try {
            $isi = service('seoService')->llmsTxt();
        } catch (Throwable $e) {
            return $this->gagal($e, 'llms');
        }

        return $this->response->setContentType('text/plain', 'UTF-8')->setBody($isi);
    }

    private function gagal(Throwable $e, string $berkas): ResponseInterface
    {
        log_message('error', '[BerkasSeo] ' . $berkas . ': ' . $e->getMessage());

        return $this->response->setStatusCode(500)->setContentType('text/plain', 'UTF-8')->setBody('Berkas gagal dibuat.');
    }
}
