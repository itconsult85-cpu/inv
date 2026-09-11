<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Riwayat Perubahan Produk
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<button type="button" class="btn btn-warning" onclick="location.href=('/barang/index')">
    <i class="fa fa-undo"></i> Kembali
</button>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<div class="mb-3">
    <strong>Kode Produk:</strong> <?= esc($kodebarang) ?><br>
    <strong>Nama Produk:</strong> <?= esc($namabarang) ?>
</div>

<?php if (empty($riwayat)) : ?>
    <div class="alert alert-secondary">Belum ada riwayat perubahan untuk produk ini.</div>
<?php else : ?>
    <?php foreach ($riwayat as $item) : ?>
        <div class="card mb-3">
            <div class="card-header">
                <i class="fa fa-clock"></i>
                <?= date('d-m-Y H:i', strtotime($item['diubah_pada'])) ?>
                &mdash; diubah oleh <strong><?= esc($item['diubah_oleh'] ?: '-') ?></strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width:25%;">Field</th>
                            <th style="width:37.5%;">Data Lama</th>
                            <th style="width:37.5%;">Data Baru</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $fields = array_unique(array_merge(array_keys($item['data_lama']), array_keys($item['data_baru'])));
                        $adaPerubahan = false;
                        ?>
                        <?php foreach ($fields as $field) : ?>
                            <?php
                            $lama = $item['data_lama'][$field] ?? '-';
                            $baru = $item['data_baru'][$field] ?? '-';
                            if ((string) $lama === (string) $baru) continue;
                            $adaPerubahan = true;
                            ?>
                            <tr>
                                <td><strong><?= esc($field) ?></strong></td>
                                <td class="text-danger"><?= esc((string) $lama) ?></td>
                                <td class="text-success"><?= esc((string) $baru) ?></td>
                            </tr>
                        <?php endforeach ?>
                        <?php if (!$adaPerubahan) : ?>
                            <tr>
                                <td colspan="3" class="text-muted">Tidak ada perubahan nilai (disimpan ulang tanpa perubahan).</td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach ?>
<?php endif ?>
<?= $this->endSection('isi') ?>
