<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CsrfTokenResponse implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Token diverifikasi dan diperbarui oleh filter CSRF bawaan.
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $response->setHeader(
            config('Security')->headerName,
            csrf_hash()
        );
    }
}
