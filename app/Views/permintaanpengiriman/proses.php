<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Proses Permintaan Pengiriman
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<style>.card-header .card-title { float: none; width: 100%; }</style>
<span class="d-flex justify-content-between align-items-center w-100">
    <button class="btn btn-warning" onclick="location.href='/barangkeluar/data#permintaan'">
        <i class="fa fa-undo"></i> Kembali
    </button>
    <a class="btn btn-primary" href="/permintaanPengiriman/pilih-cetak/<?= sha1($header['id']) ?>">
        <i class="fa fa-print"></i> Print
    </a>
</span>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?php
$formatTanggalInput = static function ($value): string {
    if (empty($value)) {
        return '';
    }

    $timestamp = strtotime((string) $value);
    return $timestamp ? date('d-m-Y', $timestamp) : (string) $value;
};
?>
<input type="hidden" id="permintaanId" value="<?= $header['id'] ?>">

<div class="row">
    <div class="col-lg-3">
        <label>Tanggal Permintaan</label>
        <input class="form-control" value="<?= esc($header['tanggal']) ?>" readonly>
    </div>
    <div class="col-lg-3">
        <label>Tanggal Pengiriman</label>
        <input type="date" id="tanggalPengiriman" class="form-control" value="<?= esc(($header['tanggal_pengiriman'] ?? '') ?: date('Y-m-d')) ?>">
    </div>
    <div class="col-lg-3">
        <label>Pelanggan</label>
        <select id="idPelangganKirim" class="form-control" disabled>
            <option value="">-- Ikut No. PO yang dipilih di tabel bawah --</option>
            <?php foreach ($pelanggan as $pel) : ?>
                <option value="<?= $pel['pelid'] ?>"><?= esc($pel['pelnama']) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="col-lg-3">
        <label>Keterangan</label>
        <input class="form-control" value="<?= esc($header['keterangan']) ?>" readonly>
    </div>
</div>

<hr>

<p class="text-muted">Klik produk dari tabel item dibawah untuk mengisi/mengubah rencana kirimnya, atau cari produk baru kalau belum ada di daftar (mis. untuk kirim langsung tanpa menunggu permintaan bertahap).</p>

<div class="row">
    <input type="hidden" id="detailProduk">
    <input type="hidden" id="kodeProduk">
    <input type="hidden" id="belumKirim">
    <input type="hidden" id="kodebarang">
    <div class="col-lg-3">
        <label for="namaProdukInput">Nama Produk</label>
        <div class="tre-inline-combobox tre-inline-combobox-solo" id="namaProdukCombobox">
            <input type="text" id="namaProdukInput" class="form-control" placeholder="Klik dari tabel di bawah, atau cari produk baru" autocomplete="off">
            <div class="tre-inline-combobox-menu" id="namaProdukComboboxMenu"></div>
        </div>
    </div>
    <div class="col-lg-3">
        <label>Asal Gudang</label>
        <select id="gudangId" class="form-control">
            <option value="">-- Pilih --</option>
            <?php foreach ($gudang as $gdg) : ?>
                <option value="<?= $gdg['gdgid'] ?>"><?= esc($gdg['gdgnama']) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="col-lg-2">
        <label>Stok</label>
        <input id="stokGudang" class="form-control" readonly>
    </div>
    <div class="col-lg-2">
        <label>Qty Kirim</label>
        <input id="qtyKirim" type="number" class="form-control" value="1" min="1">
    </div>
    <div class="col-lg-2">
        <label class="d-block">&nbsp;</label>
        <button id="simpanRencana" class="btn btn-success btn-block">
            <i class="fa fa-save"></i> Simpan
        </button>
    </div>
</div>

<hr>

<h5>Tabel Item</h5>
<table class="table table-sm table-bordered" id="tabelDetailProduk">
    <thead>
        <tr>
            <th>No</th>
            <th>Kode Produk</th>
            <th>Nama Pelanggan</th>
            <th>No. PO</th>
            <th>Tanggal PO</th>
            <th>Nama Produk</th>
            <th class="text-right">Jumlah</th>
            <th class="text-right">Terkirim</th>
            <th class="text-right">Belum Terkirim</th>
            <th class="text-center">#</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($details as $index => $detail) : ?>
            <?php $sisa = (int) $detail['qty'] - (int) $detail['terkirim']; ?>
            <tr class="<?= $sisa > 0 ? 'detail-row' : 'text-muted' ?>"
                data-id="<?= $detail['id'] ?>"
                data-kode="<?= esc($detail['kode_produk']) ?>"
                data-nama="<?= esc($detail['nama_produk']) ?>"
                data-sisa="<?= $sisa ?>"
                data-idpel="<?= (int) $detail['idpel_terpilih'] ?>"
                style="<?= $sisa > 0 ? 'cursor: pointer;' : '' ?>"
                title="<?= $sisa > 0 ? 'Klik untuk mengisi / mengubah' : 'Produk sudah terkirim semua' ?>">
                <td><?= $index + 1 ?></td>
                <td><?= esc($detail['kode_produk']) ?></td>
                <td><span class="tampil-pelanggan-detail"><?= esc($detail['pelnama_terpilih']) ?></span></td>
                <td>
                    <select class="form-control form-control-sm input-po-detail" data-detail-id="<?= $detail['id'] ?>" <?= $sisa > 0 ? '' : 'disabled' ?>>
                        <option value="">-- Pilih No. PO --</option>
                        <?php foreach ($detail['po_list'] as $po) : ?>
                            <option value="<?= esc($po['nopo']) ?>"
                                    data-idpel="<?= (int) $po['idpel'] ?>"
                                    data-pelnama="<?= esc($po['pelnama'] ?: '-') ?>"
                                    data-tglpo="<?= esc($formatTanggalInput($po['tglpo'])) ?>"
                                    data-sisa="<?= (float) $po['sisa'] ?>"
                                    <?= ($po['nopo'] === ($detail['no_po_detail'] ?? null)) ? 'selected' : '' ?>>
                                <?= esc($po['nopo']) ?> (sisa <?= number_format((float) $po['sisa'], 0, ',', '.') ?>)
                            </option>
                        <?php endforeach ?>
                    </select>
                </td>
                <td><span class="tampil-tanggalpo-detail"><?= esc($formatTanggalInput($detail['tanggal_po_detail'] ?? '')) ?: '-' ?></span></td>
                <td><?= esc($detail['nama_produk']) ?></td>
                <td class="text-right"><?= number_format($detail['qty'], 0, ',', '.') ?> Pcs</td>
                <td class="text-right"><?= number_format($detail['terkirim'], 0, ',', '.') ?> Pcs</td>
                <td class="text-right"><?= number_format($detail['qty'] - $detail['terkirim'], 0, ',', '.') ?> Pcs</td>
                <td class="text-center">
                    <?php if ($sisa > 0) : ?>
                        <button type="button" class="btn btn-sm btn-danger btn-hapus-item-permintaan" data-id="<?= $detail['id'] ?>" title="Hapus / batalkan sisa yang belum terkirim">
                            <i class="fa fa-trash-alt"></i>
                        </button>
                    <?php else : ?>
                        <span class="text-muted">-</span>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<h5 class="mt-5">Tabel Draft Pengiriman</h5>
<div id="dataRencana"><?= view('permintaanpengiriman/datarencana', ['rencana' => $rencana, 'permintaanId' => $header['id']]) ?></div>

<?= view('permintaanpengiriman/riwayat', ['riwayat' => $riwayat]) ?>

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
    let namaProdukCombobox = null;

    function escapeHtml(value) {
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

    function formatTanggalTampil(value) {
        const tanggal = String(value || '').trim();
        const isoMatch = tanggal.match(/^(\d{4})-(\d{2})-(\d{2})$/);

        if (isoMatch) {
            return `${isoMatch[3]}-${isoMatch[2]}-${isoMatch[1]}`;
        }

        return tanggal;
    }

    function formatTanggalServer(value) {
        const tanggal = String(value || '').trim().replace(/\//g, '-');
        const tampilMatch = tanggal.match(/^(\d{2})-(\d{2})-(\d{4})$/);

        if (tampilMatch) {
            return `${tampilMatch[3]}-${tampilMatch[2]}-${tampilMatch[1]}`;
        }

        return tanggal;
    }

    function rapikanTanggalTampil(input) {
        const nilaiServer = formatTanggalServer($(input).val());
        $(input).val(formatTanggalTampil(nilaiServer));
    }

    function tampilRencana() {
        $.post('/permintaanPengiriman/tampilRencana', {
            [csrfToken]: csrfHash,
            permintaan_id: $('#permintaanId').val()
        }, response => $('#dataRencana').html(response.data), 'json');
    }

    function hapusRencana(id) {
        $.post('/permintaanPengiriman/hapusRencana', {
            [csrfToken]: csrfHash, id: id
        }, function() {
            location.reload();
        }, 'json');
    }

    $(document).on('click', '.btn-hapus-riwayat', function() {
        const rencanaId = $(this).data('id');

        Swal.fire({
            title: 'Hapus item pengiriman ini?',
            text: 'Stok akan dikembalikan dan qty di permintaan ini bakal balik jadi belum terkirim.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;

            $.post('/permintaanPengiriman/hapusRiwayatPengiriman', {
                [csrfToken]: csrfHash,
                rencana_id: rencanaId
            }, function(response) {
                if (response.error) {
                    Swal.fire('Error', response.error, 'error');
                    return;
                }

                Swal.fire('Berhasil', response.sukses, 'success').then(() => location.reload());
            }, 'json').fail(function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.error || 'Gagal menghapus riwayat pengiriman', 'error');
            });
        });
    });

    function pisahkanPo(rencanaId, qtyTerkirim, noPoLama, noDoLama, kodeProduk, idPelanggan) {
        $.post('/permintaanPengiriman/poTujuanSplit', {
            [csrfToken]: csrfHash,
            kode_produk: kodeProduk,
            idpelanggan: idPelanggan,
            no_po_kecuali: noPoLama
        }, function(response) {
            const daftarPo = response.data || [];
            if (daftarPo.length === 0) {
                Swal.fire('Tidak bisa dipisahkan', 'Tidak ada PO lain milik pelanggan ini yang sudah memiliki item ' + kodeProduk + '.', 'info');
                return;
            }

            const safeNoPoLama = $('<div>').text(noPoLama).html();
            const opsiPo = daftarPo.map(po => `<option value="${$('<div>').text(po.nopo).html()}">${$('<div>').text(po.nopo).html()}</option>`).join('');

            Swal.fire({
                title: 'Pisahkan ke PO yang sudah ada',
                html: `
                    <div class="text-left">
                        <p class="mb-2">Pindahkan sebagian qty dari <b>${safeNoPoLama}</b> ke PO tujuan yang sudah memiliki item ${$('<div>').text(kodeProduk).html()}.</p>
                        <small class="d-block mb-3">Stok tidak akan dikurangi lagi karena barang sudah pernah dikirim.</small>
                        <div class="form-group">
                            <label for="splitNoPoTujuan">PO Tujuan</label>
                            <select id="splitNoPoTujuan" class="swal2-select">${opsiPo}</select>
                        </div>
                        <div class="form-group">
                            <label for="splitNoDo">No Surat Jalan</label>
                            <input type="text" id="splitNoDo" class="swal2-input" placeholder="Isi Surat Jalan lama atau input Surat Jalan baru">
                            <small class="d-block text-muted">Boleh pakai Surat Jalan lama jika masih masuk dokumen pengiriman yang sama.</small>
                        </div>
                        <div class="form-group">
                            <label for="splitQty">Qty yang dipindahkan</label>
                            <input type="number" id="splitQty" class="swal2-input" value="1" min="1" max="${qtyTerkirim - 1}" step="1">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Pisahkan PO',
                cancelButtonText: 'Batal',
                focusConfirm: false,
                didOpen: () => {
                    $('#splitNoDo').val(noDoLama);
                },
                preConfirm: () => {
                    const noPoTujuan = $('#splitNoPoTujuan').val();
                    const noDoBaru = $('#splitNoDo').val().trim();
                    const qty = Number($('#splitQty').val());

                    if (!noPoTujuan) {
                        Swal.showValidationMessage('PO tujuan wajib dipilih');
                        return false;
                    }

                    if (!noDoBaru) {
                        Swal.showValidationMessage('No Surat Jalan wajib diisi');
                        return false;
                    }

                    if (!Number.isInteger(qty) || qty < 1 || qty >= qtyTerkirim) {
                        Swal.showValidationMessage('Qty harus 1 sampai ' + (qtyTerkirim - 1));
                        return false;
                    }

                    return {
                        no_po_tujuan: noPoTujuan,
                        no_do_baru: noDoBaru,
                        qty: qty
                    };
                }
            }).then(result => {
                if (!result.isConfirmed) return;

                $.post('/permintaanPengiriman/pisahkanPoTerkirim', {
                    [csrfToken]: csrfHash,
                    rencana_id: rencanaId,
                    qty: result.value.qty,
                    no_po_tujuan: result.value.no_po_tujuan,
                    no_do_baru: result.value.no_do_baru
                }, function(response) {
                    if (response.error) {
                        return Swal.fire('Tidak dapat memisahkan PO', response.error, 'error');
                    }

                    Swal.fire({
                        title: 'PO berhasil dipisahkan',
                        html: 'PO tujuan: <b>' + response.no_po_tujuan + '</b><br>' +
                            'No Surat Jalan: <b>' + response.no_do_baru + '</b>',
                        icon: 'success'
                    }).then(() => location.reload());
                }, 'json').fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.error || 'Pemisahan PO gagal diproses', 'error');
                });
            });
        }, 'json');
    }

    $(document).on('click', '.btn-pisahkan-po', function() {
        pisahkanPo(
            Number($(this).data('id')),
            Number($(this).data('qty')),
            String($(this).data('po')),
            String($(this).data('do')),
            String($(this).data('kode')),
            Number($(this).data('idpel'))
        );
    });

    function syncPelangganDanQtyMax($row) {
        const $select = $row.find('.input-po-detail');
        const dipilih = $select.length ? $select.val() : '';
        const $opt = dipilih ? $select.find('option:selected') : null;

        $('#idPelangganKirim').val($opt ? ($opt.data('idpel') || '') : '');

        const sisaAttr = $row.attr('data-sisa');
        const sisaPermintaan = (sisaAttr === '' || sisaAttr === undefined) ? null : Number(sisaAttr);
        const sisaPo = $opt ? Number($opt.data('sisa') || 0) : null;

        let max = sisaPermintaan;
        if (sisaPo !== null) {
            max = (max !== null) ? Math.min(max, sisaPo) : sisaPo;
        }

        if (max !== null) {
            $('#qtyKirim').attr('max', max).val(max > 0 ? 1 : 0);
        } else {
            $('#qtyKirim').removeAttr('max').val(1);
        }
    }

    function activateRow($row) {
        $('#detailProduk').val($row.data('id'));
        $('#kodeProduk').val($row.data('kode'));
        $('#kodebarang').val($row.data('kode'));
        $('#namaProdukInput').val($row.data('nama'));
        $('#belumKirim').val($row.attr('data-sisa'));
        $('#stokGudang').val('');

        syncPelangganDanQtyMax($row);

        $('.detail-row').removeClass('table-primary');
        $row.addClass('table-primary');

        if ($('#gudangId').val()) {
            $('#gudangId').trigger('change');
        }
    }

    $(document).on('click', '.detail-row', function() {
        activateRow($(this));
    });

    $(document).on('click', '.btn-hapus-item-permintaan', function(e) {
        e.stopPropagation();

        const $tombol = $(this);
        const $row = $tombol.closest('tr');
        const detailId = $tombol.data('id');

        // Baris hasil pencarian yang belum disimpan (data-id kosong) --
        // cukup dibuang dari tampilan, gak ada apa-apa di database.
        if (!detailId) {
            if ($row.hasClass('table-primary')) {
                $('#detailProduk, #kodeProduk, #kodebarang, #belumKirim, #namaProdukInput, #stokGudang').val('');
                $('#idPelangganKirim').val('');
                $('#qtyKirim').removeAttr('max').val(1);
            }
            $row.remove();
            return;
        }

        Swal.fire({
            title: 'Hapus item ini?',
            text: 'Sisa qty yang belum terkirim untuk produk ini akan dibatalkan/dihapus dari permintaan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post('/permintaanPengiriman/hapusItemPermintaan', {
                [csrfToken]: csrfHash,
                detail_id: detailId
            }, function(response) {
                if (response.error) {
                    Swal.fire('Gagal', response.error, 'error');
                    return;
                }

                Swal.fire('Berhasil', response.sukses, 'success').then(() => location.reload());
            }, 'json').fail(function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.error || 'Item gagal dihapus', 'error');
            });
        });
    });

    $(document).on('focus', '.input-po-detail', function() {
        const $row = $(this).closest('tr');
        if ($row.hasClass('detail-row') && !$row.hasClass('table-primary')) {
            activateRow($row);
        }
    });

    $(document).on('change', '.input-po-detail', function() {
        const $select = $(this);
        const $row = $select.closest('tr');
        const dipilih = $select.val();
        const $opt = dipilih ? $select.find('option:selected') : null;
        const pelnama = $opt ? ($opt.data('pelnama') || '-') : '-';
        const tglpo = $opt ? ($opt.data('tglpo') || '') : '';

        $row.find('.tampil-pelanggan-detail').text(pelnama);
        $row.find('.tampil-tanggalpo-detail').text(tglpo || '-');

        if ($row.hasClass('table-primary')) {
            syncPelangganDanQtyMax($row);
        }

        const detailId = $row.data('id');
        if (!dipilih || !detailId) {
            return;
        }

        $.post('/permintaanPengiriman/updateDetailPelanggan', {
            [csrfToken]: csrfHash,
            detail_id: detailId,
            idpelanggan: $opt.data('idpel') || ''
        }, function(response) {
            if (response.error) Swal.fire('Error', response.error, 'error');
        }, 'json');

        $.post('/permintaanPengiriman/updateDetailTanggalPo', {
            [csrfToken]: csrfHash,
            detail_id: detailId,
            tanggal_po: formatTanggalServer(tglpo),
            no_po: dipilih
        }, function(response) {
            if (response.error) Swal.fire('Error', response.error, 'error');
        }, 'json');
    });

    function buatOpsiPo(daftarPo) {
        return daftarPo.map(function(po) {
            return '<option value="' + escapeHtml(po.nopo) + '"' +
                ' data-idpel="' + (po.idpel || '') + '"' +
                ' data-pelnama="' + escapeHtml(po.pelnama || '-') + '"' +
                ' data-tglpo="' + escapeHtml(formatTanggalTampil(po.tglpo || '')) + '"' +
                ' data-sisa="' + Number(po.sisa || 0) + '">' +
                escapeHtml(po.nopo) + ' (sisa ' + Number(po.sisa || 0).toLocaleString('id-ID') + ')</option>';
        }).join('');
    }

    function buatBarisProdukBaru(kode, nama, daftarPo) {
        const nomor = $('#tabelDetailProduk tbody tr').length + 1;
        const $row = $(
            '<tr class="detail-row" data-id="" data-kode="' + escapeHtml(kode) + '" data-nama="' + escapeHtml(nama) + '" data-sisa="" data-idpel="" style="cursor:pointer;" title="Klik untuk mengisi / mengubah">' +
            '<td>' + nomor + '</td>' +
            '<td>' + escapeHtml(kode) + '</td>' +
            '<td><span class="tampil-pelanggan-detail">-</span></td>' +
            '<td><select class="form-control form-control-sm input-po-detail" data-detail-id=""><option value="">-- Pilih No. PO --</option>' + buatOpsiPo(daftarPo) + '</select></td>' +
            '<td><span class="tampil-tanggalpo-detail">-</span></td>' +
            '<td>' + escapeHtml(nama) + '</td>' +
            '<td class="text-right">-</td>' +
            '<td class="text-right">-</td>' +
            '<td class="text-right">-</td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-hapus-item-permintaan" data-id="" title="Hapus baris ini"><i class="fa fa-trash-alt"></i></button></td>' +
            '</tr>'
        );
        $('#tabelDetailProduk tbody').append($row);
        return $row;
    }

    function pilihProdukBaru(kode) {
        // Cuma baris yang masih ada sisa (class detail-row) yang dipakai ulang.
        // Kalau produk ini sudah ada tapi baris lamanya udah full terkirim
        // (text-muted), atau baris baru hasil pencarian sebelumnya di sesi
        // ini (juga class detail-row, data-id kosong), tetap lolos ke sini
        // -- baris baru dianggap tambahan qty, nanti otomatis digabung ke
        // qty permintaan yang sudah ada oleh simpanRencana().
        const $existingRow = $('#tabelDetailProduk tbody tr.detail-row[data-kode="' + kode + '"]');
        if ($existingRow.length) {
            $('#namaProdukInput').val($existingRow.data('nama'));
            activateRow($existingRow);
            return;
        }

        $.post('/permintaanPengiriman/ambilDataBarang', {
            [csrfToken]: csrfHash,
            kodebarang: kode
        }, function(response) {
            if (response.error) {
                Swal.fire('Error', response.error, 'error');
                return;
            }

            $.post('/permintaanPengiriman/poListProduk', {
                [csrfToken]: csrfHash,
                kode_produk: kode
            }, function(poResponse) {
                const $row = buatBarisProdukBaru(kode, response.sukses.namabarang, poResponse.sukses || []);
                $('#namaProdukInput').val(response.sukses.namabarang);
                activateRow($row);
            }, 'json');
        }, 'json');
    }

    $(function() {
        namaProdukCombobox = window.treInitInlineCombobox({
            box: '#namaProdukCombobox',
            input: '#namaProdukInput',
            hidden: '#kodebarang',
            menu: '#namaProdukComboboxMenu',
            options: produkOptions,
            onSelect: function(option) {
                pilihProdukBaru(option.id);
            }
        });
    });

    $('#gudangId').change(function() {
        if (!$('#kodeProduk').val()) return;
        $.post('/permintaanPengiriman/ambilStok', {
            [csrfToken]: csrfHash,
            kode_produk: $('#kodeProduk').val(),
            gudang_id: $(this).val()
        }, response => $('#stokGudang').val(response.stok), 'json');
    });

    $('#simpanRencana').click(function(e) {
        e.preventDefault();
        const detailId = $('#detailProduk').val();
        const kodeProduk = $('#kodeProduk').val();
        const $activeRow = $('.detail-row.table-primary');
        const $poSelect = $activeRow.find('.input-po-detail');
        const noPo = $poSelect.length ? $poSelect.val() : '';
        const $poOpt = noPo ? $poSelect.find('option:selected') : null;
        const tanggalPo = formatTanggalServer($poOpt ? ($poOpt.data('tglpo') || '') : '');
        const idPelanggan = $poOpt ? ($poOpt.data('idpel') || '') : '';

        if (!kodeProduk) {
            Swal.fire('Error', 'Klik produk dari tabel atau cari produk terlebih dahulu', 'error');
            return;
        }

        if (!noPo) {
            Swal.fire('Error', 'Pilih No. PO untuk produk ini terlebih dahulu', 'error');
            return;
        }

        $.post('/permintaanPengiriman/simpanRencana', {
            [csrfToken]: csrfHash,
            permintaan_id: $('#permintaanId').val(),
            kode_produk_baru: detailId ? '' : kodeProduk,
            detail_id: detailId,
            gudang_id: $('#gudangId').val(),
            qty: $('#qtyKirim').val(),
            no_po: noPo,
            tanggal_po: tanggalPo,
            tanggal_pengiriman: $('#tanggalPengiriman').val(),
            idpelanggan: idPelanggan
        }, function(response) {
            if (response.error) return Swal.fire('Error', response.error, 'error');
            Swal.fire('Berhasil', response.sukses, 'success').then(() => location.reload());
        }, 'json');
    });

    $(document).on('change', '.input-dokumen-rencana', function() {
        $.post('/permintaanPengiriman/updateRencanaDokumen', {
            [csrfToken]: csrfHash,
            id: $(this).data('id'),
            field: $(this).data('field'),
            value: $(this).data('field') === 'tanggal_po' ? formatTanggalServer($(this).val()) : $(this).val()
        }, function(response) {
            if (response.error) {
                Swal.fire('Error', response.error, 'error');
            }
        }, 'json');
    });

    $('#tanggalPengiriman').change(function() {
        $.post('/permintaanPengiriman/updateTanggalPengiriman', {
            [csrfToken]: csrfHash,
            permintaan_id: $('#permintaanId').val(),
            tanggal_pengiriman: $(this).val()
        }, function(response) {
            if (response.error) {
                Swal.fire('Error', response.error, 'error');
            }
        }, 'json');
    });

    $(document).on('blur', '.input-dokumen-rencana[data-field="tanggal_po"]', function() {
        rapikanTanggalTampil(this);
    });

    $(document).on('click', '#kirimProduk', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Kirim produk?',
            text: 'Pastikan Tanggal Pengiriman, No. PO dan No Surat Jalan sudah diisi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, kirim',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;

            $.post('/permintaanPengiriman/kirimProduk', {
                [csrfToken]: csrfHash,
                permintaan_id: $('#permintaanId').val(),
                tanggal_pengiriman: $('#tanggalPengiriman').val()
            }, function(response) {
                if (response.error) return Swal.fire('Error', response.error, 'error');
                Swal.fire('Berhasil', response.sukses, 'success').then(() => location.href = '/barangkeluar/data#riwayat');
            }, 'json');
        });
    });
</script>
<?= $this->endSection('isi') ?>
