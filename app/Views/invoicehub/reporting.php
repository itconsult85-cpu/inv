<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Reporting
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<a href="<?= site_url('invoiceHub') ?>" class="btn btn-warning"><i class="fa fa-undo"></i> Kembali</a>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>

<style>
    .reporting-result-card {
        border: 1px solid #e3ebf2;
        border-radius: 1.2rem;
        box-shadow: 0 1rem 2.4rem rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .reporting-result-card .card-body {
        padding: 1.55rem;
    }

    .reporting-tab-filter {
        background: #f8fbfd;
        border: 1px solid #e3ebf2;
        border-radius: 1rem;
        margin-bottom: 1.25rem;
        padding: 1.1rem 1.25rem;
    }

    .reporting-periode-info {
        color: #6b7a90;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .reporting-summary-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-bottom: 1.25rem;
    }

    .reporting-summary-card {
        background: #f8fbfd;
        border: 1px solid #e3ebf2;
        border-radius: 1rem;
        padding: 1rem 1.1rem;
    }

    .reporting-summary-label {
        color: #6b7a90;
        font-size: .85rem;
        font-weight: 700;
        margin-bottom: .35rem;
    }

    .reporting-summary-value {
        color: #071226;
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .reporting-list {
        display: grid;
        gap: 1rem;
    }

    .reporting-item {
        background: #fff;
        border: 1px solid #e2eaf2;
        border-radius: 1rem;
        overflow: hidden;
    }

    .reporting-item-header {
        align-items: flex-start;
        background: linear-gradient(135deg, #f8fbfd 0%, #eef5f8 100%);
        border-bottom: 1px solid #e2eaf2;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        padding: 1rem 1.15rem;
    }

    .reporting-item-number {
        align-items: center;
        background: #12869a;
        border-radius: .85rem;
        color: #fff;
        display: inline-flex;
        flex: 0 0 auto;
        font-weight: 800;
        height: 2.2rem;
        justify-content: center;
        width: 2.2rem;
    }

    .reporting-product-title {
        color: #071226;
        font-weight: 800;
        line-height: 1.35;
        margin: 0;
    }

    .reporting-product-meta {
        color: #6b7a90;
        font-size: .88rem;
        margin-top: .2rem;
    }

    .reporting-item-body {
        display: grid;
        gap: 1rem;
        grid-template-columns: minmax(16rem, 1.15fr) minmax(20rem, 1.85fr) minmax(16rem, 1.15fr);
        padding: 1.15rem;
    }

    .reporting-panel {
        background: #fbfdff;
        border: 1px solid #e6eef5;
        border-radius: .9rem;
        padding: 1rem;
    }

    .reporting-panel-title {
        color: #071226;
        font-size: .9rem;
        font-weight: 800;
        margin-bottom: .85rem;
    }

    .reporting-metric {
        align-items: center;
        display: flex;
        gap: .75rem;
        justify-content: space-between;
        padding: .45rem 0;
    }

    .reporting-metric + .reporting-metric {
        border-top: 1px dashed #d7e2eb;
    }

    .metric-label {
        color: #6b7a90;
        font-size: .86rem;
        font-weight: 700;
    }

    .metric-value {
        color: #071226;
        font-weight: 800;
        text-align: right;
    }

    .metric-value.positive {
        color: #16a34a;
    }

    .metric-value.negative {
        color: #dc3545;
    }

    .reporting-input-grid {
        display: grid;
        gap: .85rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .reporting-input-box label {
        color: #6b7a90;
        display: block;
        font-size: .83rem;
        font-weight: 800;
        margin-bottom: .35rem;
    }

    .reporting-input-box .form-control {
        border-radius: .7rem;
        min-height: 2.6rem;
    }

    .reporting-note {
        color: #6b7a90;
        display: block;
        font-size: .82rem;
        line-height: 1.5;
        margin-top: .4rem;
    }

    .reporting-note details {
        color: inherit;
    }

    .reporting-note details.text-warning {
        color: #f59e0b !important;
    }

    .reporting-tabs .nav-link {
        color: #526173;
        font-weight: 800;
        border-radius: .8rem .8rem 0 0;
    }

    .reporting-tabs .nav-link.active {
        color: #071226;
    }

    .reporting-data-table th {
        background: #f5f8fb;
        color: #263244;
        font-size: .86rem;
        white-space: nowrap;
    }

    .reporting-data-table td {
        vertical-align: middle;
    }

    .reporting-empty {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: .8rem;
        color: #9a3412;
        font-weight: 700;
        padding: .85rem 1rem;
    }

    .material-override-note {
        color: #16a34a;
        display: none;
        font-size: .78rem;
        font-weight: 800;
        margin-top: .35rem;
    }

    .reporting-result-input {
        max-width: 12rem;
        text-align: right;
    }

    .reporting-result-note {
        color: #16a34a;
        display: none;
        font-size: .78rem;
        font-weight: 800;
        margin-top: .25rem;
        text-align: right;
    }

    @media (max-width: 1199.98px) {
        .reporting-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .reporting-item-body {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .reporting-summary-grid,
        .reporting-input-grid {
            grid-template-columns: 1fr;
        }

        .reporting-item-header {
            flex-direction: column;
        }
    }
</style>

<?php
    $renderFilterPelanggan = static function () use ($pelanggans) {
        foreach ($pelanggans as $pel) {
            echo '<option value="' . esc($pel['pelid']) . '">' . esc($pel['pelnama']) . '</option>';
        }
    };
?>

<div class="card reporting-result-card">
    <div class="card-body">
        <ul class="nav nav-tabs reporting-tabs mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#tabMargin" role="tab">Margin</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tabPiutang" role="tab">Piutang</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tabHutang" role="tab">Hutang</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tabCashflow" role="tab">Cashflow</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tabOmzetSuratJalan" role="tab">Omzet per Surat Jalan</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tabMargin" role="tabpanel">
                <form class="reporting-tab-filter" data-target="#tabMarginContent" data-url="<?= site_url('invoiceHub/reportingMargin') ?>">
                    <div class="row align-items-end">
                        <div class="col-md-3 form-group mb-2">
                            <label>Tanggal Awal</label>
                            <input type="date" name="tglawal" class="form-control" required>
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="tglakhir" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            <label>Pelanggan</label>
                            <select name="pelanggan" class="form-control">
                                <option value="">-- Semua Pelanggan --</option>
                                <?php $renderFilterPelanggan(); ?>
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Tampilkan</button>
                        </div>
                    </div>
                </form>
                <div id="tabMarginContent">
                    <?= view('invoicehub/tab_margin', ['rows' => [], 'periodeDipilih' => false, 'tglawal' => null, 'tglakhir' => null, 'namaPelanggan' => '']) ?>
                </div>
            </div>

            <div class="tab-pane fade" id="tabPiutang" role="tabpanel">
                <form class="reporting-tab-filter" data-target="#tabPiutangContent" data-url="<?= site_url('invoiceHub/reportingPiutang') ?>">
                    <div class="row align-items-end">
                        <div class="col-md-3 form-group mb-2">
                            <label>Tanggal Awal</label>
                            <input type="date" name="tglawal" class="form-control" required>
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="tglakhir" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            <label>Pelanggan</label>
                            <select name="pelanggan" class="form-control">
                                <option value="">-- Semua Pelanggan --</option>
                                <?php $renderFilterPelanggan(); ?>
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Tampilkan</button>
                        </div>
                    </div>
                </form>
                <div id="tabPiutangContent">
                    <?= view('invoicehub/tab_piutang', ['piutangRows' => [], 'periodeDipilih' => false, 'tglawal' => null, 'tglakhir' => null, 'namaPelanggan' => '']) ?>
                </div>
            </div>

            <div class="tab-pane fade" id="tabHutang" role="tabpanel">
                <form class="reporting-tab-filter" data-target="#tabHutangContent" data-url="<?= site_url('invoiceHub/reportingHutang') ?>">
                    <div class="row align-items-end">
                        <div class="col-md-3 form-group mb-2">
                            <label>Tanggal Awal</label>
                            <input type="date" name="tglawal" class="form-control" required>
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="tglakhir" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button>
                        </div>
                    </div>
                </form>
                <div id="tabHutangContent">
                    <?= view('invoicehub/tab_hutang', ['hutangRows' => [], 'periodeDipilih' => false, 'tglawal' => null, 'tglakhir' => null]) ?>
                </div>
            </div>

            <div class="tab-pane fade" id="tabCashflow" role="tabpanel">
                <form class="reporting-tab-filter" data-target="#tabCashflowContent" data-url="<?= site_url('invoiceHub/reportingCashflow') ?>">
                    <div class="row align-items-end">
                        <div class="col-md-3 form-group mb-2">
                            <label>Tanggal Awal</label>
                            <input type="date" name="tglawal" class="form-control" required>
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="tglakhir" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            <label>Pelanggan</label>
                            <select name="pelanggan" class="form-control">
                                <option value="">-- Semua Pelanggan --</option>
                                <?php $renderFilterPelanggan(); ?>
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Tampilkan</button>
                        </div>
                    </div>
                </form>
                <div id="tabCashflowContent">
                    <?= view('invoicehub/tab_cashflow', ['cashflowRows' => [], 'periodeDipilih' => false, 'tglawal' => null, 'tglakhir' => null, 'namaPelanggan' => '']) ?>
                </div>
            </div>

            <div class="tab-pane fade" id="tabOmzetSuratJalan" role="tabpanel">
                <form class="reporting-tab-filter" data-target="#tabOmzetSuratJalanContent" data-url="<?= site_url('invoiceHub/reportingOmzetSuratJalan') ?>">
                    <input type="hidden" name="periode" id="omzetPeriode" value="<?= esc($reportBulananAwal['periode']) ?>">
                    <div class="row align-items-end">
                        <div class="col-md-4 form-group mb-2">
                            <label>Periode</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" id="omzetPeriodePrev"><i class="fa fa-chevron-left"></i> Sebelumnya</button>
                                <button type="button" class="btn btn-outline-secondary" id="omzetPeriodeNext">Berikutnya <i class="fa fa-chevron-right"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-2">
                            <label>Pelanggan</label>
                            <select name="pelanggan" class="form-control">
                                <option value="">-- Semua Pelanggan --</option>
                                <?php $renderFilterPelanggan(); ?>
                            </select>
                        </div>
                        <div class="col-md-2 form-group mb-2">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Tampilkan</button>
                        </div>
                    </div>
                </form>
                <div id="tabOmzetSuratJalanContent">
                    <?= view('invoicehub/tab_omzet_surat_jalan', ['report' => $reportBulananAwal, 'periodeDipilih' => true, 'namaPelanggan' => '']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    function formatRupiah(angka) {
        return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
    }

    function hitungMargin() {
        let grandTotal = 0;
        document.querySelectorAll('.reporting-item').forEach(function (item) {
            const qtyPcs = parseFloat(item.querySelector('.qty-pcs').dataset.qtyPcs || 0);

            const material = item.querySelector('.input-material');
            const jasa = item.querySelector('.input-jasa');
            const trpoh = item.querySelector('.input-trpoh');
            const totalModalInput = item.querySelector('.input-total-modal');
            const hargaJualInput = item.querySelector('.input-harga-jual');
            const materialNote = item.querySelector('.material-override-note');
            const totalModalNote = item.querySelector('.total-modal-note');
            const hargaJualNote = item.querySelector('.harga-jual-note');
            const defaultMaterial = parseFloat(material.dataset.defaultValue || 0);
            const currentMaterial = parseFloat(material.value || 0);
            const componentModal = currentMaterial + parseFloat(jasa.value || 0) + parseFloat(trpoh.value || 0);

            const marginUnitCell = item.querySelector('.margin-unit');
            const prosentaseCell = item.querySelector('.prosentase');
            const marginTotalCells = item.querySelectorAll('.margin-total');

            if (materialNote) {
                materialNote.style.display = Math.abs(currentMaterial - defaultMaterial) > 0.009 ? 'block' : 'none';
            }

            if (totalModalInput.dataset.manual !== '1') {
                totalModalInput.value = componentModal.toFixed(2);
            }

            const totalModal = parseFloat(totalModalInput.value || 0);
            const hargaJual = parseFloat(hargaJualInput.value || 0);
            const marginUnit = hargaJual - totalModal;
            const marginTotal = marginUnit * qtyPcs;
            const prosentase = totalModal > 0 ? (marginUnit / totalModal * 100) : 0;

            if (totalModalNote) {
                totalModalNote.style.display = totalModalInput.dataset.manual === '1' ? 'block' : 'none';
            }
            if (hargaJualNote) {
                const defaultHargaJual = parseFloat(hargaJualInput.dataset.defaultValue || 0);
                hargaJualNote.style.display = Math.abs(hargaJual - defaultHargaJual) > 0.009 ? 'block' : 'none';
            }

            marginUnitCell.textContent = formatRupiah(marginUnit);
            prosentaseCell.textContent = totalModal > 0 ? prosentase.toFixed(2).replace('.', ',') + '%' : '#DIV/0!';
            marginTotalCells.forEach(function (cell) {
                cell.textContent = formatRupiah(marginTotal);
                cell.classList.toggle('positive', marginTotal >= 0);
                cell.classList.toggle('negative', marginTotal < 0);
            });
            marginUnitCell.classList.toggle('positive', marginUnit >= 0);
            marginUnitCell.classList.toggle('negative', marginUnit < 0);
            grandTotal += marginTotal;
        });

        const grandTotalMargin = document.getElementById('grandTotalMargin');
        if (grandTotalMargin) {
            grandTotalMargin.textContent = formatRupiah(grandTotal);
        }
    }

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('input-modal')) {
            if (e.target.classList.contains('input-total-modal')) {
                e.target.dataset.manual = '1';
            }
            if (
                e.target.classList.contains('input-material')
                || e.target.classList.contains('input-jasa')
                || e.target.classList.contains('input-trpoh')
            ) {
                const item = e.target.closest('.reporting-item');
                const totalModalInput = item ? item.querySelector('.input-total-modal') : null;
                if (totalModalInput && totalModalInput.dataset.manual !== '1') {
                    totalModalInput.dataset.manual = '0';
                }
            }
            hitungMargin();
        }
    });

    document.addEventListener('click', function (e) {
        const button = e.target.closest('.reset-material');
        if (!button) {
            return;
        }

        const box = button.closest('.reporting-input-box');
        const input = box ? box.querySelector('.input-material') : null;
        if (input) {
            input.value = input.dataset.defaultValue || 0;
            hitungMargin();
        }
    });

    document.addEventListener('click', function (e) {
        const resetTotal = e.target.closest('.reset-total-modal');
        const resetHarga = e.target.closest('.reset-harga-jual');
        if (!resetTotal && !resetHarga) {
            return;
        }

        const item = e.target.closest('.reporting-item');
        if (!item) {
            return;
        }

        if (resetTotal) {
            const input = item.querySelector('.input-total-modal');
            if (input) {
                input.dataset.manual = '0';
            }
        }

        if (resetHarga) {
            const input = item.querySelector('.input-harga-jual');
            if (input) {
                input.value = input.dataset.defaultValue || 0;
            }
        }

        hitungMargin();
    });

    $(document).on('submit', '.reporting-tab-filter', function (e) {
        e.preventDefault();
        const $form = $(this);
        const target = $form.data('target');
        const url = $form.data('url');
        const $button = $form.find('button[type="submit"]');
        const originalHtml = $button.html();

        $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memuat...');

        $.ajax({
            type: 'post',
            url: url,
            data: $form.serialize() + '&' + csrfToken + '=' + csrfHash,
            dataType: 'json',
            success: function (response) {
                $(target).html(response.data);
                if (target === '#tabMarginContent') {
                    hitungMargin();
                }
            },
            error: function () {
                Swal.fire('Gagal', 'Terjadi kesalahan saat mengambil data reporting.', 'error');
            },
            complete: function () {
                $button.prop('disabled', false).html(originalHtml);
            }
        });
    });

    $('#omzetPeriodePrev').on('click', function () {
        const $input = $('#omzetPeriode');
        $input.val(parseInt($input.val(), 10) - 1);
        $input.closest('form').trigger('submit');
    });

    $('#omzetPeriodeNext').on('click', function () {
        const $input = $('#omzetPeriode');
        $input.val(parseInt($input.val(), 10) + 1);
        $input.closest('form').trigger('submit');
    });
</script>

<?= $this->endSection('isi') ?>
