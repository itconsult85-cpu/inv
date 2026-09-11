<?php if (!empty($riwayat)) : ?>
    <hr>
    <h5>Riwayat Pengiriman Permintaan Ini</h5>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th>Tanggal Kirim</th>
                    <th>No. PO</th>
                    <th>Tanggal PO</th>
                    <th>No Surat Jalan</th>
                    <th>Kode Produk</th>
                    <th>Nama Produk</th>
                    <th class="text-right">Qty Terkirim</th>
                    <th>Asal Gudang</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($riwayat as $index => $row) : ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td><?= !empty($row['tanggal_pengiriman']) ? date('d-m-Y', strtotime($row['tanggal_pengiriman'])) : '-' ?></td>
                        <td><?= esc($row['no_po']) ?></td>
                        <td><?= !empty($row['tanggal_po']) ? date('d-m-Y', strtotime($row['tanggal_po'])) : '-' ?></td>
                        <td><?= esc($row['no_do']) ?></td>
                        <td><?= esc($row['kode_produk']) ?></td>
                        <td><?= esc($row['nama_produk']) ?></td>
                        <td class="text-right"><?= number_format($row['qty_terkirim'], 0, ',', '.') ?> Pcs</td>
                        <td><?= esc($row['gdgnama'] ?? '-') ?></td>
                        <td class="text-center text-nowrap">
                            <?php if ((int) $row['qty'] > 1) : ?>
                                <button type="button"
                                        class="btn btn-sm btn-warning btn-pisahkan-po"
                                        title="Pisahkan ke PO lain"
                                        data-id="<?= (int) $row['id'] ?>"
                                        data-qty="<?= (int) $row['qty'] ?>"
                                        data-po="<?= esc($row['no_po'], 'attr') ?>"
                                        data-do="<?= esc($row['no_do'], 'attr') ?>"
                                        data-kode="<?= esc($row['kode_produk'], 'attr') ?>"
                                        data-idpel="<?= (int) $row['idpelanggan'] ?>">
                                    <i class="fas fa-code-branch"></i> Pisahkan PO
                                </button>
                            <?php endif ?>
                            <button type="button"
                                    class="btn btn-sm btn-danger btn-hapus-riwayat"
                                    title="Hapus item ini, stok akan dikembalikan"
                                    data-id="<?= (int) $row['id'] ?>">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
