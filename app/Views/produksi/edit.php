<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Edit Produksi
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<button type="button" class="btn btn-warning" onclick="location.href=('/barangmasuk/data#tab-produksi')">
    <i class="fa fa-undo"></i> Kembali
</button>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>

<style>
    #tabelDetailProduksi tbody tr { cursor: pointer; }
    #tabelDetailProduksi tbody tr.table-active { background-color: #d1ecf1; }
</style>

<div class="card">
    <div class="card-header py-2">
        <strong>Edit Produksi dari Material</strong>
        <small class="text-muted d-block">
            Klik salah satu baris di tabel buat mengedit produk itu, lalu Simpan. Kalau langsung isi form tanpa klik
            baris (atau setelah Reset), Simpan akan menambahkan produk baru -- buat item yang kelupaan diinput.
        </small>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            Data produksi masih bisa diedit selama stok produk hasil produksi ini belum dipakai di transaksi lain.
        </div>

        <input type="hidden" id="no_produksi" value="<?= esc($produksi['no_produksi']) ?>">

        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <label>No. Produksi</label>
                    <input type="text" class="form-control" value="<?= esc($produksi['no_produksi']) ?>" readonly>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label for="tgl_produksi">Tanggal Produksi</label>
                    <input type="date" class="form-control" id="tgl_produksi" value="<?= esc($produksi['tgl_produksi']) ?>">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label for="gudang">Gudang</label>
                    <select id="gudang" class="form-control" disabled>
                        <?php foreach ($datagudang as $gdg) : ?>
                            <option value="<?= $gdg['gdgid'] ?>" <?= (string) $gdg['gdgid'] === (string) $produksi['gudang'] ? 'selected' : '' ?>><?= esc($gdg['gdgnama']) ?></option>
                        <?php endforeach ?>
                    </select>
                    <small class="text-muted d-block">Gudang berlaku buat semua produk di batch ini, gak bisa diubah lewat sini.</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="materialProduksi">Material yang Digunakan</label>
                    <select id="materialProduksi" class="form-control select2" disabled style="width: 100%;">
                        <option value="">-- Pilih produk terlebih dahulu --</option>
                    </select>
                    <small id="materialProduksiHelp" class="form-text text-muted">Material inti/default akan dipilih otomatis. Jika stoknya habis, pilih material alternatif yang telah ditentukan pada Produk.</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <label for="kodebarang">Kode Produk</label>
                    <div class="input-group mb-3 tre-inline-combobox tre-inline-combobox-solo" id="produkCombobox">
                        <input type="text" class="form-control" id="kodebarang" autocomplete="off">
                        <input type="hidden" id="kodebarang_pilih">
                        <input type="hidden" id="idbarang">
                        <input type="hidden" id="idgudang" value="<?= esc($produksi['gudang']) ?>">
                        <input type="hidden" id="gdgid" value="<?= esc($produksi['gudang']) ?>">
                        <div class="tre-inline-combobox-menu" id="produkComboboxMenu"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label for="namabarang">Nama Produk</label>
                    <input type="text" class="form-control" id="namabarang" readonly>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label for="stok1">Stok Saat Ini</label>
                    <input type="number" class="form-control" id="stok1" readonly>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label for="qty_produk">Qty Diproduksi</label>
                    <input type="number" class="form-control" id="qty_produk" min="0.0001" step="0.0001" value="1">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label>#</label>
                    <div class="input-group mb-3">
                        <button type="button" class="btn btn-success" title="Simpan" id="tombolSimpanProduksi">
                            <i class="fa fa-save"></i>
                        </button>&nbsp;
                        <button type="button" class="btn btn-sm btn-warning" title="Reset Form" id="tombolResetProduksi">
                            <i class="fa fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <small class="text-muted d-block mb-2">Klik salah satu baris di tabel buat memuat produk itu ke form di atas dan mengeditnya.</small>
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-sm table-hover mb-0" id="tabelDetailProduksi">
                <thead>
                    <tr>
                        <th style="width:5%; text-align:center;">No</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Kode Material</th>
                        <th style="text-align:right;">QTY (pcs)</th>
                        <th style="text-align:right;">QTY Dipakai (kg)</th>
                        <th style="width:5%; text-align:center;">#</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="viewmodal" style="display: none;"></div>

<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    const produkOptions = <?= json_encode(array_map(static function ($row) {
                                $kode = (string) $row['brgkode'];
                                $nama = (string) $row['brgnama'];
                                return [
                                    'id' => $kode,
                                    'text' => $kode . ' - ' . $nama,
                                    'value' => $kode,
                                ];
                            }, $databarang ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    let produkCombobox = null;
    let materialProdukData = [];
    let materialIdAktif = null;

    function escapeMaterialOption(material) {
        let stok = material.stok === null || material.stok === undefined ? 'stok -' : 'stok ' + Number(material.stok).toLocaleString('id-ID');
        let berat = material.berat_per_pcs === null || material.berat_per_pcs === undefined
            ? 'berat belum diisi'
            : 'berat/pcs ' + Number(material.berat_per_pcs * 1000).toLocaleString('id-ID', { maximumFractionDigits: 4 }) + ' gram';
        let label = (material.default ? 'Default - ' : 'Alternatif - ') + material.namamaterial + ' (' + stok + ', ' + berat + ')';
        if (!material.satuan_sesuai) {
            label += ' [satuan belum sesuai]';
        }
        return label;
    }

    function tampilkanMaterialProduk(materials, defaultId, tanpaBerat, pilihanId) {
        materialProdukData = materials || [];
        let select = $('#materialProduksi');
        select.empty();
        if (tanpaBerat) {
            select.append('<option value="">Tidak ada pemakaian material (jasa/tanpa berat)</option>');
            select.prop('disabled', true).trigger('change');
            $('#materialProduksiHelp').text('Produk ini ditandai jasa/tanpa berat, sehingga edit tidak mengubah stok material.');
            return;
        }
        let selectedId = pilihanId || defaultId;
        materialProdukData.forEach(function(material) {
            let selected = Number(material.materialid) === Number(selectedId);
            select.append(new Option(escapeMaterialOption(material), material.materialid, selected, selected));
        });
        select.prop('disabled', materialProdukData.length === 0).trigger('change');
        $('#materialProduksiHelp').text(materialProdukData.length
            ? 'Material inti/default dipilih otomatis. Pilih alternatif jika produksi menggunakan material berbeda.'
            : 'Produk belum memiliki material inti/alternatif.');
    }

    function ambilMaterialProduk(pilihanId) {
        let kodebarang = $('#kodebarang').val();
        let gudang = $('#gudang').val();
        if (!kodebarang || !gudang) {
            return;
        }
        $.ajax({
            type: 'post',
            url: '<?= site_url('produksi/materialProduk') ?>',
            data: { [csrfToken]: csrfHash, kodebarang: kodebarang, gudang: gudang },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    $('#materialProduksi').prop('disabled', true).html('<option value="">Material belum tersedia</option>').trigger('change');
                    Swal.fire('Material Produk', response.error, 'warning');
                    return;
                }
                let data = response.sukses;
                let idDipilih = pilihanId || materialIdAktif;
                let masihTerdaftar = (data.materials || []).some(function(material) { return Number(material.materialid) === Number(idDipilih); });
                if (idDipilih && !masihTerdaftar) {
                    idDipilih = data.default_material_id;
                }
                materialIdAktif = idDipilih || data.default_material_id || null;
                tampilkanMaterialProduk(data.materials, data.default_material_id, data.tanpa_berat, materialIdAktif);
            },
            error: function() {
                $('#materialProduksi').prop('disabled', true).html('<option value="">Material belum tersedia</option>').trigger('change');
                Swal.fire('Material Produk', 'Data material produk tidak dapat dimuat.', 'error');
            }
        });
    }

    // no_produksi batch ini TETAP sepanjang hidup halaman -- semua produk yang
    // ditambahkan di sini (baru maupun edit) selalu masuk ke batch yang sama.
    const noProduksiBatch = <?= json_encode($produksi['no_produksi']) ?>;

    // id (produksi_produk) dari baris yang lagi "dipilih" buat diedit. null
    // berarti form ini lagi dipakai buat menambah produk baru ke batch.
    let produksiProdukIdAktif = null;

    // Sumber kebenaran buat tabel di bawah -- diisi dari data awal (semua
    // produk yang sudah ada di batch ini), lalu di-upsert tiap kali Simpan
    // berhasil (edit yang sudah ada maupun tambah baru).
    let semuaItemProduksi = <?= json_encode(array_map(static function ($line) {
                                    return [
                                        'produksiProdukId' => (int) $line['id'],
                                        'kodeProduk' => (string) $line['kode_produk'],
                                        'namaProduk' => (string) $line['nama_produk'],
                                        'qtyProduk' => (float) $line['qty_produk'],
                                        'materials' => array_map(static function ($row) {
                                            return [
                                                'materialId' => (int) $row['materialid'],
                                                'kodeMaterial' => (string) $row['kode_material'],
                                                'qtyMaterial' => (float) $row['qty_material'],
                                            ];
                                        }, $line['materials'] ?? []),
                                    ];
                                }, $lines ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    function syncProdukCombobox() {
        if (produkCombobox && typeof produkCombobox.sync === 'function') {
            produkCombobox.sync();
        }
    }

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

    function kosongProduk() {
        $('#kodebarang').val('');
        $('#kodebarang_pilih').val('');
        $('#namabarang').val('');
        $('#stok1').val('');
        $('#idbarang').val('');
        $('#qty_produk').val('1');
        materialIdAktif = null;
        $('#materialProduksi').prop('disabled', true).html('<option value="">-- Pilih produk terlebih dahulu --</option>').trigger('change');
        $('#materialProduksiHelp').text('Material inti/default akan dipilih otomatis. Jika stoknya habis, pilih material alternatif yang telah ditentukan pada Produk.');
    }

    function ambilDataBarang() {
        syncProdukCombobox();
        let kodebarang = $('#kodebarang').val();
        let idgudang = $('#idgudang').val();
        if (kodebarang.length === 0) {
            return;
        }
        $.ajax({
            type: 'post',
            url: '<?= site_url('produksi/ambilDataBarangProduksi') ?>',
            data: {
                [csrfToken]: csrfHash,
                kodebarang: kodebarang,
                idgudang: idgudang
            },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    Swal.fire('Error', response.error, 'error');
                    kosongProduk();
                    return;
                }
                let data = response.sukses;
                $('#namabarang').val(data.namabarang);
                $('#stok1').val(data.stok);
                ambilMaterialProduk();
                $('#qty_produk').focus();
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError);
            }
        });
    }

    function renderTabelDetail() {
        const $tbody = $('#tabelDetailProduksi tbody');
        // Batch "Material Pelanggan" itu produknya emang sengaja nggak punya
        // detail material sama sekali (bukan berarti datanya kosong) --
        // jadi baris "tidak ada data" ini cuma boleh muncul kalau
        // produknya sendiri emang nggak ada, bukan pas materialnya doang
        // yang kosong (itu ditangani per-item di bawah).
        if (!semuaItemProduksi.length) {
            $tbody.html('<tr class="baris-detail-produksi-kosong"><td colspan="7" class="text-center text-muted">Tidak ada detail material tersimpan untuk produksi ini.</td></tr>');
            return;
        }
        let rows = [];
        let no = 0;
        semuaItemProduksi.forEach(function(item) {
            const tombolHapus = '<button type="button" class="btn btn-sm btn-danger tombol-hapus-produksi" data-produksi-produk-id="' + item.produksiProdukId + '"><i class="fa fa-trash-alt"></i></button>';
            if (!item.materials || !item.materials.length) {
                no++;
                rows.push(
                    '<tr data-produksi-produk-id="' + item.produksiProdukId + '">' +
                    '<td class="text-center">' + no + '</td>' +
                    '<td>' + escapeHtml(item.kodeProduk) + '</td>' +
                    '<td>' + escapeHtml(item.namaProduk) + '</td>' +
                    '<td class="text-muted">-</td>' +
                    '<td class="text-right">' + Number(item.qtyProduk).toLocaleString('id-ID') + '</td>' +
                    '<td class="text-right">-</td>' +
                    '<td class="text-center">' + tombolHapus + '</td></tr>'
                );
                return;
            }
            item.materials.forEach(function(m) {
                no++;
                rows.push(
                    '<tr data-produksi-produk-id="' + item.produksiProdukId + '">' +
                    '<td class="text-center">' + no + '</td>' +
                    '<td>' + escapeHtml(item.kodeProduk) + '</td>' +
                    '<td>' + escapeHtml(item.namaProduk) + '</td>' +
                    '<td>' + escapeHtml(m.kodeMaterial) + '</td>' +
                    '<td class="text-right">' + Number(item.qtyProduk).toLocaleString('id-ID') + '</td>' +
                    '<td class="text-right">' + Number(m.qtyMaterial).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + '</td>' +
                    '<td class="text-center">' + tombolHapus + '</td></tr>'
                );
            });
        });
        $tbody.html(rows.join(''));
        tandaiBarisAktif();
    }

    function tandaiBarisAktif() {
        $('#tabelDetailProduksi tbody tr').removeClass('table-active');
        if (produksiProdukIdAktif) {
            $('#tabelDetailProduksi tbody tr[data-produksi-produk-id="' + produksiProdukIdAktif + '"]').addClass('table-active');
        }
    }

    function pilihBarisUntukEdit(produksiProdukId) {
        const item = semuaItemProduksi.find(it => it.produksiProdukId === produksiProdukId);
        if (!item) {
            return;
        }
        produksiProdukIdAktif = produksiProdukId;
        syncProdukCombobox();
        $('#kodebarang').val(item.kodeProduk);
        $('#kodebarang_pilih').val(item.kodeProduk);
        $('#qty_produk').val(item.qtyProduk);
        materialIdAktif = item.materials && item.materials.length ? item.materials[0].materialId : null;
        ambilDataBarang();
        tandaiBarisAktif();
    }

    function hapusProduksi(produksiProdukId) {
        Swal.fire({
            title: 'Hapus Produksi Ini?',
            text: 'Stok material yang tadi dipakai akan dikembalikan, dan stok produk hasil produksi ini akan dikurangi lagi. Yakin dihapus?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }
            $.ajax({
                type: 'post',
                url: '<?= site_url('produksi/hapusTransaksi') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    id: produksiProdukId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        Swal.fire('Gagal', response.error, 'error');
                        return;
                    }
                    if (response.batch_dihapus) {
                        Swal.fire('Berhasil', response.sukses, 'success').then(() => {
                            window.location.href = '/barangmasuk/data#tab-produksi';
                        });
                        return;
                    }
                    semuaItemProduksi = semuaItemProduksi.filter(it => it.produksiProdukId !== produksiProdukId);
                    renderTabelDetail();
                    if (produksiProdukIdAktif === produksiProdukId) {
                        produksiProdukIdAktif = null;
                        kosongProduk();
                    }
                    Swal.fire('Berhasil', response.sukses, 'success');
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError);
                }
            });
        });
    }

    function upsertItemProduksi(produksiProdukId, kodeProduk, namaProduk, qtyProduk, materials) {
        const idx = semuaItemProduksi.findIndex(it => it.produksiProdukId === produksiProdukId);
        const item = {
            produksiProdukId: produksiProdukId,
            kodeProduk: kodeProduk,
            namaProduk: namaProduk,
            qtyProduk: Number(qtyProduk),
            materials: materials || []
        };
        if (idx >= 0) {
            semuaItemProduksi[idx] = item;
        } else {
            semuaItemProduksi.push(item);
        }
        renderTabelDetail();
    }

    function simpanProduksi() {
        let tglProduksi = $('#tgl_produksi').val();
        let gudang = $('#gudang').val();
        let kodebarang = $('#kodebarang').val();
        let namabarang = $('#namabarang').val();
        let qtyProduk = $('#qty_produk').val();
        let materialid = $('#materialProduksi').prop('disabled') ? '' : ($('#materialProduksi').val() || materialIdAktif || '');

        if (!tglProduksi) {
            Swal.fire('Pesan', 'Tanggal Produksi belum diisi.', 'warning');
            return;
        }
        if (!gudang) {
            Swal.fire('Pesan', 'Gudang belum dipilih.', 'warning');
            return;
        }
        if (!kodebarang) {
            Swal.fire('Pesan', 'Produk yang diproduksi belum dipilih.', 'warning');
            return;
        }
        if (!qtyProduk || Number(qtyProduk) <= 0) {
            Swal.fire('Pesan', 'Qty Diproduksi harus lebih dari 0.', 'warning');
            return;
        }
        if (!$('#materialProduksi').prop('disabled') && !materialid) {
            Swal.fire('Pesan', 'Material yang digunakan belum dipilih.', 'warning');
            return;
        }

        const modeEdit = !!produksiProdukIdAktif;
        const url = modeEdit ? '/produksi/update' : '/produksi/simpanOtomatis';
        const data = {
            [csrfToken]: csrfHash,
            tgl_produksi: tglProduksi,
            gudang: gudang,
            kodebarang: kodebarang,
            namabarang: namabarang,
            qty_produk: qtyProduk,
            materialid: materialid
        };
        if (modeEdit) {
            data.id = produksiProdukIdAktif;
        } else {
            data.no_produksi = noProduksiBatch;
        }

        const teksKonfirmasi = modeEdit ?
            'Stok lama akan dikoreksi, lalu stok baru akan diterapkan. Yakin simpan perubahan?' :
            'Produk ini akan disimpan sebagai transaksi produksi baru. Stok material akan berkurang, stok produk akan bertambah. Yakin simpan?';

        Swal.fire({
            title: 'Simpan Produksi',
            text: teksKonfirmasi,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }
            $.ajax({
                type: 'post',
                url: url,
                data: data,
                dataType: 'json',
                beforeSend: function() {
                    $('#tombolSimpanProduksi').prop('disabled', true);
                },
                success: function(response) {
                    if (response.error) {
                        Swal.fire('Gagal', response.error, 'error');
                        return;
                    }

                    const materials = (response.detail || []).map(function(d) {
                        return {
                            materialId: Number(d.materialid),
                            kodeMaterial: d.kode_material,
                            qtyMaterial: d.qty_material
                        };
                    });
                    upsertItemProduksi(response.produksi_produk_id, response.kodebarang, response.namabarang, response.qty_produk, materials);

                    let pesan = response.sukses;
                    if (response.peringatan && response.peringatan.length) {
                        pesan += '<br><br><small class="text-warning">' + response.peringatan.join('<br>') + '</small>';
                    }
                    Swal.fire({
                        title: 'Berhasil',
                        icon: response.peringatan && response.peringatan.length ? 'warning' : 'success',
                        html: pesan
                    }).then(() => {
                        produksiProdukIdAktif = null;
                        kosongProduk();
                        tandaiBarisAktif();
                        $('#kodebarang').focus();
                    });
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError);
                },
                complete: function() {
                    $('#tombolSimpanProduksi').prop('disabled', false);
                }
            });
        });
    }

    $(document).ready(function() {
        produkCombobox = window.treInitInlineCombobox({
            box: '#produkCombobox',
            input: '#kodebarang',
            hidden: '#kodebarang_pilih',
            menu: '#produkComboboxMenu',
            options: produkOptions,
            onSelect: function() {
                ambilDataBarang();
            }
        });

        renderTabelDetail();
        kosongProduk();

        $('#kodebarang').keydown(function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                ambilDataBarang();
            }
        });

        $('#tombolSimpanProduksi').click(function(e) {
            e.preventDefault();
            simpanProduksi();
        });

        $('#materialProduksi').on('change', function() {
            materialIdAktif = $(this).val() ? Number($(this).val()) : null;
        });

        $('#tombolResetProduksi').click(function(e) {
            e.preventDefault();
            produksiProdukIdAktif = null;
            kosongProduk();
            tandaiBarisAktif();
        });

        $(document).on('click', '#tabelDetailProduksi tbody tr', function() {
            const produksiProdukId = $(this).data('produksi-produk-id');
            if (!produksiProdukId) {
                return;
            }
            pilihBarisUntukEdit(Number(produksiProdukId));
        });

        $(document).on('click', '.tombol-hapus-produksi', function(e) {
            e.preventDefault();
            e.stopPropagation();
            hapusProduksi(Number($(this).data('produksi-produk-id')));
        });
    });
</script>

<?= $this->endSection('isi') ?>
