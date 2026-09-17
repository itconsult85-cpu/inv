<?= $this->extend('main/layout') ?>



<?= $this->section('isi'); ?>
<!-- SECTION - Dashboard stok material -->

<!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
<!-- <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0/dist/chartjs-plugin-datalabels.min.js"></script> -->
<script src="<?= base_url() ?>/plugins/npm/chart.js"></script>
<!-- <script src="<?= base_url() ?>/plugins/chart.js/Chart.min.js"></script> -->
<!-- <script src="<?= base_url() ?>/plugins/jquery/jquery.min.js"></script> -->

<div class="row">
    <!-- chart-produk -->
    <div class="col-lg-12">
        <div class="card card-success">
            <div class="card-header border-0">
                <h3 class="card-title">Stok Produk dengan Minimal Stok</h3>
                <!-- <div class="d-flex justify-content-between">
                </div> -->
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="position-relative mb-4">
                    <canvas id="prod-stok-chart" height="100"></canvas>
                    <div id="prod-chart-tooltip" class="chart-tooltip" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); text-align: center; display: none;">
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
    <div class="col-lg-12">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Stok Material dengan Minimal Stok</h3>
                <!-- <div class="d-flex justify-content-between">
                </div>
            </div> -->
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="position-relative mb-4">
                    <canvas id="mat-stok-chart" height="100"></canvas>
                    <div id="mat-chart-tooltip" class="chart-tooltip" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); text-align: center; display: none;">
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
    <!-- sample-chart -->
    <div class="col-lg-12">
        <div class="card card-success">
            <div class="card-header">
                <form id="yearForm">
                    <label for="year">Select Year:</label>
                    <select id="year" name="year">
                        <?php for ($i = date('Y'); $i >= 2000; $i--) : ?>
                            <option value="<?= $i ?>" <?= isset($year) && $year == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </form>
                <h3 class="card-title">Produk terbanyak keluar</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="position-relative mb-4">
                    <canvas id="barangKeluarChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>

                <div class="d-flex flex-row justify-content-end">
                    <span class="mr-2">
                        <i id="mat-product-icon" class="fas fa-square"></i> Tahun <?= isset($year) ? $year : date('Y') ?>
                    </span>
                    <span>
                        <i class="fas fa-square text-gray"></i> Tahun <?= isset($year) ? $year - 1 : date('Y') - 1 ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <!-- sample-chart-aziz -->
    <div class="col-lg-6">
        <div class="card-header">

            <h3 class="card-title">Stok Material dengan Minimal Stok</h3>
            <!-- <div class="d-flex justify-content-between">
                </div> -->
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="position-relative mb-4">
                <canvas id="myChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>

            <div class="d-flex flex-row justify-content-end">
                <span class="mr-2">
                    <i id="mat-product-icon" class="fas fa-square"></i> Stok Material
                </span>
                <span>
                    <i class="fas fa-square text-gray"></i> Minimal Material
                </span>
            </div>
        </div>
    </div>
</div>
<!-- <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">Bar Chart</h3>
        </div>
        <div class="card-body">
            <div class="chart">
                <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div> -->
<!-- /.card-body -->
<!-- </div> -->
<!-- chart-produk-keluar -->
<!-- <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                    <h3 class="card-title">Produk Keluar</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="position-relative mb-4">
                    <canvas id="sales-chart" height="200"></canvas>
                </div>

                <div class="d-flex flex-row justify-content-end">
                    <span class="mr-2">
                        <i class="fas fa-square text-primary"></i> This year
                    </span>

                    <span>
                        <i class="fas fa-square text-gray"></i> Last year
                    </span>
                </div>
            </div>
        </div>
    </div> -->
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
        <?php if (isset($materialData) && !empty($materialData)) : ?>
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
        var barChartData = $.extend(true, {}, areaChartData)
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
        //     showBootstrapModal({
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
    <?php else : ?>
        console.log("No data available.");
    <?php endif; ?>
</script> -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var ctxBarang = document.getElementById('prod-stok-chart').getContext('2d');
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
                        display: false,
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
                        display: false // Menghilangkan legenda
                    },
                    title: {
                        display: false,
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
    $(document).ready(function() {
        function updateChart(data) {
            myChart.data.labels = data.labels;
            myChart.data.datasets = data.datasets.map(dataset => ({
                label: dataset.label,
                backgroundColor: dataset.backgroundColor,
                borderColor: dataset.borderColor,
                borderWidth: dataset.borderWidth,
                data: dataset.data
            }));
            myChart.update();
        }

        function updateChartData(year) {
            $.ajax({
                url: '<?= base_url('dashboard/getChartData') ?>',
                type: 'GET',
                data: {
                    year: year
                },
                dataType: 'json',
                success: function(data) {
                    console.log(data); // Debugging
                    updateChart(data);
                },
                error: function(xhr, status, error) {
                    console.error('Failed to fetch chart data:', error);
                }
            });
        }

        var initialChartData = <?= json_encode($barang_keluar_chart_data) ?>;
        var ctx = document.getElementById('barangKeluarChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: initialChartData,
            options: {
                responsive: true,
                scales: {
                    xAxes: [{
                        stacked: true,
                        ticks: {
                            autoSkip: false,
                            maxRotation: 0,
                            callback: function(value, index, values) {
                                return value; // Ensure all labels are shown
                            }
                        }
                    }],
                    yAxes: [{
                        stacked: true
                    }]
                }
            }
        });

        $('#year').change(function() {
            var selectedYear = $(this).val();
            updateChartData(selectedYear);
        });
    });
</script>
<?= $this->endSection('isi') ?>