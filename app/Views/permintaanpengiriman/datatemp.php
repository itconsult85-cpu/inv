<?php $total = array_sum(array_column($items, 'qty')); ?>
<div class="text-right mb-3">
    <h2 class="font-weight-bold">Total : <?= number_format($total, 0, ',', '.') ?> Pcs</h2>
</div>
<table class="table table-sm table-bordered table-hover">
    <thead>
        <tr>
            <th class="text-center">No</th>
            <th class="text-center">Kode Produk</th>
            <th class="text-center">Nama Produk</th>
            <th class="text-right">Jumlah</th>
            <th class="text-center">#</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $index => $item) : ?>
            <tr>
                <td class="text-center"><?= $index + 1 ?></td>
                <td class="text-center"><?= esc($item['kode_produk']) ?></td>
                <td><?= esc($item['nama_produk']) ?></td>
                <td class="text-right"><?= number_format($item['qty'], 0, ',', '.') ?> Pcs</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-danger" onclick="hapusItemPengiriman(<?= $item['id'] ?>)">
                        <i class="fa fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
