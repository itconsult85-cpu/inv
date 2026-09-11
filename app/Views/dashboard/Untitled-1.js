< script >
    $(function() {
        var areaChartData = {
        labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
    datasets: [{
        label: 'Digital Goods',
    backgroundColor: 'rgba(60,141,188,0.9)',
    borderColor: 'rgba(60,141,188,0.8)',
    pointRadius: false,
    pointColor: '#3b8bba',
    pointStrokeColor: 'rgba(60,141,188,1)',
    pointHighlightFill: '#fff',
    pointHighlightStroke: 'rgba(60,141,188,1)',
    data: [28, 48, 40, 19, 86, 27, 90]
                },
    {
        label: 'Electronics',
    backgroundColor: 'rgba(210, 214, 222, 1)',
    borderColor: 'rgba(210, 214, 222, 1)',
    pointRadius: false,
    pointColor: 'rgba(210, 214, 222, 1)',
    pointStrokeColor: '#c1c7d1',
    pointHighlightFill: '#fff',
    pointHighlightStroke: 'rgba(220,220,220,1)',
    data: [65, 59, 80, 81, 56, 55, 40]
                },
    ]
        }

    //-------------
    //- BAR CHART -
    //-------------
    var barChartCanvas = $('#barChart').get(0).getContext('2d')
    var barChartData = $.extend(true, { }, areaChartData)
    var temp0 = areaChartData.datasets[0]
    var temp1 = areaChartData.datasets[1]
    barChartData.datasets[0] = temp1
    barChartData.datasets[1] = temp0

    var barChartOptions = {
        responsive: true,
    maintainAspectRatio: false,
    datasetFill: false
        }

    new Chart(barChartCanvas, {
        type: 'bar',
    data: barChartData,
    options: barChartOptions
        })
    }) <
    /script>

    <
        script >
        <?php if (isset($materialData) && !empty($materialData)) : ?>
        const materialData = <?= json_encode($materialData) ?>;
materialData.sort((a, b) => a.matstok - b.matstok);

        const ctx = document.getElementById('myChart');

        new Chart(ctx, {
            type: 'bar',
        data: {
            labels: materialData.map(e => e.matnama),
        datasets: [{
            label: 'Jumlah stok',
            data: materialData.map(e => e.matstok),
        borderWidth: 1,
            backgroundColor: materialData.map(e => e.matstok >= 500 ? "#4BC0C0" : "#FF6384"),
        }]
    },
        options: {
            scales: {
            y: {
            beginAtZero: true
            }
        }
    }
});

        let i = 0;
        const stokKurang = [];
        while (i < materialData.length && materialData[i].matstok < 500) {
            stokKurang.push(materialData[i]);
        i++;
}

if (stokKurang.length > 0) {
            Swal.fire({
                icon: 'info',
                title: 'Stok hampir habis',
                html: `
                        <p>Berikut material yang kurang dari 500:</p><br>
                        <table class="table">
                        <thead>
                        <tr>
                        <th>Nama Material</th>
                        <th>Jumlah Stok</th>
                        </tr>
                        </thead>
                        <tbody>
                        ${stokKurang.map(e => `<tr><td>${e.matnama}</td><td>${e.matstok}</td></tr>`).join('')}
                        </tbody>
                        </table>
                    `,
            });
        }
        <?php else : ?>
        console.log("No data available.");
        <?php endif; ?>

        <?php if (isset($barangData) && !empty($barangData)) : ?>
        const barangData = <?= json_encode($barangData) ?>;
        barangData.sort((a, b) => a.brgstok - b.minmat);

        const ctxBarang = document.getElementById('myBarangChart');

        new Chart(ctxBarang, {
            type: 'bar',
        data: {
            labels: barangData.map(e => e.brgkode),
        datasets: [{
            label: 'Jumlah stok',
                    data: barangData.map(e => e.stok),
        borderWidth: 1,
                    backgroundColor: barangData.map(e => e.stok >= 100 ? "#36A2EB" : "#FFCE56"),
                }]
            },
        options: {
            scales: {
            y: {
            beginAtZero: true
                    }
                }
            }
        });

        let j = 0;
        const barangStokKurang = [];
        while (j < barangData.length && barangData[j].stok < 100) {
            barangStokKurang.push(barangData[j]);
        j++;
        }

        if (barangStokKurang.length > 0) {
            Swal.fire({
                icon: 'info',
                title: 'Stok barang hampir habis',
                html: `
                    <p>Berikut barang yang kurang dari 100:</p><br>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Jumlah Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${barangStokKurang.map(e => `<tr><td>${e.nama}</td><td>${e.stok}</td></tr>`).join('')}
                        </tbody>
                    </table>
                `,
            });
        }
        <?php else : ?>
        console.log("No barang data available.");
        <?php endif; ?>
    </script>


    <?= $this->extend('main/layout') ?>

    <?= $this->section('judul') ?>
    Dashboard
    <?= $this->endSection('judul') ?>

    <?= $this->section('isi'); ?>
    <!-- SECTION - Dashboard stok material -->

    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0/dist/chartjs-plugin-datalabels.min.js"></script> -->
    <script src="<?= base_url() ?>/plugins/npm/chart.js"></script>
    <!-- <script src="<?= base_url() ?>/plugins/npm/sweetalert211.js"></script> -->
    <!-- <script src="<?= base_url() ?>/plugins/chart.js/Chart.min.js"></script> -->
    <!-- <script src="<?= base_url() ?>/plugins/jquery/jquery.min.js"></script> -->

    <div class="row">
        <!-- chart-produk -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header border-0">
                    <div class="d-flex justify-content-between">
                        <h3 class="card-title">Stok Produk dengan Minimal Stok</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="position-relative mb-4" style="height: 250px;">
                        <canvas id="prod-stok-chart" height="250"></canvas>
                        <div id="prod-chart-tooltip" class="chart-tooltip" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); text-align: center; display: none; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                            Data Stok Belum Tersedia.
                        </div>
                    </div>

                    <div class="d-flex flex-row justify-content-end">
                        <span class="mr-2">
                            <i class="fas fa-square text-primary"></i> Stok
                        </span>
                        <span>
                            <i class="fas fa-square text-warning"></i> Stok Cukup
                        </span>
                        <span>
                            <i class="fas fa-square text-danger"></i> Stok Kurang
                        </span>
                        <span>
                            <i class="fas fa-square text-gray"></i> Minimal Produk
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- chart-material -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header border-0">
                    <div class="d-flex justify-content-between">
                        <h3 class="card-title">Stok Material dengan Minimal Stok</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="position-relative mb-4" style="height: 200px;">
                        <canvas id="mat-stok-chart" style="height: 100%;"></canvas>
                        <div id="mat-chart-tooltip" class="chart-tooltip" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); text-align: center; display: none; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                            Data Material Belum Tersedia.
                        </div>
                    </div>

                    <div class="d-flex flex-row justify-content-end">
                        <span class="mr-2">
                            <i class="fas fa-square text-primary"></i> Stok
                        </span>
                        <span>
                            <i class="fas fa-square text-warning"></i> Stok Cukup
                        </span>
                        <span>
                            <i class="fas fa-square text-danger"></i> Stok Kurang
                        </span>
                        <span>
                            <i class="fas fa-square text-gray"></i> Minimal Material
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- <script>
        $(function() {
            'use strict'

        var ticksStyle = {
            fontColor: '#495057',
        fontStyle: 'bold'
        }

        var mode = 'index'
        var intersect = true

        //chart-barang
        <?php if (isset($barangData) && !empty($barangData)) : ?>
        const barangData = <?= json_encode($barangData) ?>;
            // Filter barangData based on minstok greater than 0
            const filteredBarangData = barangData.filter(e => e.minstok > 0);

            if (filteredBarangData.length > 0) {
                var $barangChart = $('#prod-stok-chart');
        var barangChart = new Chart($barangChart, {
            type: 'bar',
        data: {
            labels: filteredBarangData.map(e => e.brgkode),
        datasets: [{
            label: 'Stok Produk',
                                backgroundColor: filteredBarangData.map(e => {
                                    if (e.brgstok < e.minstok) {
                                        return "#FF0000"; // Merah
                                    } else if (e.brgstok === e.minstok) {
                                        return "#FFCE56"; // Kuning
                                    } else {
                                        return "#36A2EB"; // Biru
                                    }
                                }),
                                borderColor: filteredBarangData.map(e => {
                                    if (e.brgstok < e.minstok) {
                                        return "#FF0000"; // Merah
                                    } else if (e.brgstok === e.minstok) {
                                        return "#FFCE56"; // Kuning
                                    } else {
                                        return "#36A2EB"; // Biru
                                    }
                                }),
                                data: filteredBarangData.map(e => e.brgstok)
                            },
        {
            label: 'Minimal Produk',
        backgroundColor: '#ced4da',
        borderColor: '#ced4da',
                                data: filteredBarangData.map(e => e.minstok)
                            }
        ]
                    },
        options: {
            maintainAspectRatio: false,
        tooltips: {
            enabled: true, // Nonaktifkan tooltip default
        mode: mode,
        intersect: intersect,
        custom: function(tooltipModel) {
                                var tooltipEl = document.getElementById('prod-chart-tooltip');

        // Tampilkan pesan jika tidak ada data yang sesuai
        if (filteredBarangData.length === 0) {
            tooltipEl.style.display = 'block';
                                } else {
            tooltipEl.style.display = 'none';
                                }
                            }
                        },
        hover: {
            mode: mode,
        intersect: intersect
                        },
        legend: {
            display: false
                        },
        scales: {
            yAxes: [{
            gridLines: {
            display: true,
        lineWidth: '4px',
        color: 'rgba(0, 0, 0, .2)',
        zeroLineColor: 'transparent'
                                },
        ticks: $.extend({
            beginAtZero: true,

        // Include a unit in the ticks
        callback: function(value) {
                                        return value.toLocaleString() + ' Pcs';
                                    }
                                }, ticksStyle)
                            }],
        xAxes: [{
            display: true,
        gridLines: {
            display: false
                                },
        ticks: ticksStyle
                            }]
                        }
                    }
                });

        // Determine the color for the icon based on the filteredBarangData
        let iconColor;
                // Check the first item or any logic to determine the icon color
                if (filteredBarangData.some(e => e.brgstok < e.minstok)) {
            iconColor = "#FF0000"; // Merah jika ada stok kurang dari minimal
                } else if (filteredBarangData.some(e => e.brgstok === e.minstok)) {
            iconColor = "#FFCE56"; // Kuning jika ada stok sama dengan minimal
                } else {
            iconColor = "#36A2EB"; // Biru jika semua stok lebih dari minimal
                }
        // Set the icon color
        $('#stok-product-icon').css('color', iconColor);

            } else {
            // Tampilkan pesan bahwa tidak ada data yang sesuai
            $('#prod-chart-tooltip').html('Data Produk Belum Tersedia.').show();
            }
        <?php else : ?>
        // Tampilkan pesan bahwa tidak ada data yang tersedia
        $('#prod-chart-tooltip').html('Tidak Ada Data.').show();
        <?php endif; ?>

        // chart-material
        <?php if (isset($materialData) && !empty($materialData)) : ?>
        const materialData = <?= json_encode($materialData) ?>;
            // Filter materialData based on minstok greater than 0
            const filteredMaterialData = materialData.filter(e => e.minmat > 0);

            if (filteredMaterialData.length > 0) {
                var $materialChart = $('#mat-stok-chart');
        var materialChart = new Chart($materialChart, {
            type: 'bar',
        data: {
            labels: filteredMaterialData.map(e => e.matkode),
        datasets: [{
            label: 'Stok Material',
                                backgroundColor: materialData.map(e => {
                                    if (e.matstok < e.minmat) {
                                        return "#FF0000"; // Merah
                                    } else if (e.matstok === e.minmat) {
                                        return "#FFCE56"; // Kuning
                                    } else {
                                        return "#36A2EB"; // Biru
                                    }
                                }),
                                borderColor: materialData.map(e => {
                                    if (e.matstok < e.minmat) {
                                        return "#FF0000"; // Merah
                                    } else if (e.matstok === e.minmat) {
                                        return "#FFCE56"; // Kuning
                                    } else {
                                        return "#36A2EB"; // Biru
                                    }
                                }),
                                data: filteredMaterialData.map(e => e.matstok)
                            },
        {
            label: 'Minimal Material',
        backgroundColor: '#ced4da',
        borderColor: '#ced4da',
                                data: filteredMaterialData.map(e => e.minmat)
                            }
        ]
                    },
        options: {
            maintainAspectRatio: false,
        tooltips: {
            enabled: true, // Nonaktifkan tooltip default
        mode: mode,
        intersect: intersect,
        custom: function(tooltipModel) {
                                var tooltipEl = document.getElementById('mat-chart-tooltip');

        // Tampilkan pesan jika tidak ada data yang sesuai
        if (filteredMaterialData.length === 0) {
            tooltipEl.style.display = 'block';
                                } else {
            tooltipEl.style.display = 'none';
                                }
                            }
                        },
        hover: {
            mode: mode,
        intersect: intersect
                        },
        legend: {
            display: false
                        },
        scales: {
            yAxes: [{
            gridLines: {
            display: true,
        lineWidth: '4px',
        color: 'rgba(0, 0, 0, .2)',
        zeroLineColor: 'transparent'
                                },
        ticks: $.extend({
            beginAtZero: true,

        // Include a unit in the ticks
        callback: function(value) {
                                        return value.toLocaleString();
                                    }
                                }, ticksStyle)
                            }],
        xAxes: [{
            display: true,
        gridLines: {
            display: false
                                },
        ticks: ticksStyle
                            }]
                        }
                    }
                });

        // Determine the color for the icon based on the filteredMaterialData
        let iconColor;
                // Check the first item or any logic to determine the icon color
                if (filteredMaterialData.some(e => e.matstok < e.minmat)) {
            iconColor = "#FF0000"; // Merah jika ada stok kurang dari minimal
                } else if (filteredMaterialData.some(e => e.matstok === e.minmat)) {
            iconColor = "#FFCE56"; // Kuning jika ada stok sama dengan minimal
                } else {
            iconColor = "#36A2EB"; // Biru jika semua stok lebih dari minimal
                }
        // Set the icon color
        $('#mat-product-icon').css('color', iconColor);

            } else {
            // Tampilkan pesan bahwa tidak ada data yang sesuai
            $('#mat-chart-tooltip').html('Data Material Belum Tersedia.').show();
            }
        <?php else : ?>
        // Tampilkan pesan bahwa tidak ada data yang tersedia
        $('#mat-chart-tooltip').html('Tidak Ada Data.').show();
        <?php endif; ?>


        var $salesChart = $('#sales-chart')
        // eslint-disable-next-line no-unused-vars
        var salesChart = new Chart($salesChart, {
            type: 'bar',
        data: {
            labels: ['JUL', 'AGS', 'SEP', 'OKT', 'NOV', 'DES'],
        datasets: [{
            backgroundColor: '#007bff',
        borderColor: '#007bff',
        data: [1000, 2000, 3000, 2500, 2700, 2500, 3000]
                    },
        {
            backgroundColor: '#ced4da',
        borderColor: '#ced4da',
        data: [700, 1700, 2700, 2000, 1800, 1500, 2000]
                    }
        ]
            },
        options: {
            maintainAspectRatio: false,
        tooltips: {
            mode: mode,
        intersect: intersect
                },
        hover: {
            mode: mode,
        intersect: intersect
                },
        legend: {
            display: false
                },
        scales: {
            yAxes: [{
            // display: false,
            gridLines: {
            display: true,
        lineWidth: '4px',
        color: 'rgba(0, 0, 0, .2)',
        zeroLineColor: 'transparent'
                        },
        ticks: $.extend({
            beginAtZero: true,

        // Include a dollar sign in the ticks
        callback: function(value) {
                                if (value >= 1000) {
            value /= 1000
                                    value += 'k'
                                }

        return '$' + value
                            }
                        }, ticksStyle)
                    }],
        xAxes: [{
            display: true,
        gridLines: {
            display: false
                        },
        ticks: ticksStyle
                    }]
                }
            }
        })
    })
    </script> -->

    <script>
        $(function() {
        <? php if (isset($materialData) && !empty($materialData)) : ?>
        const materialData = <?= json_encode($materialData) ?>;
            // Filter materialData based on minmat greater than 0
            const filteredMaterialData = materialData.filter(e => e.minmat > 0);
        console.log('Filtered Material Data:', filteredMaterialData);

            if (filteredMaterialData.length > 0) {
                var areaChartData = {
            labels: filteredMaterialData.map(e => e.matkode),
        datasets: [{
            label: 'Stok Material',
                            backgroundColor: filteredMaterialData.map(e => {
            console.log(`Material Kode: ${e.matkode}, Stok: ${e.matstok}, Min Stok: ${e.minmat}`);
                                if (e.matstok > e.minmat) {
                                    return "#36A2EB"; // Merah
                                } else if (e.matstok === e.minmat) {
                                    return "#FFCE56"; // Kuning
                                } else {
                                    return "#FF0000"; // Biru
                                }
                            }),

                            borderColor: filteredMaterialData.map(e => {
                                if (e.matstok > e.minmat) {
                                    return "#36A2EB"; // Merah
                                } else if (e.matstok === e.minmat) {
                                    return "#FFCE56"; // Kuning
                                } else {
                                    return "#FF0000"; // Biru
                                }
                            }),
        pointRadius: false,
        pointColor: '#3b8bba',
        pointStrokeColor: 'rgba(60,141,188,1)',
        pointHighlightFill: '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
                            data: filteredMaterialData.map(e => e.matstok)
                        },
        {
            label: 'Minimal Material',
        backgroundColor: '#ced4da',
        borderColor: '#ced4da',
        pointRadius: false,
        pointColor: '#ced4da',
        pointStrokeColor: '#c1c7d1',
        pointHighlightFill: '#fff',
        pointHighlightStroke: 'rgba(220,220,220,1)',
                            data: filteredMaterialData.map(e => e.minmat)
                        },
        ]
                }
            }
        <?php endif; ?>

        //-------------
        //- BAR CHART -
        //-------------
        var barChartCanvas = $('#barChart').get(0).getContext('2d')
        var barChartData = $.extend(true, { }, areaChartData)
        var temp0 = areaChartData.datasets[0]
        var temp1 = areaChartData.datasets[1]
        barChartData.datasets[0] = temp1
        barChartData.datasets[1] = temp0

        var barChartOptions = {
            responsive: true,
        maintainAspectRatio: false,
        datasetFill: false
        }

        new Chart(barChartCanvas, {
            type: 'bar',
        data: barChartData,
        options: barChartOptions
        })
    })
    </script>

    <!-- <script>
        <?php if (isset($materialData) && !empty($materialData)) : ?>
        const materialData = <?= json_encode($materialData) ?>;
        const filteredMaterialData = materialData.filter(e => e.minmat > 0);
        materialData.sort((a, b) => a.matstok - b.matstok);

        const ctx = document.getElementById('myChart');
        if (filteredMaterialData.length > 0) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: filteredMaterialData.map(e => e.matkode),
                    // labels: materialData.map(e => e.matnama),
                    datasets: [{
                        label: 'Jumlah stok',
                        data: materialData.map(e => e.matstok),
                        borderWidth: 1,
                        backgroundColor: materialData.map(e => e.matstok >= e.minmat ? "#4BC0C0" : "#FF6384"),

                    },
                    {
                        label: 'Minimal stok',
                        data: filteredMaterialData.map(e => e.minmat),
                        borderWidth: 1,
                        backgroundColor: '#ced4da'
                    }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // let i = 0;
        // const stokKurang = [];
        // while (i < materialData.length && materialData[i].matstok < 500) {
        //     stokKurang.push(materialData[i]);
        //     i++;
        // }

        // if (stokKurang.length > 0) {
        //     Swal.fire({
        //         icon: 'info',
        //         title: 'Stok hampir habis',
        //         html: `
        //                 <p>Berikut material yang kurang dari 500:</p><br>
        //                 <table class="table">
        //                 <thead>
        //                 <tr>
        //                 <th>Nama Material</th>
        //                 <th>Jumlah Stok</th>
        //                 </tr>
        //                 </thead>
        //                 <tbody>
        //                 ${stokKurang.map(e => `<tr><td>${e.matnama}</td><td>${e.matstok}</td></tr>`).join('')}
        //                 </tbody>
        //                 </table>
        //             `,
        //     });
        // }
    <? php else : ?>
        console.log("No data available.");
        <?php endif; ?>
    </script> -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
        var ctxBarang = document.getElementById('prod-stok-chart').getContext('2d');
        var chartContainerProd = document.querySelector('.position-relative');
        var noDataMessageProd = document.getElementById('prod-chart-tooltip');

        var hasData = <?= json_encode(!empty($mat_chart_data['labels']) && !empty($mat_chart_data['datasets']) && !empty($mat_chart_data['datasets'][0]['data'])); ?>;


        var barangChart = new Chart(ctxBarang, {
            type: 'bar',
        data: {
            labels: <?= json_encode($prod_chart_data['labels']); ?>,
        datasets: <?= json_encode($prod_chart_data['datasets']); ?>
            },
        options: {
            responsive: true,
        plugins: {
            legend: {
            display: false // Menghilangkan legenda
                    },
        title: {
            display: true,
        text: 'Stok Produk dan Minimal Stok'
                    }
                },
        interaction: {
            mode: 'index',
        intersect: false,
                },
        scales: {
            x: {
            stacked: false,
                    },
        y: {
            beginAtZero: true,
        stacked: false
                    }
                }
            }
        });
    });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('mat-stok-chart').getContext('2d');
        var chartContainerMat = document.querySelector('.position-relative');
        var noDataMessageMat = document.getElementById('mat-chart-tooltip');

        // Periksa apakah ada data yang tersedia
        var hasData = <?= json_encode(!empty($mat_chart_data['labels']) && !empty($mat_chart_data['datasets']) && !empty($mat_chart_data['datasets'][0]['data'])); ?>;

        if (!hasData) {
            // Sembunyikan canvas chart dan tampilkan pesan "Data Material Belum Tersedia"
            ctx.canvas.style.display = 'none';
        noDataMessage.style.display = 'block';
        } else {
            // Buat chart jika data tersedia
            var matChart = new Chart(ctx, {
            type: 'bar',
        data: {
            labels: <?= json_encode($mat_chart_data['labels']); ?>,
        datasets: <?= json_encode($mat_chart_data['datasets']); ?>
                },
        options: {
            responsive: true,
        plugins: {
            legend: {
            position: 'top',
                        },
        title: {
            display: true,
        text: 'Stok Produk dan Minimal Stok'
                        }
                    },
        interaction: {
            mode: 'index',
        intersect: false,
                    },
        scales: {
            x: {
            stacked: false,
                        },
        y: {
            beginAtZero: true,
        stacked: false
                        }
                    }
                }
            });
        }
    });
    </script>
    <?= $this->endSection('isi') ?>