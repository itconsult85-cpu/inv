<?= $this->extend('main/layout') ?>
<?= $this->section('judul') ?>Retur NG Produk / Barang<?= $this->endSection('judul') ?>
<?= $this->section('subjudul') ?><a href="<?= site_url('barangmasuk/data') ?>" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Kembali</a><?= $this->endSection('subjudul') ?>
<?= $this->section('isi') ?>
<?php if ($message = session()->getFlashdata('error')) : ?><div class="alert alert-danger"><?= esc($message) ?></div><?php endif ?>
<div class="card"><div class="card-header"><strong>Retur NG Produk</strong></div><div class="card-body">
<div class="row"><div class="col-md-4"><b>Faktur</b><br><?= esc($header['faktur']) ?></div><div class="col-md-4"><b>Supplier</b><br><?= esc($header['supnama'] ?? '-') ?></div><div class="col-md-4"><b>PO Keluar</b><br><?= esc($header['po_keluar_id'] ?? '-') ?></div></div><hr>
<?= form_open('produkretur/simpan') ?><input type="hidden" name="faktur" value="<?= esc($header['faktur']) ?>">
<div class="form-group"><label>Tanggal Retur</label><input type="date" name="tgl_retur" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Produk</th><th>Qty Masuk</th><th>Sudah NG</th><th>Sisa NG</th><th>Qty Retur NG</th><th>Keterangan</th></tr></thead><tbody>
<?php foreach ($details as $detail) : ?><tr><td><?= esc($detail['detbrgkode']) ?> - <?= esc($detail['detbrgnama']) ?></td><td><?= number_format((float) $detail['detjml'], 3, ',', '.') ?></td><td><?= number_format((float) $detail['qty_retur'], 3, ',', '.') ?></td><td><?= number_format((float) $detail['sisa_retur'], 3, ',', '.') ?></td><td><input type="number" step="0.001" min="0" max="<?= esc($detail['sisa_retur']) ?>" name="qty[<?= (int) $detail['id'] ?>]" class="form-control"></td><td><input type="text" name="keterangan[<?= (int) $detail['id'] ?>]" class="form-control" placeholder="NG"></td></tr><?php endforeach ?>
</tbody></table></div><div class="form-group"><label>Catatan</label><textarea name="catatan" class="form-control"></textarea></div><button class="btn btn-danger" type="submit"><i class="fa fa-save"></i> Simpan Retur NG</button><?= form_close() ?>
</div></div><?= $this->endSection('isi') ?>
