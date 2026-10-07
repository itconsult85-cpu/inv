<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Managemen Data Jasa
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<?= form_button('', '<i class="fa fa-plus-circle"></i> Tambah Data Jasa', [
    'class' => 'btn btn-primary',
    'id' => 'btnTambahJasa',
    'type' => 'button',
]) ?>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .jasa-toolbar { align-items: flex-start; display: flex; justify-content: flex-end; margin-bottom: 1rem; position: relative; z-index: 30; }
    .jasa-filter-anchor { align-items: flex-end; display: flex; flex-direction: column; position: relative; width: min(100%, 32rem); }
    .jasa-search-shell { align-items: center; background: #fff; border: 1px solid #eef1f7; border-radius: 999px; box-shadow: 0 3px 10px rgba(15, 23, 42, .025); display: flex; gap: .65rem; min-height: 3.4rem; padding: .35rem .45rem .35rem 1.25rem; width: 100%; }
    .jasa-search-shell > i { color: #6b7280; font-size: 1.1rem; }
    .jasa-search-input { background: transparent; border: 0; box-shadow: none; color: #4b5563; flex: 1 1 auto; font-size: .95rem; min-width: 0; outline: 0; }
    .jasa-search-input:focus { box-shadow: none; outline: 0; }
    .jasa-filter-toggle { align-items: center; background: #16869a; border: 0; border-radius: 999px; color: #fff; display: inline-flex; flex: 0 0 2.65rem; height: 2.65rem; justify-content: center; width: 2.65rem; }
    .jasa-filter-toggle:hover, .jasa-filter-toggle:focus { background: #126f7f; color: #fff; outline: 0; }
    .jasa-filter-panel { background: #fff; border: 1px solid #edf1f5; border-radius: 22px; box-shadow: 0 18px 42px rgba(15, 23, 42, .1); display: none; opacity: 0; overflow: hidden; pointer-events: none; position: absolute; right: 0; top: calc(100% + .75rem); transform: translateY(-.45rem) scale(.985); transform-origin: top right; transition: opacity .18s ease, transform .18s ease; width: 26rem; }
    .jasa-filter-panel.is-open { display: block; opacity: 1; pointer-events: auto; transform: translateY(0) scale(1); }
    .jasa-filter-header { align-items: center; display: flex; justify-content: space-between; padding: 1.2rem 1.35rem .9rem; }
    .jasa-filter-title { color: #111827; font-size: 1.2rem; font-weight: 800; margin: 0; }
    .jasa-filter-close { align-items: center; background: #fff; border: 0; border-radius: 999px; box-shadow: 0 8px 22px rgba(15, 23, 42, .12); color: #111827; display: inline-flex; height: 2.4rem; justify-content: center; width: 2.4rem; }
    .jasa-filter-section { border-top: 1px solid #edf1f5; padding: 1.05rem 1.35rem; }
    .jasa-filter-label { color: #718096; font-size: .9rem; font-weight: 800; margin-bottom: .75rem; }
    .jasa-filter-select, .jasa-filter-reset { border: 0; border-radius: 999px; min-height: 2.55rem; padding: .45rem .85rem; }
    .jasa-filter-select { background: #fff; border: 1px solid #e5eaf1; color: #111827; min-width: 11rem; }
    .jasa-filter-reset { background: #eef2f7; color: #4b5563; font-weight: 800; padding: .45rem 1.2rem; }
    @media (max-width: 768px) { .jasa-toolbar, .jasa-filter-anchor { width: 100%; } .jasa-filter-panel { left: 0; right: auto; width: 100%; } .jasa-filter-select, .jasa-filter-reset { width: 100%; } }
</style>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<div class="jasa-toolbar">
    <div class="jasa-filter-anchor">
        <div class="jasa-search-shell">
            <i class="fas fa-search"></i>
            <input type="search" id="jasaSearchInput" class="jasa-search-input" placeholder="Search anything..." aria-label="Search anything">
            <button type="button" class="jasa-filter-toggle" id="jasaFilterToggle" title="Buka filter" aria-controls="jasaFilterPanel" aria-expanded="false"><i class="fas fa-sliders-h"></i></button>
        </div>
        <section class="jasa-filter-panel" id="jasaFilterPanel" aria-hidden="true">
            <div class="jasa-filter-header"><h3 class="jasa-filter-title">Filter</h3><button type="button" class="jasa-filter-close" id="jasaFilterClose"><i class="fas fa-times"></i></button></div>
            <div class="jasa-filter-section"><div class="jasa-filter-label">Show Data</div><select id="jasaPageLength" class="jasa-filter-select"><option value="10">10 entries</option><option value="25">25 entries</option><option value="50" selected>50 entries</option><option value="100">100 entries</option></select></div>
            <div class="jasa-filter-section"><button type="button" class="jasa-filter-reset" id="jasaFilterReset">Reset</button></div>
        </section>
    </div>
</div>

<table class="table table-bordered table-striped" id="datajasaMaster" style="width:100%">
    <thead><tr><th style="width:5%">No</th><th>Nama Jasa</th><th>Harga Modal</th><th style="width:18%">Aksi</th></tr></thead>
    <tbody></tbody>
</table>

<div class="viewmodal" style="display:none"></div>
<div class="modal fade" id="modalEditJasaMaster" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit Jasa</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div><div class="modal-body"><form id="formEditJasaMaster"><input type="hidden" name="id_jasa" id="editIdJasaMaster"><div class="form-group"><label>Nama Jasa</label><input type="text" class="form-control" name="editNamaJasa" id="editNamaJasaMaster" required><div class="invalid-feedback" id="errorEditNamaJasaMaster"></div></div><div class="form-group"><label>Harga Modal / Pcs (Rp)</label><input type="number" min="0" step="0.01" class="form-control" name="editHargaModal" id="editHargaModalMaster" value="0"></div></form></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="button" class="btn btn-primary" id="btnUpdateJasaMaster">Update</button></div></div></div></div>

<script>
$(function () {
    const table = $('#datajasaMaster').DataTable({ responsive: true, processing: true, serverSide: true, ajax: '<?= site_url('jasa/listData') ?>', order: [], pageLength: 50, columns: [
        { data: 'nomor', orderable: false, className: 'text-center' },
        { data: 'namajasa' },
        { data: 'harga_modal', className: 'text-right' },
        { data: null, orderable: false, searchable: false, className: 'text-center', render: function (data, type, row) {
            const nama = $('<div>').text(row.namajasa || '').html();
            return '<button type="button" class="btn btn-sm btn-primary btn-edit-jasa" data-id="' + row.idjasa + '" data-nama="' + nama + '" data-harga="' + (row.harga_modal_raw || 0) + '" title="Edit Data"><i class="fa fa-edit"></i></button> ' + '<button type="button" class="btn btn-sm btn-danger btn-delete-jasa" data-id="' + row.idjasa + '" data-nama="' + nama + '" title="Hapus Data"><i class="fa fa-trash-alt"></i></button>';
        }}
    ] });
    $('#jasaSearchInput').on('keyup search', function () { table.search(this.value).draw(); });
    $('#jasaPageLength').on('change', function () { table.page.len(this.value).draw(); });
    $('#jasaFilterToggle').on('click', function () { $('#jasaFilterPanel').toggleClass('is-open'); });
    $('#jasaFilterClose').on('click', function () { $('#jasaFilterPanel').removeClass('is-open'); });
    $('#jasaFilterReset').on('click', function () { $('#jasaSearchInput').val(''); $('#jasaPageLength').val('50'); table.search('').page.len(50).draw(); $('#jasaFilterPanel').removeClass('is-open'); });
    $('#btnTambahJasa').on('click', function () { $.getJSON('<?= site_url('jasa/formtambah') ?>', function (response) { if (response.data) { $('.viewmodal').html(response.data).show(); $('#modaltambahjasa').modal('show'); } }); });
    $('.viewmodal').on('hidden.bs.modal', '#modaltambahjasa', function () { table.ajax.reload(null, false); });
    $('#datajasaMaster').on('click', '.btn-edit-jasa', function () { const b = $(this); $('#editIdJasaMaster').val(b.data('id')); $('#editNamaJasaMaster').val(b.data('nama')); $('#editHargaModalMaster').val(b.data('harga') || 0); $('#editNamaJasaMaster').removeClass('is-invalid'); $('#modalEditJasaMaster').modal('show'); });
    $('#btnUpdateJasaMaster').on('click', function () { const form = $('#formEditJasaMaster'); $.post('<?= site_url('jasa/update') ?>', form.serialize() + '&' + encodeURIComponent(csrfToken) + '=' + encodeURIComponent(csrfHash), function (response) { if (response.error) { $('#editNamaJasaMaster').addClass('is-invalid'); $('#errorEditNamaJasaMaster').text(response.error.errnamaJasa || 'Data tidak valid.'); return; } $('#modalEditJasaMaster').modal('hide'); table.ajax.reload(null, false); showBootstrapModal({ icon: 'success', title: 'Update Data', text: response.sukses || 'Data jasa berhasil diupdate.' }); }, 'json'); });
    $('#datajasaMaster').on('click', '.btn-delete-jasa', function () { const b = $(this); showBootstrapModal({ title: 'Hapus Jasa?', text: 'Yakin menghapus data jasa ' + b.data('nama') + '?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal' }).then(function (result) { if (!result.isConfirmed) return; $.post('<?= site_url('jasa/hapus') ?>', { id: b.data('id'), [csrfToken]: csrfHash }, function (response) { if (response.error) { showBootstrapModal('Tidak dapat dihapus', response.error, 'error'); return; } table.ajax.reload(null, false); showBootstrapModal({ icon: 'success', title: 'Hapus Data', text: response.sukses || 'Data jasa berhasil dihapus.' }); }, 'json'); }); });
});
</script>
<?= $this->endSection('isi') ?>