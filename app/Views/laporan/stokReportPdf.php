<!-- <!DOCTYPE html>
<html>

<head>
    <title>Laporan Stok</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <img src="<?= base_url('image/logo.png') ?>" alt="Logo Perusahaan" class="logo">
    <h2 class="title">Laporan Stok</h2>
    <h2 class="title">Pelanggan : <?= $nama_pelanggan ?></h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Stok Cikarang (Pcs)</th>
                <th>Stok Cirebon (Pcs)</th>
                <th>Total Stok (Pcs)</th>
                <th>Total PO</th>
                <th>QTY Terkirim (Pcs)</th>
                <th>Belum Terkirim (Pcs)</th>
                <th>Kebutuhan Produksi (Pcs)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $nomor = 1;
            foreach ($data as $index => $row) : ?>
                <tr>
                    <td><?= $nomor++ ?></td>
                    <td><?= $row['kodebarang'] ?></td>
                    <td class="text-right"><?= number_format($row['totmascik'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($row['totmascir'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($row['totalstok'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($row['total_po'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($row['qty_terkirim'], 0, ',', '.') ?></td>
                    <td class="text-right" style="color: <?= $row['color_kekurangan'] ?>">
                        <?= number_format($row['kekurangan'], 0, ',', '.') ?>
                    </td>
                    <td class="text-right" style="color: <?= $row['color_kebutuhan_produksi'] ?>">
                        <?= number_format($row['kebutuhan_produksi'], 0, ',', '.') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html> -->

<!-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Stok</title>
    
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 150px;
            margin-right: 20px;
        }

        .title {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .table {
            margin-top: 20px;
        }

        .table th {
            background-color: #343a40;
            color: #fff;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
        }

        @media print {
            #downloadPDF {
                display: none;
            }

            .logo {
                margin-top: 0;
                margin-bottom: 0;
            }

            .title {
                margin-top: 0;
                margin-bottom: 0;
            }

            .footer {
                margin-top: 0;
            }

            .table {
                margin-top: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row header">
            <div class="col-md-2 text-left">
                <img src="<?= base_url('image/logo.png') ?>" alt="Logo Perusahaan" class="logo">
            </div>
            <div class="col-md-8 text-center">
                <h2 class="title">Laporan Stok</h2>
                <h4 class="title">Pelanggan: <?= $nama_pelanggan ?></h4>
            </div>
            <div class="col-md-2 text-right">
                <button id="downloadPDF" class="btn btn-primary">Download PDF</button>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Barang</th>
                            <th>Stok Cikarang (Pcs)</th>
                            <th>Stok Cirebon (Pcs)</th>
                            <th>Total Stok (Pcs)</th>
                            <th>Total PO</th>
                            <th>QTY Terkirim (Pcs)</th>
                            <th>Belum Terkirim (Pcs)</th>
                            <th>Kebutuhan Produksi (Pcs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nomor = 1;
                        foreach ($data as $index => $row) : ?>
                            <tr>
                                <td><?= $nomor++ ?></td>
                                <td><?= $row['kodebarang'] ?></td>
                                <td class="text-right"><?= number_format($row['totmascik'], 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($row['totmascir'], 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($row['totalstok'], 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($row['total_po'], 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($row['qty_terkirim'], 0, ',', '.') ?></td>
                                <td class="text-right" style="color: <?= $row['color_kekurangan'] ?>">
                                    <?= number_format($row['kekurangan'], 0, ',', '.') ?>
                                </td>
                                <td class="text-right" style="color: <?= $row['color_kebutuhan_produksi'] ?>">
                                    <?= number_format($row['kebutuhan_produksi'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row footer">
            <div class="col-12">
                <p>Generated by Company System &copy; <?= date('Y') ?></p>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        window.addEventListener('load', () => {
            window.print();
        });

        document.getElementById('downloadPDF').addEventListener('click', () => {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF('landscape');
            const elementHTML = document.body;

            html2canvas(elementHTML).then((canvas) => {
                const imgData = canvas.toDataURL('image/png');
                const imgProps = doc.getImageProperties(imgData);
                const pdfWidth = doc.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                doc.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                doc.save('Laporan_Stok.pdf');
            });
        });
    </script>
</body>

</html> -->


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Stok</title>
    <link href="<?= base_url() ?>/plugins/report/bootstrap.min.css" rel="stylesheet">
    <style>
        .header {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo {
            width: 100px;
            margin-top: 20px;
        }

        .title {
            text-align: center;
            margin-top: 20px;
        }

        .table {
            margin-top: 20px;
        }

        .table th {
            background-color: #343a40;
            color: #fff;
            vertical-align: middle;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
        }

        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }

            .header,
            .title-container,
            .footer {
                margin: 0;
                padding: 0;
            }

            .header {
                justify-content: space-between;
                align-items: center;
                margin-bottom: 0;
            }

            .logo {
                margin: 0;
            }

            .title {
                margin: 0;
            }

            .footer {
                margin-top: 0;
            }

            .table {
                margin-top: 0;
            }

            .table-striped tbody tr:nth-of-type(odd) {
                background-color: rgba(0, 0, 0, 0.05) !important;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row header">
            <div class="col-md-2">
                <div class="logo-container">
                    <img src="<?= base_url('image/logo.png') ?>" alt="Logo Perusahaan" class="logo">
                </div>
            </div>
            <div class="col-md-6 title-container">
                <h2 class="title">Laporan Stok</h2>
                <h4 class="title">Pelanggan: <?= $nama_pelanggan ?></h4>
            </div>
            <div class="col-md-3 text-right">
                <h4 class="title">Tanggal : <?= date('d-m-Y') ?></h4>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 5%; vertical-align: middle;">No</th>
                            <th style="width: 12%; text-align: center; vertical-align: middle;">Kode Barang</th>
                            <th style="text-align: center; width: auto; vertical-align: middle;">Stok Cikarang (Pcs)</th>
                            <th style="text-align: center; width: auto; vertical-align: middle;">Stok Cirebon (Pcs)</th>
                            <th style="text-align: center; width: auto; vertical-align: middle;">Total Stok (Pcs)</th>
                            <th style="text-align: center; width: auto; vertical-align: middle;">Total PO</th>
                            <th style="text-align: center; width: auto; vertical-align: middle;">QTY Terkirim (Pcs)</th>
                            <th style="text-align: center; width: auto; vertical-align: middle;">Belum Terkirim (Pcs)</th>
                            <th style="text-align: center; width: auto; vertical-align: middle;">Kebutuhan Produksi (Pcs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nomor = 1;
                        foreach ($data as $index => $row) : ?>
                            <tr>
                                <td><?= $nomor++ ?></td>
                                <td><?= $row['kodebarang'] ?></td>
                                <td class="text-right"><?= number_format($row['totmascik'], 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($row['totmascir'], 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($row['totalstok'], 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($row['total_po'], 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($row['qty_terkirim'], 0, ',', '.') ?></td>
                                <td class="text-right" style="color: <?= $row['color_kekurangan'] ?>">
                                    <?= number_format($row['kekurangan'], 0, ',', '.') ?>
                                </td>
                                <td class="text-right" style="color: <?= $row['color_kebutuhan_produksi'] ?>">
                                    <?= number_format($row['kebutuhan_produksi'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row footer">
            <div class="col-12">
                <p>Generated by Trisentosa Raya Esolusi &copy; <?= date('Y') ?></p>
            </div>
        </div>
    </div>

    <script src="<?= base_url() ?>/plugins/report/jquery-3.5.1.min.js"></script>
    <script src="<?= base_url() ?>/plugins/report/popper.min.js"></script>
    <script src="<?= base_url() ?>/plugins/report/bootstrap.min.js"></script>
    <script src="<?= base_url() ?>/plugins/report/jspdf.umd.min.js"></script>
    <script src="<?= base_url() ?>/plugins/report/html2canvas.min.js"></script>
</body>

</html>