<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Input Pengiriman
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<button class="btn btn-warning" onclick="location.href='/barangkeluar/data#daftar'">
    <i class="fa fa-undo"></i> Kembali
</button>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .btn-hapus-daftar-kirim:hover {
        background-color: #fff;
        color: #dc3545;
        border-color: #dc3545;
    }
</style>
<input type="hidden" id="permintaanId" value="">
<input type="hidden" id="idPelangganKirim" value="">

<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label for="noPoKirim">No. PO</label>
            <div class="tre-inline-combobox tre-inline-combobox-solo" id="noPoCombobox">
                <input type="text" id="noPoKirim" class="form-control" placeholder="-- Pilih atau ketik No. PO --" autocomplete="off">
                <input type="hidden" id="noPoKirimPilih">
                <div class="tre-inline-combobox-menu" id="noPoComboboxMenu"></div>
            </div>
            <small class="text-muted">Cuma PO yang masih ada sisa belum terkirim/qty migrasi yang muncul di sini.</small>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label for="sumberKirim">Jenis Pengiriman</label>
            <select id="sumberKirim" class="form-control">
                <option value="baru">Baru (potong stok)</option>
                <option value="migrasi">Migrasi (surat jalan telat, gak potong stok)</option>
            </select>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label for="tanggalPoKirim">Tanggal PO</label>
            <input type="text" id="tanggalPoKirim" class="form-control" placeholder="dd-MM-YYYY" readonly>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label>Pelanggan</label>
            <input id="namaPelangganKirim" class="form-control" readonly placeholder="Terisi otomatis dari PO">
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label for="tanggalPengiriman">Tanggal Pengiriman</label>
            <input type="date" id="tanggalPengiriman" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label for="noDoKirim">No. Surat Jalan</label>
            <input type="text" id="noDoKirim" class="form-control" maxlength="100">
            <small class="text-danger d-none" id="noDoKirimError"></small>
            <small class="text-muted" id="noDoKirimHint">Item yang ditambah dengan No. Surat Jalan yang sama akan digabung jadi 1 surat jalan. Ganti isinya kalau mau mulai surat jalan baru dalam sesi ini (misal 1 PO dikirim lewat beberapa surat jalan).</small>
        </div>
    </div>
</div>

<hr>

<div id="areaItemPo" style="display: none;">
    <p class="text-muted">Klik salah satu item di bawah untuk mengisi detail pengirimannya.</p>
    <table class="table table-sm table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th class="text-right">Jumlah</th>
                <th class="text-right">Terkirim</th>
                <th class="text-right">Belum Terkirim</th>
                <th class="text-right">Migrasi (Belum Ada SJ)</th>
            </tr>
        </thead>
        <tbody id="tabelItemPo"></tbody>
    </table>

    <hr>

    <div class="row">
        <input type="hidden" id="kodeProduk">
        <div class="col-lg-4">
            <div class="form-group">
                <label>Nama Produk</label>
                <input id="namaProduk" class="form-control" placeholder="Klik item PO di atas" readonly>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label for="gudangId">Asal Gudang</label>
                <select id="gudangId" class="form-control">
                    <option value="">-- Pilih --</option>
                    <?php foreach ($gudang as $gdg) : ?>
                        <option value="<?= (int) $gdg['gdgid'] ?>"><?= esc($gdg['gdgnama']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="form-group">
                <label for="stokGudang">Stok</label>
                <input id="stokGudang" class="form-control" readonly>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label for="qtyKirim">Qty Kirim</label>
                <input id="qtyKirim" type="number" class="form-control" min="1" value="1">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4">
            <div class="form-group">
                <label class="d-block">&nbsp;</label>
                <button id="simpanRencana" class="btn btn-success btn-block">
                    <i class="fa fa-truck-loading"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<hr>

<div class="card">
    <div class="card-header py-2">
        <strong>Item yang Akan Dikirim (Sesi Ini)</strong>
        <div class="text-muted" style="font-size:.85rem;">Dikelompokkan per No. Surat Jalan -- 1 sesi boleh berisi lebih dari 1 surat jalan sekaligus.</div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0" id="tabelDaftarKirim">
                <thead>
                    <tr>
                        <th style="width: 4%;">No</th>
                        <th>No. Surat Jalan</th>
                        <th>No. PO</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th style="width: 10%;" class="text-right">Qty</th>
                        <th style="width: 12%;">Sumber</th>
                        <th style="width: 12%;">Gudang</th>
                        <th style="width: 6%;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="baris-daftar-kirim-kosong">
                        <td colspan="9" class="text-center text-muted">Belum ada item ditambahkan</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row justify-content-end mt-3">
    <button type="button" class="btn btn-success" id="tombolSelesaiKirim">
        <i class="fa fa-check"></i> Save dan Kirim
    </button>
</div>

<script>
    const csrfToken = '<?= csrf_token() ?>';
    const csrfHash = '<?= csrf_hash() ?>';
    const poOptions = <?= json_encode(array_map(static function ($po) {
                            $nopo = (string) $po['nopo'];
                            $pelanggan = (string) ($po['pelnama'] ?: '-');
                            return [
                                'id' => $nopo,
                                'text' => $nopo . ' - ' . $pelanggan,
                                'value' => $nopo,
                            ];
                        }, $poAktif ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const draftAwal = <?= json_encode($draft, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    let noPoCombobox = null;
    let daftarKirim = [];
    let gudangNama = {};
    <?php foreach ($gudang as $gdg) : ?>
        gudangNama['<?= (int) $gdg['gdgid'] ?>'] = <?= json_encode($gdg['gdgnama']) ?>;
    <?php endforeach ?>

    function escapeHtmlKirim(value) {
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

    $(function() {
        noPoCombobox = window.treInitInlineCombobox({
            box: '#noPoCombobox',
            input: '#noPoKirim',
            hidden: '#noPoKirimPilih',
            menu: '#noPoComboboxMenu',
            options: poOptions,
            onSelect: function() {
                cariItemPo();
            }
        });

        $('#noPoKirim').on('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                cariItemPo();
            }
        });

        if (draftAwal) {
            $('#permintaanId').val(draftAwal.permintaan_id);
            $('#idPelangganKirim').val(draftAwal.idpel);
            $('#namaPelangganKirim').val(draftAwal.pelnama);
            $('#tanggalPengiriman').val(draftAwal.tanggal_pengiriman);
            $('#noDoKirim').val(draftAwal.no_do);

            daftarKirim = draftAwal.items.map(function(item) {
                return {
                    rencanaId: item.rencana_id,
                    noDo: item.no_do,
                    noPo: item.no_po,
                    kodeProduk: item.kode_produk,
                    namaProduk: item.nama_produk,
                    qty: item.qty,
                    sumber: item.sumber,
                    gudangNama: item.gdgnama || item.gudang_id
                };
            });
        }

        renderDaftarKirim();
    });

    function noPoTerpilih() {
        if (noPoCombobox && typeof noPoCombobox.sync === 'function') {
            noPoCombobox.sync();
        }
        return String($('#noPoKirim').val() || '').trim();
    }

    function cariItemPo() {
        const noPo = noPoTerpilih();
        if (!noPo) return;

        $('#areaItemPo').hide();
        $('#tabelItemPo').empty();
        $('#namaPelangganKirim').val('');
        $('#idPelangganKirim').val('');
        $('#kodeProduk').val('');
        $('#namaProduk').val('');

        $.post('/permintaanPengiriman/itemPo', {
            [csrfToken]: csrfHash,
            no_po: noPo
        }, function(response) {
            if (response.error) {
                showBootstrapModal('PO Tidak Ditemukan', response.error, 'error');
                return;
            }

            const data = response.sukses;
            $('#namaPelangganKirim').val(data.pelnama || '-');
            $('#idPelangganKirim').val(data.idpel);
            $('#tanggalPoKirim').val(formatTanggalTampil(data.tglpo));

            if (!data.items || data.items.length === 0) {
                $('#tabelItemPo').html('<tr><td colspan="7" class="text-center text-muted">PO ini belum punya item</td></tr>');
                $('#areaItemPo').show();
                return;
            }

            let baris = '';
            data.items.forEach((item, index) => {
                const sisa = Number(item.sisa);
                const migrasi = Number(item.migrasi || 0);
                const bisaDipilih = sisa > 0 || migrasi > 0;
                baris += `
                    <tr class="${bisaDipilih ? 'item-po-row' : 'text-muted'}"
                        data-kode="${item.kode_produk}"
                        data-nama="${item.nama_produk}"
                        data-sisa="${sisa}"
                        data-migrasi="${migrasi}"
                        style="${bisaDipilih ? 'cursor: pointer;' : ''}"
                        title="${bisaDipilih ? 'Klik untuk pilih item ini' : 'Item sudah terkirim semua'}">
                        <td>${index + 1}</td>
                        <td>${item.kode_produk}</td>
                        <td>${item.nama_produk}</td>
                        <td class="text-right">${Number(item.qty).toLocaleString('id-ID')} Pcs</td>
                        <td class="text-right">${Number(item.terkirim).toLocaleString('id-ID')} Pcs</td>
                        <td class="text-right">${sisa.toLocaleString('id-ID')} Pcs</td>
                        <td class="text-right">${migrasi.toLocaleString('id-ID')} Pcs</td>
                    </tr>
                `;
            });
            $('#tabelItemPo').html(baris);
            $('#areaItemPo').show();
        }, 'json').fail(function(xhr) {
            showBootstrapModal('Error', xhr.responseJSON?.error || 'Gagal mengambil data PO', 'error');
        });
    }

    $(document).on('click', '.item-po-row', function() {
        const sisa = Number($(this).data('sisa'));
        const migrasi = Number($(this).data('migrasi'));

        $('#kodeProduk').val($(this).data('kode'));
        $('#namaProduk').val($(this).data('nama'));
        $('#stokGudang').val('');

        // Default ke sumber yang tersedia -- kalau cuma migrasi yang ada,
        // langsung pilihin migrasi biar user gak perlu ganti manual.
        if (sisa <= 0 && migrasi > 0) {
            $('#sumberKirim').val('migrasi');
        } else {
            $('#sumberKirim').val('baru');
        }
        $('#sumberKirim').data('sisa', sisa).data('migrasi', migrasi);
        terapkanMaxQty();

        $('.item-po-row').removeClass('table-primary');
        $(this).addClass('table-primary');

        if ($('#gudangId').val()) {
            $('#gudangId').trigger('change');
        }
    });

    function terapkanMaxQty() {
        const sumber = $('#sumberKirim').val();
        const max = sumber === 'migrasi' ? Number($('#sumberKirim').data('migrasi') || 0) : Number($('#sumberKirim').data('sisa') || 0);
        $('#qtyKirim').attr('max', max).val(max > 0 ? 1 : 0);
    }

    $('#sumberKirim').on('change', function() {
        terapkanMaxQty();
    });

    $('#gudangId').change(function() {
        if (!$('#kodeProduk').val()) return;
        $.post('/permintaanPengiriman/ambilStok', {
            [csrfToken]: csrfHash,
            kode_produk: $('#kodeProduk').val(),
            gudang_id: $(this).val()
        }, response => $('#stokGudang').val(response.stok), 'json');
    });

    function tampilkanErrorNoDo(pesan) {
        if (pesan) {
            $('#noDoKirim').addClass('is-invalid');
            $('#noDoKirimError').text(pesan).removeClass('d-none');
            $('#noDoKirimHint').addClass('d-none');
        } else {
            $('#noDoKirim').removeClass('is-invalid');
            $('#noDoKirimError').text('').addClass('d-none');
            $('#noDoKirimHint').removeClass('d-none');
        }
    }

    $('#noDoKirim').on('blur', function() {
        const noDo = $(this).val().trim();
        if (!noDo) {
            tampilkanErrorNoDo('');
            return;
        }

        $.post('/permintaanPengiriman/cekNoDo', {
            [csrfToken]: csrfHash,
            no_do: noDo,
            permintaan_id: $('#permintaanId').val()
        }, function(response) {
            tampilkanErrorNoDo(response.duplikat ? ('No Surat Jalan ini sudah dipakai pada ' + response.sumber + '.') : '');
        }, 'json');
    });

    $('#noDoKirim').on('input', function() {
        tampilkanErrorNoDo('');
    });

    function renderDaftarKirim() {
        if (daftarKirim.length === 0) {
            $('#tabelDaftarKirim tbody').html('<tr class="baris-daftar-kirim-kosong"><td colspan="9" class="text-center text-muted">Belum ada item ditambahkan</td></tr>');
            return;
        }

        // Kelompokkan SEMUA item yang No. Surat Jalan-nya sama jadi 1 grup,
        // biar diselingi surat jalan lain di antaranya (misal: test, test2,
        // lalu balik nambah ke test lagi) tetap kegabung ke grup "test" yang
        // sama, bukan bikin grup "test" baru yang terpisah. Urutan grup
        // ngikutin kapan No. Surat Jalan itu PERTAMA KALI muncul.
        const kelompokMap = new Map();
        daftarKirim.forEach(function(item) {
            if (!kelompokMap.has(item.noDo)) {
                kelompokMap.set(item.noDo, []);
            }
            kelompokMap.get(item.noDo).push(item);
        });
        const kelompok = Array.from(kelompokMap.entries()).map(function(entry) {
            return { noDo: entry[0], items: entry[1] };
        });

        let rows = [];
        let nomor = 0;
        kelompok.forEach(function(grup, grupIndex) {
            grup.items.forEach(function(item, indexDalamGrup) {
                nomor++;
                const badgeSumber = item.sumber === 'migrasi'
                    ? '<span class="badge badge-warning">Migrasi</span>'
                    : '<span class="badge badge-success">Baru</span>';
                const kelasPemisah = (grupIndex > 0 && indexDalamGrup === 0) ? ' style="border-top: 2px solid #adb5bd;"' : '';
                rows.push('<tr' + kelasPemisah + '>' +
                    '<td class="text-center">' + nomor + '</td>' +
                    (indexDalamGrup === 0
                        ? '<td rowspan="' + grup.items.length + '" class="align-middle font-weight-bold">' + escapeHtmlKirim(grup.noDo) + '</td>'
                        : '') +
                    '<td>' + escapeHtmlKirim(item.noPo) + '</td>' +
                    '<td>' + escapeHtmlKirim(item.kodeProduk) + '</td>' +
                    '<td>' + escapeHtmlKirim(item.namaProduk) + '</td>' +
                    '<td class="text-right">' + Number(item.qty).toLocaleString('id-ID') + '</td>' +
                    '<td>' + badgeSumber + '</td>' +
                    '<td>' + escapeHtmlKirim(item.gudangNama) + '</td>' +
                    '<td class="text-center">' +
                    '<button type="button" class="btn btn-sm btn-danger btn-hapus-daftar-kirim" data-rencana-id="' + item.rencanaId + '" title="Hapus item ini">' +
                    '<i class="fa fa-trash"></i>' +
                    '</button>' +
                    '</td>' +
                    '</tr>');
            });
        });
        $('#tabelDaftarKirim tbody').html(rows.join(''));
    }

    $(document).on('click', '.btn-hapus-daftar-kirim', function() {
        const rencanaId = $(this).data('rencana-id');
        const tombol = $(this);

        showBootstrapModal({
            title: 'Hapus item ini?',
            text: 'Item akan dihapus dari daftar dan tidak ikut dikirim.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;

            tombol.prop('disabled', true);

            $.post('/permintaanPengiriman/hapusRencana', {
                [csrfToken]: csrfHash,
                id: rencanaId
            }, function() {
                // Bandingin sebagai string -- rencanaId dari draft (dibaca
                // dari hasil query mentah lewat getResultArray()) itu tipenya
                // string ("249"), sedangkan yang dari respons AJAX simpanRencana
                // (lewat json_encode int) jadi number (249). Kalau dibanding
                // pakai !== langsung, dua tipe beda ini nggak akan pernah cocok
                // -- item yang seharusnya kehapus malah ketinggalan di daftar.
                daftarKirim = daftarKirim.filter(item => String(item.rencanaId) !== String(rencanaId));
                renderDaftarKirim();
            }, 'json').fail(function() {
                tombol.prop('disabled', false);
                showBootstrapModal('Error', 'Gagal menghapus item', 'error');
            });
        });
    });

    $('#simpanRencana').click(function(e) {
        e.preventDefault();

        const kodeProduk = $('#kodeProduk').val();
        if (!kodeProduk) {
            showBootstrapModal('Error', 'Pilih salah satu item PO terlebih dahulu', 'error');
            return;
        }

        if (!$('#gudangId').val()) {
            showBootstrapModal('Error', 'Pilih Asal Gudang terlebih dahulu', 'error');
            return;
        }

        if (!$('#noDoKirim').val().trim()) {
            showBootstrapModal('Error', 'No. Surat Jalan wajib diisi', 'error');
            return;
        }

        const sumber = $('#sumberKirim').val();
        const noPo = noPoTerpilih();
        const namaProduk = $('#namaProduk').val();
        const gudangId = $('#gudangId').val();
        const qty = $('#qtyKirim').val();

        $('#simpanRencana').prop('disabled', true);

        $.post('/permintaanPengiriman/simpanRencana', {
            [csrfToken]: csrfHash,
            permintaan_id: $('#permintaanId').val(),
            kode_produk_baru: kodeProduk,
            gudang_id: gudangId,
            qty: qty,
            sumber: sumber,
            no_po: noPo,
            no_do: $('#noDoKirim').val().trim(),
            tanggal_po: formatTanggalServer($('#tanggalPoKirim').val()),
            tanggal_pengiriman: $('#tanggalPengiriman').val(),
            idpelanggan: $('#idPelangganKirim').val()
        }, function(response) {
            $('#simpanRencana').prop('disabled', false);

            if (response.error) {
                return showBootstrapModal('Error', response.error, 'error');
            }

            $('#permintaanId').val(response.permintaan_id);

            // Backend gabungin ke baris yang sudah ada kalau kode produk,
            // No. Surat Jalan, No. PO, gudang, dan sumbernya sama persis --
            // di sini tinggal update qty baris yang sama itu di tampilan,
            // bukan nambah baris baru.
            const itemLama = response.digabung
                ? daftarKirim.find(function(item) { return item.rencanaId === response.rencana_id; })
                : null;

            if (itemLama) {
                itemLama.qty = response.qty_total;
            } else {
                daftarKirim.push({
                    rencanaId: response.rencana_id,
                    noDo: $('#noDoKirim').val().trim(),
                    noPo: noPo,
                    kodeProduk: kodeProduk,
                    namaProduk: namaProduk,
                    qty: response.qty_total,
                    sumber: sumber,
                    gudangNama: gudangNama[gudangId] || gudangId
                });
            }
            renderDaftarKirim();

            showBootstrapModal({
                title: 'Ditambahkan',
                icon: 'success',
                text: 'Item masuk ke daftar. Klik "Save dan Kirim" kalau semua item sudah lengkap.',
                timer: 1200,
                showConfirmButton: false
            });

            // Reset field item doang -- No Surat Jalan, tanggal, dan
            // sesi permintaan tetap kebawa buat item berikutnya
            // (boleh dari PO yang sama atau PO lain).
            $('#kodeProduk').val('');
            $('#namaProduk').val('');
            $('#stokGudang').val('');
            $('#qtyKirim').val(1);
            $('.item-po-row').removeClass('table-primary');
            cariItemPo();
        }, 'json').fail(function() {
            $('#simpanRencana').prop('disabled', false);
        });
    });

    $('#tombolSelesaiKirim').click(function(e) {
        e.preventDefault();
        if (daftarKirim.length === 0) {
            showBootstrapModal('Pesan', 'Belum ada item yang ditambahkan.', 'warning');
            return;
        }

        const permintaanId = $('#permintaanId').val();
        if (!permintaanId) {
            showBootstrapModal('Pesan', 'Belum ada item yang ditambahkan.', 'warning');
            return;
        }

        showBootstrapModal({
            title: 'Kirim semua item sekarang?',
            text: 'Stok untuk item "Baru" akan langsung berkurang dan surat jalan akan tercatat.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, kirim',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;

            $('#tombolSelesaiKirim').prop('disabled', true);

            $.post('/permintaanPengiriman/kirimProduk', {
                [csrfToken]: csrfHash,
                permintaan_id: permintaanId,
                tanggal_pengiriman: $('#tanggalPengiriman').val()
            }, function(kirimResponse) {
                $('#tombolSelesaiKirim').prop('disabled', false);

                if (kirimResponse.error) {
                    showBootstrapModal('Belum Terkirim', kirimResponse.error, 'warning');
                    return;
                }

                showBootstrapModal({
                    title: 'Berhasil',
                    icon: 'success',
                    text: kirimResponse.sukses
                }).then(() => {
                    location.href = '/barangkeluar/data#daftar';
                });
            }, 'json').fail(function() {
                $('#tombolSelesaiKirim').prop('disabled', false);
            });
        });
    });
</script>
<?= $this->endSection('isi') ?>
