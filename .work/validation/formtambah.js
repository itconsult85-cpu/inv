
    var materialBeratContainer = document.getElementById('materialBeratContainer');
    var totalBeratElement = document.getElementById('totalMaterialTerpakai');
    var beratProdukJadiElement = document.getElementById('beratProdukJadi');
    var tanpaBeratElement = document.getElementById('tanpaBerat');
    var sumberMaterialElement = document.getElementById('sumberMaterial');
    var materialUtamaElement = document.getElementById('materialUtama');
    var materialAlternatifElement = document.getElementById('materialAlternatif');

    var GRAM_KE_KG = 1000;
    var beratProdukJadiManual = false;

    function hitungTotalBerat() {
        var totalGram = 0;
        materialBeratContainer.querySelectorAll('.berat-material').forEach(function(input) {
            totalGram += parseFloat(input.value) || 0;
        });
        totalBeratElement.value = (totalGram / GRAM_KE_KG).toFixed(4);
        hitungKalkulasiWise();
    }

    function tampilkanBeratMaterial() {
        if (tanpaBeratElement.checked || sumberMaterialElement.value === 'vendor' || sumberMaterialElement.value === 'beli_jadi') {
            var pesan = sumberMaterialElement.value === 'vendor'
                ? 'Material dari customer: detail berat material tidak wajib dan tidak dihitung sebagai kebutuhan material TRE.'
                : sumberMaterialElement.value === 'beli_jadi'
                ? 'Beli barang jadi dari vendor: material tidak dipakai, jadi tidak perlu diisi.'
                : 'Produk jasa/tanpa berat: detail berat material tidak wajib diisi.';
            materialBeratContainer.innerHTML = '<span class="text-muted">' + pesan + '</span>';
            totalBeratElement.value = '0.0000';
            return;
        }

        var selected = [];
        if (materialUtamaElement.value) {
            selected.push(materialUtamaElement.options[materialUtamaElement.selectedIndex]);
        }
        Array.from(materialAlternatifElement.selectedOptions).forEach(function(opt) {
            if (!selected.some(function(item) { return item.value === opt.value; })) {
                selected.push(opt);
            }
        });
        materialBeratContainer.innerHTML = '';

        if (selected.length === 0) {
            materialBeratContainer.innerHTML = '<span class="text-muted">Pilih material terlebih dahulu.</span>';
            totalBeratElement.value = '';
            return;
        }

        selected.forEach(function(opt) {
            var wrapper = document.createElement('div');
            wrapper.className = 'form-group mb-2';

            var label = document.createElement('label');
            label.textContent = opt.textContent;

            var input = document.createElement('input');
            input.type = 'number';
            input.step = '0.0001';
            input.min = '0.0001';
            input.required = true;
            input.className = 'form-control berat-material';
            input.dataset.materialId = opt.value;
            input.name = 'berat_material[' + opt.value + ']';
            input.placeholder = 'Berat dalam gram';
            input.addEventListener('input', hitungTotalBerat);

            label.className = 'font-weight-bold mb-1';
            wrapper.appendChild(label);
            var materialLabel = document.createElement('small');
            materialLabel.className = 'd-block text-muted';
            materialLabel.textContent = 'Berat material terpakai (gram)';
            wrapper.appendChild(materialLabel);
            wrapper.appendChild(input);
            var detailRow = document.createElement('div');
            detailRow.className = 'row mt-1';
            detailRow.innerHTML = '<div class="col-md-6"><label class="small text-muted mb-1">Wise master (%)</label><input type="number" step="0.001" min="0" max="100" class="form-control wise-material" name="wise_material[' + opt.value + ']" readonly></div>'
                + '<div class="col-md-6"><label class="small text-muted mb-1">Berat produk jadi (gram)</label><input type="number" step="0.001" min="0.001" required class="form-control berat-produk-jadi-material" name="berat_produk_jadi_material[' + opt.value + ']" placeholder="Contoh: 9.820"></div>'
                + '<div class="col-12 mt-2"><div class="border rounded bg-light p-2"><strong class="small d-block mb-2">Kalibrasi aktual material ini (opsional)</strong><div class="row"><div class="col-md-4"><label class="small mb-1">Material masuk (Kg)</label><input type="number" step="0.001" min="0" class="form-control kalibrasi-material" name="kalibrasi_material_kg[' + opt.value + ']"></div><div class="col-md-4"><label class="small mb-1">Qty jadi (pcs)</label><input type="number" step="1" min="1" class="form-control kalibrasi-qty" name="kalibrasi_qty_produk[' + opt.value + ']"></div><div class="col-md-4"><label class="small mb-1">Scrap (Kg)</label><input type="number" step="0.001" min="0" class="form-control kalibrasi-scrap" name="kalibrasi_scrap_kg[' + opt.value + ']" placeholder="38.736"></div><div class="col-md-6 mt-2"><label class="small mb-1">Material/pcs (gram)</label><input type="text" class="form-control kalibrasi-hasil-material" value="-" disabled></div><div class="col-md-6 mt-2"><label class="small mb-1">Wise aktual (%)</label><input type="text" class="form-control kalibrasi-hasil-wise" value="-" disabled></div></div></div></div>';
            detailRow.querySelectorAll('.kalibrasi-material, .kalibrasi-qty, .kalibrasi-scrap').forEach(function(input) { input.addEventListener('input', function() { var box = detailRow.querySelector('.kalibrasi-hasil-material'); var wiseBox = detailRow.querySelector('.kalibrasi-hasil-wise'); var kg = parseFloat(detailRow.querySelector('.kalibrasi-material').value) || 0; var qty = parseFloat(detailRow.querySelector('.kalibrasi-qty').value) || 0; var scrap = parseFloat(detailRow.querySelector('.kalibrasi-scrap').value) || 0; box.value = kg > 0 && qty > 0 ? (kg * 1000 / qty).toFixed(6) : '-'; wiseBox.value = kg > 0 && scrap >= 0 && scrap <= kg ? (scrap / kg * 100).toFixed(6) : '-'; if (opt.value === materialUtamaElement.value && wiseBox.value !== '-') { document.getElementById('wise').value = wiseBox.value; } }); });
            detailRow.querySelector('.wise-material').addEventListener('input', function() {
                hitungKalkulasiWise();
                if (opt.value === materialUtamaElement.value) {
                    document.getElementById('wise').value = this.value;
                    beratProdukJadiManual = false;
                    hitungBeratMaterialDariProduk();
                    hitungKalkulasiWise('wise');
                }
            });
            detailRow.querySelector('.berat-produk-jadi-material').addEventListener('input', function() {
                hitungKalkulasiWise();
                if (opt.value === materialUtamaElement.value) {
                    document.getElementById('beratProdukJadi').value = this.value;
                    beratProdukJadiManual = true;
                    hitungBeratMaterialDariProduk();
                    hitungKalkulasiWise('finished');
                }
            });
            wrapper.appendChild(detailRow);
            materialBeratContainer.appendChild(wrapper);
        });

        hitungTotalBerat();
    }

    function toggleTanpaBerat() {
        var isVendorMaterial = sumberMaterialElement.value === 'vendor';
        if (isVendorMaterial) {
            tanpaBeratElement.checked = true;
        }
        var isTanpaBerat = tanpaBeratElement.checked || isVendorMaterial;
        beratProdukJadiElement.required = !isTanpaBerat;
        beratProdukJadiElement.disabled = isTanpaBerat;
        beratProdukJadiElement.value = isTanpaBerat ? '0' : beratProdukJadiElement.value;
        materialUtamaElement.required = !isTanpaBerat && sumberMaterialElement.value !== 'beli_jadi';
        document.getElementById('materialHelp').textContent = isVendorMaterial
            ? 'Material tetap boleh dipilih sebagai referensi, tapi stok/kebutuhan material TRE tidak akan ikut dihitung.'
            : sumberMaterialElement.value === 'beli_jadi'
            ? 'Produk dibeli jadi dari vendor: material tidak perlu diisi sama sekali.'
            : isTanpaBerat
            ? 'Material boleh dipilih sebagai referensi, tapi tidak wajib dan tidak perlu berat.'
            : 'Material inti wajib dipilih. Material inti dan alternatif dipakai sebagai pilihan satu-per-satu saat produksi.';
        tampilkanBeratMaterial();
    }

    var wiseElement = document.getElementById('wise');
    var hasilKalkulasiPerMaterialElement = document.getElementById('hasilKalkulasiPerMaterial');
    var hargaMaterialTerakhirPerMaterial = {};

    function hitungKalibrasiLapangan() {
        var inputKg = parseFloat(document.querySelector('[name="kalibrasi_material_kg"]').value) || 0;
        var qty = parseFloat(document.querySelector('[name="kalibrasi_qty_produk"]').value) || 0;
        var scrapKg = parseFloat(document.querySelector('[name="kalibrasi_scrap_kg"]').value) || 0;
        document.getElementById('kalibrasiMaterialPcsHasil').value = inputKg > 0 && qty > 0
            ? (inputKg * 1000 / qty).toFixed(6) : '-';
        document.getElementById('kalibrasiWiseHasil').value = inputKg > 0 && scrapKg >= 0 && scrapKg <= inputKg
            ? (scrapKg / inputKg * 100).toFixed(6) : '-';
    }
    document.querySelectorAll('[name="kalibrasi_material_kg"], [name="kalibrasi_qty_produk"], [name="kalibrasi_scrap_kg"]')
        .forEach(function(input) { input.addEventListener('input', hitungKalibrasiLapangan); });

    function formatRupiahKalkulasi(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value));
    }

    function escapeKalkulasi(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    // Semua angka di sini dihitung dari baris material masing-masing, bukan total global.
    function hitungKalkulasiWise() {
        var rows = Array.from(materialBeratContainer.querySelectorAll('.berat-material'));
        if (!rows.length) {
            hasilKalkulasiPerMaterialElement.innerHTML = '<span class="text-muted">Pilih material untuk melihat kalkulasi masing-masing material.</span>';
            return;
        }
        hasilKalkulasiPerMaterialElement.innerHTML = rows.map(function(input) {
            var wrapper = input.closest('.form-group');
            var materialId = input.dataset.materialId;
            var materialGram = parseFloat(input.value) || 0;
            var produkInput = wrapper.querySelector('.berat-produk-jadi-material');
            var wiseInput = wrapper.querySelector('.wise-material');
            var produkGram = parseFloat(produkInput && produkInput.value) || 0;
            var wise = parseFloat(wiseInput && wiseInput.value);
            var wasteGram = produkGram > 0 ? Math.max(materialGram - produkGram, 0) : 0;
            var wiseHitung = materialGram > 0 && produkGram > 0 ? wasteGram / materialGram * 100 : wise;
            var pcsPerKg = produkGram > 0 ? GRAM_KE_KG / produkGram : null;
            var harga = hargaMaterialTerakhirPerMaterial[materialId];
            var hargaPcs = harga != null && materialGram > 0 ? materialGram / GRAM_KE_KG * harga : null;
            var label = wrapper.querySelector('label').textContent;
            return '<div class="border rounded p-2 mb-2 bg-light"><strong>' + escapeKalkulasi(label) + '</strong>' +
                '<div class="row mt-1"><div class="col-md-3"><small>Total Material Terpakai</small><br><strong>' + (materialGram > 0 ? (materialGram / GRAM_KE_KG).toLocaleString('id-ID', {maximumFractionDigits: 4}) + ' Kg' : '-') + '</strong></div>' +
                '<div class="col-md-3"><small>Wise</small><br><strong>' + (wiseHitung != null && !isNaN(wiseHitung) ? wiseHitung.toLocaleString('id-ID', {maximumFractionDigits: 4}) + '%' : '-') + '</strong></div>' +
                '<div class="col-md-3"><small>Berat 1 Pcs Produk Jadi</small><br><strong>' + (produkGram > 0 ? produkGram.toLocaleString('id-ID', {maximumFractionDigits: 4}) + ' gram' : '-') + '</strong></div>' +
                '<div class="col-md-3"><small>PCS/KG</small><br><strong>' + (pcsPerKg ? pcsPerKg.toLocaleString('id-ID', {maximumFractionDigits: 4}) : '-') + '</strong></div>' +
                '<div class="col-md-3 mt-2"><small>Estimasi Waste/Pcs</small><br><strong>' + (produkGram > 0 ? wasteGram.toLocaleString('id-ID', {maximumFractionDigits: 4}) + ' gram' : '-') + '</strong></div>' +
                '<div class="col-md-3 mt-2"><small>Harga Material Terakhir/KG</small><br><strong>' + (harga != null ? formatRupiahKalkulasi(harga) : 'Belum ada histori pembelian') + '</strong></div>' +
                '<div class="col-md-3 mt-2"><small>Harga Material/PCS</small><br><strong>' + (hargaPcs != null ? formatRupiahKalkulasi(hargaPcs) : '-') + '</strong></div></div></div>';
        }).join('');
    }

    // Wise didefinisikan sebagai persentase waste dari berat material masuk.
    // Karena itu, dari berat produk jadi + Wise kita dapat membalik rumusnya:
    // berat material = berat produk jadi / (1 - Wise/100).
    function hitungBeratMaterialDariProduk() {
        if (tanpaBeratElement.checked || sumberMaterialElement.value === 'vendor' || sumberMaterialElement.value === 'beli_jadi') {
            return;
        }
        var beratProdukGram = parseFloat(beratProdukJadiElement.value) || 0;
        var wisePersen = parseFloat(wiseElement.value) || 0;
        var penyebut = 1 - (wisePersen / 100);
        if (beratProdukGram <= 0 || wisePersen < 0 || wisePersen >= 100 || penyebut <= 0) {
            return;
        }
        var materialUtama = materialUtamaElement.value;
        if (!materialUtama) {
            return;
        }
        var inputMaterialUtama = Array.from(materialBeratContainer.querySelectorAll('.berat-material'))
            .find(function(input) { return input.dataset.materialId === materialUtama; });
        if (inputMaterialUtama) {
            inputMaterialUtama.value = (beratProdukGram / penyebut).toFixed(4);
            hitungTotalBerat();
        }
    }

    function ambilHargaMaterialTerakhir() {
        var ids = Array.from(materialBeratContainer.querySelectorAll('.berat-material')).map(function(input) { return input.dataset.materialId; });
        ids.forEach(function(matid) {
            $.getJSON('/barang/hargaMaterialTerakhir', { matid: matid }, function(response) {
                hargaMaterialTerakhirPerMaterial[matid] = response.harga !== null && response.harga !== undefined ? Number(response.harga) : null;
                hitungKalkulasiWise();
            }).fail(function() {
                hargaMaterialTerakhirPerMaterial[matid] = null;
                hitungKalkulasiWise();
            });
        });
        hitungKalkulasiWise();
    }

    $('#materialUtama, #materialAlternatif').on('change', function() {
        var coreId = materialUtamaElement.value;
        Array.from(materialAlternatifElement.options).forEach(function(option) {
            option.disabled = coreId !== '' && option.value === coreId;
        });
        if (coreId) {
            $('#materialAlternatif').trigger('change.select2');
            document.getElementById('satuanberat').value = materialUtamaElement.options[materialUtamaElement.selectedIndex].getAttribute('data-matsatid');
        }
        tampilkanBeratMaterial();
        ambilHargaMaterialTerakhir();
    });

    beratProdukJadiElement.addEventListener('input', function() {
        // Field beneran dikosongin (bukan cuma lagi ketik angka baru) --
        // balikin ke mode saran otomatis lagi, jangan nyangkut manual
        // selamanya cuma gara-gara sempet dihapus.
        var kosong = beratProdukJadiElement.value.trim() === '';
        beratProdukJadiManual = !kosong;
        hitungBeratMaterialDariProduk();
        hitungKalkulasiWise('finished');
    });
    wiseElement.addEventListener('input', function() {
        beratProdukJadiManual = false;
        hitungBeratMaterialDariProduk();
        hitungKalkulasiWise('wise');
    });

    tanpaBeratElement.addEventListener('change', toggleTanpaBerat);
    sumberMaterialElement.addEventListener('change', toggleTanpaBerat);

    // Input berat per material ditampilkan & diisi dalam gram (lebih mudah
    // buat angka kecil), tapi yang beneran dikirim ke server harus dalam Kg
    // (satuan yang dipakai tabel berat/berat_material) -- jadi dikonversi
    // pas submit, bukan pas diketik, biar tampilannya tetap gram terus.
    document.getElementById('formProduk').addEventListener('submit', function() {
        syncTreComboboxValues();
        if (tanpaBeratElement.checked || sumberMaterialElement.value === 'vendor' || sumberMaterialElement.value === 'beli_jadi') {
            beratProdukJadiElement.disabled = false;
            beratProdukJadiElement.value = '0';
            return;
        }

        materialBeratContainer.querySelectorAll('.berat-material').forEach(function(input) {
            var gram = parseFloat(input.value) || 0;
            input.value = (gram / GRAM_KE_KG).toFixed(6);
        });
        materialBeratContainer.querySelectorAll('.berat-produk-jadi-material').forEach(function(input) {
            var gram = parseFloat(input.value) || 0;
            input.value = (gram / GRAM_KE_KG).toFixed(6);
        });
        var gramProdukJadi = parseFloat(beratProdukJadiElement.value) || 0;
        beratProdukJadiElement.value = (gramProdukJadi / GRAM_KE_KG).toFixed(6);
    });

    toggleTanpaBerat();

    // Keterangan panjang di bawah tiap field disembunyiin default biar form
    // nggak kelihatan penuh -- diganti link "Info" kecil yang bisa
    // di-klik buat buka-tutup. Otomatis, nggak perlu sentuh HTML tiap field.
    document.querySelectorAll('.form-text.text-muted').forEach(function(el) {
        var toggle = document.createElement('a');
        toggle.href = '#';
        toggle.className = 'form-text-toggle';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.innerHTML = '<i class="fas fa-info-circle"></i> Info <i class="fas fa-chevron-down"></i>';
        el.classList.add('d-none');
        el.parentNode.insertBefore(toggle, el);
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            var tersembunyi = el.classList.toggle('d-none');
            toggle.setAttribute('aria-expanded', tersembunyi ? 'false' : 'true');
        });
    });


    function initTreComboboxes() {
        $('.tre-combobox').each(function() {
            const $box = $(this);
            const $select = $('#' + $box.data('target'));
            const $input = $box.find('.tre-combobox-input');
            const $menu = $box.find('.tre-combobox-menu');

            function options() {
                return $select.find('option').map(function() {
                    return {
                        value: this.value,
                        text: $(this).text()
                    };
                }).get().filter(function(option) {
                    return option.value !== '';
                });
            }

            function render(query) {
                const normalized = (query || '').toLowerCase();
                const filtered = options().filter(function(option) {
                    return option.text.toLowerCase().includes(normalized);
                });

                $menu.empty();
                if (filtered.length === 0) {
                    $menu.append('<div class="tre-combobox-empty">Tidak ada data yang cocok.</div>');
                    return;
                }

                filtered.forEach(function(option, index) {
                    $('<div class="tre-combobox-option"></div>')
                        .toggleClass('is-active', index === 0)
                        .text(option.text)
                        .attr('data-value', option.value)
                        .appendTo($menu);
                });
            }

            function setSelected(value, text) {
                $select.val(value).trigger('change');
                $input.val(text);
                $box.removeClass('is-open');
            }

            const selectedText = $select.find('option:selected').val() ? $select.find('option:selected').text() : '';
            $input.val(selectedText);

            $input.on('focus input', function() {
                render(this.value);
                $box.addClass('is-open');
            });

            $input.on('keydown', function(event) {
                const $active = $menu.find('.tre-combobox-option.is-active');
                if (event.key === 'Enter' && $active.length) {
                    event.preventDefault();
                    setSelected($active.data('value'), $active.text());
                }
            });

            $menu.on('mousedown', '.tre-combobox-option', function(event) {
                event.preventDefault();
                setSelected($(this).data('value'), $(this).text());
            });

            $input.on('blur', function() {
                window.setTimeout(function() {
                    const typed = $input.val().trim().toLowerCase();
                    const match = options().find(function(option) {
                        return option.text.toLowerCase() === typed;
                    });
                    if (match) {
                        setSelected(match.value, match.text);
                    } else if (!typed) {
                        $select.val('');
                    }
                    $box.removeClass('is-open');
                }, 120);
            });
        });
    }

    function syncTreComboboxValues() {
        $('.tre-combobox').each(function() {
            const $box = $(this);
            const $select = $('#' + $box.data('target'));
            const typed = $box.find('.tre-combobox-input').val().trim().toLowerCase();
            let matchedValue = '';

            $select.find('option').each(function() {
                if (this.value !== '' && $(this).text().trim().toLowerCase() === typed) {
                    matchedValue = this.value;
                    return false;
                }
            });

            $select.val(matchedValue);
        });
    }

    $(document).ready(initTreComboboxes);

