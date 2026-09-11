<?php
    $totalProduk = count($rows);
    $totalQtyPcs = 0.0;
    $totalQtyKg = 0.0;
    foreach ($rows as $row) {
        $totalQtyPcs += (float) ($row['qty_pcs'] ?? 0);
        $totalQtyKg += (float) ($row['qty_kg'] ?? 0);
    }
?>
<p class="text-muted">
    Harga modal Material otomatis terkunci saat harganya sudah lengkap ketemu dari Invoice In.
    Untuk invoice yang harga modalnya belum lengkap, sistem coba hitung ulang tiap halaman ini dibuka
    pakai Invoice In terbaru, dan langsung dikunci begitu lengkap. Nilai Material,
    Jasa, dan Transport &amp; OH bisa diubah manual untuk simulasi margin pada layar ini.
</p>
<?php if ($periodeDipilih) : ?>
    <p class="reporting-periode-info">
        Periode <?= date('d-m-Y', strtotime($tglawal)) ?> s/d <?= date('d-m-Y', strtotime($tglakhir)) ?>
        <?php if ($namaPelanggan !== '') : ?>
            <span class="text-muted">| Pelanggan: <?= esc($namaPelanggan) ?></span>
        <?php endif ?>
    </p>
<?php endif ?>
<?php if (!$periodeDipilih) : ?>
    <div class="reporting-empty">Silakan pilih periode di atas, lalu klik Tampilkan.</div>
<?php elseif (empty($rows)) : ?>
    <div class="reporting-empty">Tidak ada produk yang tertagih pada periode ini.</div>
<?php else : ?>
    <div class="reporting-summary-grid">
        <div class="reporting-summary-card">
            <div class="reporting-summary-label">Total Produk</div>
            <div class="reporting-summary-value"><?= number_format($totalProduk, 0, ',', '.') ?></div>
        </div>
        <div class="reporting-summary-card">
            <div class="reporting-summary-label">Total Qty (Pcs)</div>
            <div class="reporting-summary-value"><?= number_format($totalQtyPcs, 0, ',', '.') ?></div>
        </div>
        <div class="reporting-summary-card">
            <div class="reporting-summary-label">Total Qty (Kg)</div>
            <div class="reporting-summary-value"><?= number_format($totalQtyKg, 3, ',', '.') ?></div>
        </div>
        <div class="reporting-summary-card">
            <div class="reporting-summary-label">Total Margin Periode Ini</div>
            <div class="reporting-summary-value" id="grandTotalMargin">Rp 0</div>
        </div>
    </div>

    <div class="reporting-list" id="reportingResult">
        <?php foreach ($rows as $i => $row) : ?>
            <?php
                $materialAuto = round((float) ($row['harga_material_per_pcs'] ?? 0), 2);
                $jasaAuto = round((float) ($row['harga_jasa_per_pcs'] ?? 0), 2);
                $totalModalAuto = $materialAuto + $jasaAuto;
                $hargaJualAuto = round((float) ($row['harga_jual'] ?? 0), 2);
            ?>
            <div class="reporting-item">
                <div class="reporting-item-header">
                    <div class="d-flex align-items-start">
                        <span class="reporting-item-number mr-3"><?= $i + 1 ?></span>
                        <div>
                            <h5 class="reporting-product-title"><?= esc($row['product_code'] . ' - ' . $row['product_name']) ?></h5>
                            <div class="reporting-product-meta">Qty tertagih dan harga jual dari Invoice Out aktif.</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="metric-label">Total Margin</div>
                        <div class="metric-value margin-total">-</div>
                    </div>
                </div>
                <div class="reporting-item-body">
                    <div class="reporting-panel">
                        <div class="reporting-panel-title">Qty Tertagih</div>
                        <div class="reporting-metric">
                            <span class="metric-label">Qty (Pcs)</span>
                            <span class="metric-value qty-pcs" data-qty-pcs="<?= (float) $row['qty_pcs'] ?>"><?= number_format($row['qty_pcs'], 0, ',', '.') ?> <?= esc($row['unit']) ?></span>
                        </div>
                        <div class="reporting-metric">
                            <span class="metric-label">Qty (Kg)</span>
                            <span class="metric-value"><?= number_format($row['qty_kg'], 3, ',', '.') ?> Kg</span>
                        </div>
                    </div>

                    <div class="reporting-panel">
                        <div class="reporting-panel-title">Harga Modal / Pcs (Rp)</div>
                        <div class="reporting-input-grid">
                            <div class="reporting-input-box">
                                <label>Material</label>
                                <div class="input-group">
                                    <input type="number" min="0" step="0.01" class="form-control text-right input-modal input-material" value="<?= esc($materialAuto) ?>" data-default-value="<?= esc($materialAuto) ?>">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary reset-material" title="Reset ke nilai otomatis">Reset</button>
                                    </div>
                                </div>
                                <span class="material-override-note">Diubah manual. Default: Rp <?= number_format($materialAuto, 0, ',', '.') ?></span>
                                <span class="reporting-note">
                                    <?php if (empty($row['material_lengkap'])) : ?>
                                        <details class="text-warning">
                                            <summary>data belum lengkap</summary>
                                            <?= esc($row['material_detail'] ?? '') ?>
                                        </details>
                                    <?php else : ?>
                                        <details class="text-muted">
                                            <summary><?= esc($row['material_sumber'] ?? 'otomatis') ?></summary>
                                            <?= esc($row['material_detail'] ?? '') ?>
                                        </details>
                                    <?php endif ?>
                                </span>
                            </div>
                            <div class="reporting-input-box">
                                <label>Jasa</label>
                                <input type="number" min="0" step="0.01" class="form-control text-right input-modal input-jasa" value="<?= esc($jasaAuto) ?>">
                                <span class="reporting-note"><?= esc($row['jasa_detail'] ?? 'isi manual kalau ada') ?></span>
                            </div>
                            <div class="reporting-input-box">
                                <label>Trp &amp; OH</label>
                                <input type="number" min="0" step="0.01" class="form-control text-right input-modal input-trpoh" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="reporting-panel">
                        <div class="reporting-panel-title">Hasil Margin</div>
                        <div class="reporting-metric">
                            <span class="metric-label">Total Modal</span>
                            <span>
                                <div class="input-group">
                                    <input type="number" min="0" step="0.01" class="form-control reporting-result-input input-modal input-total-modal" value="<?= esc($totalModalAuto) ?>" data-default-value="<?= esc($totalModalAuto) ?>" data-manual="0">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary reset-total-modal" title="Reset total modal">Reset</button>
                                    </div>
                                </div>
                                <span class="reporting-result-note total-modal-note">Total modal diubah manual.</span>
                            </span>
                        </div>
                        <div class="reporting-metric">
                            <span class="metric-label">Jual / Pcs</span>
                            <span>
                                <div class="input-group">
                                    <input type="number" min="0" step="0.01" class="form-control reporting-result-input input-modal input-harga-jual" value="<?= esc($hargaJualAuto) ?>" data-default-value="<?= esc($hargaJualAuto) ?>">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary reset-harga-jual" title="Reset harga jual">Reset</button>
                                    </div>
                                </div>
                                <span class="reporting-result-note harga-jual-note">Harga jual diubah manual.</span>
                            </span>
                        </div>
                        <div class="reporting-metric">
                            <span class="metric-label">Margin / Pcs</span>
                            <span class="metric-value margin-unit">-</span>
                        </div>
                        <div class="reporting-metric">
                            <span class="metric-label">Prosentase</span>
                            <span class="metric-value prosentase">-</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>
