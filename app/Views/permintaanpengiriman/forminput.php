<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Input Permintaan Pengiriman
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<button class="btn btn-warning" onclick="location.href='/barangkeluar/data#permintaan'">
    <i class="fa fa-undo"></i> Kembali
</button>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .user-search { position: relative; }
    .user-search-result {
        position: absolute; z-index: 1050; top: 100%; left: 0; right: 0;
        display: none; background: #fff; border: 1px solid #80bdff;
        box-shadow: 0 .25rem .5rem rgba(0,0,0,.12);
    }
    .user-search-item, .user-search-empty { padding: .55rem .75rem; }
    .user-search-item { cursor: pointer; }
    .user-search-item:hover { color: #fff; background: #007bff; }
</style>

<input type="hidden" id="token" value="<?= esc($token) ?>">

<div class="row">
    <div class="col-lg-4">
        <label>Tanggal Permintaan</label>
        <input type="date" id="tanggal" class="form-control" value="<?= date('Y-m-d') ?>">
    </div>
    <div class="col-lg-4">
        <label>User</label>
        <input type="text" class="form-control" value="<?= esc($currentUser['usernama']) ?> (<?= esc($currentUser['userid']) ?>)" readonly>
    </div>
    <div class="col-lg-4">
        <label>Keterangan <span class="text-muted font-weight-normal">(opsional)</span></label>
        <input type="text" id="keteranganPengiriman" class="form-control" placeholder="Silakan isi keterangan">
    </div>
</div>

<hr>

<div class="row">
    <div class="col-lg-3">
        <label for="kodebarangInput">Kode Produk</label>
        <div class="tre-inline-combobox tre-inline-combobox-solo" id="kodebarangCombobox">
            <input type="text" id="kodebarangInput" class="form-control" placeholder="-- Pilih atau ketik Kode/Nama Produk --" autocomplete="off">
            <input type="hidden" id="kodebarang">
            <div class="tre-inline-combobox-menu" id="kodebarangComboboxMenu"></div>
        </div>
    </div>
    <div class="col-lg-3">
        <label>Nama Produk</label>
        <input type="text" id="namabarang" class="form-control" readonly>
    </div>
    <div class="col-lg-2">
        <label>Stok</label>
        <input type="text" id="stok" class="form-control" readonly>
        <input type="hidden" id="berat">
    </div>
    <div class="col-lg-2">
        <label>Qty</label>
        <input type="number" id="qty" class="form-control" value="1" min="1">
    </div>
    <div class="col-lg-2">
        <label>#</label><br>
        <button class="btn btn-success" id="simpanItem"><i class="fa fa-save"></i></button>
        <button class="btn btn-warning" id="reloadItem"><i class="fa fa-sync-alt"></i></button>
    </div>
</div>

<div class="mt-3" id="dataTempPengiriman"></div>
<div class="text-right">
    <button class="btn btn-success" id="selesaiPermintaan"><i class="fa fa-save"></i> Selesai Transaksi</button>
</div>

<script>
    const csrfToken = '<?= csrf_token() ?>';
    const csrfHash = '<?= csrf_hash() ?>';
    const produkOptions = <?= json_encode(array_map(static function ($p) {
                                return [
                                    'id' => (string) $p['brgkode'],
                                    'text' => $p['brgkode'] . ' - ' . $p['brgnama'],
                                    'value' => $p['brgkode'],
                                ];
                            }, $produk ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    let kodebarangCombobox = null;

    function tampilTempPengiriman() {
        $.post('/permintaanPengiriman/tampilTemp', {
            [csrfToken]: csrfHash,
            token: $('#token').val()
        }, response => $('#dataTempPengiriman').html(response.data), 'json');
    }

    function ambilDataBarang() {
        $.post('/permintaanPengiriman/ambilDataBarang', {
            [csrfToken]: csrfHash,
            kodebarang: $('#kodebarang').val()
        }, function(response) {
            if (response.error) return Swal.fire('Error', response.error, 'error');
            $('#namabarang').val(response.sukses.namabarang);
            $('#stok').val(response.sukses.stok);
            $('#berat').val(response.sukses.berat);
        }, 'json');
    }

    function hapusItemPengiriman(id) {
        $.post('/permintaanPengiriman/hapusItem', {
            [csrfToken]: csrfHash, id: id
        }, tampilTempPengiriman, 'json');
    }

    $(function() {
        tampilTempPengiriman();

        kodebarangCombobox = window.treInitInlineCombobox({
            box: '#kodebarangCombobox',
            input: '#kodebarangInput',
            hidden: '#kodebarang',
            menu: '#kodebarangComboboxMenu',
            options: produkOptions,
            onSelect: function() {
                ambilDataBarang();
            }
        });

        $('#simpanItem').click(function(e) {
            e.preventDefault();
            $.post('/permintaanPengiriman/simpanItem', {
                [csrfToken]: csrfHash,
                token: $('#token').val(),
                kodebarang: $('#kodebarang').val(),
                namabarang: $('#namabarang').val(),
                berat: $('#berat').val(),
                qty: $('#qty').val()
            }, function(response) {
                if (response.error) return Swal.fire('Error', response.error, 'error');
                tampilTempPengiriman();
                $('#kodebarangInput,#namabarang,#stok,#berat').val('');
                $('#kodebarang').val('');
                $('#qty').val(1);
            }, 'json');
        });

        $('#reloadItem').click(tampilTempPengiriman);

        $('#selesaiPermintaan').click(function() {
            $.post('/permintaanPengiriman/selesai', {
                [csrfToken]: csrfHash,
                token: $('#token').val(),
                tanggal: $('#tanggal').val(),
                keterangan: $('#keteranganPengiriman').val()
            }, function(response) {
                if (response.error) return Swal.fire('Error', response.error, 'error');
                Swal.fire('Berhasil', response.sukses, 'success').then(() => location.href='/barangkeluar/data#permintaan');
            }, 'json');
        });
    });
</script>
<?= $this->endSection('isi') ?>
