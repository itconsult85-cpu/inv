<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>Mode Maintenance<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
Aktifkan "Sedang Dalam Perbaikan" per Fitur
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    Kalau sebuah fitur diaktifkan mode maintenance-nya, SEMUA user selain superadmin akan lihat halaman
    "Sedang Dalam Perbaikan" waktu buka fitur itu (lihat, tambah, edit, hapus -- semuanya). Fitur lain di
    aplikasi tetap jalan normal seperti biasa. Superadmin tetap bisa buka fitur itu seperti biasa buat tes.
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped" id="tabelMaintenance">
        <thead>
            <tr>
                <th style="width:16%">Kategori</th>
                <th style="width:20%">Fitur</th>
                <th style="width:8%" class="text-center">Status</th>
                <th style="width:32%">Pesan Buat User (opsional)</th>
                <th style="width:24%">Terakhir Diubah</th>
                <th style="width:10%" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($features as $f) : ?>
                <tr data-feature-key="<?= esc($f['key']) ?>">
                    <td><?= esc($f['section_label']) ?></td>
                    <td><?= esc($f['label']) ?></td>
                    <td class="text-center">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input toggle-aktif" id="sw-<?= esc($f['key']) ?>" <?= $f['aktif'] ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="sw-<?= esc($f['key']) ?>"></label>
                        </div>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm input-pesan" maxlength="255" placeholder="Contoh: Lagi ada perbaikan, coba lagi 30 menit lagi" value="<?= esc($f['pesan']) ?>">
                    </td>
                    <td class="text-muted small ket-terakhir">
                        <?php if ($f['aktif'] && $f['updated_at']) : ?>
                            <?= esc($f['updated_by']) ?> &middot; <?= date('d-m-Y H:i', strtotime($f['updated_at'])) ?>
                        <?php else : ?>
                            -
                        <?php endif ?>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-primary btn-sm btn-simpan-maintenance">Simpan</button>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>

<script>
$(document).on('click', '.btn-simpan-maintenance', function () {
    const $tr = $(this).closest('tr');
    const $btn = $(this);
    const featureKey = $tr.data('feature-key');
    const aktif = $tr.find('.toggle-aktif').is(':checked') ? '1' : '0';
    const pesan = $tr.find('.input-pesan').val();

    $btn.prop('disabled', true).text('Menyimpan...');

    $.post('<?= site_url('maintenance/toggle') ?>', {
        feature_key: featureKey,
        aktif: aktif,
        pesan: pesan
    }).done(function (res) {
        if (res && res.success) {
            const now = new Date();
            const tgl = String(now.getDate()).padStart(2, '0') + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + now.getFullYear()
                + ' ' + String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
            $tr.find('.ket-terakhir').text(aktif === '1' ? ('<?= esc(session()->get('userid')) ?> · ' + tgl) : '-');
        } else {
            alert((res && res.error) ? res.error : 'Gagal menyimpan.');
        }
    }).fail(function () {
        alert('Gagal menyimpan, coba lagi.');
    }).always(function () {
        $btn.prop('disabled', false).text('Simpan');
    });
});
</script>
<?= $this->endSection('isi') ?>
