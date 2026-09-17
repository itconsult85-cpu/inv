<?php
$format = static fn ($value): string => number_format((float) $value, 2, ',', '.');
?>
<div class="modal fade" id="modalDetailNg" tabindex="-1" role="dialog" aria-labelledby="modalDetailNgLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailNgLabel"><i class="fas fa-edit mr-2 text-primary"></i>Koreksi Data NG</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small">
                    Koreksi dilakukan melalui transaksi sumber agar stok dan data NG diperbarui oleh trigger database secara konsisten.
                </div>
                <?php if (!$rows) : ?>
                    <div class="text-muted text-center py-3">Detail transaksi NG tidak ditemukan.</div>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No. Transaksi</th>
                                    <th>Produk</th>
                                    <th class="text-right">Material Masuk</th>
                                    <th class="text-right">Material Keluar</th>
                                    <th class="text-right">NG</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row) :
                                    $isProduct = trim((string) ($row['detbrgkode'] ?? '')) !== '';
                                    $editPath = $isProduct
                                        ? 'barangmasuk/edit/' . rawurlencode((string) $row['detfaktur'])
                                        : ((float) $row['beratmatmasuk'] > 0
                                            ? 'materialmasuk/edit/' . rawurlencode((string) $row['detfaktur'])
                                            : 'materialkeluar/edit/' . rawurlencode((string) $row['detfaktur']));
                                ?>
                                    <tr>
                                        <td><?= esc($row['tgl'] ?? '-') ?></td>
                                        <td><?= esc($row['detfaktur'] ?? '-') ?></td>
                                        <td><?= esc($isProduct ? ($row['detbrgkode'] ?? '-') : ($row['matkode'] ?? '-')) ?></td>
                                        <td class="text-right"><?= $format($row['beratmatmasuk'] ?? 0) ?></td>
                                        <td class="text-right"><?= $format($row['beratmatkeluar'] ?? 0) ?></td>
                                        <td class="text-right font-weight-bold"><?= $format($row['beratng'] ?? 0) ?></td>
                                        <td class="text-center">
                                            <a href="<?= site_url($editPath) ?>" class="btn btn-sm btn-primary" title="Buka transaksi sumber">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>
<script>
    $('#modalDetailNg').on('hidden.bs.modal', function() {
        $(this).remove();
    });
    $('#modalDetailNg').modal('show');
</script>
