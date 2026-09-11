<?php

namespace App\Libraries;

class NoDoChecker
{
    public function findSource(string $noDo): ?string
    {
        $noDo = trim($noDo);
        if ($noDo === '') {
            return null;
        }

        $db = \Config\Database::connect();
        $sources = [
            ['table' => 'materialmasuk', 'field' => 'no_do', 'label' => 'Data Material Masuk'],
            ['table' => 'materialkeluar', 'field' => 'faktur', 'label' => 'Data Material Keluar'],
            ['table' => 'barangmasuk', 'field' => 'faktur', 'label' => 'Data Produk Masuk'],
            ['table' => 'barangkeluar', 'field' => 'faktur', 'label' => 'Data Produk Keluar'],
            ['table' => 'permintaanbarang', 'field' => 'permintaan', 'label' => 'Data Permintaan Transfer'],
            ['table' => 'permintaanbarangkirim', 'field' => 'faktur', 'label' => 'Data Pengiriman Transfer'],
            ['table' => 'rencana_pengiriman', 'field' => 'no_do', 'label' => 'Permintaan Pengiriman'],
        ];

        foreach ($sources as $source) {
            if (
                !$db->tableExists($source['table']) ||
                !$db->fieldExists($source['field'], $source['table'])
            ) {
                continue;
            }

            if ($db->table($source['table'])
                ->where($source['field'], $noDo)
                ->countAllResults() > 0
            ) {
                return $source['label'];
            }
        }

        return null;
    }
}
