<?php
    $poLocked = (bool) ($poLocked ?? false);
    $canEditDetail = !$poLocked && \App\Libraries\AccessControl::can('order.po_masuk.edit_detail');
    $canCloseItem = \App\Libraries\AccessControl::can('order.po_masuk.close_item');
    $canReopenClose = $canCloseItem;
    $closingKeluarByKode = [];
    $closingMasukByKode = [];

    foreach (($closing['keluar'] ?? []) as $log) {
        if (!empty($log['reopened_at'])) {
            continue;
        }

        $kode = (string) ($log['kodebrg'] ?? '');
        $closingKeluarByKode[$kode][] = $log;
    }

    foreach (($closing['masuk'] ?? []) as $log) {
        if (!empty($log['reopened_at'])) {
            continue;
        }

        $kode = (string) ($log['kodebrg'] ?? '');
        $closingMasukByKode[$kode][] = $log;
    }
?>
<style>
    .po-section-card,
    .po-section-body.tampilDataDetail,
    .po-section-body.po-table-wrap,
    .tampilDataDetail.po-table-wrap {
        overflow: visible !important;
    }    #datadetail .po-close-action {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: .35rem;
        flex-wrap: nowrap;
        width: 100%;
    }

    #datadetail th:last-child,
    #datadetail td:last-child {
        min-width: 230px;
        width: 230px;
        text-align: right !important;
    }

    #datadetail .po-close-action > * {
        flex: 0 0 auto;
    }

    #datadetail .po-close-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        background: #ffc107;
        color: #2f2500;
        font-size: .72rem;
        font-weight: 800;
        line-height: 1;
        padding: .38rem .58rem;
    }

    #datadetail .po-close-history-toggle,
    .po-close-history-popover .btn-reopen-close {
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
        padding: .34rem .62rem;
    }

    .po-close-history-popover {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        z-index: 2050;
        width: min(430px, calc(100vw - 24px));
        padding: .95rem 1rem;
        border: 1px solid #dce7ef;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 18px 40px rgba(15, 45, 70, .16);
        color: #111827;
        text-align: left;
    }

    .po-close-history-popover.is-open {
        display: block;
    }

    .po-close-history-title {
        display: flex;
        align-items: center;
        gap: .4rem;
        color: #111827;
        font-weight: 800;
        margin-bottom: .55rem;
    }

    .po-close-history-list {
        margin: 0;
        padding-left: 1rem;
        color: #6b7280;
        font-size: .84rem;
        line-height: 1.55;
    }

    .po-close-history-list li + li {
        margin-top: .55rem;
    }

    .po-close-history-list strong {
        color: #111827;
    }

    .po-close-history-list .btn-reopen-close {
        margin-left: .45rem;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .po-close-history-list .btn-reopen-close {
            display: inline-flex;
            margin-top: .45rem;
            margin-left: 0;
        }
    }
</style>
<table class="table table-sm table-hover table bordered" style="width:100%" id="datadetail">
    <thead>
        <tr>
            <?php
            $totalBerat = 0;
            foreach ($tampildata->getResultArray() as $row) :
                $totalBerat += $row['detsubtotal'];
            endforeach
            ?>
            <input type="hidden" id="totalberat" value="<?= $totalBerat ?>">
        </tr>
    </thead>
    <thead>
        <tr>
            <th style="text-align: center;">No</th>
            <th style="text-align: center;">Kode Produk</th>
            <th style="text-align: center;">Nama Produk</th>
            <th style="text-align: center;">Berat Satuan</th>
            <th style="text-align: center;">Jumlah</th>
            <th style="text-align: center;">QTY Terkirim</th>
            <th style="text-align: center;">Nilai Sudah Ditagihkan</th>
            <th style="text-align: center;">Subtotal</th>
            <th style="text-align: center;">Harga</th>
            <?php if ($canEditDetail || $canCloseItem) :  ?>
                <th style="text-align: center;">Aksi</th>
            <?php endif ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $nomor = 1;
        foreach ($tampildata->getResultArray() as $row) :
            $qtySisa = max((float) $row['detqty'] - (float) ($row['detkirim_awal'] ?? 0) - (float) ($row['detkirim'] ?? 0), 0);
            $kodeProduk = (string) $row['detkodebrg'];
            $closingKeluarItem = $closingKeluarByKode[$kodeProduk] ?? [];
            $closingMasukItem = $closingMasukByKode[$kodeProduk] ?? [];
            $hasClosingHistory = $closingKeluarItem || $closingMasukItem;
            $historyId = 'poCloseHistory_' . (int) $row['id'];
            $hargaTampil = (float) ($row['detharga'] ?? 0);
            if ($hargaTampil <= 0) {
                $hargaOutstanding = (float) ($row['harga_outstanding'] ?? 0);
                $hargaMaster = (float) ($row['harga_master'] ?? 0);
                if ($hargaOutstanding > 0) {
                    $hargaTampil = $hargaOutstanding;
                } elseif ($hargaMaster > 0) {
                    $hargaTampil = (float) $row['detqty'] * $hargaMaster;
                }
            }
        ?>
            <tr data-qty="<?= esc($row['detqty']) ?>" data-terkirim-awal="<?= esc($row['detkirim_awal'] ?? 0) ?>" data-invoice-awal="<?= esc($row['detinvoice_awal'] ?? 0) ?>">
                <td style="text-align: center;">
                    <?= $nomor++; ?>
                    <input type="hidden" value="<?= $row['id'] ?>" id="iddetail">
                </td>
                <td style="text-align: center;"><?= $row['detkodebrg'] ?></td>
                <td style="text-align: center;"><?= $row['namabarang'] ?></td>
                <td style="text-align: right;"><?= number_format($row['detberat'], 4, ",", ".") ?> KG</td>
                <td style="text-align: right;"><?= number_format($row['detqty'], 0, ",", ".") ?> Pcs</td>
                <td style="text-align: right;"><?= number_format((float) ($row['detkirim_awal'] ?? 0) + (float) ($row['detkirim'] ?? 0), 0, ",", ".") ?> Pcs</td>
                <td style="text-align: right;"><?= number_format((float) ($row['detinvoice_awal'] ?? 0), 0, ",", ".") ?></td>
                <td style="text-align: right;"><?= number_format($row['detsubtotal'], 4, ",", ".") ?> KG</td>
                <td style="text-align: right;"><?= number_format($hargaTampil, 0, ",", ".") ?> </td>
                <?php if ($canEditDetail || $canCloseItem) :  ?>
                    <td style="text-align: center;">
                        <div class="po-close-action">
                            <?php if ($hasClosingHistory) : ?>
                                <span class="po-close-badge">Ditutup</span>
                                <button type="button" class="btn btn-sm btn-outline-info po-close-history-toggle" data-target="#<?= esc($historyId) ?>" aria-expanded="false" aria-controls="<?= esc($historyId) ?>">
                                    Riwayat <i class="fa fa-chevron-down"></i>
                                </button>
                            <?php endif ?>
                            <?php if ($canEditDetail) : ?>
                                <button type="button" class="btn btn-sm btn-danger" onclick="hapusItem('<?= $row['id'] ?>')" title="Hapus Item">
                                    <i class="fa fa-trash-alt"></i>
                                </button>
                            <?php endif ?>
                            <?php if ($canCloseItem && $qtySisa > 0) : ?>
                                <button type="button" class="btn btn-sm btn-secondary btn-close-item" data-id="<?= (int) $row['id'] ?>" data-kode="<?= esc($row['detkodebrg']) ?>" data-qty-sisa="<?= $qtySisa ?>" title="Close Item (pindahkan sisa qty ke PO lain)">
                                    <i class="fa fa-box-open"></i>
                                </button>
                            <?php endif ?>
                            <?php if ($poLocked && $canCloseItem) : ?>
                                <button type="button" class="btn btn-sm btn-info btn-koreksi-qty" data-id="<?= (int) $row['id'] ?>" data-kode="<?= esc($row['detkodebrg']) ?>" data-qty="<?= (float) $row['detqty'] ?>" data-terkirim="<?= (float) ($row['detkirim_awal'] ?? 0) + (float) ($row['detkirim'] ?? 0) ?>" title="Koreksi Qty PO">
                                    <i class="fa fa-edit"></i>
                                </button>
                            <?php endif ?>
                        <?php if ($hasClosingHistory) : ?>
                            <div class="po-close-history-popover" id="<?= esc($historyId) ?>">
                                <div class="po-close-history-title"><i class="fa fa-history"></i> Riwayat penutupan item</div>
                                <ul class="po-close-history-list">
                                    <?php foreach ($closingKeluarItem as $log) : ?>
                                        <li>
                                            <strong><?= esc($log['kodebrg']) ?></strong> &middot; <?= number_format((float) $log['qty_dipindah'], 0, ',', '.') ?> pcs
                                            <?php if (!empty($log['nopo_tujuan'])) : ?>
                                                dicatat sebagai bagian dari PO <?= esc($log['nopo_tujuan']) ?>
                                            <?php else : ?>
                                                disesuaikan (qty salah input)
                                            <?php endif ?>
                                            <?php if (!empty($log['ditutup_pada'])) : ?>
                                                &middot; <?= date('d-m-Y H:i', strtotime($log['ditutup_pada'])) ?>
                                            <?php endif ?>
                                            <?php if (!empty($log['ditutup_oleh'])) : ?>
                                                oleh <?= esc($log['ditutup_oleh']) ?>
                                            <?php endif ?>
                                            <?php if ($canReopenClose) : ?>
                                                <button type="button" class="btn btn-sm btn-outline-info btn-reopen-close" data-id="<?= (int) $log['id'] ?>" data-kode="<?= esc($log['kodebrg']) ?>" data-qty="<?= (float) $log['qty_dipindah'] ?>">
                                                    <i class="fa fa-unlock"></i> Buka Close
                                                </button>
                                            <?php endif ?>
                                        </li>
                                    <?php endforeach ?>
                                    <?php foreach ($closingMasukItem as $log) : ?>
                                        <li>
                                            Qty <strong><?= esc($log['kodebrg']) ?></strong> di sini sebagian (<?= number_format((float) $log['qty_dipindah'], 0, ',', '.') ?> pcs) sebenarnya bagian dari PO <?= esc($log['nopo_asal']) ?> yang ditutup
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                            </div>
                        <?php endif ?>
                        </div>
                    </td>
                <?php endif ?>
            </tr>

        <?php
        endforeach
        ?>
    </tbody>
</table>
<script>
    <?php if (!$poLocked) : ?>
    $('#datadetail tbody').on('click', 'tr', function(event) {
        if ($(event.target).closest('button, a').length) {
            return;
        }

        let row = $(this).closest('tr');

        let kodebarang = row.find('td:eq(1)').text();
        let id = row.find('td input').val();
        let qty = row.data('qty') || 1;
        let terkirimAwal = row.data('terkirim-awal') || 0;
        let invoiceAwal = row.data('invoice-awal') || 0;

        $('#iddetail').val(id);
        $('#kodebarang').val(kodebarang);
        $('#kodebarang_pilih').val(kodebarang);
        $('#jml').val(qty);
        $('#terkirim_awal').val(terkirimAwal);
        $('#invoice_awal').val(invoiceAwal);
        if (typeof syncProdukCombobox === 'function') {
            syncProdukCombobox();
        }

        $('#tombolBatal').fadeIn();
        $('#tombolEditItem').fadeIn();
        $('#tombolSimpanItem').fadeOut();
        ambilDataBarang();
    });

    <?php endif ?>

    $(document).on('click', '#tombolBatal', function(e) {
        e.preventDefault();
        kosong();
        tampilDataDetail();
        $('#tombolSimpanItem').fadeIn();
        $('#tombolEditItem').fadeOut();
        $('#tombolBatal').fadeOut();
    });
    $(document).on('click', '.po-close-history-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const button = $(this);
        const target = $(button.data('target'));
        const isOpen = target.hasClass('is-open');

        $('.po-close-history-popover').removeClass('is-open');
        $('.po-close-history-toggle').attr('aria-expanded', 'false').find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');

        if (isOpen) {
            return;
        }

        target.addClass('is-open');
        button.attr('aria-expanded', 'true');
        button.find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
    });

    $(document).on('click', function(e) {
        if ($(e.target).closest('.po-close-history-popover, .po-close-history-toggle, .btn-reopen-close').length) {
            return;
        }

        $('.po-close-history-popover').removeClass('is-open');
        $('.po-close-history-toggle').attr('aria-expanded', 'false').find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });

    $(window).on('scroll resize', function() {
        $('.po-close-history-popover').removeClass('is-open');
        $('.po-close-history-toggle').attr('aria-expanded', 'false').find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });

    $(document).on('click', '.btn-close-item', function(e) {
        e.stopPropagation();
        closeItem($(this).data('id'), $(this).data('kode'), parseFloat($(this).data('qty-sisa')) || 0);
    });

    $(document).on('click', '.btn-reopen-close', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('.po-close-history-popover').removeClass('is-open');
        $('.po-close-history-toggle').attr('aria-expanded', 'false').find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        reopenCloseLog($(this).data('id'), $(this).data('kode'), parseFloat($(this).data('qty')) || 0);
    });

    $(document).on('click', '.btn-koreksi-qty', function(e) {
        e.preventDefault();
        e.stopPropagation();
        koreksiQtyItem($(this).data('id'), $(this).data('kode'), parseFloat($(this).data('qty')) || 0, parseFloat($(this).data('terkirim')) || 0);
    });

    // Baris rincian alokasi penutupan -- 1 baris = 1 pecahan qty, bisa
    // "Pindah" (catatan ke PO lain yang udah punya item ini, angka PO
    // tujuan TIDAK berubah) atau "Sesuaikan" (qty emang salah input, cuma
    // dihapus, tidak nyambung kemana-mana). Dipakai bareng buat Close Item
    // (1 item) maupun Close PO (banyak item, tiap item punya container-nya
    // sendiri).
    function buatBarisAlokasi(containerSelector, totalSelector, daftarPoOptions, qtyAwal) {
        const opsiPo = daftarPoOptions.map(po => `<option value="${$('<div>').text(po.nopo).html()}">${$('<div>').text(po.nopo).html()}</option>`).join('');
        const row = $(`
            <div class="alokasi-row d-flex align-items-center mb-2" style="gap:6px;">
                <select class="form-control form-control-sm alokasi-tipe" style="max-width:150px;">
                    <option value="pindah">Pindah (catatan)</option>
                    <option value="sesuaikan">Sesuaikan (hapus)</option>
                </select>
                <select class="form-control form-control-sm alokasi-po" style="max-width:170px;">
                    <option value="">-- Pilih PO --</option>
                    ${opsiPo}
                </select>
                <input type="number" class="form-control form-control-sm alokasi-qty" placeholder="Qty" min="1" step="1" style="max-width:100px;" value="${qtyAwal ?? ''}">
                <button type="button" class="btn btn-sm btn-outline-danger alokasi-hapus"><i class="fa fa-times"></i></button>
            </div>
        `);

        row.find('.alokasi-tipe').on('change', function() {
            const isPindah = $(this).val() === 'pindah';
            row.find('.alokasi-po').toggle(isPindah).prop('disabled', !isPindah);
        });
        row.find('.alokasi-hapus').on('click', function() {
            row.remove();
            hitungAlokasiTotal(containerSelector, totalSelector);
        });
        row.find('.alokasi-qty').on('input', function() {
            hitungAlokasiTotal(containerSelector, totalSelector);
        });

        if (daftarPoOptions.length === 0) {
            row.find('.alokasi-tipe').val('sesuaikan').trigger('change');
            row.find('.alokasi-tipe option[value="pindah"]').prop('disabled', true);
        }

        $(containerSelector).append(row);
        hitungAlokasiTotal(containerSelector, totalSelector);
    }

    function hitungAlokasiTotal(containerSelector, totalSelector) {
        let total = 0;
        $(containerSelector).find('.alokasi-qty').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        $(totalSelector).text(total.toLocaleString('id-ID'));
        return total;
    }

    // Return null kalau ada baris yang belum valid (qty <=0, atau tipe
    // Pindah tapi PO tujuan belum dipilih). Container kosong (0 baris)
    // dianggap VALID dengan alokasi = [] -- dipakai di Close PO buat nandain
    // "item ini dilewati/dibiarkan", bukan error.
    function bacaAlokasi(containerSelector) {
        const alokasi = [];
        let valid = true;
        $(containerSelector).find('.alokasi-row').each(function() {
            const tipe = $(this).find('.alokasi-tipe').val();
            const nopoTujuan = tipe === 'pindah' ? $(this).find('.alokasi-po').val() : '';
            const qty = parseFloat($(this).find('.alokasi-qty').val()) || 0;
            if (qty <= 0 || (tipe === 'pindah' && !nopoTujuan)) {
                valid = false;
                return false;
            }
            alokasi.push({
                qty: qty,
                nopo_tujuan: nopoTujuan
            });
        });
        return valid ? alokasi : null;
    }

    function closeItem(id, kodebarang, qtySisa) {
        const idpelanggan = $('#idpelanggan').val();
        const nopo = $('#nopo').val();

        $.post('/po/daftarPoTujuanClose', {
            [csrfToken]: csrfHash,
            idpelanggan: idpelanggan,
            no_po_kecuali: nopo,
            kode_produk: kodebarang
        }, function(response) {
            const daftarPo = response.data || [];

            Swal.fire({
                title: 'Close Item',
                width: 650,
                html: `
                    <div class="text-left">
                        <p class="mb-2">Tutup item <b>${$('<div>').text(kodebarang).html()}</b>, sisa <b>${qtySisa.toLocaleString('id-ID')} pcs</b>.</p>
                        ${daftarPo.length === 0 ? '<div class="alert alert-info py-2 mb-2">Belum ada PO lain milik pelanggan ini yang punya item ini -- cuma bisa "Sesuaikan".</div>' : ''}
                        <div id="alokasiContainer"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="tambahAlokasiBtn"><i class="fa fa-plus"></i> Tambah Baris</button>
                        <div class="mt-2">Total dialokasikan: <b id="alokasiTotalTampil">0</b> / ${qtySisa.toLocaleString('id-ID')} pcs</div>
                        <small class="text-muted">Boleh kurang dari sisa -- sisanya tetap terbuka/outstanding seperti biasa di PO ini.</small>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Tutup Item',
                cancelButtonText: 'Batal',
                focusConfirm: false,
                didOpen: () => {
                    buatBarisAlokasi('#alokasiContainer', '#alokasiTotalTampil', daftarPo, qtySisa);
                    $('#tambahAlokasiBtn').on('click', () => buatBarisAlokasi('#alokasiContainer', '#alokasiTotalTampil', daftarPo));
                },
                preConfirm: () => {
                    const alokasi = bacaAlokasi('#alokasiContainer');
                    if (!alokasi || alokasi.length === 0) {
                        Swal.showValidationMessage('Minimal 1 baris, pastikan tiap baris punya qty > 0, dan PO tujuan dipilih kalau tipenya Pindah');
                        return false;
                    }
                    const total = alokasi.reduce((sum, a) => sum + a.qty, 0);
                    if (total - qtySisa > 0.0001) {
                        Swal.showValidationMessage('Total qty yang dialokasikan (' + total.toLocaleString('id-ID') + ') tidak boleh lebih dari sisa ' + qtySisa.toLocaleString('id-ID'));
                        return false;
                    }
                    return alokasi;
                }
            }).then(result => {
                if (!result.isConfirmed) return;

                $.post('/po/closeItem', {
                    [csrfToken]: csrfHash,
                    id: id,
                    alokasi: result.value
                }, function(closeResponse) {
                    if (closeResponse.error) {
                        Swal.fire('Gagal', closeResponse.error, 'error');
                        return;
                    }
                    Swal.fire('Berhasil', closeResponse.sukses, 'success');
                    tampilDataDetail();
                    ambilTotalBerat();
                }, 'json').fail(function(xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.error || 'Item PO gagal ditutup.', 'error');
                });
            });
        }, 'json').fail(function(xhr) {
            Swal.fire('Gagal', xhr.responseJSON?.error || 'Gagal memuat daftar PO tujuan.', 'error');
        });
    }

    function koreksiQtyItem(id, kodebarang, qtyLama, qtyTerkirim) {
        Swal.fire({
            title: 'Koreksi Qty PO',
            width: 560,
            html: `
                <div class="text-left">
                    <p class="mb-2">Item <b>${$('<div>').text(kodebarang).html()}</b></p>
                    <small class="text-muted d-block mb-2">Qty sekarang: ${qtyLama.toLocaleString('id-ID')} pcs. Qty sudah terkirim: ${qtyTerkirim.toLocaleString('id-ID')} pcs.</small>
                    <label class="mb-1">Qty PO yang benar</label>
                    <input type="number" id="koreksiQtyBaru" class="form-control" min="${qtyTerkirim}" step="1" value="${qtyLama}">
                    <small class="text-muted">Qty baru tidak boleh lebih kecil dari qty yang sudah terkirim.</small>
                    <label class="mb-1 mt-3">Alasan koreksi</label>
                    <textarea id="koreksiQtyAlasan" class="form-control" rows="3" placeholder="Contoh: qty PO dari customer ternyata salah input"></textarea>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Simpan Koreksi',
            cancelButtonText: 'Batal',
            focusConfirm: false,
            preConfirm: () => {
                const qtyBaru = parseFloat($('#koreksiQtyBaru').val()) || 0;
                const alasan = $('#koreksiQtyAlasan').val().trim();
                if (qtyBaru <= 0) {
                    Swal.showValidationMessage('Qty baru harus lebih dari 0');
                    return false;
                }
                if (qtyBaru < qtyTerkirim) {
                    Swal.showValidationMessage('Qty baru tidak boleh lebih kecil dari qty yang sudah terkirim');
                    return false;
                }
                if (Math.abs(qtyBaru - qtyLama) < 0.0001) {
                    Swal.showValidationMessage('Qty baru masih sama dengan qty sekarang');
                    return false;
                }
                if (!alasan) {
                    Swal.showValidationMessage('Alasan koreksi wajib diisi');
                    return false;
                }
                return { qtyBaru, alasan };
            }
        }).then(result => {
            if (!result.isConfirmed) return;

            $.post('/po/koreksiQtyItem', {
                [csrfToken]: csrfHash,
                id: id,
                qty_baru: result.value.qtyBaru,
                alasan: result.value.alasan
            }, function(response) {
                if (response.error) {
                    Swal.fire('Gagal', response.error, 'error');
                    return;
                }
                Swal.fire('Berhasil', response.sukses, 'success');
                tampilDataDetail();
                ambilTotalBerat();
            }, 'json').fail(function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.error || 'Qty PO gagal dikoreksi.', 'error');
            });
        });
    }

    function reopenCloseLog(id, kodebarang, qty) {
        Swal.fire({
            title: 'Buka Close Item?',
            width: 560,
            html: `
                <div class="text-left">
                    <p class="mb-2">Qty <b>${qty.toLocaleString('id-ID')} pcs</b> untuk item <b>${$('<div>').text(kodebarang).html()}</b> akan dibuka lagi ke PO ini.</p>
                    <small class="text-muted">Transaksi lama seperti pengiriman dan invoice tidak diubah. Yang dibuka hanya sisa qty yang dulu ditutup.</small>
                    <textarea id="reopenCloseNote" class="form-control mt-3" rows="3" placeholder="Catatan alasan buka close"></textarea>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Buka Close',
            cancelButtonText: 'Batal',
            focusConfirm: false,
            preConfirm: () => {
                const note = $('#reopenCloseNote').val().trim();
                if (!note) {
                    Swal.showValidationMessage('Catatan alasan buka close wajib diisi');
                    return false;
                }
                return note;
            }
        }).then(result => {
            if (!result.isConfirmed) return;

            $.post('/po/reopenCloseLog', {
                [csrfToken]: csrfHash,
                id: id,
                note: result.value
            }, function(response) {
                if (response.error) {
                    Swal.fire('Gagal', response.error, 'error');
                    return;
                }
                Swal.fire('Berhasil', response.sukses, 'success');
                tampilDataDetail();
                ambilTotalBerat();
            }, 'json').fail(function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.error || 'Close gagal dibuka.', 'error');
            });
        });
    }

    function hapusItem(id) {
        Swal.fire({
            title: 'Hapus Item ?',
            text: "Yakin item ini dihapus ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus !'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: '<?= site_url('po/hapusItemDetail') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        id: id
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            Swal.fire('Berhasil', response.sukses, 'success');
                            tampilDataDetail();
                            ambilTotalBerat();
                            kosong();
                            $('#tombolSimpanItem').fadeIn();
                            $('#tombolEditItem').fadeOut();
                            $('#tombolBatal').fadeOut();
                        }
                    }
                });
            }
        })
    }
</script>
