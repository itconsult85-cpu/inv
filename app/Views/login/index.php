<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Login - Trisentosa Inventory</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,600,700&display=fallback">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('plugins/fontawesome-free/css/all.min.css') ?>">
    <link rel="icon" type="image/png" href="<?= base_url() ?>image/logo.png">

    <style>
        :root {
            --tre-dark: #2b2b2b;
            --tre-dark-soft: #3a3a3a;
            --tre-red: #e8323a;
            --tre-red-dark: #c9271e;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Source Sans Pro', -apple-system, BlinkMacSystemFont, sans-serif;
            background:
                radial-gradient(circle at 50% 0%, rgba(232, 50, 58, 0.08), transparent 45%),
                linear-gradient(180deg, #dcecfb 0%, #eaf3fb 40%, #f6fafd 75%, #ffffff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 24px;
        }

        body::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 620px;
            height: 620px;
            transform: translate(-50%, -50%);
            border: 1px solid rgba(43, 43, 43, 0.06);
            border-radius: 50%;
            z-index: 0;
        }

        body::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 860px;
            height: 860px;
            transform: translate(-50%, -50%);
            border: 1px solid rgba(43, 43, 43, 0.05);
            border-radius: 50%;
            z-index: 0;
        }

        .clouds-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .clouds-bg svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .brand {
            position: absolute;
            top: 28px;
            left: 32px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 2;
        }

        .brand img { height: 30px; width: auto; }

        .brand span {
            font-weight: 700;
            font-size: 15px;
            color: var(--tre-dark);
            letter-spacing: .2px;
        }

        .login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.62);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 22px;
            box-shadow: 0 20px 50px rgba(43, 43, 43, 0.10), 0 2px 8px rgba(43, 43, 43, 0.06);
            padding: 36px 32px 30px;
            text-align: center;
        }

        .login-icon {
            width: fit-content;
            max-width: 150px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-icon img {
            width: 100%;
            height: auto;
            display: block;
            filter: drop-shadow(0 6px 14px rgba(43, 43, 43, 0.18));
        }

        .login-card h1 {
            font-size: 21px;
            font-weight: 700;
            color: var(--tre-dark);
            margin: 0 0 6px;
        }

        .login-card p.subtitle {
            font-size: 13px;
            color: #8a8a8a;
            margin: 0 0 26px;
            line-height: 1.5;
        }

        .field {
            margin-bottom: 14px;
            text-align: left;
        }

        .input-wrap {
            position: relative;
        }

        .field i.icon-left {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #a3a3a3;
            font-size: 14px;
        }

        .field input {
            width: 100%;
            padding: 12px 14px 12px 38px;
            border-radius: 12px;
            border: 1px solid #e4e4e7;
            background: #fafafa;
            font-size: 14px;
            color: var(--tre-dark);
            outline: none;
            transition: border-color .15s, background .15s;
        }

        .field input:focus {
            border-color: var(--tre-red);
            background: #fff;
        }

        .field input.is-invalid {
            border-color: var(--tre-red);
            background: #fff5f5;
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #a3a3a3;
            font-size: 14px;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }

        .field-error {
            margin-top: 6px;
            font-size: 12px;
            color: var(--tre-red-dark);
        }

        .btn-login {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 12px 0;
            background: var(--tre-dark);
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background .15s;
        }

        .btn-login:hover { background: var(--tre-red); }

        .login-footnote {
            margin-top: 22px;
            font-size: 11.5px;
            color: #b0b0b0;
        }
    </style>
</head>

<body>
    <div class="clouds-bg" aria-hidden="true">
        <svg viewBox="0 0 1600 900" preserveAspectRatio="xMidYMax slice" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="cloudSoften" x="-20%" y="-20%" width="140%" height="140%">
                    <feGaussianBlur stdDeviation="6" />
                </filter>
                <g id="cloudShape">
                    <ellipse cx="0" cy="42" rx="95" ry="34" />
                    <circle cx="-58" cy="22" r="34" />
                    <circle cx="-8" cy="0" r="46" />
                    <circle cx="48" cy="14" r="38" />
                    <circle cx="95" cy="28" r="28" />
                </g>
            </defs>

            <!-- lapis belakang: lebih kecil, lebih atas, agak buram, tersaput biru langit -->
            <g fill="#eaf2fb" filter="url(#cloudSoften)" opacity="0.9">
                <use href="#cloudShape" transform="translate(160,660) scale(1.15)" />
                <use href="#cloudShape" transform="translate(560,610) scale(1.4)" />
                <use href="#cloudShape" transform="translate(1010,650) scale(1.2)" />
                <use href="#cloudShape" transform="translate(1420,620) scale(1.35)" />
            </g>

            <!-- lapis depan: putih bersih, lebih besar/dekat, tanpa garis tepi biar nyatu jadi satu gumpalan halus -->
            <g fill="#ffffff" filter="url(#cloudSoften)">
                <use href="#cloudShape" transform="translate(60,800) scale(1.7)" />
                <use href="#cloudShape" transform="translate(520,840) scale(2)" />
                <use href="#cloudShape" transform="translate(1000,810) scale(1.8)" />
                <use href="#cloudShape" transform="translate(1440,850) scale(1.9)" />
            </g>
        </svg>
    </div>

    <div class="brand">
        <img src="<?= base_url('image/logo-pt-tre.png') ?>" alt="TRE">
        <span>TRE Inventory</span>
    </div>

    <div class="login-card">
        <div class="login-icon"><img src="<?= base_url('image/logo-pt-tre.png') ?>" alt="TRE"></div>
        <h1>Masuk ke Akun Anda</h1>
        <p class="subtitle">Kelola stok, PO, dan invoice TRE dalam satu tempat.</p>

        <?= form_open('login/cekUser') ?>

        <?php $isInvalidUser = session()->getFlashdata('errIdUser') ? 'is-invalid' : ''; ?>
        <div class="field">
            <div class="input-wrap">
                <i class="fas fa-user icon-left"></i>
                <input type="text" name="userid" class="<?= $isInvalidUser ?>" placeholder="ID User" autofocus autocomplete="username">
            </div>
            <?php if (session()->getFlashdata('errIdUser')) : ?>
                <div class="field-error"><?= esc(session()->getFlashdata('errIdUser')) ?></div>
            <?php endif ?>
        </div>

        <?php $isInvalidPassword = session()->getFlashdata('errPassword') ? 'is-invalid' : ''; ?>
        <div class="field">
            <div class="input-wrap">
                <i class="fas fa-lock icon-left"></i>
                <input type="password" name="password" id="passwordInput" class="<?= $isInvalidPassword ?>" placeholder="Password" autocomplete="current-password">
                <button type="button" class="toggle-password" onclick="togglePassword()"><i class="fas fa-eye-slash" id="toggleIcon"></i></button>
            </div>
            <?php if (session()->getFlashdata('errPassword')) : ?>
                <div class="field-error"><?= esc(session()->getFlashdata('errPassword')) ?></div>
            <?php endif ?>
        </div>

        <button type="submit" class="btn-login">Masuk</button>

        <?= form_close() ?>

        <p class="login-footnote">&copy; <?= date('Y') ?> PT Trisentosa Raya Esolusi</p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('toggleIcon');
            const willShow = input.type === 'password';
            input.type = willShow ? 'text' : 'password';
            // Mata terbuka = password sedang terlihat, mata dicoret = sedang disembunyikan.
            icon.classList.toggle('fa-eye', willShow);
            icon.classList.toggle('fa-eye-slash', !willShow);
        }
    </script>
</body>

</html>
