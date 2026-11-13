<?= $this->extend('main/layout') ?>
<?= $this->section('judul') ?>Koreksi Retur Produk NG<?= $this->endSection('judul') ?>
<?= $this->section('subjudul') ?><a href="<?= site_url('barangmasuk/data') ?>" class="btn btn-warning"><i
        class="fa fa-arrow-left"></i> Kembali</a><?= $this->endSection('subjudul') ?>
<?= $this->section('isi') ?>
<?php if ($message = session()->getFlashdata('error')) : ?><div class="alert alert-danger"><?= esc($message) ?></div>
<?php endif ?>
<?php if ($message = session()->getFlashdata('success')) : ?><div class="alert alert-success"><?= esc($message) ?></div>
<?php endif ?>
<div class="card">
    <div class="card-header"><strong>Retur Produk NG</strong> — <?= esc($header['faktur']) ?></div>
    <div class="card-body">
        <p class="text-muted">Qty Retur adalah histori retur asli dan tidak diubah saat barang pengganti diterima. Qty
            Pengganti dan Sisa NG menunjukkan progres penggantian.</p>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>No Retur</th>
                        <th>Tanggal</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Qty Retur</th>
                        <th>Qty Pengganti</th>
                        <th>Sisa NG</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($returns as $row) : $formId = 'edit-retur-produk-' . (int) $row['id']; $terkunci = (float) ($row['qty_pengganti'] ?? 0) > 0.000001; ?><tr>
                        <td><?= esc($row['nomor_retur']) ?></td>
                        <td><?= esc($row['tgl_retur']) ?></td>
                        <td><?= esc($row['kode_barang']) ?></td>
                        <td><?= esc($row['detbrgnama'] ?: '-') ?></td>
                        <td><input form="<?= esc($formId) ?>" type="number" name="qty_retur"
                                class="form-control form-control-sm" min="0" max="<?= esc($row['detjml']) ?>"
                                step="0.001" value="<?= esc($row['qty_retur']) ?>" style="width:110px" <?= $terkunci ? 'disabled' : '' ?>></td>
                        <td><?= number_format((float) ($row['qty_pengganti'] ?? 0), 3, ',', '.') ?></td>
                        <td><span
                                class="badge <?= ((float) ($row['sisa_ng'] ?? 0) > 0) ? 'badge-danger' : 'badge-success' ?>"><?= number_format((float) ($row['sisa_ng'] ?? 0), 3, ',', '.') ?></span>
                        </td>
                        <td><input form="<?= esc($formId) ?>" type="text" name="keterangan"
                                class="form-control form-control-sm" value="<?= esc($row['keterangan']) ?>" <?= $terkunci ? 'disabled' : '' ?>></td>
                        <td>
                            <form id="<?= esc($formId) ?>" method="post"
                                action="<?= site_url('produkretur/koreksi') ?>"><input type="hidden"
                                    name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>"><input type="hidden" name="id"
                                    value="<?= (int) $row['id'] ?>"><button class="btn btn-sm btn-primary"
                                    type="submit" <?= $terkunci ? 'disabled' : '' ?>><i class="fa fa-save"></i> Simpan</button></form>
                            <?php if ($terkunci) : ?><span class="badge badge-secondary d-block mt-1">Terkunci: pengganti sudah diterima</span><?php else : ?><form method="post" action="<?= site_url('produkretur/hapusDetail') ?>" class="d-inline"
                                data-bootstrap-confirm="Batalkan retur ini dan kembalikan stok?"><input type="hidden"
                                    name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>"><input type="hidden" name="id"
                                    value="<?= (int) $row['id'] ?>"><button class="btn btn-sm btn-danger"
                                    type="submit"><i class="fa fa-undo"></i> Batalkan</button></form><?php endif ?>
                        </td>
                    </tr>
                    <?php endforeach ?><?php if (!$returns) : ?><tr>
                        <td colspan="9" class="text-center text-muted">Belum ada retur NG pada transaksi ini.</td>
                    </tr><?php endif ?></tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection('isi') ?>
