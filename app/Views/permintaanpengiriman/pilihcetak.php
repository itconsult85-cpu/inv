<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Pilih PO untuk Print
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<button class="btn btn-warning" onclick="history.back()">
    <i class="fa fa-undo"></i> Kembali
</button>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .po-options { max-width: 900px; margin: 0 auto; }
    .po-option {
        display: flex; align-items: center; gap: 14px; width: 100%;
        margin-bottom: 10px; padding: 14px 16px; cursor: pointer;
        border: 1px solid #ced4da; border-radius: 5px; background: #fff;
    }
    .po-option:hover { border-color: #80bdff; background: #f7fbff; }
    .po-option input { width: 18px; height: 18px; flex: 0 0 auto; }
    .po-option-text { display: grid; grid-template-columns: 1fr 1fr 2fr; gap: 18px; width: 100%; }
    .po-option-text strong { display: block; font-size: .78rem; color: #6c757d; }
    @media (max-width: 767px) { .po-option-text { grid-template-columns: 1fr; gap: 6px; } }
</style>

<div class="po-options">
    <div class="form-group">
        <label>Tanggal Permintaan</label>
        <input class="form-control" value="<?= date('d-m-Y', strtotime($header['tanggal'])) ?>" readonly>
    </div>

    <label>Pilih PO</label>
    <?php foreach ($poList as $index => $po) : ?>
        <label class="po-option" for="po<?= $index ?>">
            <input type="radio" name="pilihan_po" id="po<?= $index ?>" value="<?= esc($po['no_po'], 'attr') ?>">
            <span class="po-option-text">
                <span><strong>No. PO</strong><?= esc($po['no_po']) ?></span>
                <span><strong>Pengiriman</strong><?= number_format($po['jumlah_pengiriman'], 0, ',', '.') ?> kali</span>
                <span><strong>Pelanggan</strong><?= esc($po['pelnama'] ?: '-') ?></span>
            </span>
        </label>
    <?php endforeach ?>

    <?php if (!$poList) : ?>
        <div class="alert alert-warning">Belum ada No. PO yang tersimpan pada permintaan ini.</div>
    <?php endif ?>

    <button id="btnPrintPo" class="btn btn-primary btn-block mt-3" <?= !$poList ? 'disabled' : '' ?>>
        <i class="fa fa-print"></i> Print PO Terpilih
    </button>
</div>

<script>
    $('#btnPrintPo').click(function() {
        const po = $('input[name="pilihan_po"]:checked').val();
        if (!po) {
            Swal.fire('Pilih PO', 'Silakan pilih satu PO yang akan dicetak.', 'warning');
            return;
        }

        const url = '/permintaanPengiriman/cetak/<?= esc($hash, 'js') ?>?po=' + encodeURIComponent(po);
        const printWindow = window.open(url, '_blank');
        if (printWindow) printWindow.focus();
    });
</script>
<?= $this->endSection('isi') ?>
