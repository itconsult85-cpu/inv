<?php

namespace App\Filters;

use App\Libraries\AccessControl;
use CodeIgniter\HTTP\RequestInterface;

class FilterUrlHelper
{
    public static function urlDiizinkan(RequestInterface $request, string $aliasFilter): bool
    {
        $path = method_exists($request, 'getPath') ? $request->getPath() : $request->getUri()->getPath();
        $uri = strtolower(trim($path, '/ '));
        $userid = (string) session()->get('userid');

        $dynamicAllowed = AccessControl::routeAllowed($uri, $userid);
        if ($dynamicAllowed !== null) {
            return $dynamicAllowed;
        }

        $except = config('Filters')->globals['after'][$aliasFilter]['except'] ?? [];

        if (empty($except)) {
            return true;
        }

        foreach ($except as $path) {
            $path = str_replace('/', '\/', trim($path, '/ '));
            $path = strtolower(str_replace('*', '.*', $path));
            if (preg_match('#^' . $path . '$#', $uri) === 1) {
                return true;
            }
        }

        return false;
    }

    public static function masihAktif(): bool
    {
        $userid = session()->get('userid');
        if (!$userid) {
            return true;
        }

        $row = db_connect()->table('users')->select('useraktif')->where('userid', $userid)->get()->getRowArray();

        return $row === null || (string) $row['useraktif'] === '1';
    }
}
