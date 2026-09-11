<?= $this->extend('main/layout') ?>
<?= $this->section('judul') ?>Catat Invoice In<?= $this->endSection('judul') ?>
<?= $this->section('subjudul') ?><a href="<?= site_url('invoiceIn/data') ?>" class="btn btn-warning"><i class="fas fa-undo"></i> Kembali</a><?= $this->endSection('subjudul') ?>
<?= $this->section('isi') ?>
<?php if(session('error')):?><div class="alert alert-danger"><?= session('error') ?></div><?php endif ?>
<?php
$selectedSourceLabel = '';
foreach ($sources as $row) {
    if ($selectedType === $row['source_type'] && $selectedSource === $row['source_no']) {
        $sourceLabel = $row['source_label'] ?? $row['source_no'];
        if ($row['source_type'] === 'po_keluar') {
            if (($row['jenis_po'] ?? '') === 'jasa') {
                $label = 'PO Keluar (Jasa)';
            } else {
                $label = ((int) ($row['kirim_langsung'] ?? 0) === 1) ? 'PO Keluar (Kirim Langsung)' : 'PO Keluar';
            }
        } else {
            $labelTipe = ['material' => 'Material Masuk', 'produk' => 'Produk Masuk'];
            $label = $labelTipe[$row['source_type']] ?? strtoupper((string) $row['source_type']);
        }
        $selectedSourceLabel = $label . ' | ' . $sourceLabel . ' | ' . date('d-m-Y', strtotime($row['source_date'])) . ' | ' . $row['supplier_name'];
        break;
    }
}
$shipments = $shipments ?? [];
$selectedFakturs = $selectedFakturs ?? [];
$sourceNoForSave = $sourceNoForSave ?? $selectedSource;
?>
<div class="form-group">
    <label>Pilih transaksi penerimaan / PO Keluar dari supplier/vendor</label>
    <div class="tre-inline-combobox tre-inline-combobox-solo" id="pilihSumberCombobox">
        <input type="hidden" id="pilihSumber" value="<?= $selectedSource ? esc($selectedType . '|' . $selectedSource) : '' ?>">
        <input type="text" id="pilihSumberText" class="form-control" value="<?= esc($selectedSourceLabel) ?>" placeholder="-- Pilih Sumber --">
        <div class="tre-inline-combobox-menu" id="pilihSumberComboboxMenu"></div>
    </div>
</div>
<?php if($selectedType === 'po_keluar' && $selectedSource && !empty($shipments)):?>
    <div class="card">
        <div class="card-header"><strong>List Surat Jalan berdasarkan PO yang dipilih</strong></div>
        <div class="card-body">
            <p class="text-muted">Pilih satu atau beberapa surat jalan yang mau digabung jadi 1 Invoice In. Surat jalan yang sudah dibuat Invoice In tidak muncul lagi di daftar ini.</p>
            <form method="get" action="<?= site_url('invoiceIn/create') ?>">
                <input type="hidden" name="type" value="po_keluar">
                <input type="hidden" name="source" value="<?= esc($selectedSource) ?>">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th style="width:5%;"></th>
                            <th>No. Surat Jalan</th>
                            <th>Tanggal</th>
                            <th>Item & Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($shipments as $shipment):?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="faktur[]" value="<?= esc($shipment['faktur']) ?>" <?= in_array($shipment['faktur'], $selectedFakturs, true) ? 'checked' : '' ?>>
                                </td>
                                <td><?= esc($shipment['no_surat_jalan'] ?: $shipment['faktur']) ?></td>
                                <td><?= $shipment['tglfaktur'] ? date('d-m-Y', strtotime($shipment['tglfaktur'])) : '-' ?></td>
                                <td>
                                    <?php foreach($shipment['lines'] as $line):?>
                                        <?= esc($line['item_code']) ?> - <?= number_format((float) $line['qty'], 0, ',', '.') ?> <?= esc($line['unit']) ?><br>
                                    <?php endforeach ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary"><i class="fas fa-eye"></i> Tampilkan Item Invoice</button>
            </form>
        </div>
    </div>
<?php endif ?>
<?php if($source):?>
<?= form_open_multipart('/invoiceIn/save') ?><input type="hidden" name="source_type" value="<?= esc($selectedType) ?>"><input type="hidden" name="source_no" value="<?= esc($sourceNoForSave) ?>">
<div class="row"><div class="col-md-3 form-group"><label>No. Invoice Supplier</label><input name="invoice_no" class="form-control" value="<?= old('invoice_no') ?>" required></div><div class="col-md-3 form-group"><label>Tanggal Invoice</label><input type="date" name="invoice_date" class="form-control" value="<?= old('invoice_date',date('Y-m-d')) ?>" required></div><div class="col-md-3 form-group"><label>Supplier</label><input class="form-control" value="<?= esc($source['header']['supplier_name']) ?>" readonly></div><div class="col-md-3 form-group"><label><?= $selectedType==='po_keluar'?'No. PO Keluar':'No. Transaksi Masuk' ?></label><input class="form-control" value="<?= esc($source['header']['source_label'] ?? $source['header']['source_no']) ?>" readonly></div></div>
<div class="form-group"><label>Upload File Invoice Supplier/Vendor</label><input type="file" name="invoice_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png"><small class="form-text text-muted">Opsional. Format yang didukung: PDF, JPG, JPEG, PNG. Maksimal 10 MB.</small></div>
<?php if($selectedType==='po_keluar'):?><div class="alert alert-info"><i class="fas fa-info-circle"></i> Harga satuan terisi otomatis dari PO Keluar. Sesuaikan kalau ada perbedaan dengan invoice supplier.</div><?php else:?><div class="alert alert-info"><i class="fas fa-info-circle"></i> Harga beli belum tersedia pada transaksi masuk. Isi harga satuan berdasarkan invoice supplier.</div><?php endif ?>
<div class="card">
    <div class="card-header"><strong>Pengaturan Invoice</strong></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 form-group">
                <label>PPN</label>
                <div class="input-group">
                    <div class="input-group-prepend"><div class="input-group-text"><input type="checkbox" name="ppn_enabled" id="ppnEnabled" value="1" <?= old('ppn_enabled', '1') ? 'checked' : '' ?>></div></div>
                    <input type="number" name="ppn_percent" id="ppnPercent" class="form-control" min="0" max="100" step="0.01" value="<?= old('ppn_percent', 11) ?>">
                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
                <small class="text-muted">Centang kalau invoice memakai PPN.</small>
            </div>
            <div class="col-md-4 form-group">
                <label>PPh 23</label>
                <div class="input-group">
                    <div class="input-group-prepend"><div class="input-group-text"><input type="checkbox" name="pph_enabled" id="pphEnabled" value="1" <?= old('pph_enabled', '1') ? 'checked' : '' ?>></div></div>
                    <input type="number" name="pph_percent" id="pphPercent" class="form-control" min="0" max="100" step="0.01" value="<?= old('pph_percent', 2) ?>">
                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
                <small class="text-muted">Centang kalau invoice memakai PPh 23.</small>
            </div>
            <div class="col-md-4 form-group mb-0">
                <label>DP</label>
                <div class="input-group">
                    <div class="input-group-prepend"><div class="input-group-text"><input type="checkbox" name="dp_enabled" id="dpEnabled" value="1" <?= old('dp_enabled') ? 'checked' : '' ?>></div></div>
                    <input type="number" name="dp_percent" id="dpPercent" class="form-control" min="0" max="100" step="0.01" value="<?= old('dp_percent', 50) ?>">
                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                </div>
                <small class="text-muted">Centang kalau supplier sudah menerima DP.</small>
            </div>
        </div>
    </div>
</div>
<div class="table-responsive"><table class="table table-bordered" id="detailIn"><thead><tr><th>No</th><th>No. Surat Jalan</th><th>Kode</th><th>Nama Item</th><th>Qty</th><th>UoM</th><th>Harga Satuan</th><th>Amount</th></tr></thead><tbody><?php foreach($source['lines'] as $i=>$line):?><tr><td><?= $i+1 ?></td><td><?= esc($line['no_surat_jalan'] ?: '-') ?></td><td><?= esc($line['item_code']) ?></td><td><?= esc($line['item_name']) ?></td><td class="qty text-right" data-value="<?= (float)$line['qty'] ?>"><?= number_format($line['qty'],0,',','.') ?></td><td><?= esc($line['unit']) ?></td><td><input type="number" min="0" step="0.01" name="harga[<?= esc($line['source_detail_id']) ?>]" class="form-control harga text-right" value="<?= old('harga.'.$line['source_detail_id'], $line['unit_price_default'] ?? 0) ?>" required></td><td class="amount text-right">Rp 0</td></tr><?php endforeach ?></tbody><tfoot>
<tr><th colspan="7" class="text-right">Subtotal</th><th id="subtotal" class="text-right">Rp 0</th></tr>
<tr id="rowPpn"><th colspan="7" class="text-right">PPN <span id="ppnLabel">11%</span></th><th id="ppn" class="text-right">Rp 0</th></tr>
<tr id="rowPph"><th colspan="7" class="text-right">PPh 23 (<span id="pphLabel">2%</span>)</th><th id="pph" class="text-right">Rp 0</th></tr>
<tr id="rowDp"><th colspan="7" class="text-right">DP <span id="dpLabel">50%</span></th><th id="dp" class="text-right">Rp 0</th></tr>
<tr><th colspan="7" class="text-right">Grand Total</th><th id="grand" class="text-right">Rp 0</th></tr>
</tfoot></table></div><button class="btn btn-success"><i class="fas fa-save"></i> Simpan Invoice In</button><?= form_close() ?>
<?php elseif($selectedSource && !($selectedType === 'po_keluar' && !empty($shipments))):?><div class="alert alert-warning">Transaksi penerimaan tidak ditemukan atau sudah memiliki Invoice In.</div><?php endif ?>
<script>
$(function(){
    window.treInitInlineCombobox({
        box:'#pilihSumberCombobox',
        input:'#pilihSumberText',
        hidden:'#pilihSumber',
        menu:'#pilihSumberComboboxMenu',
        options:<?= json_encode(array_map(static function($row){
            $sourceLabel=$row['source_label']??$row['source_no'];
            if($row['source_type']==='po_keluar'){
                if(($row['jenis_po']??'')==='jasa'){$label='PO Keluar (Jasa)';}
                else{$label=((int)($row['kirim_langsung']??0)===1)?'PO Keluar (Kirim Langsung)':'PO Keluar';}
            }else{
                $labelTipe=['material'=>'Material Masuk','produk'=>'Produk Masuk'];
                $label=$labelTipe[$row['source_type']]??strtoupper((string)$row['source_type']);
            }
            return ['id'=>$row['source_type'].'|'.$row['source_no'],'text'=>$label.' | '.$sourceLabel.' | '.date('d-m-Y',strtotime($row['source_date'])).' | '.$row['supplier_name']];
        }, $sources), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        onSelect:function(option){
            if(!option||!option.id)return;
            const p=option.id.split('|');
            const nextUrl='<?= site_url('invoiceIn/create') ?>?type='+encodeURIComponent(p.shift())+'&source='+encodeURIComponent(p.join('|'));
            window.location.href=(typeof window.treApplyManualPreview==='function')?window.treApplyManualPreview(nextUrl):nextUrl;
        }
    });
});
function hitung(){
    let sub=0;
    $('#detailIn tbody tr').each(function(){const q=parseFloat($(this).find('.qty').data('value'))||0;const h=parseFloat($(this).find('.harga').val())||0;const a=q*h;sub+=a;$(this).find('.amount').text('Rp '+Math.round(a).toLocaleString('id-ID'));});

    const ppnEnabled=$('#ppnEnabled').is(':checked');
    const ppnPercent=parseFloat($('#ppnPercent').val())||0;
    const ppn=ppnEnabled?sub*(ppnPercent/100):0;

    const pphEnabled=$('#pphEnabled').is(':checked');
    const pphPercent=parseFloat($('#pphPercent').val())||0;
    const pph=pphEnabled?sub*(pphPercent/100):0;

    const dpEnabled=$('#dpEnabled').is(':checked');
    const dpPercent=parseFloat($('#dpPercent').val())||0;
    const dpAmount=dpEnabled?(sub+ppn)*(dpPercent/100):0;
    const grand=Math.max((sub+ppn)-dpAmount,0);

    $('#subtotal').text('Rp '+Math.round(sub).toLocaleString('id-ID'));
    $('#ppnLabel').text((ppnPercent||0).toLocaleString('id-ID',{maximumFractionDigits:2})+'%');
    $('#ppn').text('Rp '+Math.round(ppn).toLocaleString('id-ID'));
    $('#rowPpn').toggle(ppnEnabled);
    $('#pphLabel').text((pphPercent||0).toLocaleString('id-ID',{maximumFractionDigits:2})+'%');
    $('#pph').text('Rp '+Math.round(pph).toLocaleString('id-ID'));
    $('#rowPph').toggle(pphEnabled);
    $('#dpLabel').text((dpPercent||0).toLocaleString('id-ID',{maximumFractionDigits:2})+'%');
    $('#dp').text('Rp '+Math.round(dpAmount).toLocaleString('id-ID'));
    $('#rowDp').toggle(dpEnabled);
    $('#grand').text('Rp '+Math.round(grand).toLocaleString('id-ID'));
}
$('.harga').on('input',hitung);
$('#ppnPercent, #pphPercent, #dpPercent').on('input',hitung);
$('#ppnEnabled, #pphEnabled, #dpEnabled').on('change',hitung);
hitung();
</script>
<?= $this->endSection('isi') ?>