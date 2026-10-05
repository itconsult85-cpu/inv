<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-1"><i class="fa fa-gift text-info"></i> Produk Sample Customer</h5>
        <small class="text-muted">Tanpa PO dan Invoice. Hanya tercatat sebagai surat jalan dan mengurangi stok produk.</small>
    </div>
    <a class="btn btn-primary" href="<?= site_url('sampleproduk/input') ?>"><i class="fa fa-plus"></i> Sample Baru</a>
</div>
<?php if (session()->getFlashdata('success')) : ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif ?>
<?php if (session()->getFlashdata('error')) : ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif ?>
<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <thead><tr><th>No Surat Jalan</th><th>Tanggal</th><th>Customer</th><th>Produk</th><th>Qty</th><th>Gudang</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row) : ?>
            <tr>
                <td><?= esc($row['faktur']) ?></td>
                <td><?= esc(date('d-m-Y', strtotime($row['tglfaktur']))) ?></td>
                <td><?= esc($row['pelnama'] ?? '-') ?></td>
                <td><?php foreach ($row['details'] as $detail) : ?><div><?= esc($detail['detbrgkode']) ?> - <?= esc($detail['namabarang']) ?></div><?php endforeach ?></td>
                <td class="text-right"><?php foreach ($row['details'] as $detail) : ?><div><?= number_format((float) $detail['detjml'], 0, ',', '.') ?></div><?php endforeach ?></td>
                <td><?= esc($row['gdgnama'] ?? '-') ?></td>
                <td class="text-nowrap">
                    <a class="btn btn-sm btn-primary" href="<?= site_url('sampleproduk/input/' . rawurlencode($row['faktur'])) ?>" title="Edit"><i class="fa fa-edit"></i></a>
                    <form class="d-inline" method="post" action="<?= site_url('sampleproduk/hapus/' . sha1($row['faktur'])) ?>" onsubmit="return confirm('Hapus surat jalan sample dan kembalikan stok?')"><?= csrf_field() ?><button class="btn btn-sm btn-danger" title="Hapus"><i class="fa fa-trash"></i></button></form>
                </td>
            </tr>
        <?php endforeach ?>
        <?php if (!$rows) : ?><tr><td colspan="7" class="text-center text-muted">Belum ada produk sample.</td></tr><?php endif ?>
        </tbody>
    </table>
</div>
