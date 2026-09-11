<?php

namespace App\Controllers;

use App\Libraries\AccessControl;
use App\Libraries\MaintenanceMode;

class Maintenance extends BaseController
{
    public function index()
    {
        if (!AccessControl::can('utility.maintenance_mode.view')) {
            return redirect()->to(site_url('main/index'));
        }

        $activeMap = MaintenanceMode::activeMap();
        $features = [];
        foreach (MaintenanceMode::targets() as $target) {
            $info = $activeMap[$target['key']] ?? null;
            $features[] = [
                'key' => $target['key'],
                'label' => $target['label'],
                'section_label' => $target['section_label'],
                'aktif' => $info !== null,
                'pesan' => $info['pesan'] ?? '',
                'updated_by' => $info['updated_by'] ?? '',
                'updated_at' => $info['updated_at'] ?? '',
            ];
        }

        return view('maintenance/index', ['features' => $features]);
    }

    public function toggle()
    {
        if (!AccessControl::can('utility.maintenance_mode.manage')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Anda tidak punya akses ke fitur ini.']);
        }

        $featureKey = trim((string) $this->request->getPost('feature_key'));
        $aktif = (string) $this->request->getPost('aktif') === '1';
        $pesan = trim((string) $this->request->getPost('pesan'));

        $label = null;
        foreach (MaintenanceMode::targets() as $target) {
            if ($target['key'] === $featureKey) {
                $label = $target['label'];
                break;
            }
        }

        if ($label === null) {
            return $this->response->setJSON(['error' => 'Fitur tidak ditemukan.']);
        }

        if ($aktif) {
            MaintenanceMode::activate($featureKey, $label, $pesan, (string) session()->get('userid'));
        } else {
            MaintenanceMode::deactivate($featureKey);
        }

        return $this->response->setJSON(['success' => true]);
    }
}
