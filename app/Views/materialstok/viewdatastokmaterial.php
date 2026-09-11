<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Data Stok Material
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<style>
    .material-stock-toolbar {
        align-items: center;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 1rem;
        position: relative;
        z-index: 20;
    }

    .material-stock-length {
        align-items: center;
        color: #111827;
        display: inline-flex;
        gap: .45rem;
        white-space: nowrap;
    }

    .material-stock-length select {
        appearance: auto;
        background: #fff;
        border: 1px solid #d8e2ef;
        border-radius: 10px;
        color: #4b5563;
        height: 2.65rem;
        padding: 0 .65rem;
        width: 4.3rem;
    }

    .material-stock-search-shell {
        align-items: center;
        background: #fff;
        border: 1px solid #eef1f7;
        border-radius: 999px;
        box-shadow: 0 3px 10px rgba(15, 23, 42, .025);
        display: flex;
        gap: .65rem;
        min-height: 3.4rem;
        padding: .35rem 1.25rem;
        width: min(100%, 32rem);
    }

    .material-stock-search-shell > i {
        color: #6b7280;
        font-size: 1.1rem;
    }

    .material-stock-search-input {
        background: transparent;
        border: 0;
        box-shadow: none;
        color: #4b5563;
        flex: 1 1 auto;
        font-size: .95rem;
        min-width: 0;
        outline: 0;
    }

    .material-stock-search-input:focus {
        box-shadow: none;
        outline: 0;
    }

    @media (max-width: 768px) {
        .material-stock-toolbar {
            align-items: stretch;
            flex-direction: column-reverse;
        }

        .material-stock-search-shell {
            width: 100%;
        }
    }
</style>

<div class="material-stock-toolbar">
    <label class="material-stock-length" for="materialStockPageLength">
        <span>Show</span>
        <select id="materialStockPageLength" aria-label="Show entries">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50" selected>50</option>
            <option value="100">100</option>
        </select>
        <span>entries</span>
    </label>

    <div class="material-stock-search-shell">
        <i class="fas fa-search"></i>
        <input type="search" id="materialStockSearchInput" class="material-stock-search-input" placeholder="Search material..." aria-label="Search material">
    </div>
</div>

<table class="table table-bordered table-striped" id="datastokmaterial">
    <thead>
        <tr>
            <th style="width: 5%; vertical-align: middle">No</th>
            <th style="vertical-align: middle;">Kode Material</th>
            <th style="vertical-align: middle;">Stok Cikarang (Kg)</th>
            <th style="vertical-align: middle;">Stok Cirebon (Kg)</th>
            <th style="vertical-align: middle;">Total Stok (Kg)</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>
<script>
    // var pusher = new Pusher('8f027ac11961f0fa1906', {
    //     cluster: 'ap1'
    // });

    // var channel = pusher.subscribe('my-channel');
    // channel.bind('my-event', function(data) {
    //     table.ajax.reload(null, false);
    // });
</script>
<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    var table;

    $(document).ready(function() {
        table = $('#datastokmaterial').DataTable({
            lengthChange: true,
            autoWidth: false,
            responsive: true,
            searching: true,
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            processing: true,
            serverSide: true,
            pageLength: 10,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: {
                url: '<?= site_url('stokmaterial/data') ?>',
                type: 'POST',
                data: function(d) {
                    d.tglawal = $('#tglawal').val();
                    d.tglakhir = $('#tglakhir').val();
                    d.material = $('#material').val();
                    d[csrfToken] = csrfHash;
                }
            },
            order: [
                [1, 'asc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'kodematerial',
                    className: 'text-center'
                },
                {
                    data: 'totmascik',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'totmascir',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'totalstok',
                    name: 'stok.stok',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
            ],
        });

        $('#materialStockSearchInput').val(table.search());

        let searchTimer = null;
        $('#materialStockSearchInput').on('input', function() {
            const keyword = this.value;
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(function() {
                table.search(keyword).draw();
            }, 350);
        });

        $('#materialStockPageLength').on('change', function() {
            table.page.len(Number(this.value)).draw();
        });

        $('#material').on('change', function() {
            table.ajax.reload();
        });
        $('#btnFilter').on('click', function() {
            table.ajax.reload();
        });

        function formatNumber(number) {
            return String(number || 0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

    });
</script>
<?= $this->endSection('isi') ?>