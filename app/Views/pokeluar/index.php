<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Data PO Keluar
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<a href="<?= site_url('poKeluar/input') ?>" class="btn btn-primary">
    <i class="fa fa-plus-circle"></i> Input PO Keluar
</a>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?php if (session('message')) : ?>
    <div class="alert alert-success"><?= session('message') ?></div>
<?php endif ?>
<?php if (session('error')) : ?>
    <div class="alert alert-danger"><?= session('error') ?></div>
<?php endif ?>

<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<style>
    .aksi-buttons {
        align-items: center;
        display: flex;
        gap: 6px;
        justify-content: center;
    }

    .aksi-buttons .btn {
        align-items: center;
        border-radius: 50% !important;
        display: inline-flex;
        height: 34px;
        justify-content: center;
        padding: 0;
        width: 34px;
    }

    .po-toolbar {
        align-items: flex-start;
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
        position: relative;
        z-index: 30;
    }

    .po-filter-anchor {
        align-items: flex-end;
        display: flex;
        flex-direction: column;
        position: relative;
        width: min(100%, 32rem);
    }

    .po-search-shell {
        align-items: center;
        background: #fff;
        border: 1px solid #eef1f7;
        border-radius: 999px;
        box-shadow: 0 3px 10px rgba(15, 23, 42, .025);
        display: flex;
        gap: .65rem;
        min-height: 3.4rem;
        padding: .35rem .45rem .35rem 1.25rem;
        width: 100%;
    }

    .po-search-shell > i {
        color: #6b7280;
        font-size: 1.1rem;
    }

    .po-search-input {
        background: transparent;
        border: 0;
        box-shadow: none;
        color: #4b5563;
        flex: 1 1 auto;
        font-size: .95rem;
        min-width: 0;
        outline: 0;
    }

    .po-search-input:focus {
        box-shadow: none;
        outline: 0;
    }

    .po-filter-toggle {
        align-items: center;
        background: #16869a;
        border: 0;
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        flex: 0 0 2.65rem;
        height: 2.65rem;
        justify-content: center;
        width: 2.65rem;
    }

    .po-filter-toggle:hover,
    .po-filter-toggle:focus {
        background: #126f7f;
        color: #fff;
        outline: 0;
    }

    .po-filter-panel {
        background: #fff;
        border: 1px solid #edf1f5;
        border-radius: 22px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .1);
        display: none;
        max-width: min(46rem, calc(100vw - 4rem));
        opacity: 0;
        overflow: hidden;
        pointer-events: none;
        position: absolute;
        right: 0;
        top: calc(100% + .75rem);
        transform: translateY(-.45rem) scale(.985);
        transform-origin: top right;
        transition: opacity .18s ease, transform .18s ease;
        width: 42rem;
    }

    .po-filter-panel.is-visible {
        display: block;
    }

    .po-filter-panel.is-open {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    .po-filter-header {
        align-items: center;
        display: flex;
        justify-content: space-between;
        padding: 1.2rem 1.35rem .9rem;
    }

    .po-filter-title {
        color: #111827;
        font-size: 1.2rem;
        font-weight: 800;
        margin: 0;
    }

    .po-filter-close {
        align-items: center;
        background: #fff;
        border: 0;
        border-radius: 999px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .12);
        color: #111827;
        display: inline-flex;
        height: 2.4rem;
        justify-content: center;
        width: 2.4rem;
    }

    .po-filter-section {
        border-top: 1px solid #edf1f5;
        padding: 1.05rem 1.35rem;
    }

    .po-filter-label {
        color: #718096;
        font-size: .9rem;
        font-weight: 800;
        margin-bottom: .75rem;
    }

    .po-filter-chip-row,
    .po-filter-field-row,
    .po-filter-action-row {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .po-filter-chip {
        background: #fff;
        border: 1px solid #e8edf4;
        border-radius: 999px;
        color: #111827;
        font-weight: 600;
        min-height: 2.45rem;
        padding: .45rem .9rem;
    }

    .po-filter-chip.is-active,
    .po-filter-chip:hover {
        background: #eef6e8;
        border-color: #d9e9cf;
    }

    .po-filter-date,
    .po-filter-select {
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 999px;
        color: #111827;
        min-height: 2.55rem;
        padding: .45rem .85rem;
    }

    .po-filter-date {
        min-width: 11rem;
    }

    .po-filter-select {
        min-width: 11rem;
    }

    .po-filter-apply,
    .po-filter-reset {
        border: 0;
        border-radius: 999px;
        font-weight: 800;
        min-height: 2.55rem;
        padding: .45rem 1.2rem;
    }

    .po-filter-apply {
        background: #16869a;
        color: #fff;
    }

    .po-filter-reset {
        background: #eef2f7;
        color: #4b5563;
    }

    @media (max-width: 768px) {
        .po-toolbar {
            justify-content: stretch;
        }

        .po-filter-anchor,
        .po-search-shell,
        .po-filter-panel {
            max-width: none;
            width: 100%;
        }

        .po-filter-panel {
            left: 0;
            right: auto;
        }

        .po-filter-date,
        .po-filter-select,
        .po-filter-apply,
        .po-filter-reset {
            width: 100%;
        }
    }
</style>

<div class="po-toolbar">
    <div class="po-filter-anchor">
        <div class="po-search-shell">
            <i class="fas fa-search"></i>
            <input type="search" id="poKeluarSearchInput" class="po-search-input" placeholder="Search anything..." aria-label="Search anything">
            <button type="button" class="po-filter-toggle" id="poKeluarFilterToggle" title="Buka filter" aria-controls="poKeluarFilterPanel" aria-expanded="false">
                <i class="fas fa-sliders-h"></i>
            </button>
        </div>

        <section class="po-filter-panel" id="poKeluarFilterPanel" aria-hidden="true">
            <div class="po-filter-header">
                <h3 class="po-filter-title">Filter</h3>
                <button type="button" class="po-filter-close" id="poKeluarFilterClose" title="Tutup filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="po-filter-section">
                <div class="po-filter-label">Filter by Date Range</div>
                <div class="po-filter-chip-row">
                    <button type="button" class="po-filter-chip" data-range="30">Last 30 Days</button>
                    <button type="button" class="po-filter-chip" data-range="180">Last 6 Months</button>
                </div>
            </div>

            <div class="po-filter-section">
                <div class="po-filter-label">Custom Date Range</div>
                <div class="po-filter-field-row">
                    <input type="date" id="poKeluarTglAwal" class="po-filter-date" aria-label="Start date">
                    <input type="date" id="poKeluarTglAkhir" class="po-filter-date" aria-label="End date">
                </div>
            </div>

            <div class="po-filter-section">
                <div class="po-filter-label">Show Data</div>
                <div class="po-filter-field-row">
                    <select id="poKeluarPageLength" class="po-filter-select" aria-label="Show data">
                        <option value="10">10 entries</option>
                        <option value="25" selected>25 entries</option>
                        <option value="50">50 entries</option>
                        <option value="100">100 entries</option>
                    </select>
                    <select id="poKeluarStatusFilter" class="po-filter-select" aria-label="Status">
                        <option value="">Semua Status</option>
                        <option value="AKTIF">AKTIF</option>
                        <option value="NG">NG</option>
                        <option value="DIBATALKAN">DIBATALKAN</option>
                    </select>
                    <select id="poKeluarJenisFilter" class="po-filter-select" aria-label="Jenis PO">
                        <option value="">Semua Jenis PO</option>
                        <option value="PO Material">PO Material</option>
                        <option value="PO Produk">PO Produk</option>
                        <option value="PO Jasa">PO Jasa</option>
                    </select>
                    <select id="poKeluarTransaksiFilter" class="po-filter-select" aria-label="Transaksi">
                        <option value="">Semua Transaksi</option>
                        <option value="Beli">Beli</option>
                        <option value="Titip Proses">Titip Proses</option>
                    </select>
                    <select id="poKeluarSupplierFilter" class="po-filter-select" aria-label="Nama Supplier">
                        <option value="">Semua Supplier</option>
                        <?php foreach (array_unique(array_filter(array_column($rows, 'supplier_nama'))) as $supplierName) : ?>
                            <option value="<?= esc($supplierName) ?>"><?= esc($supplierName) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>

            <div class="po-filter-section">
                <div class="po-filter-action-row">
                    <button type="button" class="po-filter-apply" id="poKeluarFilterApply">Tampilkan</button>
                    <button type="button" class="po-filter-reset" id="poKeluarFilterReset">Reset</button>
                </div>
            </div>
        </section>
    </div>
</div>
<div class="table-responsive">
    <table id="tablePoKeluar" class="table table-bordered table-striped table-hover" style="width:100%">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Status</th>
                <th>Jenis PO</th>
                <th>No. PO</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Transaksi</th>
                <th>QTY</th>
                <th>Total Nominal</th>
                <th>Status Penerimaan</th>
                <th style="width: 120px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $i => $row) : ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td class="text-center">
                        <span class="badge badge-<?= $row['status'] === 'NG' ? 'danger' : ($row['status'] === 'AKTIF' ? 'success' : 'secondary') ?>">
                            <?= esc($row['status']) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php
                            $jenisPo = strtolower((string) ($row['jenis_po'] ?? 'material'));
                            $badgeJenisPo = [
                                'produk' => 'success',
                                'jasa' => 'warning',
                                'material' => 'primary',
                            ][$jenisPo] ?? 'secondary';
                            $labelJenisPo = [
                                'produk' => 'PO Produk',
                                'jasa' => 'PO Jasa',
                                'material' => 'PO Material',
                            ][$jenisPo] ?? 'PO Material';
                        ?>
                        <span class="badge badge-<?= $badgeJenisPo ?>"><?= esc($labelJenisPo) ?></span>
                    </td>
                    <td><?= esc($row['no_po']) ?></td>
                    <td class="text-center"><?= date('d-m-Y', strtotime($row['tgl_po'])) ?></td>
                    <td><?= esc($row['supplier_nama']) ?></td>
                    <td class="text-center">
                        <span class="badge badge-<?= ($row['jenis_transaksi'] ?? 'Beli') === 'Titip Proses' ? 'info' : 'light' ?>">
                            <?= esc($row['jenis_transaksi'] ?? 'Beli') ?>
                        </span>
                    </td>
                    <td class="text-right"><?= number_format((float) $row['total_qty'], 0, ',', '.') ?></td>
                    <td class="text-right">Rp <?= number_format((float) $row['total_nominal'], 0, ',', '.') ?></td>
                    <td class="text-center">
                        <?php
                            $badgePenerimaan = [
                                'Belum Diterima' => 'secondary',
                                'Diterima Sebagian' => 'warning',
                                'Diterima Lengkap' => 'success',
                                'Kirim Langsung (Tanpa Stok TRE)' => 'info',
                                'Dikirim untuk Proses ke Vendor' => 'info',
                                'Menunggu Invoice Jasa' => 'warning',
                                'Selesai' => 'primary',
                                'NG' => 'danger',
                            ][$row['status_penerimaan']] ?? 'secondary';
                        ?>
                        <span class="badge badge-<?= $badgePenerimaan ?>"><?= esc($row['status_penerimaan']) ?></span>
                    </td>
                    <td class="text-center">
                        <div class="aksi-buttons">
                            <a href="<?= site_url('poKeluar/detail/' . $row['id']) ?>" class="btn btn-sm btn-info" title="Detail">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="<?= site_url('poKeluar/edit/' . $row['id']) ?>" class="btn btn-sm btn-secondary" title="Edit">
                                <i class="fa fa-pencil-alt"></i>
                            </a>
                            <?php if ($row['status'] === 'AKTIF') : ?>
                                <?= form_open('/poKeluar/batal/' . $row['id'], ['class' => 'd-inline form-batal']) ?>
                                <button type="submit" class="btn btn-sm btn-danger" title="Batalkan">
                                    <i class="fa fa-ban"></i>
                                </button>
                                <?= form_close() ?>
                            <?php endif ?>
                            <?php if ($row['status'] === 'DIBATALKAN') : ?>
                                <?= form_open('/poKeluar/hapus/' . $row['id'], ['class' => 'd-inline form-hapus']) ?>
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="fa fa-trash-alt"></i>
                                </button>
                                <?= form_close() ?>
                            <?php endif ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>

<script>
    $(function() {
        const table = $('#tablePoKeluar').DataTable({
            searching: true,
            stateSave: true,
            stateDuration: -1,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            pageLength: 10,
            order: [
                [4, 'desc']
            ]
        });
        $('#poKeluarSearchInput').val(table.search());

        const filterPanel = $('#poKeluarFilterPanel');
        const filterToggle = $('#poKeluarFilterToggle');
        const filterClose = $('#poKeluarFilterClose');
        let searchTimer = null;
        let filterPanelTimer = null;

        function toggleFilterPanel(forceOpen = null) {
            const willOpen = forceOpen === null ? !filterPanel.hasClass('is-open') : forceOpen;
            window.clearTimeout(filterPanelTimer);

            if (willOpen) {
                filterPanel.addClass('is-visible');
                window.requestAnimationFrame(function() {
                    filterPanel.addClass('is-open');
                });
            } else {
                filterPanel.removeClass('is-open');
                filterPanelTimer = window.setTimeout(function() {
                    filterPanel.removeClass('is-visible');
                }, 180);
            }

            filterPanel.attr('aria-hidden', willOpen ? 'false' : 'true');
            filterToggle.attr('aria-expanded', willOpen ? 'true' : 'false');
        }

        function formatDateInput(date) {
            return date.toISOString().slice(0, 10);
        }

        function parseDateColumn(value) {
            const match = String(value || '').match(/^(\d{2})-(\d{2})-(\d{4})$/);
            if (!match) {
                return null;
            }

            return new Date(Number(match[3]), Number(match[2]) - 1, Number(match[1]));
        }

        $.fn.dataTable.ext.search.push(function(settings, data) {
            if (settings.nTable.id !== 'tablePoKeluar') {
                return true;
            }

            const startValue = $('#poKeluarTglAwal').val();
            const endValue = $('#poKeluarTglAkhir').val();
            const rowDate = parseDateColumn(data[4]);
            const hasDateFilter = Boolean(startValue || endValue);

            if (hasDateFilter) {
                if (!rowDate) {
                    return false;
                }

                if (startValue && rowDate < new Date(startValue + 'T00:00:00')) {
                    return false;
                }

                if (endValue && rowDate > new Date(endValue + 'T23:59:59')) {
                    return false;
                }
            }

            const statusFilter = $('#poKeluarStatusFilter').val();
            const jenisFilter = $('#poKeluarJenisFilter').val();
            const transaksiFilter = $('#poKeluarTransaksiFilter').val();
            const supplierFilter = $('#poKeluarSupplierFilter').val();

            if (statusFilter && String(data[1] || '').trim() !== statusFilter) {
                return false;
            }

            if (jenisFilter && String(data[2] || '').trim() !== jenisFilter) {
                return false;
            }

            if (supplierFilter && String(data[5] || '').trim() !== supplierFilter) {
                return false;
            }

            if (transaksiFilter && String(data[6] || '').trim() !== transaksiFilter) {
                return false;
            }
            return true;
        });

        filterToggle.on('click', function() {
            toggleFilterPanel();
        });

        filterClose.on('click', function() {
            toggleFilterPanel(false);
        });

        $('#poKeluarSearchInput').on('input', function() {
            const keyword = this.value;
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(function() {
                table.search(keyword).draw();
            }, 350);
        });

        $('.po-filter-chip').on('click', function() {
            const days = Number($(this).data('range')) || 30;
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - days);
            $('.po-filter-chip').removeClass('is-active');
            $(this).addClass('is-active');
            $('#poKeluarTglAwal').val(formatDateInput(startDate));
            $('#poKeluarTglAkhir').val(formatDateInput(endDate));
        });

        $('#poKeluarPageLength').on('change', function() {
            table.page.len(Number(this.value)).draw();
        });

        $('#poKeluarStatusFilter, #poKeluarJenisFilter, #poKeluarTransaksiFilter, #poKeluarSupplierFilter').on('change', function() {
            table.draw();
        });

        $('#poKeluarFilterApply').on('click', function() {
            table.draw();
            toggleFilterPanel(false);
        });

        $('#poKeluarFilterReset').on('click', function() {
            $('#poKeluarTglAwal').val('');
            $('#poKeluarTglAkhir').val('');
            $('#poKeluarSearchInput').val('');
            $('#poKeluarStatusFilter').val('');
            $('#poKeluarJenisFilter').val('');
            $('#poKeluarTransaksiFilter').val('');
            $('#poKeluarSupplierFilter').val('');
            $('.po-filter-chip').removeClass('is-active');
            $('#poKeluarPageLength').val('25');
            table.search('').page.len(25).draw();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                toggleFilterPanel(false);
            }
        });

        $('.form-batal').on('submit', function(e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Batalkan PO Keluar?',
                text: 'PO Keluar akan diberi status DIBATALKAN.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        $('.form-hapus').on('submit', function(e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Hapus PO Keluar?',
                text: 'Data PO Keluar ini akan dihapus permanen dan tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
<?= $this->endSection('isi') ?>
