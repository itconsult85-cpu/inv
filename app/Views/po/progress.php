<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Progress PO
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/po/data')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .po-progress-steps {
        position: relative;
        display: flex;
        justify-content: space-between;
        margin: 8px 20px 24px;
    }

    .po-progress-steps .line-bg {
        position: absolute;
        top: 15px;
        left: 6%;
        right: 6%;
        height: 2px;
        background: #e9ecef;
        z-index: 0;
    }

    .po-progress-steps .line-fg {
        position: absolute;
        top: 15px;
        left: 6%;
        height: 2px;
        background: #28a745;
        z-index: 0;
    }

    .po-progress-steps .step {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
    }

    .po-progress-steps .step .dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 600;
    }

    .po-progress-steps .step.done .dot {
        background: #28a745;
        color: #fff;
    }

    .po-progress-steps .step.active .dot {
        background: #fff;
        border: 2px solid #007bff;
        color: #007bff;
    }

    .po-progress-steps .step.pending .dot {
        background: #e9ecef;
        color: #adb5bd;
    }

    .po-progress-steps .step .step-label {
        margin-top: 8px;
        font-size: 11px;
        text-align: center;
        color: #495057;
    }

    .po-progress-steps .step.active .step-label {
        color: #007bff;
        font-weight: 600;
    }

    .po-progress-steps .step.pending .step-label {
        color: #adb5bd;
    }

    .po-progress-detail .detail-row {
        padding: 10px 0;
        border-bottom: 1px solid #f1f3f5;
    }

    .po-progress-detail .detail-row:last-child {
        border-bottom: none;
    }

    .po-progress-detail .detail-sub {
        margin: 8px 0 2px 23px;
        padding-left: 12px;
        border-left: 2px solid #e9ecef;
        font-size: 12px;
        color: #495057;
    }
</style>

<table class="table table-sm table-borderless" style="max-width: 600px;">
    <tr>
        <th style="width: 180px;">No. PO</th>
        <td>: <?= esc($nopo) ?></td>
    </tr>
    <tr>
        <th>Tanggal PO</th>
        <td>: <?= date('d-m-Y', strtotime($tanggal)) ?></td>
    </tr>
    <tr>
        <th>Pelanggan</th>
        <td>: <?= esc($namapelanggan) ?></td>
    </tr>
</table>

<?php
    $badgeClass = [
        'tidak_ada' => 'secondary', 'belum' => 'warning', 'sebagian' => 'warning', 'selesai' => 'success',
        'sudah' => 'success', 'lunas' => 'success',
    ];
    $doneCount = count(array_filter($progress['steps'], fn($s) => $s['state'] === 'done'));
    $numGaps = count($progress['steps']) - 1;
    $gapsCompleted = max(0, $doneCount - 1);
    $lineFgPercent = $numGaps > 0 ? round($gapsCompleted / $numGaps * 88) : 0;
?>
<div class="card">
    <div class="card-header">
        <strong>Progress PO</strong>
    </div>
    <div class="card-body">
        <div class="po-progress-steps">
            <div class="line-bg"></div>
            <div class="line-fg" style="width: <?= $lineFgPercent ?>%;"></div>
            <?php foreach ($progress['steps'] as $i => $step) : ?>
                <div class="step <?= $step['state'] ?>">
                    <div class="dot">
                        <?= $step['state'] === 'done' ? '<i class="fa fa-check"></i>' : ($i + 1) ?>
                    </div>
                    <span class="step-label"><?= esc($step['label']) ?></span>
                </div>
            <?php endforeach ?>
        </div>

        <div class="po-progress-detail">
            <div class="detail-row">
                <div class="d-flex align-items-center justify-content-between">
                    <span><i class="fa fa-box mr-2 text-muted"></i>Pengadaan material</span>
                    <span class="badge badge-<?= $badgeClass[$progress['material']['status']] ?>"><?= esc($progress['material']['badge']) ?></span>
                </div>
                <?php if ($progress['material']['list']) : ?>
                    <div class="detail-sub">
                        <?php foreach ($progress['material']['list'] as $item) : ?>
                            <div><?= esc($item['no_po']) ?> &middot; <?= esc($item['supplier_nama']) ?> &middot; <?= esc($item['status']) ?></div>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>
            </div>

            <div class="detail-row">
                <div class="d-flex align-items-center justify-content-between">
                    <span><i class="fa fa-truck mr-2 text-muted"></i>Pengiriman ke pelanggan</span>
                    <span class="badge badge-<?= $badgeClass[$progress['kirim']['status']] ?>"><?= esc($progress['kirim']['badge']) ?></span>
                </div>
                <?php if ($progress['kirim']['shipments']) : ?>
                    <div class="detail-sub">
                        <?php foreach ($progress['kirim']['shipments'] as $idx => $shipment) : ?>
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Pengiriman <?= $idx + 1 ?>: <?= esc($shipment['faktur']) ?> &middot; <?= date('d-m-Y', strtotime($shipment['tglfaktur'])) ?> &middot; <?= number_format((float) $shipment['qtykeluar'], 0, ',', '.') ?> pcs</span>
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        onclick='lihatBtb(<?= json_encode([
                                            'faktur' => $shipment['faktur'],
                                            'list' => $shipment['btb_list'] ?? [],
                                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>
                                    <i class="fa fa-file-alt"></i> Lihat BTB
                                </button>
                            </div>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>
            </div>

            <?php
                $closingKeluarAktif = array_values(array_filter($progress['closing']['keluar'] ?? [], fn($log) => empty($log['reopened_at'])));
                $closingMasukAktif = array_values(array_filter($progress['closing']['masuk'] ?? [], fn($log) => empty($log['reopened_at'])));
            ?>
            <?php if ($closingKeluarAktif || $closingMasukAktif) : ?>
            <div class="detail-row">
                <div class="d-flex align-items-center justify-content-between">
                    <span><i class="fa fa-arrows-alt-h mr-2 text-muted"></i>Item Ditutup / Dipindah</span>
                </div>
                <div class="detail-sub">
                    <?php foreach ($closingKeluarAktif as $log) : ?>
                        <?php
                            $isReopened = !empty($log['reopened_at']);
                            $reopenInfo = $isReopened
                                ? ' &middot; dibuka kembali ' . date('d-m-Y H:i', strtotime($log['reopened_at'])) . ' oleh ' . esc($log['reopened_by'] ?: '-') . (!empty($log['reopen_note']) ? ' &middot; ' . esc($log['reopen_note']) : '')
                                : '';
                            $statusText = $isReopened ? ' <span class="badge badge-light">Dibuka kembali</span>' : ' <span class="badge badge-warning">Ditutup</span>';
                        ?>
                        <?php if (!empty($log['nopo_tujuan'])) : ?>
                            <div class="<?= $isReopened ? 'text-muted' : '' ?>"><?= esc($log['kodebrg']) ?> &middot; <?= number_format((float) $log['qty_dipindah'], 0, ',', '.') ?> pcs ditutup, dicatat sebagai bagian dari PO <a href="<?= site_url('po/edit/' . sha1($log['nopo_tujuan'])) ?>"><?= esc($log['nopo_tujuan']) ?></a> &middot; <?= date('d-m-Y', strtotime($log['ditutup_pada'])) ?> oleh <?= esc($log['ditutup_oleh'] ?: '-') ?><?= $statusText ?><?= $reopenInfo ?></div>
                        <?php else : ?>
                            <div class="<?= $isReopened ? 'text-muted' : '' ?>"><?= esc($log['kodebrg']) ?> &middot; <?= number_format((float) $log['qty_dipindah'], 0, ',', '.') ?> pcs disesuaikan (qty salah input, tidak dipindah kemana-mana) &middot; <?= date('d-m-Y', strtotime($log['ditutup_pada'])) ?> oleh <?= esc($log['ditutup_oleh'] ?: '-') ?><?= $statusText ?><?= $reopenInfo ?></div>
                        <?php endif ?>
                    <?php endforeach ?>
                    <?php foreach ($closingMasukAktif as $log) : ?>
                        <?php $isReopened = !empty($log['reopened_at']); ?>
                        <div class="<?= $isReopened ? 'text-muted' : '' ?>">Qty <?= esc($log['kodebrg']) ?> di PO ini sebagian (<?= number_format((float) $log['qty_dipindah'], 0, ',', '.') ?> pcs) sebenarnya bagian dari PO <a href="<?= site_url('po/edit/' . sha1($log['nopo_asal'])) ?>"><?= esc($log['nopo_asal']) ?></a> yang ditutup &middot; <?= date('d-m-Y', strtotime($log['ditutup_pada'])) ?><?= $isReopened ? ' &middot; close asal sudah dibuka kembali' : '' ?></div>
                    <?php endforeach ?>
                </div>
            </div>
            <?php endif ?>

            <div class="detail-row">
                <div class="d-flex align-items-center justify-content-between">
                    <span><i class="fa fa-file-invoice mr-2 text-muted"></i>Penagihan</span>
                    <span class="badge badge-<?= $badgeClass[$progress['tagih']['status']] ?>"><?= esc($progress['tagih']['badge']) ?></span>
                </div>
                <?php if ($progress['tagih']['invoices']) : ?>
                    <div class="detail-sub">
                        <?php foreach ($progress['tagih']['invoices'] as $invoice) : ?>
                            <div class="d-flex align-items-center justify-content-between">
                                <span>
                                    <a href="<?= site_url('invoiceOut/detail/' . $invoice['id']) ?>"><?= esc($invoice['invoice_no']) ?></a>
                                    &middot; <?= date('d-m-Y', strtotime($invoice['invoice_date'])) ?>
                                    &middot; Rp <?= number_format((float) $invoice['grand_total'], 0, ',', '.') ?>
                                </span>
                                <span class="badge badge-<?= ($invoice['status_bayar'] ?? 'Belum Lunas') === 'Lunas' ? 'success' : 'warning' ?>"><?= esc($invoice['status_bayar'] ?? 'Belum Lunas') ?></span>
                            </div>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>

<script>
    function escapeHtmlProgressPo(value) {
        return String(value ?? '').replace(/[&<>"']/g, function(match) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[match];
        });
    }

    function lihatBtb(data) {
        const list = data.list || [];

        let bodyHtml;
        if (list.length === 0) {
            bodyHtml = '<div class="text-muted">Belum ada BTB untuk surat jalan ini.</div>';
        } else {
            bodyHtml = list.map(function(item, idx) {
                const fileLink = item.btb_file_url
                    ? `<a href="${escapeHtmlProgressPo(item.btb_file_url)}" target="_blank">${escapeHtmlProgressPo(item.btb_original_name || 'Lihat file BTB')}</a>`
                    : '<span class="text-muted">Belum ada file BTB.</span>';

                return `
                    <div class="mb-2 pb-2" style="border-bottom:1px solid #eee;">
                        <div><strong>No BTB${list.length > 1 ? ' ' + (idx + 1) : ''}:</strong> ${item.no_btb ? escapeHtmlProgressPo(item.no_btb) : '-'}</div>
                        <div><strong>File:</strong> ${fileLink}</div>
                    </div>
                `;
            }).join('');
        }

        showBootstrapModal({
            title: 'Dokumen BTB',
            html: `
                <div class="text-left">
                    <div class="mb-2"><strong>No Surat Jalan:</strong> ${escapeHtmlProgressPo(data.faktur)}</div>
                    ${bodyHtml}
                </div>
            `,
            icon: 'info'
        });
    }
</script>
<?= $this->endSection('isi') ?>
