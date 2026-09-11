(function (window) {
    'use strict';

    /**
     * Menggabungkan cell pada baris-baris berurutan yang memiliki group key sama.
     *
     * @param {object} api Instance $.fn.dataTable.Api dari callback DataTables.
     * @param {object} options Konfigurasi { groupBy: function(row), columns: number[] }.
     */
    window.treRenderMergedGroupRows = function (api, options) {
        if (!api || typeof api.rows !== 'function') return;

        options = options || {};
        var groupBy = typeof options.groupBy === 'function'
            ? options.groupBy
            : function (row) { return row && row.id; };
        var columns = Array.isArray(options.columns)
            ? options.columns.map(function (column) { return Number(column); }).filter(Number.isInteger)
            : [];

        if (!columns.length) return;

        var rowsApi = api.rows({ page: 'current' });
        var data = rowsApi.data().toArray();
        var nodes = rowsApi.nodes().toArray();
        if (!data.length || !nodes.length) return;

        // DataTables membangun ulang tbody saat draw. Reset juga dilakukan agar
        // callback tetap aman apabila dipanggil lebih dari satu kali pada draw yang sama.
        nodes.forEach(function (rowNode) {
            Array.prototype.forEach.call(rowNode.cells || [], function (cell) {
                cell.rowSpan = 1;
                cell.style.display = '';
            });
            rowNode.classList.remove('tre-merged-group-row');
        });

        var groups = [];
        var current = null;
        data.forEach(function (row, index) {
            var rawKey;
            try {
                rawKey = groupBy(row, index);
            } catch (error) {
                rawKey = null;
            }

            // Baris tanpa key tidak boleh tergabung satu sama lain.
            var key = rawKey === null || rawKey === undefined || rawKey === ''
                ? '__tre_unique_' + index
                : String(rawKey);

            if (!current || current.key !== key) {
                current = { key: key, start: index, length: 1 };
                groups.push(current);
            } else {
                current.length += 1;
            }
        });

        groups.forEach(function (group) {
            if (group.length < 2) return;
            var firstRow = nodes[group.start];
            var lastIndex = group.start + group.length;
            if (!firstRow) return;

            firstRow.classList.add('tre-merged-group-row');
            columns.forEach(function (columnIndex) {
                var firstCell = firstRow.cells[columnIndex];
                if (!firstCell) return;
                firstCell.rowSpan = group.length;

                for (var rowIndex = group.start + 1; rowIndex < lastIndex; rowIndex += 1) {
                    var cell = nodes[rowIndex] && nodes[rowIndex].cells[columnIndex];
                    if (cell) cell.style.display = 'none';
                }
            });
        });
    };
})(window);
