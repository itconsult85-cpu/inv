<?= $this->extend('main/layout') ?>
<?= $this->section('judul') ?>
Waste / Wise Aktual Produksi
<?= $this->endSection('judul') ?>
<?= $this->section('subjudul') ?>
Perhitungan berdasarkan material yang benar-benar dipakai pada transaksi produksi, bukan outstanding PO.
<?= $this->endSection('subjudul') ?>
<?= $this->section('isi') ?>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<style>
    .angka { text-align: right; white-space: nowrap; }
    .summary-card { border-left: 4px solid #17a2b8; min-height: 88px; }
    .summary-card .value { font-size: 1.5rem; font-weight: 700; }
    .summary-card .label { color: #6c757d; }
</style>
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-2 mb-2"><label for="tanggalAwal">Tanggal Awal</label><input type="date" id="tanggalAwal" class="form-control"></div>
            <div class="col-md-2 mb-2"><label for="tanggalAkhir">Tanggal Akhir</label><input type="date" id="tanggalAkhir" class="form-control"></div>
            <div class="col-md-3 mb-2"><label for="filterProduk">Produk</label><select id="filterProduk" class="form-control"><option value="">-- Semua Produk --</option><?php foreach ($produks as $produk) : ?><option value="<?= esc($produk['brgkode']) ?>"><?= esc($produk['brgkode'] . ' - ' . $produk['brgnama']) ?></option><?php endforeach ?></select></div>
            <div class="col-md-3 mb-2"><label for="filterMaterial">Material</label><select id="filterMaterial" class="form-control"><option value="">-- Semua Material --</option><?php foreach ($materials as $material) : ?><option value="<?= (int) $material['matid'] ?>"><?= esc($material['matkode'] . ' - ' . $material['matnama']) ?></option><?php endforeach ?></select></div>
            <div class="col-md-2 mb-2 d-flex align-items-end"><button type="button" id="btnTampilkan" class="btn btn-primary btn-block"><i class="fas fa-calculator"></i> Hitung</button></div>
        </div>
        <small class="text-muted">Waste = material aktual terpakai dikurangi berat hasil jadi. Wise = waste / material aktual terpakai. Data ini hanya membaca transaksi produksi dan tidak mengubah stok.</small>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3 mb-2"><div class="card summary-card mb-0"><div class="card-body"><div class="value" id="totalProduksi">0</div><div class="label">Baris Detail Produksi</div></div></div></div>
            <div class="col-md-3 mb-2"><div class="card summary-card mb-0"><div class="card-body"><div class="value" id="totalMaterial">0</div><div class="label">Material</div></div></div></div>
            <div class="col-md-3 mb-2"><div class="card summary-card mb-0"><div class="card-body"><div class="value" id="totalTerpakai">0</div><div class="label">Material Terpakai</div></div></div></div>
            <div class="col-md-3 mb-2"><div class="card summary-card mb-0"><div class="card-body"><div class="value text-danger" id="totalWaste">0</div><div class="label">Total Waste</div></div></div></div>
        </div>
        <div id="peringatanWrapper" class="alert alert-warning d-none"><strong><i class="fas fa-exclamation-triangle"></i> Data belum lengkap</strong><ul id="peringatanData" class="mb-0"></ul></div>
        <div class="table-responsive"><table id="tabelWasteProduksi" class="table table-bordered table-striped table-hover"><thead><tr><th>No</th><th>Kode Material</th><th>Nama Material</th><th>Satuan</th><th>Transaksi</th><th>Qty Produk</th><th>Material Terpakai</th><th>Hasil Jadi</th><th>Waste</th><th>Wise</th><th>Detail</th></tr></thead><tbody></tbody></table></div>
    </div>
</div>
<div class="modal fade" id="modalDetailWaste" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-xl" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="judulDetailWaste">Detail Waste Produksi</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div><div class="modal-body"><div class="table-responsive"><table class="table table-bordered table-sm"><thead><tr><th>No Produksi</th><th>Tanggal</th><th>Produk</th><th>Qty Produk</th><th>Material Terpakai</th><th>Hasil Jadi</th><th>Waste</th><th>Wise</th></tr></thead><tbody id="isiDetailWaste"></tbody></table></div></div></div></div></div>
<script>
let dataWasteProduksi = [];
function formatAngka(value, suffix = '') { if (value === null || value === undefined || value === '') return '-'; return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 4 }).format(Number(value)) + suffix; }
function escapeHtml(value) { return $('<div>').text(value == null ? '' : value).html(); }
function muatData() {
    const btn = $('#btnTampilkan'); btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menghitung...');
    $.getJSON('<?= site_url('materialwasteproduksi/data') ?>', { tanggal_awal: $('#tanggalAwal').val(), tanggal_akhir: $('#tanggalAkhir').val(), produk: $('#filterProduk').val(), material: $('#filterMaterial').val() })
        .done(function(response) {
            dataWasteProduksi = response.data || [];
            const summary = response.summary || {};
            $('#totalProduksi').text(formatAngka(summary.total_produksi || 0)); $('#totalMaterial').text(formatAngka(summary.total_material || 0)); $('#totalTerpakai').text(formatAngka(summary.total_material_terpakai || 0, ' Kg')); $('#totalWaste').text(formatAngka(summary.total_waste || 0, ' Kg'));
            const warning = $('#peringatanWrapper'); const list = $('#peringatanData').empty(); (response.warnings || []).forEach(function(item) { list.append('<li>' + escapeHtml(item) + '</li>'); }); warning.toggleClass('d-none', !(response.warnings || []).length);
            if ($.fn.DataTable.isDataTable('#tabelWasteProduksi')) $('#tabelWasteProduksi').DataTable().destroy();
            const body = $('#tabelWasteProduksi tbody').empty(); dataWasteProduksi.forEach(function(row, index) { body.append('<tr><td class="text-center">' + (index + 1) + '</td><td>' + escapeHtml(row.kode_material) + '</td><td>' + escapeHtml(row.nama_material) + '</td><td>' + escapeHtml(row.satuan) + '</td><td class="angka">' + formatAngka(row.total_produksi) + '</td><td class="angka">' + formatAngka(row.total_qty_produk) + '</td><td class="angka">' + formatAngka(row.material_terpakai) + '</td><td class="angka">' + formatAngka(row.data_lengkap ? row.hasil_jadi_kg : null) + '</td><td class="angka text-danger font-weight-bold">' + formatAngka(row.waste_kg) + '</td><td class="angka">' + formatAngka(row.wise_persen, '%') + '</td><td class="text-center"><button type="button" class="btn btn-info btn-sm btn-detail" data-index="' + index + '"><i class="fas fa-info-circle"></i> Detail</button></td></tr>'); });
            $('#tabelWasteProduksi').DataTable({ responsive: true, autoWidth: false, pageLength: 10, order: [[8, 'desc']], language: { emptyTable: 'Belum ada detail transaksi produksi.' }, columnDefs: [{ orderable: false, targets: [0, 10] }] });
        }).fail(function() { showBootstrapModal('Gagal', 'Laporan waste aktual produksi gagal dimuat.', 'error'); }).always(function() { btn.prop('disabled', false).html('<i class="fas fa-calculator"></i> Hitung'); });
}
$(document).on('click', '.btn-detail', function() { const row = dataWasteProduksi[Number($(this).data('index'))]; if (!row) return; $('#judulDetailWaste').text('Detail ' + row.kode_material + ' - ' + row.nama_material); const body = $('#isiDetailWaste').empty(); (row.detail || []).forEach(function(detail) { body.append('<tr><td>' + escapeHtml(detail.no_produksi) + '</td><td>' + escapeHtml(detail.tanggal) + '</td><td>' + escapeHtml(detail.kode_produk + ' - ' + detail.nama_produk) + '</td><td class="angka">' + formatAngka(detail.qty_produk) + '</td><td class="angka">' + formatAngka(detail.material_terpakai) + ' Kg</td><td class="angka">' + formatAngka(detail.hasil_jadi_kg) + ' Kg</td><td class="angka">' + formatAngka(detail.waste_kg) + ' Kg</td><td class="angka">' + formatAngka(detail.wise_persen, '%') + '</td></tr>'); }); $('#modalDetailWaste').modal('show'); });
$('#btnTampilkan').on('click', muatData); $(document).ready(muatData);
</script>
<?= $this->endSection('isi') ?>
