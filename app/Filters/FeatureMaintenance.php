<?php

namespace App\Filters;

use App\Libraries\AccessControl;
use App\Libraries\MaintenanceMode;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Blokir akses ke SATU fitur yang lagi ditandai "sedang dalam perbaikan"
 * (lewat menu Mode Maintenance), tanpa ganggu fitur lain. Yang tetap bisa
 * masuk terus (biar bisa lanjut tes/benerin) itu: superadmin (idlevel 5)
 * DAN siapapun yang punya izin ngatur Mode Maintenance sendiri
 * (utility.maintenance_mode.manage) -- soalnya orang yang bisa
 * nyala/matiin maintenance itu pasti orang yang emang lagi nanganin
 * perbaikannya, jadi wajar nggak ikut keblokir sama maintenance yang dia
 * pasang sendiri.
 */
class FeatureMaintenance implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if ((int) session()->get('idlevel') === 5) {
            return;
        }

        if (AccessControl::can('utility.maintenance_mode.manage')) {
            return;
        }

        $path = method_exists($request, 'getPath') ? $request->getPath() : $request->getUri()->getPath();
        $uri = strtolower(trim($path, '/ '));

        if ($uri === '' || preg_match('#^(login|login/.*|main|main/.*)$#', $uri)) {
            return;
        }

        $feature = MaintenanceMode::targetForRoute($uri);
        if ($feature === null) {
            return;
        }

        $info = MaintenanceMode::info($feature['key']);
        if ($info === null) {
            return;
        }

        $pesan = trim((string) ($info['pesan'] ?? ''));

        if ($request->isAJAX()) {
            return service('response')->setStatusCode(503)->setJSON([
                'maintenance' => true,
                'error' => 'Fitur "' . $feature['label'] . '" sedang dalam perbaikan.' . ($pesan !== '' ? ' ' . $pesan : ''),
            ]);
        }

        return service('response')->setStatusCode(503)->setBody(
            view('maintenance/blocked', [
                'featureLabel' => $feature['label'],
                'pesan' => $pesan,
            ])
        );
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
