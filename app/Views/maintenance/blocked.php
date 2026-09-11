<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Sedang Dalam Perbaikan - Trisentosa Inventory</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700&display=fallback">
    <link rel="stylesheet" href="<?= base_url('plugins/fontawesome-free/css/all.min.css') ?>">

    <style>
        :root {
            --tre-dark: #2b2b2b;
            --tre-red: #e8323a;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Source Sans Pro', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(180deg, #dcecfb 0%, #eaf3fb 40%, #f6fafd 75%, #ffffff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(43, 43, 43, .12);
            padding: 44px 36px;
            max-width: 440px;
            width: 100%;
            text-align: center;
        }

        .card img.logo { height: 34px; width: auto; margin-bottom: 24px; }

        .icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(232, 50, 58, .1);
            color: var(--tre-red);
            font-size: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        h1 {
            font-size: 19px;
            font-weight: 700;
            color: var(--tre-dark);
            margin: 0 0 10px;
        }

        p.pesan {
            color: #666;
            font-size: 14.5px;
            line-height: 1.6;
            margin: 0 0 26px;
        }

        a.btn-kembali {
            display: inline-block;
            background: var(--tre-dark);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            padding: 11px 26px;
            border-radius: 8px;
        }

        a.btn-kembali:hover { opacity: .9; }
    </style>
</head>

<body>
    <div class="card">
        <img src="<?= base_url('image/logo-pt-tre.png') ?>" class="logo" alt="TRE">
        <div class="icon"><i class="fas fa-tools"></i></div>
        <h1>Fitur "<?= esc($featureLabel) ?>" Sedang Dalam Perbaikan</h1>
        <p class="pesan">
            <?= $pesan !== '' ? esc($pesan) : 'Kami sedang melakukan perbaikan pada fitur ini. Fitur lain di aplikasi tetap bisa dipakai seperti biasa. Mohon coba lagi beberapa saat lagi.' ?>
        </p>
        <a href="<?= site_url('main/index') ?>" class="btn-kembali">Kembali ke Beranda</a>
    </div>
</body>

</html>
