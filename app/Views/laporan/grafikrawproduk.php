<link rel="stylesheet" href="<?= base_url() . '/plugins/chart.js/Chart.min.css' ?>">
<script src="<?= base_url() . '/plugins/chart.js/Chart.bundle.min.js' ?>"></script>

<canvas id="myChart" style="height:50vh; width:80vh; "></canvas>

<?php
$tanggal = "";
$total = "";

foreach ($grafik as $row) :
    $tgl = $row->tgl;

    $tanggal .= "'$tgl'" . ",";

    $totalBerat = $row->beratng;
    $total .= "'$totalBerat'" . ",";
endforeach;
?>

<script>
    var ctx = document.getElementById('myChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',
        responsive: true,
        data: {
            labels: [<?= $tanggal ?>],
            datasets: [{
                label: 'Total Berat',
                backgroundColor: ['rgb(255,199,132)', 'rgb(255,19,132)'],
                borderColor: ['rgb(14,199,132)'],
                data: [<?= $total ?>]
            }]
        },
        duration: 1000
    })
</script>