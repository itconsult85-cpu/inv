<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Hak Akses User
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<a href="<?= site_url('users/index') ?>" class="btn btn-warning">
    <i class="fa fa-undo"></i> Kembali
</a>
<button type="button" class="btn btn-success btnsimpan">
    <i class="fa fa-save"></i> Simpan Hak Akses
</button>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .access-matrix-wrap {
        background: #fff;
        border: 1px solid #e5e9f0;
        border-radius: .35rem;
        overflow: hidden;
    }

    .access-matrix-toolbar {
        align-items: center;
        background: #fff;
        border-bottom: 1px solid #edf0f5;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        padding: .8rem 1rem;
    }

    .access-search {
        flex: 1 1 16rem;
        max-width: 18rem;
        position: relative;
        width: 100%;
    }

    .access-search i {
        color: #8a94a6;
        left: .85rem;
        position: absolute;
        top: .8rem;
    }

    .access-search input {
        border: 1px solid #e0e6ef;
        border-radius: .35rem;
        height: 2.45rem;
        padding-left: 2.25rem;
        width: 100%;
    }

    .access-filter-level {
        flex: 0 1 12rem;
    }

    .access-filter-level select {
        border: 1px solid #e0e6ef;
        border-radius: .35rem;
        height: 2.45rem;
        width: 100%;
    }

    .access-note {
        color: #6c7585;
        flex: 1 1 auto;
        font-size: .9rem;
        text-align: right;
    }

    .access-matrix-scroll {
        max-height: calc(100vh - 285px);
        overflow: auto;
    }

    .access-matrix {
        border-collapse: separate;
        border-spacing: 0;
        margin: 0;
        min-width: 62rem;
        width: 100%;
    }

    .access-matrix th,
    .access-matrix td {
        border-bottom: 1px solid #edf0f5;
        border-right: 1px solid #edf0f5;
        vertical-align: middle;
    }

    .access-matrix thead th {
        background: #fff;
        color: #222b3a;
        font-size: .78rem;
        padding: .75rem .8rem;
        position: sticky;
        text-align: left;
        top: 0;
        z-index: 5;
    }

    .access-matrix thead th:first-child,
    .access-name-cell {
        left: 0;
        position: sticky;
        z-index: 4;
    }

    .access-matrix thead th:first-child {
        z-index: 8;
    }

    .access-name-cell {
        background: #fff;
        min-width: 24rem;
        width: 24rem;
    }

    .access-role-head {
        min-width: 11rem;
        width: 11rem;
    }

    .access-role-label {
        color: #a2aabb;
        display: block;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .access-role-name {
        display: block;
        font-size: .85rem;
        font-weight: 800;
        margin-top: .15rem;
    }

    .access-row-section td,
    .access-row-feature td {
        background: #f6f8fb;
    }

    .access-row-section .access-name-cell,
    .access-row-feature .access-name-cell {
        background: #f6f8fb;
    }

    .access-row-feature td,
    .access-row-permission td {
        background: #fff;
    }

    .access-row-feature .access-name-cell,
    .access-row-permission .access-name-cell {
        background: #fff;
    }

    .access-name-content {
        align-items: center;
        display: flex;
        gap: .55rem;
        min-height: 2.75rem;
        padding: .5rem .8rem;
    }

    .access-row-section .access-name-content {
        cursor: pointer;
        font-weight: 800;
        min-height: 3.1rem;
    }

    .access-row-feature .access-name-content {
        font-weight: 700;
        padding-left: 2.15rem;
    }

    .access-row-permission .access-name-content {
        color: #606b7c;
        padding-left: 3.65rem;
    }

    .access-count {
        background: #eef2f7;
        border: 1px solid #dde4ee;
        border-radius: 999px;
        color: #6f7889;
        font-size: .72rem;
        font-weight: 700;
        margin-left: auto;
        min-width: 1.45rem;
        padding: .05rem .45rem;
        text-align: center;
    }

    .access-check-cell {
        text-align: center;
    }

    .access-check-cell input[type="checkbox"],
    .access-name-content input[type="checkbox"] {
        cursor: pointer;
        height: 1rem;
        width: 1rem;
    }

    .access-empty-row {
        display: none;
    }

    @media (max-width: 768px) {
        .access-matrix-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .access-matrix-scroll {
            max-height: calc(100vh - 330px);
        }

        .access-name-cell {
            min-width: 18rem;
            width: 18rem;
        }
    }
</style>

<?php
$selectedPermissionsByUser = $selectedPermissionsByUser ?? [];
$users = $users ?? [];
$distinctLevels = array_values(array_unique(array_column($users, 'levelnama')));
$permissionRows = [];

foreach ($permissionsBySection as $sectionKey => $section) {
    $sectionPermissionCount = 0;
    foreach ($section['features'] as $feature) {
        $sectionPermissionCount += count($feature['permissions']);
    }

    $permissionRows[] = [
        'type' => 'section',
        'section_key' => $sectionKey,
        'feature_key' => '',
        'key' => '',
        'label' => $section['label'],
        'count' => $sectionPermissionCount,
    ];

    foreach ($section['features'] as $featureKey => $feature) {
        $permissionRows[] = [
            'type' => 'feature',
            'section_key' => $sectionKey,
            'feature_key' => $featureKey,
            'key' => '',
            'label' => $feature['label'],
            'count' => count($feature['permissions']),
        ];

        foreach ($feature['permissions'] as $permission) {
            $permissionRows[] = [
                'type' => 'permission',
                'section_key' => $sectionKey,
                'feature_key' => $featureKey,
                'key' => $permission['key'],
                'label' => $permission['action_label'],
                'count' => null,
            ];
        }
    }
}
?>

<form id="formakses">
    <?= csrf_field() ?>
    <div class="access-matrix-wrap">
        <div class="access-matrix-toolbar">
            <div class="access-search">
                <i class="fa fa-search"></i>
                <input type="search" class="form-control" id="accessSearch" placeholder="Cari permission...">
            </div>
            <div class="access-search">
                <i class="fa fa-user"></i>
                <input type="search" class="form-control" id="accessUserSearch" placeholder="Cari user...">
            </div>
            <div class="access-filter-level">
                <select class="form-control" id="accessLevelFilter">
                    <option value="">Semua Role</option>
                    <?php foreach ($distinctLevels as $lvlName) : ?>
                        <option value="<?= esc($lvlName) ?>"><?= esc($lvlName) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="access-note">
                Level 5 tetap menjadi super user dan selalu punya akses penuh.
            </div>
        </div>

        <div class="access-matrix-scroll">
            <table class="access-matrix">
                <thead>
                    <tr>
                        <th class="access-name-cell">Permission</th>
                        <?php foreach ($users as $user) : ?>
                            <th class="access-role-head" data-user="<?= esc($user['userid']) ?>" data-level="<?= esc($user['levelnama']) ?>">
                                <span class="access-role-label"><?= esc($user['levelnama']) ?></span>
                                <span class="access-role-name"><?= esc($user['usernama']) ?></span>
                            </th>
                        <?php endforeach ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permissionRows as $row) : ?>
                        <?php
                        $rowType = $row['type'];
                        $rowClass = 'access-row-' . $rowType;
                        $searchText = strtolower(trim($row['label'] . ' ' . $row['section_key'] . ' ' . $row['feature_key']));
                        ?>
                        <tr
                            class="<?= esc($rowClass) ?>"
                            data-row-type="<?= esc($rowType) ?>"
                            data-section="<?= esc($row['section_key']) ?>"
                            data-feature="<?= esc($row['feature_key']) ?>"
                            data-permission="<?= esc($row['key']) ?>"
                            data-search="<?= esc($searchText) ?>">
                            <td class="access-name-cell">
                                <div class="access-name-content">
                                    <?php if ($rowType === 'section') : ?>
                                        <i class="fa fa-chevron-up"></i>
                                    <?php elseif ($rowType === 'feature') : ?>
                                        <span></span>
                                    <?php endif ?>
                                    <span><?= esc($row['label']) ?></span>
                                    <?php if ($row['count'] !== null) : ?>
                                        <span class="access-count"><?= esc($row['count']) ?></span>
                                    <?php endif ?>
                                </div>
                            </td>
                            <?php foreach ($users as $user) : ?>
                                <?php
                                $userid = $user['userid'];
                                $selectedForUser = $selectedPermissionsByUser[$userid] ?? [];
                                $checked = $rowType === 'permission' && in_array($row['key'], $selectedForUser, true);
                                ?>
                                <td class="access-check-cell" data-user="<?= esc($userid) ?>">
                                    <input
                                        type="checkbox"
                                        class="access-check access-check-<?= esc($rowType) ?>"
                                        data-user="<?= esc($userid) ?>"
                                        data-section="<?= esc($row['section_key']) ?>"
                                        data-feature="<?= esc($row['feature_key']) ?>"
                                        value="<?= esc($row['key']) ?>"
                                        <?= $rowType === 'permission' ? 'name="permissions[' . esc($userid) . '][]"' : '' ?>
                                        <?= $checked ? 'checked' : '' ?>>
                                </td>
                            <?php endforeach ?>
                        </tr>
                    <?php endforeach ?>
                    <tr class="access-empty-row">
                        <td class="access-name-cell">
                            <div class="access-name-content">Tidak ada permission yang cocok.</div>
                        </td>
                        <?php foreach ($users as $user) : ?>
                            <td></td>
                        <?php endforeach ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</form>

<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    function permissionSelector(userId, section, feature) {
        let selector = '.access-row-permission .access-check-permission[data-user="' + userId + '"]';

        if (section) {
            selector += '[data-section="' + section + '"]';
        }

        if (feature) {
            selector += '[data-feature="' + feature + '"]';
        }

        return selector;
    }

    function refreshMatrixChecks() {
        $('.access-row-feature').each(function() {
            const $row = $(this);
            const section = $row.data('section');
            const feature = $row.data('feature');

            $('.access-check-feature', $row).each(function() {
                const userId = $(this).data('user');
                const $items = $(permissionSelector(userId, section, feature));
                const checked = $items.filter(':checked').length;

                this.checked = $items.length > 0 && checked === $items.length;
                this.indeterminate = checked > 0 && checked < $items.length;
            });
        });

        $('.access-row-section').each(function() {
            const $row = $(this);
            const section = $row.data('section');

            $('.access-check-section', $row).each(function() {
                const userId = $(this).data('user');
                const $items = $(permissionSelector(userId, section, ''));
                const checked = $items.filter(':checked').length;

                this.checked = $items.length > 0 && checked === $items.length;
                this.indeterminate = checked > 0 && checked < $items.length;
            });
        });
    }

    function applySearchFilter() {
        const query = ($('#accessSearch').val() || '').toLowerCase().trim();

        if (!query) {
            $('.access-matrix tbody tr').not('.access-empty-row').show();
            $('.access-row-section[data-collapsed="1"]').each(function() {
                const section = $(this).data('section');
                $('.access-row-feature[data-section="' + section + '"], .access-row-permission[data-section="' + section + '"]').hide();
            });
            $('.access-empty-row').hide();
            return;
        }

        $('.access-row-section, .access-row-feature').hide();
        $('.access-row-permission').each(function() {
            const matched = ($(this).data('search') || '').includes(query);
            $(this).toggle(matched);

            if (matched) {
                const section = $(this).data('section');
                const feature = $(this).data('feature');
                $('.access-row-section[data-section="' + section + '"]').show();
                $('.access-row-feature[data-section="' + section + '"][data-feature="' + feature + '"]').show();
            }
        });

        $('.access-empty-row').toggle($('.access-row-permission:visible').length === 0);
    }

    $(document).on('click', '.access-row-section .access-name-content', function(event) {
        if ($(event.target).is('input')) {
            return;
        }

        const $sectionRow = $(this).closest('.access-row-section');
        const section = $sectionRow.data('section');
        const collapsed = $sectionRow.attr('data-collapsed') === '1';

        $sectionRow.attr('data-collapsed', collapsed ? '0' : '1');
        $sectionRow.find('.fa').toggleClass('fa-chevron-up', collapsed).toggleClass('fa-chevron-down', !collapsed);

        if (($('#accessSearch').val() || '').trim()) {
            applySearchFilter();
            return;
        }

        $('.access-row-feature[data-section="' + section + '"], .access-row-permission[data-section="' + section + '"]').toggle(collapsed);
    });

    $(document).on('change', '.access-check-section', function() {
        const userId = $(this).data('user');
        const section = $(this).data('section');
        $(permissionSelector(userId, section, '')).prop('checked', this.checked);
        refreshMatrixChecks();
    });

    $(document).on('change', '.access-check-feature', function() {
        const userId = $(this).data('user');
        const section = $(this).data('section');
        const feature = $(this).data('feature');
        $(permissionSelector(userId, section, feature)).prop('checked', this.checked);
        refreshMatrixChecks();
    });

    $('#formakses').on('submit', function(event) {
        event.preventDefault();
    });

    $(document).on('change', '.access-check-permission', refreshMatrixChecks);
    $('#accessSearch').on('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            applySearchFilter();
        }
    });
    $('#accessSearch').on('input', applySearchFilter);

    function applyUserColumnFilter() {
        const userQuery = ($('#accessUserSearch').val() || '').toLowerCase().trim();
        const levelFilter = $('#accessLevelFilter').val();

        $('.access-role-head').each(function() {
            const $th = $(this);
            const userId = $th.data('user');
            const userName = ($th.find('.access-role-name').text() || '').toLowerCase();
            const levelName = $th.data('level') || '';

            const matchesUser = !userQuery || userName.includes(userQuery);
            const matchesLevel = !levelFilter || levelName === levelFilter;
            const visible = matchesUser && matchesLevel;

            $th.toggle(visible);
            $('.access-check-cell[data-user="' + userId + '"]').toggle(visible);
        });
    }

    $('#accessUserSearch').on('input', applyUserColumnFilter);
    $('#accessLevelFilter').on('change', applyUserColumnFilter);

    $('.btnsimpan').on('click', function() {
        $.ajax({
            type: 'post',
            url: '<?= site_url('users/simpanakses') ?>',
            data: $('#formakses').serializeArray(),
            dataType: 'json',
            success: function(response) {
                if (response[csrfToken]) {
                    csrfHash = response[csrfToken];
                    $('input[name="' + csrfToken + '"]').val(csrfHash);
                }

                if (response.error) {
                    Swal.fire('Gagal', response.error, 'error');
                    return;
                }

                Swal.fire('Berhasil', response.sukses, 'success');
            },
            error: function() {
                Swal.fire('Gagal', 'Hak akses gagal disimpan.', 'error');
            }
        });
    });

    refreshMatrixChecks();
</script>
<?= $this->endSection('isi') ?>
