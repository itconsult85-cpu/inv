(function (window, $) {
    'use strict';

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function normaliseOptions(titleOrOptions, text, icon) {
        if (typeof titleOrOptions === 'object' && titleOrOptions !== null) {
            return titleOrOptions;
        }
        return {
            title: titleOrOptions || '',
            text: text || '',
            icon: icon || 'info'
        };
    }

    function iconClass(icon) {
        return {
            success: 'fas fa-check-circle text-success',
            error: 'fas fa-times-circle text-danger',
            warning: 'fas fa-exclamation-triangle text-warning',
            info: 'fas fa-info-circle text-info',
            question: 'fas fa-question-circle text-primary'
        }[icon] || 'fas fa-info-circle text-info';
    }

    function showBootstrapModal(titleOrOptions, text, icon) {
        var options = normaliseOptions(titleOrOptions, text, icon);
        var modal = $('#appPopupModal');
        var dialog = $.Deferred();
        var hasCancel = options.showCancelButton === true;
        var isConfirm = hasCancel || options.showConfirmButton === true && options.confirmButtonText;
        var title = options.title || (isConfirm ? 'Konfirmasi' : 'Pesan');
        var body = options.html !== undefined ? options.html : escapeHtml(options.text || '').replace(/\n/g, '<br>');
        var iconMarkup = options.icon ? '<i class="' + iconClass(options.icon) + ' mr-2" aria-hidden="true"></i>' : '';

        modal.find('.app-popup-dialog').toggleClass('app-popup-dialog-wide', Boolean(options.width || (options.html && String(options.html).length > 240)));
        modal.find('.app-popup-title').html(iconMarkup + escapeHtml(title));
        modal.find('.app-popup-body').html(body);
        modal.find('.app-popup-confirm')
            .text(options.confirmButtonText || 'OK')
            .toggle(isConfirm || options.showConfirmButton !== false)
            .removeClass('btn-primary btn-danger btn-success btn-warning')
            .addClass(options.confirmButtonColor ? 'btn-primary' : (options.icon === 'error' ? 'btn-danger' : 'btn-primary'));
        modal.find('.app-popup-cancel')
            .text(options.cancelButtonText || 'Batal')
            .toggle(hasCancel);
        modal.find('.close, .app-popup-cancel').off('click.appPopup').on('click.appPopup', function () {
            dialog.resolve({ isConfirmed: false, isDismissed: true });
            modal.modal('hide');
        });
        modal.find('.app-popup-confirm').off('click.appPopup').on('click.appPopup', function () {
            var value = true;
            if (typeof options.preConfirm === 'function') {
                value = options.preConfirm();
                if (value === false) {
                    return;
                }
            }
            dialog.resolve({ isConfirmed: true, isDismissed: false, value: value });
            modal.modal('hide');
        });
        modal.off('hidden.bs.modal.appPopup').on('hidden.bs.modal.appPopup', function () {
            if (dialog.state() !== 'pending') {
                return;
            }
            dialog.resolve({ isConfirmed: false, isDismissed: true });
        });
        modal.modal({ backdrop: 'static', keyboard: true, show: true });
        if (typeof options.didOpen === 'function') {
            options.didOpen(modal.find('.app-popup-body')[0]);
        }
        return dialog.promise();
    }

    window.showBootstrapModal = showBootstrapModal;
    // Compatibility helpers for interactive dialogs that used the old API.
    window.Swal = {
        getHtmlContainer: function () { return $('#appPopupModal .app-popup-body')[0]; },
        showValidationMessage: function (message) {
            $('#appPopupModal .app-popup-body .app-popup-validation').remove();
            $('<div class="alert alert-danger app-popup-validation mt-2 mb-0"></div>')
                .text(message)
                .prependTo('#appPopupModal .app-popup-body');
        },
        showLoading: function () {
            $('#appPopupModal .app-popup-body').prepend('<div class="app-popup-loading text-center mb-2"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>');
        }
    };
    window.alert = function (message) {
        return showBootstrapModal({ title: 'Pesan', text: message, icon: 'error' });
    };

    $(document).on('submit', '[data-bootstrap-confirm]', function (event) {
        var form = this;
        var message = $(form).attr('data-bootstrap-confirm');
        if ($(form).data('bootstrap-confirmed') === true) {
            $(form).removeData('bootstrap-confirmed');
            return;
        }
        event.preventDefault();
        showBootstrapModal({
            title: 'Konfirmasi',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Lanjutkan',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }
            $(form).data('bootstrap-confirmed', true);
            form.submit();
        });
    });

    // Keep numbering sequential after server-side sorting and pagination.
    $(document).on('preInit.dt', function (event, settings) {
        var api = new $.fn.dataTable.Api(settings);
        var headers = api.columns().header().toArray();
        var dateColumn = -1;
        headers.some(function (header, index) {
            var label = $(header).text().trim().toLowerCase();
            if (/(tanggal|tgl|date|dibuat|created|waktu)/i.test(label)) {
                dateColumn = index;
                return true;
            }
            return false;
        });

        // If a table exposes a date column, latest records are the initial view.
        if (dateColumn >= 0) {
            settings.aaSorting = [[dateColumn, 'desc']];
        }
    });

    $(document).on('draw.dt', function (event, settings) {
        var api = new $.fn.dataTable.Api(settings);
        var firstHeader = $(api.column(0).header()).text().trim().toLowerCase();
        if (firstHeader !== 'no' && firstHeader !== 'nomor' && firstHeader !== '#') {
            return;
        }
        var start = api.page.info().start;
        var visibleIndex = 0;
        api.rows({ page: 'current' }).every(function () {
            $(this.node()).find('td').first().text(start + visibleIndex + 1);
            visibleIndex += 1;
        });
    });
})(window, window.jQuery);
