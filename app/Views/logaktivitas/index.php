<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Log Aktivitas
<?= $this->endSection('judul') ?>

<?= $this->section('isi') ?>
<style>
    .log-toolbar { align-items: flex-start; display: flex; justify-content: flex-end; margin-bottom: 1rem; position: relative; z-index: 30; }
    .log-filter-anchor { align-items: flex-end; display: flex; flex-direction: column; position: relative; width: min(100%, 32rem); }
    .log-search-shell { align-items: center; background: #fff; border: 1px solid #eef1f7; border-radius: 999px; box-shadow: 0 2px 5px rgba(15, 23, 42, .0125); display: flex; gap: .65rem; min-height: 3.4rem; padding: .35rem .45rem .35rem 1.25rem; width: 100%; }
    .log-search-shell > i { color: #6b7280; font-size: 1.1rem; }
    .log-search-input { background: transparent; border: 0; box-shadow: none; color: #4b5563; flex: 1 1 auto; font-size: .95rem; min-width: 0; outline: 0; }
    .log-search-input:focus { box-shadow: none; outline: 0; }
    .log-filter-toggle { align-items: center; background: #16869a; border: 0; border-radius: 999px; color: #fff; display: inline-flex; flex: 0 0 2.65rem; height: 2.65rem; justify-content: center; width: 2.65rem; }
    .log-filter-toggle:hover, .log-filter-toggle:focus { background: #126f7f; color: #fff; outline: 0; }
    .log-filter-panel { background: #fff; border: 1px solid #edf1f5; border-radius: 22px; box-shadow: 0 9px 21px rgba(15, 23, 42, .05); display: none; max-width: min(46rem, calc(100vw - 4rem)); opacity: 0; overflow: hidden; pointer-events: none; position: absolute; right: 0; top: calc(100% + .75rem); transform: translateY(-.45rem) scale(.985); transform-origin: top right; transition: opacity .18s ease, transform .18s ease; width: 34rem; }
    .log-filter-panel.is-visible { display: block; }
    .log-filter-panel.is-open { opacity: 1; pointer-events: auto; transform: translateY(0) scale(1); }
    .log-filter-header { align-items: center; display: flex; justify-content: space-between; padding: 1.2rem 1.35rem .9rem; }
    .log-filter-title { color: #111827; font-size: 1.2rem; font-weight: 800; margin: 0; }
    .log-filter-close { align-items: center; background: #fff; border: 0; border-radius: 999px; box-shadow: 0 4px 11px rgba(15, 23, 42, .06); color: #111827; display: inline-flex; height: 2.4rem; justify-content: center; width: 2.4rem; }
    .log-filter-section { border-top: 1px solid #edf1f5; padding: 1.05rem 1.35rem; }
    .log-filter-label { color: #718096; font-size: .9rem; font-weight: 800; margin-bottom: .75rem; }
    .log-filter-field-row, .log-filter-action-row { display: flex; flex-wrap: wrap; gap: .75rem; }
    .log-filter-select, .log-filter-date { background: #fff; border: 1px solid #e5eaf1; border-radius: 999px; color: #111827; min-height: 2.55rem; min-width: 11rem; padding: .45rem .85rem; }
    .log-filter-reset { background: #eef2f7; border: 0; border-radius: 999px; color: #4b5563; font-weight: 800; min-height: 2.55rem; padding: .45rem 1.2rem; }
    @media (max-width: 768px) {
        .log-toolbar { justify-content: stretch; }
        .log-filter-anchor, .log-search-shell, .log-filter-panel { max-width: none; width: 100%; }
        .log-filter-panel { left: 0; right: auto; }
        .log-filter-select, .log-filter-date, .log-filter-reset { width: 100%; }
    }
</style>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<ul class="nav nav-tabs mb-3" id="logTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#tabAktivitas" role="tab">Aktivitas</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tabMemory" role="tab" id="tabMemoryLink">Memory</a>
    </li>
</ul>

<div class="tab-content">
<div class="tab-pane fade show active" id="tabAktivitas" role="tabpanel">

<div class="log-toolbar">
    <div class="log-filter-anchor">
        <div class="log-search-shell">
            <i class="fas fa-search"></i>
            <input type="search" id="logSearchInput" class="log-search-input" placeholder="Search anything..." aria-label="Search anything">
            <button type="button" class="log-filter-toggle" id="logFilterToggle" title="Buka filter" aria-controls="logFilterPanel" aria-expanded="false">
                <i class="fas fa-sliders-h"></i>
            </button>
        </div>

        <section class="log-filter-panel" id="logFilterPanel" aria-hidden="true">
            <div class="log-filter-header">
                <h3 class="log-filter-title">Filter</h3>
                <button type="button" class="log-filter-close" id="logFilterClose" title="Tutup filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="log-filter-section">
                <div class="log-filter-label">User &amp; Lokasi</div>
                <div class="log-filter-field-row">
                    <select id="fUserid" class="log-filter-select" aria-label="User">
                        <option value="">-- Semua User --</option>
                        <?php foreach ($users as $u) : ?>
                            <option value="<?= esc($u['userid']) ?>"><?= esc($u['usernama']) ?></option>
                        <?php endforeach ?>
                    </select>
                    <select id="fLokasi" class="log-filter-select" aria-label="Lokasi">
                        <option value="">-- Semua Lokasi --</option>
                        <?php foreach ($lokasiList as $lokasi) : ?>
                            <option value="<?= esc($lokasi) ?>"><?= esc($lokasi) ?></option>
                        <?php endforeach ?>
                    </select>
                    <select id="fAksi" class="log-filter-select" aria-label="Jenis Aksi">
                        <option value="">-- Semua Aksi --</option>
                        <?php foreach ($aksiList as $a) : ?>
                            <option value="<?= esc($a['aksi']) ?>"><?= esc(ucfirst(str_replace('_', ' ', $a['aksi']))) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>

            <div class="log-filter-section">
                <div class="log-filter-label">Tanggal</div>
                <div class="log-filter-field-row">
                    <input type="date" id="fTglAwal" class="log-filter-date" aria-label="Tanggal Awal">
                    <input type="date" id="fTglAkhir" class="log-filter-date" aria-label="Tanggal Akhir">
                </div>
            </div>

            <div class="log-filter-section">
                <div class="log-filter-label">Show Data</div>
                <div class="log-filter-field-row">
                    <select id="logPageLength" class="log-filter-select" aria-label="Show data">
                        <option value="10">10 entries</option>
                        <option value="25" selected>25 entries</option>
                        <option value="50">50 entries</option>
                        <option value="100">100 entries</option>
                    </select>
                </div>
            </div>

            <div class="log-filter-section">
                <div class="log-filter-action-row">
                    <button type="button" class="log-filter-reset" id="logFilterReset">Reset</button>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-sm table-bordered table-striped" id="tabelLog" style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 13%;">Waktu</th>
                <th style="width: 12%;">User</th>
                <th style="width: 10%;">Aksi</th>
                <th style="width: 15%;">Lokasi</th>
                <th>Keterangan</th>
                <th style="width: 8%;" class="text-center">Detail</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

</div><!-- /tabAktivitas -->

<div class="tab-pane fade" id="tabMemory" role="tabpanel">
    <p class="text-muted">
        Tiap sekian hari (atur di bawah), sistem otomatis merapikan seluruh baris Log Aktivitas yang ada jadi 1 file
        Excel yang bisa kamu download di sini, lalu ikut dikirim ke email penerima di bawah (kalau sudah diatur).
        Data di tab "Aktivitas" tetap ringan karena baris yang udah diarsipkan dihapus dari tabel utama.
    </p>

    <div class="card mb-3" style="max-width: 480px;">
        <div class="card-body">
            <label for="inputEmailPenerima" class="font-weight-bold">Email Penerima Laporan</label>
            <div class="input-group mb-3">
                <input type="email" class="form-control" id="inputEmailPenerima" placeholder="contoh: nama@perusahaan.com" value="<?= esc($emailPenerima ?? '') ?>">
            </div>

            <label for="inputIntervalHari" class="font-weight-bold">Kirim Tiap Berapa Hari</label>
            <div class="input-group">
                <input type="number" min="1" class="form-control" id="inputIntervalHari" value="<?= esc((string) ($intervalHari ?? 7)) ?>">
                <div class="input-group-append">
                    <span class="input-group-text">hari</span>
                </div>
            </div>
            <small class="text-muted d-block mt-1">Contoh: isi 7 buat mingguan, 14 buat 2 mingguan, 30 buat bulanan.</small>

            <button type="button" class="btn btn-primary mt-3" id="btnSimpanEmailPenerima">Simpan Pengaturan</button>
            <br><small class="text-muted">Kosongkan email lalu simpan kalau tidak mau ada yang menerima email (arsip tetap dibuat, cuma tidak dikirim).</small>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered table-striped" id="tabelMemory" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th>Nama File</th>
                    <th style="width: 10%;" class="text-center">Jumlah Baris</th>
                    <th style="width: 15%;">Periode</th>
                    <th style="width: 15%;">Waktu Diarsipkan</th>
                    <th style="width: 12%;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div><!-- /tabMemory -->

</div><!-- /tab-content -->

<div class="modal fade" id="modalDetailLog" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Aktivitas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-bordered" id="tabelDetailLog">
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const badgeAksi = {
        login: 'success',
        logout: 'secondary',
        login_gagal: 'danger',
        create: 'primary',
        edit: 'warning',
        delete: 'danger',
        cancel: 'danger',
        cancel_delete: 'danger',
        upload: 'info',
        print: 'info',
    };

    const labelAksi = {
        view: 'Lihat',
        create: 'Tambah',
        edit: 'Edit',
        delete: 'Hapus',
        history: 'Riwayat',
        usage_check: 'Cek Pemakaian',
        input: 'Input',
        po_keluar: 'Pilih PO Keluar',
        stock_lookup: 'Cek Stok',
        save_receipt: 'Simpan Transaksi',
        edit_detail: 'Edit Detail',
        input_direct: 'Input Langsung',
        input_request: 'Input Permintaan',
        input_manual: 'Input Manual',
        import: 'Import',
        po_lookup: 'Cari PO',
        manual_lookup: 'Cari Manual',
        save_plan: 'Simpan Rencana',
        save_manual: 'Simpan Manual',
        ship_product: 'Kirim Produk',
        process: 'Proses',
        edit_document: 'Edit Dokumen',
        split_po: 'Pisah PO',
        print: 'Cetak',
        ship_transfer: 'Kirim Antar Gudang',
        change_number: 'Ubah Nomor',
        progress: 'Lihat Progress',
        edit_header: 'Edit Header',
        cancel_delete: 'Batal / Hapus',
        generate: 'Buat',
        payment: 'Pembayaran',
        cancel: 'Batal',
        upload_invoice: 'Upload Invoice',
        upload_payment: 'Upload Bukti Bayar',
        download_file: 'Unduh File',
        reporting: 'Laporan',
        lookup: 'Cari',
        edit_profile: 'Edit Profil',
        reset_password: 'Reset Password',
        save: 'Simpan',
        manage_level: 'Kelola Role',
        print_raw_produk: 'Cetak Raw Produk',
        print_produk_masuk: 'Cetak Produk Masuk',
        print_produk_keluar: 'Cetak Produk Keluar',
        backup: 'Backup',
        restore: 'Restore',
        change: 'Ubah',
        login: 'Login',
        login_gagal: 'Login Gagal',
        logout: 'Logout',
    };

    function formatAksi(aksi) {
        const warna = badgeAksi[aksi] || 'secondary';
        const label = labelAksi[aksi] || String(aksi || '-').replace(/_/g, ' ').replace(/\w\S*/g, function(kata) {
            return kata.charAt(0).toUpperCase() + kata.slice(1).toLowerCase();
        });
        return '<span class="badge badge-' + warna + '">' + label + '</span>';
    }

    function formatWaktu(value) {
        if (!value) return '-';
        return value.replace('T', ' ').substring(0, 19);
    }

    let tabelLog = $('#tabelLog').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        searching: true,
        searchDelay: 500,
        stateSave: true,
        stateDuration: -1,
        dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        pageLength: 10,
        order: [],
        ajax: {
            url: '<?= site_url('logaktivitas/listdata') ?>',
            data: function(d) {
                d.f_userid = $('#fUserid').val();
                d.f_modul = $('#fLokasi').val();
                d.f_aksi = $('#fAksi').val();
                d.f_tglawal = $('#fTglAwal').val();
                d.f_tglakhir = $('#fTglAkhir').val();
            }
        },
        columns: [{
                data: 'nomor',
                orderable: false,
                className: 'text-center'
            },
            {
                data: 'created_at',
                orderable: false,
                render: formatWaktu
            },
            {
                data: 'usernama',
                orderable: false
            },
            {
                data: 'aksi',
                orderable: false,
                render: formatAksi
            },
            {
                data: 'modul',
                orderable: false
            },
            {
                data: 'keterangan',
                orderable: false
            },
            {
                data: 'detail_data',
                orderable: false,
                className: 'text-center',
                defaultContent: '',
                render: function(data) {
                    if (!data) return '-';
                    return '<button type="button" class="btn btn-sm btn-outline-secondary btn-lihat-detail-log"><i class="fa fa-eye"></i> Detail</button>';
                }
            },
        ]
    });

    const labelField = {
        kodebarang: 'Kode Produk', old_kodebarang: 'Kode Produk Lama', namabarang: 'Nama Produk',
        kategori: 'Kategori', satuan: 'Satuan', satuanberat: 'Satuan Berat', harga: 'Harga',
        minstok: 'Min. Stok', idpel: 'Pelanggan', idpelanggan: 'Pelanggan', pelanggan: 'Pelanggan',
        material: 'Material', sumber_material: 'Sumber Material', sumber_produk: 'Sumber Produk',
        tanpa_berat: 'Tanpa Berat', berat: 'Berat', berat_material: 'Berat per Material',
        berat_produk_jadi: 'Berat Produk Jadi', gudang: 'Gudang', idgudang: 'Gudang', gdgid: 'Gudang',
        namagudang: 'Nama Gudang', kodematerial: 'Kode Material', namamaterial: 'Nama Material',
        idmaterial: 'Material', namakategori: 'Nama Kategori', idkategori: 'Kategori',
        namasatuan: 'Nama Satuan', idsatuan: 'Satuan', kodeprd: 'Kode Produk', stok: 'Stok',
        tanggal: 'Tanggal', tglawal: 'Tanggal Awal', tglakhir: 'Tanggal Akhir', tglfaktur: 'Tanggal Faktur',
        tglpermintaan: 'Tanggal Permintaan', keterangan: 'Keterangan', faktur: 'No. Faktur',
        nofaktur: 'No. Faktur', nopo: 'No. PO', no_po: 'No. PO', po_no: 'No. PO',
        no_do_hash: 'No. Surat Jalan', no_btb: 'No. BTB', jml: 'Jumlah', qty: 'Qty',
        detjml: 'Jumlah', detqty: 'Qty', detberat: 'Berat', detkurang: 'Kurang',
        idsupplier: 'Supplier', supplier: 'Supplier', idbarang: 'Barang', jenis_item: 'Jenis Item',
        permintaan: 'Permintaan', kirim: 'Kirim', sumber: 'Sumber',
        alamat: 'Alamat', telp: 'Telepon', fax: 'Fax', to: 'Kepada (To)', email: 'Email',
        nama: 'Nama', namapel: 'Nama Pelanggan', namapic: 'Nama PIC', namajasa: 'Nama Jasa',
        id_jasa: 'Jasa', id_pelanggan: 'Pelanggan', customer_id: 'Pelanggan',
        editAlamat: 'Alamat', editEmail: 'Email', editFax: 'Fax', editGudang: 'Gudang',
        editHargaModal: 'Harga Modal', editNamaJasa: 'Nama Jasa', editNamaPelanggan: 'Nama Pelanggan',
        editNamaPic: 'Nama PIC', editTelp: 'Telepon', editTo: 'Kepada (To)',
        bank_account: 'No. Rekening', bank_name: 'Nama Bank', bank_npwp: 'NPWP', bank_owner: 'Pemilik Rekening',
        invoice_no: 'No. Invoice', invoice_date: 'Tanggal Invoice', payment_no: 'No. Pembayaran',
        payment_date: 'Tanggal Pembayaran', amount: 'Jumlah', note: 'Catatan',
        ppn_enabled: 'PPN Diaktifkan', ppn_percent: 'PPN (%)', pph_enabled: 'PPh Diaktifkan',
        pph_percent: 'PPh (%)', dp_enabled: 'DP Diaktifkan', dp_percent: 'DP (%)',
        harga_modal: 'Harga Modal', signer_name: 'Nama Penandatangan', signer_position: 'Jabatan',
        source_no: 'No. Sumber', source_type: 'Jenis Sumber', originalPermintaan: 'Permintaan Asal',
        newPermintaan: 'Permintaan Baru', userid: 'User ID', iduser: 'User', namalengkap: 'Nama Lengkap',
        level: 'Role', levelnama: 'Nama Role', permissions: 'Hak Akses', filter_kategori: 'Filter Kategori',
        filter_material: 'Filter Material', filter_kekurangan: 'Filter Kekurangan', bulan: 'Bulan',
        alokasi: 'Alokasi', id: 'ID', iddetail: 'ID Detail', po_keluar_id: 'PO Keluar',
        faktur_internal: 'No. Faktur Internal',
    };

    const fieldTersembunyi = ['btnCetak', 'btnExport', 'draw', 'length', 'start', 'search'];
    const fieldBoolean = /_enabled$|^tanpa_berat$/;

    function labelDetail(key) {
        if (labelField[key]) return labelField[key];
        const spasi = key.replace(/_/g, ' ').replace(/([a-z])([A-Z])/g, '$1 $2');
        return spasi.replace(/\w\S*/g, function(kata) {
            return kata.charAt(0).toUpperCase() + kata.slice(1).toLowerCase();
        });
    }

    // Field berat di form Managemen Data Produk disimpan/dikirim dalam KG,
    // padahal usernya ngisi & mikirnya dalam gram -- jadi buat kebutuhan
    // baca di log, dibalikin ke gram lagi (1 kg = 1000 gram).
    const fieldBeratKg = ['berat_material', 'berat_produk_jadi'];

    function keKgKeGram(nilaiKg) {
        const angka = parseFloat(nilaiKg);
        if (isNaN(angka)) return nilaiKg;
        const gram = Math.round(angka * 1000 * 10000) / 10000;
        return gram + ' gram';
    }

    function formatDetailValue(key, value) {
        if (value === null || value === '') return '-';

        // Sebagian field (misal material[], berat_material[]) udah dateng
        // sebagai array/object asli dari PHP (bukan teks JSON di dalam
        // JSON) -- tapi jaga-jaga kalau ada yang masih bentuk teks JSON,
        // di-parse dulu di sini.
        if (typeof value === 'string' && (value.startsWith('[') || value.startsWith('{'))) {
            try {
                value = JSON.parse(value);
            } catch (e) {
                // Bukan JSON valid, biarkan sebagai teks apa adanya.
            }
        }

        if (fieldBeratKg.indexOf(key) !== -1) {
            if (value && typeof value === 'object' && !Array.isArray(value)) {
                const pasangan = Object.keys(value).map(function(k) {
                    return k + ': ' + keKgKeGram(value[k]);
                });
                return pasangan.length ? pasangan.join(', ') : '-';
            }
            return keKgKeGram(value);
        }

        if (Array.isArray(value)) {
            value = value.length ? value.join(', ') : '-';
        } else if (value && typeof value === 'object') {
            const pasangan = Object.keys(value).map(function(k) {
                return k + ': ' + value[k];
            });
            value = pasangan.length ? pasangan.join(', ') : '-';
        }

        if (fieldBoolean.test(key)) {
            value = (String(value) === '1') ? 'Ya' : 'Tidak';
        }

        return value;
    }

    function escapeHtml(teks) {
        return $('<div>').text(teks == null ? '' : teks).html();
    }

    // Field "permissions" (Hak Akses) dikelompokkan per section biar
    // gampang dibaca, bukan satu baris panjang comma-separated. Bentuk
    // datanya: { "Nama Section": ["Aksi 1", "Aksi 2"] } (simpan 1 user)
    // atau { "userid": { "Nama Section": [...] } } (simpan banyak user).
    function formatPermissions(data) {
        if (!data || typeof data !== 'object') return '<span class="text-muted">-</span>';

        function renderGrup(grup) {
            const namaSection = Object.keys(grup);
            if (namaSection.length === 0) {
                return '<span class="text-muted">Tidak ada akses dipilih.</span>';
            }
            return namaSection.map(function(section) {
                const daftarAksi = Array.isArray(grup[section]) ? grup[section].join(', ') : String(grup[section]);
                return '<div style="margin-bottom:.35rem;"><strong>' + escapeHtml(section) + '</strong>: ' + escapeHtml(daftarAksi) + '</div>';
            }).join('');
        }

        const daftarKey = Object.keys(data);
        if (daftarKey.length === 0) return '<span class="text-muted">-</span>';

        const nilaiPertama = data[daftarKey[0]];
        const perUser = nilaiPertama && typeof nilaiPertama === 'object' && !Array.isArray(nilaiPertama);

        if (perUser) {
            return daftarKey.map(function(uid) {
                return '<div style="margin-bottom:.75rem;">'
                    + '<div style="font-weight:800;margin-bottom:.3rem;">' + escapeHtml(uid) + '</div>'
                    + renderGrup(data[uid])
                    + '</div>';
            }).join('');
        }

        return renderGrup(data);
    }

    $('#tabelLog').on('click', '.btn-lihat-detail-log', function() {
        const rowData = tabelLog.row($(this).closest('tr')).data();
        let parsed = null;
        if (rowData.detail_data) {
            let jsonText = null;
            try {
                jsonText = atob(rowData.detail_data);
            } catch (e) {
                jsonText = rowData.detail_data;
            }
            try {
                parsed = JSON.parse(jsonText);
            } catch (e) {
                parsed = null;
            }
        }

        const body = $('#tabelDetailLog tbody').empty();
        const keyTampil = parsed ? Object.keys(parsed).filter(function(k) {
            return fieldTersembunyi.indexOf(k) === -1;
        }) : [];

        if (!parsed || keyTampil.length === 0) {
            body.append('<tr><td class="text-muted">Tidak ada data detail.</td></tr>');
        } else {
            keyTampil.forEach(function(key) {
                if (key === 'permissions') {
                    body.append(
                        $('<tr>').append(
                            $('<th>').css('width', '30%').text(labelDetail(key)),
                            $('<td>').html(formatPermissions(parsed[key]))
                        )
                    );
                    return;
                }
                const value = formatDetailValue(key, parsed[key]);
                body.append(
                    $('<tr>').append(
                        $('<th>').css('width', '30%').text(labelDetail(key)),
                        $('<td>').text(value)
                    )
                );
            });
        }

        $('#modalDetailLog').modal('show');
    });

    $('#logSearchInput').val(tabelLog.search());

    $('#fUserid, #fLokasi, #fAksi, #fTglAwal, #fTglAkhir').on('change', function() {
        tabelLog.ajax.reload();
    });

    const logFilterPanel = $('#logFilterPanel');
    const logFilterToggle = $('#logFilterToggle');
    let logPanelTimer = null;
    let logSearchTimer = null;

    function logToggleFilterPanel(forceOpen = null) {
        const willOpen = forceOpen === null ? !logFilterPanel.hasClass('is-open') : forceOpen;
        window.clearTimeout(logPanelTimer);

        if (willOpen) {
            logFilterPanel.addClass('is-visible');
            window.requestAnimationFrame(function() {
                logFilterPanel.addClass('is-open');
            });
        } else {
            logFilterPanel.removeClass('is-open');
            logPanelTimer = window.setTimeout(function() {
                logFilterPanel.removeClass('is-visible');
            }, 180);
        }

        logFilterPanel.attr('aria-hidden', willOpen ? 'false' : 'true');
        logFilterToggle.attr('aria-expanded', willOpen ? 'true' : 'false');
    }

    logFilterToggle.on('click', function() {
        logToggleFilterPanel();
    });

    $('#logFilterClose').on('click', function() {
        logToggleFilterPanel(false);
    });

    $('#logSearchInput').on('input', function() {
        const keyword = this.value;
        window.clearTimeout(logSearchTimer);
        logSearchTimer = window.setTimeout(function() {
            tabelLog.search(keyword).draw();
        }, 350);
    });

    $('#logPageLength').on('change', function() {
        tabelLog.page.len(Number(this.value)).draw();
    });

    $('#logFilterReset').on('click', function() {
        $('#fUserid, #fLokasi, #fAksi').val('');
        $('#fTglAwal, #fTglAkhir').val('');
        $('#logPageLength').val('25');
        $('#logSearchInput').val('');
        tabelLog.search('').page.len(25);
        tabelLog.ajax.reload();
    });

    let tabelMemory = null;

    function formatWaktuMemory(value) {
        if (!value) return '-';
        return value.replace('T', ' ').substring(0, 19);
    }

    function formatTanggalMemory(value) {
        if (!value) return '-';
        const d = new Date(value.replace(' ', 'T'));
        if (isNaN(d.getTime())) return value;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    $('#tabMemoryLink').on('shown.bs.tab', function() {
        if (tabelMemory) {
            tabelMemory.ajax.reload();
            return;
        }

        tabelMemory = $('#tabelMemory').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            searching: true,
            order: [],
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: {
                url: '<?= site_url('logaktivitas/listArchive') ?>'
            },
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'original_name',
                    orderable: false
                },
                {
                    data: 'row_count',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'periode_awal',
                    orderable: false,
                    render: function(data, type, row) {
                        return formatTanggalMemory(row.periode_awal) + ' - ' + formatTanggalMemory(row.periode_akhir);
                    }
                },
                {
                    data: 'created_at',
                    orderable: false,
                    render: formatWaktuMemory
                },
                {
                    data: 'aksi',
                    orderable: false,
                    className: 'text-center'
                },
            ]
        });
    });

    $('#btnSimpanEmailPenerima').on('click', function() {
        const tombol = $(this);
        const email = $('#inputEmailPenerima').val().trim();
        const intervalHari = $('#inputIntervalHari').val();

        tombol.prop('disabled', true);
        $.ajax({
            method: 'POST',
            url: '<?= site_url('logaktivitas/simpanEmailPenerima') ?>',
            data: { email_penerima: email, interval_hari: intervalHari },
            dataType: 'json',
        }).done(function(res) {
            if (res.error) {
                Swal.fire('Gagal', res.error, 'error');
            } else {
                Swal.fire('Berhasil', res.sukses, 'success');
            }
        }).fail(function() {
            Swal.fire('Gagal', 'Terjadi kesalahan, coba lagi.', 'error');
        }).always(function() {
            tombol.prop('disabled', false);
        });
    });
</script>
<?= $this->endSection('isi') ?>
