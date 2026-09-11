<?php
$formatTanggalRencana = static function ($value): string {
    if (empty($value)) {
        return '';
    }

    $timestamp = strtotime((string) $value);
    return $timestamp ? date('d-m-Y', $timestamp) : (string) $value;
};
?>

<table class="table table-sm table-bordered table-hover">
    <thead>
        <tr>
            <th class="text-center">No</th>
            <th>No. PO</th>
            <th>Tanggal PO</th>
            <th>No Surat Jalan</th>
            <th>No BTB</th>
            <th>Kode Produk</th>
            <th>Nama Produk</th>
            <th class="text-right">Akan Dikirim</th>
            <th>Asal Gudang</th>
            <th class="text-center">#</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rencana as $index => $row) : ?>
            <tr>
                <td class="text-center"><?= $index + 1 ?></td>
                <td>
                    <input type="text"
                           class="form-control form-control-sm input-dokumen-rencana"
                           data-id="<?= $row['id'] ?>"
                           data-field="no_po"
                           value="<?= esc($row['no_po']) ?>"
                           placeholder="Isi No. PO">
                </td>
                <td>
                    <input type="text"
                           class="form-control form-control-sm input-dokumen-rencana"
                           data-id="<?= $row['id'] ?>"
                           data-field="tanggal_po"
                           value="<?= esc($formatTanggalRencana($row['tanggal_po'] ?? '')) ?>"
                           placeholder="dd-MM-YYYY"
                           inputmode="numeric">
                </td>
                <td>
                    <input type="text"
                           class="form-control form-control-sm input-dokumen-rencana"
                           data-id="<?= $row['id'] ?>"
                           data-field="no_do"
                           value="<?= esc($row['no_do']) ?>"
                           placeholder="Isi No Surat Jalan">
                </td>
                <td>
                    <input type="text"
                           class="form-control form-control-sm input-dokumen-rencana"
                           data-id="<?= $row['id'] ?>"
                           data-field="no_btb"
                           value="<?= esc($row['no_btb'] ?? '') ?>"
                           placeholder="Isi No BTB (opsional)">
                </td>
                <td><?= esc($row['kode_produk']) ?></td>
                <td><?= esc($row['nama_produk']) ?></td>
                <td class="text-right"><?= number_format($row['qty'], 0, ',', '.') ?> Pcs</td>
                <td><?= esc($row['gdgnama'] ?? '-') ?></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-danger" onclick="hapusRencana(<?= $row['id'] ?>)">
                        <i class="fa fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<?php if (!empty($rencana)) : ?>
    <div class="text-right mt-3">
        <button id="kirimProduk" class="btn btn-success">
            <i class="fa fa-arrow-circle-up"></i> Kirim Produk
        </button>
    </div>
<?php endif ?>
