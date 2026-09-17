<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Retur Material NG ke Supplier / Vendor
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<a href="<?= site_url('materialmasuk/data') ?>" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali</a>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?php if ($message = session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger"><?= esc($message) ?></div>
<?php endif ?>

<div class="card card-outline card-danger">
    <div class="card-header">
        <h3 class="card-title">Input Retur NG Partial</h3>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3"><strong>No. Material Masuk</strong><br><?= esc($header['faktur']) ?></div>
            <div class="col-md-3"><strong>No. Surat Jalan</strong><br><?= esc($header['no_do'] ?: '-') ?></div>
            <div class="col-md-3"><strong>Tanggal Masuk</strong><br><?= esc($header['tglfaktur']) ?></div>
            <div class="col-md-3"><strong>Gudang</strong><br><?= esc($header['gdgnama'] ?: '-') ?></div>
        </div>

        <?= form_open('materialretur/simpan') ?>
        <?= csrf_field() ?>
        <input type="hidden" name="faktur" value="<?= esc($header['faktur']) ?>">

        <div class="form-row mb-3">
            <div class="form-group col-md-3">
                <label for="tgl_retur">Tanggal Retur</label>
                <input type="date" class="form-control" id="tgl_retur" name="tgl_retur" value="<?= esc(old('tgl_retur') ?: date('Y-m-d')) ?>" required>
            </div>
            <div class="form-group col-md-9">
                <label for="catatan">Catatan Retur</label>
                <input type="text" class="form-control" id="catatan" name="catatan" value="<?= esc(old('catatan')) ?>" placeholder="Contoh: Material rusak saat pemeriksaan QC">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Material</th>
                        <th>Nama Material</th>
                        <th>Supplier</th>
                        <th class="text-right">Qty Diterima</th>
                        <th class="text-right">Sudah Diretur</th>
                        <th class="text-right">Sisa Dapat Diretur</th>
                        <th style="width: 140px;">Qty Retur NG</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($details as $index => $detail) : ?>
                        <?php $sisa = max(0, (float) $detail['sisa_retur']); ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= esc($detail['matkode'] ?: $detail['detmatkode']) ?></td>
                            <td><?= esc($detail['matnama'] ?: '-') ?></td>
                            <td><?= esc($detail['supnama'] ?: ($header['supnama'] ?: '-')) ?></td>
                            <td class="text-right"><?= number_format((float) $detail['detjml'], 3, ',', '.') ?></td>
                            <td class="text-right"><?= number_format((float) $detail['qty_retur'], 3, ',', '.') ?></td>
                            <td class="text-right font-weight-bold"><?= number_format($sisa, 3, ',', '.') ?></td>
                            <td>
                                <input type="number" class="form-control form-control-sm qty-retur" name="qty[<?= (int) $detail['id'] ?>]" min="0" max="<?= esc($sisa) ?>" step="0.001" value="<?= esc(old('qty.' . $detail['id']) ?: '') ?>" <?= $sisa <= 0 ? 'disabled' : '' ?> <?= $sisa <= 0 ? '' : 'data-max="' . esc($sisa) . '"' ?>>
                            </td>
                            <td><input type="text" class="form-control form-control-sm" name="keterangan[<?= (int) $detail['id'] ?>]" value="<?= esc(old('keterangan.' . $detail['id'])) ?>" placeholder="NG / rusak"></td>
                        </tr>
                    <?php endforeach ?>
                    <?php if (!$details) : ?>
                        <tr><td colspan="9" class="text-center text-muted">Detail material masuk tidak ditemukan.</td></tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>

        <div class="alert alert-info mt-3">
            Isi qty retur pada satu atau beberapa baris. Sistem mendukung retur partial dan akan menolak qty yang melebihi sisa yang belum diretur atau stok yang tersedia.
        </div>
        <button type="submit" class="btn btn-danger" <?= !$details ? 'disabled' : '' ?>><i class="fa fa-undo"></i> Simpan Retur NG</button>
        <a href="<?= site_url('materialmasuk/data') ?>" class="btn btn-secondary">Batal</a>
        <?= form_close() ?>
    </div>
</div>
<script>
    document.querySelector('form').addEventListener('submit', function (event) {
        let valid = true;
        document.querySelectorAll('.qty-retur:not([disabled])').forEach(function (input) {
            const value = Number(input.value || 0);
            const max = Number(input.dataset.max || 0);
            if (value < 0 || value > max) {
                input.classList.add('is-invalid');
                valid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        if (!valid) {
            event.preventDefault();
            showBootstrapModal('Error', 'Qty retur tidak boleh melebihi sisa yang dapat diretur.', 'error');
        }
    });
</script>
<?= $this->endSection('isi') ?>
