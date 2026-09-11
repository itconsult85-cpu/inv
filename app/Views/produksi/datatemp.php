<table class="table table-sm table-hover table-bordered" style="width:100%">
    <thead>
        <tr>
            <th style="width: 2%; text-align: center;">No</th>
            <th style="text-align: center;">Kode Material</th>
            <th style="text-align: center;">Nama Material</th>
            <th style="width: 8%; text-align: center;">Satuan</th>
            <th style="width: 10%; text-align: center;">Qty Dipakai</th>
            <th style="width: 8%; text-align: center;">#</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $nomor = 1;
        foreach ($tampildata->getResultArray() as $row) :
        ?>
            <tr>
                <td style="width: 2%; text-align: center;"><?= $nomor++; ?></td>
                <td style="text-align: center;"><?= esc($row['det_kode_material']) ?></td>
                <td style="text-align: center;"><?= esc($row['det_nama_material']) ?></td>
                <td style="text-align: center;"><?= esc($row['det_satuan']) ?></td>
                <td style="text-align: right;"><?= number_format($row['det_qty'], 2, ',', '.') ?></td>
                <td style="width: 2%; text-align: center;">
                    <button type="button" class="btn btn-sm btn-danger tombol-hapus-item-produksi" data-id="<?= esc($row['id']) ?>">
                        <i class="fa fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        <?php
        endforeach
        ?>
    </tbody>
</table>
