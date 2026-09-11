<?php
$permissionsBySection = $permissionsBySection ?? [];
$defaultPermissionsByLevel = $defaultPermissionsByLevel ?? [];
$datalevel = $datalevel ?? [];

$levelOptions = [];
foreach ($datalevel as $l) {
    $levelOptions[] = ['id' => (int) $l['levelid'], 'text' => $l['levelnama']];
}

$pickerRows = [];
foreach ($permissionsBySection as $sectionKey => $section) {
    $pickerRows[] = [
        'type' => 'section',
        'section_key' => $sectionKey,
        'feature_key' => '',
        'key' => '',
        'label' => $section['label'],
    ];

    foreach ($section['features'] as $featureKey => $feature) {
        $pickerRows[] = [
            'type' => 'feature',
            'section_key' => $sectionKey,
            'feature_key' => $featureKey,
            'key' => '',
            'label' => $feature['label'],
        ];

        foreach ($feature['permissions'] as $permission) {
            $pickerRows[] = [
                'type' => 'permission',
                'section_key' => $sectionKey,
                'feature_key' => $featureKey,
                'key' => $permission['key'],
                'label' => $permission['action_label'],
            ];
        }
    }
}
?>
<div class="modal fade" id="modaltambah" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Tambah User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?= form_open('users/simpan', ['class' => 'frmsimpan']) ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="">ID User</label>
                    <input type="text" name="iduser" id="iduser" class="form-control form-control-sm" autocomplete="off">
                    <div id="msg-iduser" class="invalid-feedback"></div>
                </div>
                <div class="form-group">
                    <label for="">Nama Lengkap</label>
                    <input type="text" name="namalengkap" id="namalengkap" class="form-control form-control-sm" autocomplete="off">
                    <div id="msg-namalengkap" class="invalid-feedback"></div>
                </div>
                <div class="form-group">
                    <label for="">Level User :</label>
                    <div class="input-group mb-0 tre-inline-combobox tre-inline-combobox-solo" id="levelCombobox">
                        <input type="text" class="form-control form-control-sm" placeholder="Nama Level" name="level_display" id="levelDisplay" autocomplete="off">
                        <input type="hidden" name="level" id="level">
                        <div class="tre-inline-combobox-menu" id="levelComboboxMenu"></div>
                    </div>
                    <div id="msg-level" class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="">
                        Hak Akses
                        <small class="text-muted" id="tambahAksesCounter"></small>
                    </label>
                    <div class="tambah-akses-wrap">
                        <div class="tambah-akses-toolbar">
                            <input type="search" id="tambahAksesSearch" class="form-control form-control-sm" placeholder="Cari permission...">
                        </div>
                        <div class="tambah-akses-scroll">
                            <?php foreach ($pickerRows as $row) : ?>
                                <?php
                                $rowType = $row['type'];
                                $searchText = strtolower(trim($row['label'] . ' ' . $row['section_key'] . ' ' . $row['feature_key']));
                                // Default semua section tertutup supaya user ngga perlu scroll banyak.
                                $hiddenByDefault = $rowType !== 'section';
                                ?>
                                <div
                                    class="tambah-akses-row tambah-akses-row-<?= esc($rowType) ?>"
                                    data-row-type="<?= esc($rowType) ?>"
                                    data-section="<?= esc($row['section_key']) ?>"
                                    data-feature="<?= esc($row['feature_key']) ?>"
                                    data-search="<?= esc($searchText) ?>"
                                    data-collapsed="<?= $rowType === 'section' ? '1' : '' ?>"
                                    <?= $hiddenByDefault ? 'style="display:none;"' : '' ?>>
                                    <?php if ($rowType === 'section') : ?>
                                        <i class="fa fa-chevron-right tambah-akses-toggle"></i>
                                    <?php endif ?>
                                    <label class="tambah-akses-check-label">
                                        <input
                                            type="checkbox"
                                            class="tambah-akses-check tambah-akses-check-<?= esc($rowType) ?>"
                                            data-section="<?= esc($row['section_key']) ?>"
                                            data-feature="<?= esc($row['feature_key']) ?>"
                                            value="<?= esc($row['key']) ?>"
                                            <?= $rowType === 'permission' ? 'name="permissions[]"' : '' ?>>
                                        <span><?= esc($row['label']) ?></span>
                                    </label>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                    <div id="msg-permissions" class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="">Password</label>
                    <div class="tambah-password-wrap">
                        <input autocomplete="off" type="password" name="password" id="password" class="form-control form-control-sm">
                        <i class="fa fa-eye toggle-password" data-target="#password"></i>
                    </div>
                    <div id="msg-password" class="invalid-feedback"></div>
                </div>
                <div class="form-group">
                    <label for="">Konfirmasi Password</label>
                    <div class="tambah-password-wrap">
                        <input autocomplete="off" type="password" name="password_confirm" id="password_confirm" class="form-control form-control-sm">
                        <i class="fa fa-eye toggle-password" data-target="#password_confirm"></i>
                    </div>
                    <div id="msg-password_confirm" class="invalid-feedback"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success btnsimpan">Simpan</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<style>
    .tambah-akses-wrap {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: .35rem;
        overflow: hidden;
    }

    .tambah-akses-toolbar {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: .5rem;
    }

    .tambah-akses-scroll {
        max-height: 16rem;
        overflow-y: auto;
        padding: .35rem 0;
    }

    .tambah-akses-row {
        padding: .2rem .75rem;
    }

    .tambah-akses-row-section {
        align-items: center;
        background: #f4f6f9;
        display: flex;
        font-weight: 800;
        gap: .5rem;
    }

    .tambah-akses-toggle {
        color: #8792a3;
        cursor: pointer;
        font-size: .75rem;
        width: .9rem;
    }

    .tambah-akses-row-feature {
        font-weight: 700;
        padding-left: 1.5rem;
    }

    .tambah-akses-row-permission {
        padding-left: 2.75rem;
    }

    .tambah-akses-check-label {
        align-items: center;
        cursor: pointer;
        display: flex;
        gap: .5rem;
        margin-bottom: 0;
    }

    .tambah-akses-row-permission .tambah-akses-check-label {
        font-size: .85rem;
        font-weight: 400;
    }

    .tambah-password-wrap {
        position: relative;
    }

    .tambah-password-wrap input {
        padding-right: 2rem;
    }

    .tambah-password-wrap .toggle-password {
        color: #8792a3;
        cursor: pointer;
        position: absolute;
        right: .6rem;
        top: 50%;
        transform: translateY(-50%);
    }
</style>

<script>
    let defaultPermissionsByLevel = <?= json_encode($defaultPermissionsByLevel) ?>;
    let levelOptions = <?= json_encode($levelOptions) ?>;

    $(document).ready(function() {
        window.treInitInlineCombobox({
            box: '#levelCombobox',
            input: '#levelDisplay',
            hidden: '#level',
            menu: '#levelComboboxMenu',
            options: levelOptions,
            onSelect: function() {
                $('#levelDisplay').removeClass('is-invalid is-valid');
                $('#msg-level').html('');
                $('#level').trigger('change');
            }
        });

        function permissionSelector(section, feature) {
            let selector = '.tambah-akses-row-permission .tambah-akses-check-permission[data-section="' + section + '"]';
            if (feature) {
                selector += '[data-feature="' + feature + '"]';
            }
            return selector;
        }

        function refreshTambahAksesChecks() {
            $('.tambah-akses-row-feature').each(function() {
                const $row = $(this);
                const section = $row.data('section');
                const feature = $row.data('feature');
                const $check = $row.find('.tambah-akses-check-feature');
                const $items = $(permissionSelector(section, feature));
                const checked = $items.filter(':checked').length;

                $check.prop('checked', $items.length > 0 && checked === $items.length);
                $check.prop('indeterminate', checked > 0 && checked < $items.length);
            });

            $('.tambah-akses-row-section').each(function() {
                const $row = $(this);
                const section = $row.data('section');
                const $check = $row.find('.tambah-akses-check-section');
                const $items = $(permissionSelector(section, ''));
                const checked = $items.filter(':checked').length;

                $check.prop('checked', $items.length > 0 && checked === $items.length);
                $check.prop('indeterminate', checked > 0 && checked < $items.length);
            });

            $('#tambahAksesCounter').text($('.tambah-akses-check-permission:checked').length + ' dipilih');
        }

        $(document).on('change', '.tambah-akses-check-section', function() {
            const section = $(this).data('section');
            $(permissionSelector(section, '')).prop('checked', this.checked);
            refreshTambahAksesChecks();
        });

        $(document).on('change', '.tambah-akses-check-feature', function() {
            const section = $(this).data('section');
            const feature = $(this).data('feature');
            $(permissionSelector(section, feature)).prop('checked', this.checked);
            refreshTambahAksesChecks();
        });

        $(document).on('change', '.tambah-akses-check-permission', refreshTambahAksesChecks);

        function restoreCollapsedState() {
            $('.tambah-akses-row-section').each(function() {
                const $row = $(this);
                const section = $row.data('section');
                const collapsed = $row.attr('data-collapsed') !== '0';
                $('.tambah-akses-row-feature[data-section="' + section + '"], .tambah-akses-row-permission[data-section="' + section + '"]').toggle(!collapsed);
            });
        }

        $(document).on('click', '.tambah-akses-toggle', function(e) {
            e.stopPropagation();
            const $row = $(this).closest('.tambah-akses-row-section');
            const section = $row.data('section');
            const collapsed = $row.attr('data-collapsed') !== '0';

            $row.attr('data-collapsed', collapsed ? '0' : '1');
            $(this).toggleClass('fa-chevron-right', !collapsed).toggleClass('fa-chevron-down', collapsed);

            if (!($('#tambahAksesSearch').val() || '').trim()) {
                $('.tambah-akses-row-feature[data-section="' + section + '"], .tambah-akses-row-permission[data-section="' + section + '"]').toggle(collapsed);
            }
        });

        function expandSection(section) {
            const $row = $('.tambah-akses-row-section[data-section="' + section + '"]');
            $row.attr('data-collapsed', '0');
            $row.find('.tambah-akses-toggle').removeClass('fa-chevron-right').addClass('fa-chevron-down');
        }

        function autoExpandSectionsWithSelection() {
            $('.tambah-akses-row-section').each(function() {
                const section = $(this).data('section');
                if ($(permissionSelector(section, '')).filter(':checked').length > 0) {
                    expandSection(section);
                }
            });
            restoreCollapsedState();
        }

        $('#tambahAksesSearch').on('input', function() {
            const query = ($(this).val() || '').toLowerCase().trim();

            if (!query) {
                restoreCollapsedState();
                return;
            }

            $('#modaltambah .tambah-akses-row').hide();
            $('.tambah-akses-row-permission').each(function() {
                if (($(this).data('search') || '').toString().includes(query)) {
                    $(this).show();
                    const section = $(this).data('section');
                    const feature = $(this).data('feature');
                    $('.tambah-akses-row-section[data-section="' + section + '"]').show();
                    $('.tambah-akses-row-feature[data-section="' + section + '"][data-feature="' + feature + '"]').show();
                }
            });
        });

        $('#level').on('change', function() {
            const levelId = this.value;
            const defaults = defaultPermissionsByLevel[levelId] || [];

            $('.tambah-akses-check-permission').prop('checked', false);
            defaults.forEach(function(key) {
                $('.tambah-akses-check-permission[value="' + key + '"]').prop('checked', true);
            });
            refreshTambahAksesChecks();
            autoExpandSectionsWithSelection();
        });

        $(document).on('click', '.toggle-password', function() {
            const $input = $($(this).data('target'));
            const isPassword = $input.attr('type') === 'password';
            $input.attr('type', isPassword ? 'text' : 'password');
            $(this).toggleClass('fa-eye fa-eye-slash');
        });

        $('#modaltambah').on('hidden.bs.modal', function() {
            $('#tambahAksesSearch').val('');
            $('.tambah-akses-row').show();
            $('.tambah-akses-check').prop('checked', false).prop('indeterminate', false);
            $('#tambahAksesCounter').text('');
            $('#levelDisplay').val('');
            $('#level').val('');
        });

        $('.frmsimpan').submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: "post",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json",
                cache: false,
                beforeSend: function() {
                    $('.btnsimpan').prop('disable', true);
                    $('.btnsimpan').html('<i class="fa fa-spin fa-spinner"></i>');
                },
                complete: function() {
                    $('.btnsimpan').prop('disable', false);
                    $('.btnsimpan').html('Simpan');
                },
                success: function(response) {
                    if (response.error) {
                        let err = response.error;

                        const messages = Object.values(err).filter(function(m) {
                            return !!m;
                        });
                        if (messages.length) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Menyimpan',
                                html: messages.join('<br>')
                            });
                        }

                        if (err.iduser) {
                            $('#iduser').addClass('is-invalid');
                            $('#msg-iduser').html(err.iduser);
                        } else {
                            $('#iduser').removeClass('is-invalid');
                            $('#iduser').addClass('is-valid');
                            $('#msg-iduser').html('');
                        }
                        if (err.namalengkap) {
                            $('#namalengkap').addClass('is-invalid');
                            $('#msg-namalengkap').html(err.namalengkap);
                        } else {
                            $('#namalengkap').removeClass('is-invalid');
                            $('#namalengkap').addClass('is-valid');
                            $('#msg-namalengkap').html('');
                        }
                        if (err.level) {
                            $('#levelDisplay').addClass('is-invalid');
                            $('#msg-level').html(err.level);
                        } else {
                            $('#levelDisplay').removeClass('is-invalid');
                            $('#levelDisplay').addClass('is-valid');
                            $('#msg-level').html('');
                        }
                        if (err.password) {
                            $('#password').addClass('is-invalid');
                            $('#msg-password').html(err.password);
                        } else {
                            $('#password').removeClass('is-invalid');
                            $('#password').addClass('is-valid');
                            $('#msg-password').html('');
                        }
                        if (err.password_confirm) {
                            $('#password_confirm').addClass('is-invalid');
                            $('#msg-password_confirm').html(err.password_confirm);
                        } else {
                            $('#password_confirm').removeClass('is-invalid');
                            $('#password_confirm').addClass('is-valid');
                            $('#msg-password_confirm').html('');
                        }
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.sukses
                        });
                        $('#modaltambah').modal('hide');
                        if (typeof dataUser !== 'undefined' && dataUser.ajax) {
                            dataUser.ajax.reload();
                        } else {
                            window.location.reload();
                        }
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError)
                }
            });
            return false;
        });
    });
</script>
