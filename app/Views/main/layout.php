 <?php
 $manualPreviewKey = (string) service('request')->getGet('manual_preview');
 $manualPreviewMode = ($manualPreviewKey !== '');
 ?>
 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
     <title>Trisentosa Inventory</title>
     
     <link rel="icon" type="image/png" href="<?= base_url() ?>image/logo.png">
     <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
     <link rel="stylesheet" href="<?= base_url() ?>plugins/fontawesome-free/css/all.min.css">
     <link rel="stylesheet" href="<?= base_url() ?>dist/css/adminlte.min.css">
     <link rel="stylesheet" href="<?= base_url() ?>plugins/select2/css/select2.min.css">
     <link rel="stylesheet" href="<?= base_url() ?>plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
     <style>
         .nav-sidebar .nav-header.sidebar-section-toggle {
             align-items: center;
             box-sizing: border-box;
             cursor: pointer;
             display: flex;
             justify-content: space-between;
             gap: .65rem;
             overflow: hidden;
             padding-right: 2.5rem;
             position: relative;
             user-select: none;
             white-space: nowrap;
             width: auto !important;
         }

         .nav-sidebar .nav-header.sidebar-section-toggle .section-label {
             align-items: center;
             display: flex;
             flex: 1 1 auto;
             gap: .75rem;
             min-width: 0;
             max-width: 100%;
             overflow: hidden;
         }

         .nav-sidebar .nav-header.sidebar-section-toggle .section-icon {
             font-size: 1rem;
             width: 1.25rem;
         }

         .nav-sidebar .nav-header.sidebar-section-toggle:hover {
             color: rgba(30, 41, 59, .72);
         }

         .nav-sidebar .nav-item>.nav-link>.nav-icon {
             display: none;
         }

         .nav-sidebar .nav-item>.nav-link>p {
             margin-left: 0;
         }

         .nav-sidebar .nav-header.sidebar-section-toggle .section-chevron {
             align-items: center;
             color: rgba(30, 41, 59, .58);
             display: inline-flex !important;
             font-size: .75rem;
             height: 1.25rem;
             justify-content: center;
             margin-left: 0;
             opacity: 1 !important;
             pointer-events: none;
             position: absolute;
             right: .75rem;
             top: 50%;
             transform: translateY(-50%) rotate(0deg);
             transition: transform .18s ease;
             width: 1.25rem;
             z-index: 3;
         }

         .nav-sidebar .nav-header.sidebar-section-toggle::after {
             content: none;
             display: none !important;
         }

         .nav-sidebar .nav-header.sidebar-section-toggle:hover::after,
         .nav-sidebar .nav-header.sidebar-section-toggle.is-open::after {
             color: rgba(30, 41, 59, .58);
         }

         .nav-sidebar .nav-header.sidebar-section-toggle.is-open::after,
         .nav-sidebar .nav-header.sidebar-section-toggle.is-open .section-chevron {
             transform: translateY(-50%) rotate(90deg);
         }

         .main-sidebar .brand-link {
             align-items: center;
             display: flex;
             gap: .5rem;
         }

         .main-sidebar {
             display: flex;
             flex-direction: column;
             overflow-x: hidden !important;
             overflow-y: visible !important;
          }

         .main-sidebar .brand-link {
             flex: 0 0 auto;
         }

         .main-sidebar .brand-link .brand-image {
             flex: 0 0 auto;
             float: none;
             margin: 0 !important;
             max-height: 2.25rem;
             max-width: 5.75rem;
             object-fit: contain;
             width: 5.75rem;
         }

         .sidebar-menu-search {
             align-items: center;
             background: rgba(255, 255, 255, .65);
             border: 1px solid rgba(15, 23, 42, .1);
             border-radius: 999px;
             box-shadow: 0 1px 3px rgba(15, 23, 42, .05);
             display: flex;
             gap: .5rem;
             margin: .9rem .65rem .7rem;
             padding: .55rem .9rem;
             transition: background .15s ease, border-color .15s ease;
         }

         .sidebar-menu-search:focus-within {
             background: #fff;
             border-color: rgba(22, 134, 154, .45);
         }

         .sidebar-menu-search > i {
             color: rgba(23, 32, 51, .45);
             flex: 0 0 auto;
             font-size: .82rem;
         }

         .sidebar-menu-search input {
             background: transparent;
             border: 0;
             color: rgba(23, 32, 51, .92);
             flex: 1 1 auto;
             font-size: .85rem;
             min-width: 0;
             outline: 0;
             padding: 0;
         }

         .sidebar-menu-search input::placeholder {
             color: rgba(23, 32, 51, .4);
         }

         .sidebar-menu-search-clear {
             background: transparent;
             border: 0;
             color: rgba(23, 32, 51, .45);
             flex: 0 0 auto;
             font-size: .78rem;
             line-height: 1;
             padding: 0;
         }

         .sidebar-menu-search-clear:hover {
             color: rgba(23, 32, 51, .85);
         }

         .sidebar-menu-search-empty {
             color: rgba(23, 32, 51, .5);
             font-size: .82rem;
             margin: 0 .65rem .6rem;
             text-align: center;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .sidebar-menu-search,
         body.sidebar-collapse .main-sidebar:not(:hover) .sidebar-menu-search-empty {
             display: none !important;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .brand-link {
             display: flex !important;
             height: 3.35rem;
             justify-content: center;
             margin: .75rem 0 .45rem !important;
             min-height: 3.35rem;
             overflow: hidden;
             padding-left: 0;
             padding-right: 0;
             position: relative;
             width: 4.6rem !important;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) {
             overflow: hidden !important;
             scrollbar-width: none;
             -ms-overflow-style: none;
         }

         body.sidebar-collapse .main-sidebar:not(:hover)::-webkit-scrollbar {
             display: none;
             height: 0;
             width: 0;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .brand-link .brand-image {
             display: block;
             height: auto;
             left: 50%;
             margin: 0 !important;
             max-height: 1.35rem;
             max-width: 2.85rem;
             object-fit: contain;
             object-position: center center;
             position: absolute;
             top: 50%;
             transform: translate(-50%, -50%);
             width: 2.85rem;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .sidebar {
             margin-top: 0 !important;
             overflow: hidden !important;
             padding-top: .25rem;
             scrollbar-width: none;
             -ms-overflow-style: none;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .sidebar::-webkit-scrollbar {
             display: none;
             height: 0;
             width: 0;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .sidebar>.user-panel {
             display: block !important;
             height: 3.45rem;
             margin: 0 0 .65rem !important;
             overflow: visible !important;
             padding: 0 !important;
             position: relative;
             width: 100%;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .sidebar>.user-panel .image {
             float: none !important;
             left: 50%;
             margin: 0 !important;
             overflow: visible !important;
             padding: 0 !important;
             position: absolute;
             top: 50%;
             transform: translate(-50%, -50%);
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .sidebar>.user-panel img {
             display: block;
             margin: 0 !important;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .sidebar>nav.mt-2 {
             margin-top: .25rem !important;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar .nav-header.sidebar-section-toggle .section-chevron {
             display: none !important;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar .nav-header.sidebar-section-toggle::after {
             display: none;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar .nav-header.sidebar-section-toggle {
             display: flex !important;
             height: 2.5rem;
             justify-content: center;
             overflow: visible;
             padding: .65rem 0;
             text-indent: 0;
             width: 100%;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar .nav-header.sidebar-section-toggle .section-label {
             flex: 0 0 100%;
             gap: 0;
             justify-content: center;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar .nav-header.sidebar-section-toggle .section-label span {
             display: none;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar .nav-header.sidebar-section-toggle .section-icon {
             font-size: 1.15rem;
             margin: 0;
             text-align: center;
             width: auto;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar>.nav-item {
             display: none !important;
         }

         body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-header.sidebar-section-toggle {
             align-items: center;
             display: flex !important;
             justify-content: space-between;
             margin-right: .65rem;
             max-width: calc(100% - .65rem);
             min-height: 2.5rem;
             padding: .5rem 2.5rem .5rem .8rem;
             width: auto !important;
         }

         body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-header.sidebar-section-toggle .section-label {
             flex: 1 1 auto;
             gap: .75rem;
             justify-content: flex-start;
             min-width: 0;
         }

         body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-header.sidebar-section-toggle .section-label span {
             display: inline;
             overflow: hidden;
             text-overflow: ellipsis;
         }

         body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-header.sidebar-section-toggle .section-chevron {
             display: inline-flex !important;
             margin-left: 0;
             right: .75rem;
         }

         body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-header.sidebar-section-toggle::after {
             display: none;
             margin-left: auto;
         }

         body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-header.sidebar-section-toggle .section-icon {
             font-size: 1rem;
             width: 1.25rem;
         }

         body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-item>.nav-link {
             margin-left: 2.5rem;
             margin-right: .65rem;
             max-width: calc(100% - 3.15rem);
             width: auto !important;
         }

         body.sidebar-collapse .main-sidebar:hover .sidebar>.user-panel {
             align-items: center;
             display: flex !important;
             height: auto;
             margin-top: 1rem !important;
             padding-bottom: 1rem !important;
             position: static;
             width: auto;
         }

         body.sidebar-collapse .main-sidebar:hover .sidebar>.user-panel .image {
             float: left !important;
             left: auto;
             padding-left: .8rem !important;
             position: static;
             top: auto;
             transform: none;
         }

         .sidebar-edge-toggle {
             align-items: center;
             background: #fff;
             border: 1px solid rgba(0, 0, 0, .08);
             border-radius: 999px;
             box-shadow: 0 5px 16px rgba(0, 0, 0, .16);
             color: #4b5563;
             display: flex;
             height: 2rem;
             justify-content: center;
             opacity: 1;
             left: calc(250px - 1rem);
             position: fixed;
             top: 7.15rem;
             transition: background .15s ease, color .15s ease, left .18s ease, opacity .12s ease, transform .15s ease;
             width: 2rem;
             z-index: 1050;
         }

         body.sidebar-collapse .sidebar-edge-toggle {
             left: calc(4.6rem - 1rem);
         }

         .sidebar-edge-toggle:hover {
             background: #f8f9fa;
             color: #111827;
             text-decoration: none;
             transform: scale(1.04);
         }

         .sidebar-edge-toggle i {
             font-size: .85rem;
         }

         .content-wrapper {
             position: relative;
         }

         body.sidebar-collapse .main-sidebar:hover~.content-wrapper .sidebar-edge-toggle {
             opacity: 0;
             pointer-events: none;
         }

         /* Bootstrap popup: compact by default, wide only for real forms. */
         #appPopupModal .app-popup-dialog {
             max-width: 24rem !important;
             margin: 1.75rem auto;
         }

         #appPopupModal .app-popup-dialog.app-popup-dialog-wide {
             max-width: 42rem !important;
         }

         #appPopupModal .app-popup-content {
             border: 0;
             border-radius: .9rem;
             box-shadow: 0 1rem 2.5rem rgba(15, 23, 42, .22);
             overflow: hidden;
         }

         #appPopupModal .modal-header {
             align-items: center;
             border-bottom: 1px solid rgba(15, 23, 42, .08);
             min-height: 3.35rem;
             padding: .75rem 1rem;
         }

         #appPopupModal .modal-title {
             color: #1f2937;
             font-size: 1.05rem;
             font-weight: 600;
             line-height: 1.35;
         }

         #appPopupModal .modal-body {
             color: #4b5563;
             font-size: .94rem;
             line-height: 1.5;
             max-height: min(68vh, 34rem);
             overflow-y: auto;
             padding: 1rem;
         }

         #appPopupModal .modal-footer {
             border-top: 1px solid rgba(15, 23, 42, .08);
             gap: .5rem;
             justify-content: flex-end;
             padding: .7rem 1rem;
         }

         #appPopupModal .modal-footer .btn {
             border-radius: .4rem;
             font-size: .875rem;
             min-width: 5.25rem;
             padding: .4rem .85rem;
         }

         #appPopupModal .modal-footer .app-popup-cancel {
             background: #f3f4f6;
             border-color: #e5e7eb;
             color: #4b5563;
         }

         #appPopupModal .close {
             font-size: 1.35rem;
             font-weight: 400;
             margin: -.35rem -.35rem -.35rem auto;
             opacity: .55;
             padding: .35rem;
         }

         @media (max-width: 575.98px) {
             #appPopupModal .app-popup-dialog,
             #appPopupModal .app-popup-dialog.app-popup-dialog-wide {
                 margin: .75rem;
                 max-width: none !important;
             }
         }

         /* Mobile: sidebar jadi panel overlay, digeser pakai transform (bukan
            margin-left) biar nggak bentrok sama mekanisme AdminLTE bawaan. */
         @media (max-width: 767.98px) {
             .main-sidebar,
             .main-sidebar::before {
                 margin-left: 0 !important;
                 transform: translateX(-100%) !important;
                 transition: transform .3s ease-in-out !important;
                 width: 250px !important;
                 z-index: 1046;
             }

             body.sidebar-open .main-sidebar,
             body.sidebar-open .main-sidebar::before {
                 transform: translateX(0) !important;
             }

             .sidebar-edge-toggle,
             body.sidebar-collapse .sidebar-edge-toggle {
                 left: .75rem !important;
                 top: .75rem !important;
             }

             .sidebar-mobile-backdrop {
                 display: none;
                 position: fixed;
                 inset: 0;
                 background: rgba(0, 0, 0, .4);
                 z-index: 1045;
             }

             body.sidebar-open .sidebar-mobile-backdrop {
                 display: block;
             }

             /* .sidebar biasanya pakai "height: calc(100vh - 4.8rem)", nebak
                tinggi .brand-link itu selalu 4.8rem persis. Kalau di HP
                ternyata beda dikit aja, sisa .sidebar kepotong sama
                overflow-y:hidden punya .main-sidebar -- termasuk menu Logout
                di paling bawah, jadi ilang total & ga bisa discroll ke situ.
                Di HP, biarin flexbox yang ngitung sisa tingginya sendiri,
                nggak usah nebak-nebak angka. */
             .main-sidebar {
                 display: flex;
                 flex-direction: column;
             }

             .main-sidebar .brand-link {
                 flex: 0 0 auto;
             }

             .main-sidebar .sidebar {
                 flex: 1 1 auto;
                 height: auto;
                 min-height: 0;
             }
         }

         :root {
             --tre-red: #ef3e4a;
             --tre-red-dark: #c82734;
             --tre-charcoal: #20262d;
             --tre-charcoal-2: #2d343c;
             --tre-ink: #111827;
             --tre-muted: #6b7280;
             --tre-line: #e7edf3;
             --tre-soft: #eef4f7;
             --tre-card: #ffffff;
             --tre-blue: #0f7b9b;
             --tre-teal: #167d8f;
             --tre-shadow: 0 16px 36px rgba(31, 41, 55, .08);
         }

         body {
             background: var(--tre-soft);
             color: var(--tre-ink);
             font-size: 15px;
         }

         .wrapper {
             background:
                 radial-gradient(circle at top left, rgba(239, 62, 74, .07), transparent 30rem),
                 linear-gradient(135deg, #f6fafc 0%, #edf4f7 48%, #f9fbfc 100%);
         }

         .main-header.navbar {
             background: rgba(255, 255, 255, .86) !important;
             border-bottom: 1px solid rgba(226, 232, 240, .9);
             box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
             min-height: 4rem;
         }

         .main-header .nav-link {
             align-items: center;
             border-radius: 12px;
             color: #607080 !important;
             display: inline-flex;
             height: 2.5rem;
             justify-content: center;
             margin: 0 .25rem;
             min-width: 2.5rem;
             transition: background .18s ease, color .18s ease, transform .18s ease;
         }

         .main-header .nav-link:hover {
             background: #f1f5f9;
             color: var(--tre-red) !important;
             transform: translateY(-1px);
         }

         .navbar-user-chip {
             align-items: center;
             background: rgba(255, 255, 255, .82);
             border: 1px solid rgba(226, 232, 240, .95);
             border-radius: 999px;
             box-shadow: 0 8px 22px rgba(15, 23, 42, .07);
             color: #172033;
             display: inline-flex;
             gap: .65rem;
             min-height: 2.55rem;
             padding: .25rem .85rem .25rem .3rem;
             text-decoration: none;
         }

         .navbar-user-chip:hover {
             background: #fff;
             color: #172033;
             text-decoration: none;
         }

         .navbar-user-avatar {
             align-items: center;
             background: #e2e8f0;
             border: 2px solid rgba(255, 255, 255, .95);
             border-radius: 50%;
             box-shadow: 0 5px 13px rgba(15, 23, 42, .16);
             color: #94a3b8;
             display: inline-flex;
             font-size: 1.05rem;
             height: 2.05rem;
             justify-content: center;
             width: 2.05rem;
         }

         .navbar-user-name {
             font-weight: 800;
             line-height: 1;
             min-width: 0;
             max-width: min(16rem, 42vw);
             overflow: hidden;
             text-overflow: ellipsis;
             white-space: nowrap;
         }

         .main-header .navbar-nav {
             max-width: 100%;
             min-width: 0;
         }

         .main-header .navbar-user-chip {
             max-width: calc(100vw - 1.5rem);
             min-width: 0;
         }

         .main-sidebar {
             background:
                 linear-gradient(180deg, rgba(255, 255, 255, .54) 0%, rgba(241, 248, 251, .42) 100%) !important;
             -webkit-backdrop-filter: blur(24px) saturate(1.25);
             backdrop-filter: blur(24px) saturate(1.25);
             border-right: 1px solid rgba(255, 255, 255, .58);
             box-shadow: 14px 0 34px rgba(15, 23, 42, .075);
         }

         .main-sidebar.sidebar-dark-primary .brand-link,
         .main-sidebar .brand-link {
             background: transparent !important;
             border: 0 !important;
             box-shadow: none !important;
             margin: .75rem .65rem .45rem;
             min-height: 3.6rem;
             padding: .55rem .8rem;
         }

         .main-sidebar .brand-link .brand-text {
             color: rgba(23, 32, 51, .88) !important;
             flex: 1 1 auto;
             font-weight: 600 !important;
             letter-spacing: 0;
             line-height: 1;
             min-width: 0;
             white-space: nowrap;
         }

         .main-sidebar .brand-link .brand-image {
             opacity: 1 !important;
         }

         .main-sidebar .sidebar {
             display: flex;
             flex: 1 1 auto;
             flex-direction: column;
             height: auto;
             min-height: 0;
             overflow-x: hidden;
             padding: .35rem .65rem 1rem;
         }

         .main-sidebar .sidebar>nav.mt-2 {
             flex: 1 1 auto;
             min-height: 0;
             overflow-x: hidden;
             overflow-y: auto;
             padding-bottom: 1rem;
             scrollbar-color: rgba(15, 23, 42, .22) transparent;
             scrollbar-gutter: stable;
             scrollbar-width: thin;
             width: 100%;
         }

         .main-sidebar .sidebar>nav.mt-2::-webkit-scrollbar {
             width: 4px;
         }

         .main-sidebar .sidebar>nav.mt-2::-webkit-scrollbar-track {
             background: transparent;
         }

         .main-sidebar .sidebar>nav.mt-2::-webkit-scrollbar-thumb {
             background: rgba(15, 23, 42, .18);
             border-radius: 999px;
         }

         .main-sidebar .sidebar>nav.mt-2:hover::-webkit-scrollbar-thumb {
             background: rgba(15, 23, 42, .28);
         }

         .user-panel {
             border-bottom: 1px solid rgba(15, 23, 42, .08) !important;
             margin-bottom: 1rem !important;
         }

         .user-panel .image img {
             border: 2px solid rgba(255, 255, 255, .7);
             box-shadow: 0 7px 18px rgba(0, 0, 0, .24);
         }

         .user-panel .info a {
             color: #172033 !important;
             font-weight: 600;
         }

         .nav-sidebar .nav-header.sidebar-section-toggle {
             border-radius: 12px;
             color: rgba(30, 41, 59, .72);
             font-size: .77rem;
             font-weight: 700;
             letter-spacing: .02em;
             margin: .25rem 0;
             min-height: 2.55rem;
             padding: .55rem 2.5rem .55rem .8rem;
             transition: background .18s ease, color .18s ease;
             max-width: 100%;
         }

         .nav-sidebar {
             max-width: 100%;
             min-width: 0;
             overflow-x: hidden;
          }

         .nav-sidebar .nav-header.sidebar-section-toggle:hover,
         .nav-sidebar .nav-header.sidebar-section-toggle.is-open {
             background: rgba(15, 23, 42, .055);
             color: #172033;
         }

         .nav-sidebar .nav-header.sidebar-section-toggle .section-icon {
             filter: saturate(1.15);
         }

         .nav-sidebar .nav-item>.nav-link {
             border-radius: 12px;
             box-sizing: border-box;
             color: rgba(30, 41, 59, .68);
             margin: .18rem 0 .18rem 2.5rem;
             max-width: calc(100% - 2.5rem);
             min-height: 2.35rem;
             padding: .5rem .8rem;
             transition: background .18s ease, color .18s ease, transform .18s ease;
             width: auto !important;
         }

         .nav-sidebar>.nav-item {
             display: none;
         }

         .nav-sidebar .nav-item.sidebar-section-item {
             position: relative;
         }

         .nav-sidebar .nav-item.sidebar-section-item::before {
             background: rgba(15, 23, 42, .12);
             bottom: -.18rem;
             content: "";
             left: 1.25rem;
             position: absolute;
             top: -.18rem;
             width: 1px;
         }

         .nav-sidebar .nav-item.sidebar-section-item.section-first::before {
             top: .55rem;
         }

         .nav-sidebar .nav-item.sidebar-section-item.section-last::before {
             bottom: 1.15rem;
         }

         .nav-sidebar .sidebar-rail-indicator {
             background: #172033;
             border: 2px solid rgba(255, 255, 255, .9);
             border-radius: 999px;
             box-shadow: 0 0 0 4px rgba(232, 240, 246, .08);
             display: block;
             height: .55rem;
             left: .98rem;
             opacity: 0;
             pointer-events: none;
             position: absolute;
             top: 0;
             transform: translateY(-50%);
             transition: top .24s cubic-bezier(.22, 1, .36, 1), opacity .16s ease, transform .18s ease;
             width: .55rem;
             z-index: 6;
         }

         .nav-sidebar .sidebar-rail-indicator.is-visible {
             opacity: 1;
         }

         .nav-sidebar .sidebar-rail-indicator.is-moving {
             transform: translateY(-50%) scale(1.12);
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar .sidebar-rail-indicator {
             display: none;
         }

         .nav-sidebar .nav-item>.nav-link:hover {
             background: rgba(15, 23, 42, .05);
             color: rgba(30, 41, 59, .68) !important;
             transform: translateX(2px);
         }

         .nav-sidebar .nav-item>.nav-link:hover p,
         .nav-sidebar .nav-item>.nav-link:hover .text {
             color: rgba(30, 41, 59, .68) !important;
         }

         .main-sidebar.sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link:hover:not(.active),
         .main-sidebar.sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link:hover:not(.active) p,
         .main-sidebar.sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link:hover:not(.active) .text {
             color: rgba(30, 41, 59, .68) !important;
         }

         .nav-sidebar .nav-item>.nav-link.active {
             background: linear-gradient(135deg, var(--tre-red), var(--tre-red-dark)) !important;
             box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .08);
             color: #fff !important;
         }

         .nav-sidebar .nav-item>.nav-link p {
             font-weight: 600;
         }

         .sidebar-bottom-menu {
             border-top: 1px solid rgba(15, 23, 42, .08);
             flex: 0 0 auto;
             margin-top: auto;
             padding-top: .85rem;
         }

         .sidebar-bottom-list {
             margin: 0;
         }

         .sidebar-bottom-list>.nav-item {
             display: block;
             width: 100%;
         }

         .sidebar-bottom-list>.nav-item>.nav-link {
             align-items: center;
             border-radius: 12px;
             box-sizing: border-box;
             color: rgba(30, 41, 59, .68);
             display: flex;
             gap: .65rem;
             min-height: 2.45rem;
             padding: .55rem .8rem;
             transition: background .18s ease, color .18s ease, transform .18s ease;
             width: 100%;
         }

         .sidebar-bottom-list>.nav-item>.nav-link>.nav-icon {
             color: var(--tre-red) !important;
             display: inline-flex;
             justify-content: center;
             width: 1.25rem;
         }

         .sidebar-bottom-list>.nav-item>.nav-link p {
             color: var(--tre-red);
             font-weight: 700;
             margin: 0;
         }

         .sidebar-bottom-list>.nav-item>.nav-link:hover {
             background: rgba(239, 62, 74, .08);
             color: var(--tre-red) !important;
             transform: translateX(2px);
         }

         .sidebar-bottom-list>.sidebar-manual-item>.nav-link>.nav-icon,
         .sidebar-bottom-list>.sidebar-manual-item>.nav-link p {
             color: #12879a !important;
         }

         .sidebar-bottom-list>.sidebar-manual-item>.nav-link:hover,
         .sidebar-bottom-list>.sidebar-manual-item>.nav-link.active {
             background: rgba(18, 135, 154, .1);
             color: #12879a !important;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .sidebar-bottom-menu {
             border-top: 0;
             padding-top: .25rem;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .sidebar-bottom-list>.nav-item>.nav-link {
             justify-content: center;
             padding-left: 0;
             padding-right: 0;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .sidebar-bottom-list>.nav-item>.nav-link>.nav-icon {
             font-size: 1.15rem;
             width: auto;
         }

         body.sidebar-collapse .main-sidebar:not(:hover) .sidebar-bottom-list>.nav-item>.nav-link p {
             display: none;
         }

         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-header.sidebar-section-toggle:hover,
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-header.sidebar-section-toggle:hover .section-label,
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-header.sidebar-section-toggle:hover .section-label span,
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-header.sidebar-section-toggle:hover .section-chevron {
             color: rgba(30, 41, 59, .72) !important;
         }

         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-item > .nav-link:hover:not(.active),
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-item > .nav-link:hover:not(.active) p,
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-item > .nav-link:hover:not(.active) .text {
             color: rgba(30, 41, 59, .68) !important;
         }

         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-item > .nav-link:not(.active):hover,
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-item > .nav-link:not(.active):focus,
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-item > .nav-link:not(.active):hover > p,
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-item > .nav-link:not(.active):focus > p,
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-item > .nav-link:not(.active):hover > .text,
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-item > .nav-link:not(.active):focus > .text,
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-item > .nav-link:not(.active):hover span,
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-item > .nav-link:not(.active):focus span {
             color: rgba(30, 41, 59, .68) !important;
         }

         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-header.sidebar-section-toggle:hover,
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-header.sidebar-section-toggle:hover *,
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-header.sidebar-section-toggle:focus,
         .main-sidebar.sidebar-dark-primary .nav-sidebar .nav-header.sidebar-section-toggle:focus * {
             color: rgba(30, 41, 59, .72) !important;
         }

         [class*="sidebar-dark-"] .nav-sidebar > .nav-item:hover > .nav-link:not(.active) {
             background-color: rgba(15, 23, 42, .05) !important;
             color: rgba(30, 41, 59, .68) !important;
         }

         [class*="sidebar-dark-"] .nav-sidebar > .nav-item:hover > .nav-link:not(.active) p,
         [class*="sidebar-dark-"] .nav-sidebar > .nav-item:hover > .nav-link:not(.active) .text,
         [class*="sidebar-dark-"] .nav-sidebar > .nav-item:hover > .nav-link:not(.active) span {
             color: rgba(30, 41, 59, .68) !important;
         }

         .content-wrapper {
             background: transparent !important;
             padding: 1.1rem 1.15rem 1.5rem;
         }

         .content-header {
             padding: .6rem .35rem 1rem;
         }

         .content-header h1 {
             color: #101827;
             font-size: 1.95rem;
             font-weight: 800;
             letter-spacing: 0;
         }

         .content>.card {
             background: rgba(255, 255, 255, .76);
             border: 1px solid rgba(231, 237, 243, .95);
             border-radius: 22px;
             box-shadow: var(--tre-shadow);
             overflow: visible;
         }

         .content>.card>.card-header {
             background: transparent;
             border-bottom: 1px solid rgba(231, 237, 243, .9);
             border-radius: 22px 22px 0 0;
             min-height: 3.25rem;
             padding: .95rem 1.15rem;
         }

         .content>.card>.card-header:empty,
         .content>.card>.card-header .card-title:empty {
             display: none;
         }

         .content>.card>.card-body {
             padding: 1.15rem;
         }

         .content>.card>.card-footer:empty {
             display: none;
         }

         .card:not(.payroll-style-card):not(.dashboard-panel) {
             border: 1px solid var(--tre-line);
             border-radius: 16px;
             box-shadow: 0 10px 26px rgba(15, 23, 42, .055);
         }

         .card-header {
             background: #fff;
             border-bottom-color: var(--tre-line);
         }

         .card-title {
             color: var(--tre-ink);
             font-weight: 800;
         }

         .table {
             color: #172033;
         }

         .table thead th {
             background: #f7fafc;
             border-bottom: 1px solid var(--tre-line) !important;
             color: #243041;
             font-size: .86rem;
             font-weight: 800;
             vertical-align: middle;
         }

         .table td {
             border-color: var(--tre-line);
             vertical-align: middle;
         }

         .table-striped tbody tr:nth-of-type(odd) {
             background-color: rgba(248, 250, 252, .78);
         }

         .form-control,
         .custom-select,
         .select2-container--default .select2-selection--single,
         .select2-container--default .select2-selection--multiple {
             border-color: #d8e1ea;
             border-radius: 12px;
             min-height: 2.65rem;
         }

         .form-control:focus,
         .custom-select:focus,
         .select2-container--default.select2-container--focus .select2-selection--multiple,
         .select2-container--default .select2-selection--single:focus {
             border-color: rgba(239, 62, 74, .55);
             box-shadow: 0 0 0 .2rem rgba(239, 62, 74, .12);
         }

         label {
             color: #172033;
             font-weight: 700;
         }

         .btn {
             border-radius: 12px;
             font-weight: 700;
             letter-spacing: 0;
         }

         .btn-primary {
             background: #147a91;
             border-color: #147a91;
         }

         .btn-primary:hover {
             background: #0f6578;
             border-color: #0f6578;
         }

         .btn-danger {
             background: var(--tre-red);
             border-color: var(--tre-red);
         }

         .btn-danger:hover {
             background: var(--tre-red-dark);
             border-color: var(--tre-red-dark);
         }

         .badge,
         .label {
             border-radius: 999px;
             font-weight: 800;
             padding: .35rem .55rem;
         }

         .dataTables_wrapper .dataTables_filter input,
         .dataTables_wrapper .dataTables_length select {
             border: 1px solid #d8e1ea;
             border-radius: 10px;
             min-height: 2.35rem;
         }

         .main-footer {
             background: rgba(255, 255, 255, .86);
             border-top: 1px solid var(--tre-line);
             color: #718096;
         }

         .main-footer a {
             color: var(--tre-red);
             font-weight: 800;
         }

         /* Responsive foundation: semua halaman harus bisa mengecil tanpa
            membuat tabel/form mendorong viewport ke arah horizontal. */
         html,
         body {
             max-width: 100%;
             overflow-x: hidden;
         }

         .content-wrapper,
         .content-wrapper > .content,
         .content-wrapper > .content-header,
         .content-wrapper .card,
         .content-wrapper .card-body {
             min-width: 0;
         }

         .content-wrapper {
             max-width: 100%;
             overflow-x: hidden;
         }

         .content > .card,
         .content > .card > .card-body {
             max-width: 100%;
         }

         .content-wrapper > .content .card-body {
             overflow-x: auto;
         }

         .table-responsive,
         .dataTables_wrapper {
             max-width: 100%;
             overflow-x: auto;
             -webkit-overflow-scrolling: touch;
         }

         .table-responsive > .table {
             margin-bottom: 0;
         }

         .table-responsive > .table:not(.dataTable) {
             min-width: 36rem;
         }

         .dataTables_wrapper .row {
             margin-left: 0;
             margin-right: 0;
         }

         .dataTables_wrapper .dataTables_scroll,
         .dataTables_wrapper .dataTables_scrollHead,
         .dataTables_wrapper .dataTables_scrollBody {
             max-width: 100%;
         }

         .table {
             max-width: 100%;
         }

         .table th,
         .table td {
             overflow-wrap: anywhere;
             word-break: normal;
         }

         .table td .btn,
         .table td .btn-group,
         .table td .btn-toolbar {
             white-space: nowrap;
         }

         img,
         video,
         canvas,
         iframe {
             max-width: 100%;
         }

         .form-row,
         .form-group,
         .input-group,
         .custom-file,
         .select2-container {
             max-width: 100%;
         }

         .select2-container {
             width: 100% !important;
         }

         .modal-dialog {
             width: auto;
             max-width: calc(100vw - 1rem);
             margin: .5rem auto;
         }

         .modal-content,
         .modal-body {
             max-width: 100%;
         }

         .modal-body {
             overflow-x: auto;
             -webkit-overflow-scrolling: touch;
         }

         .modal-body .table-responsive {
             margin-left: -.25rem;
             margin-right: -.25rem;
         }

         @media (max-width: 991.98px) {
             .content-wrapper {
                 padding: .9rem .75rem 1.15rem;
             }

             .content-header {
                 padding: .45rem .15rem .75rem;
             }

             .content-header h1 {
                 font-size: 1.55rem;
                 line-height: 1.2;
                 overflow-wrap: anywhere;
             }

             .content-header .row.mb-2 > [class*="col-"] {
                 width: 100%;
                 max-width: 100%;
                 flex: 0 0 100%;
             }

             .content-header .breadcrumb {
                 float: none !important;
                 justify-content: flex-start;
                 margin-top: .45rem;
                 overflow-x: auto;
                 white-space: nowrap;
             }

             .content > .card {
                 border-radius: 16px;
             }

             .content > .card > .card-header,
             .content > .card > .card-body {
                 padding: .85rem;
             }

             .card-header .card-title {
                 display: block;
                 max-width: 100%;
                 overflow-wrap: anywhere;
             }

             .dataTables_wrapper .dataTables_length,
             .dataTables_wrapper .dataTables_filter,
             .dataTables_wrapper .dataTables_info,
             .dataTables_wrapper .dataTables_paginate {
                 float: none !important;
                 text-align: left !important;
                 width: 100%;
                 max-width: 100%;
                 margin-bottom: .5rem;
             }

             .dataTables_wrapper .dataTables_filter label,
             .dataTables_wrapper .dataTables_length label {
                 display: flex;
                 align-items: center;
                 gap: .4rem;
                 flex-wrap: wrap;
                 max-width: 100%;
             }

             .dataTables_wrapper .dataTables_filter input {
                 width: min(100%, 18rem) !important;
                 margin-left: 0 !important;
             }

             .dataTables_wrapper .dataTables_paginate .pagination {
                 flex-wrap: wrap;
                 gap: .15rem;
             }

             .form-row {
                 margin-left: -.35rem;
                 margin-right: -.35rem;
             }

             .content-wrapper > .content .form-row > [class*="col-"],
             .content-wrapper > .content .row > [class*="col-"] {
                 padding-left: .35rem;
                 padding-right: .35rem;
             }
         }

         @media (max-width: 575.98px) {
             .main-header.navbar {
                 min-height: 3.35rem;
                 padding-left: .45rem;
                 padding-right: .45rem;
             }

             .main-header .navbar-user-chip {
                 gap: .35rem;
                 padding-right: .5rem;
             }

             .navbar-user-name {
                 max-width: 10rem;
             }

             .content-wrapper {
                 padding: .65rem .45rem .9rem;
             }

             .content-header h1 {
                 font-size: 1.3rem;
             }

             .content > .card > .card-header,
             .content > .card > .card-body {
                 padding: .65rem;
             }

             .table th,
             .table td {
                 font-size: .78rem;
                 padding: .45rem .4rem;
             }

             .table-responsive > .table:not(.dataTable) {
                 min-width: 32rem;
             }

             .btn {
                 max-width: 100%;
                 white-space: normal;
             }

             .btn-group,
             .btn-toolbar {
                 flex-wrap: wrap;
                 gap: .25rem;
             }

             .modal-dialog {
                 max-width: calc(100vw - .5rem);
                 margin: .25rem auto;
             }

         .modal-body {
             padding: .65rem;
         }

         #appPopupModal .app-popup-dialog {
             max-width: 24rem;
             margin: 1.75rem auto;
         }

         #appPopupModal .app-popup-dialog.app-popup-dialog-wide {
             max-width: 42rem;
         }

         #appPopupModal .app-popup-content {
             border: 0;
             border-radius: .9rem;
             box-shadow: 0 1rem 2.5rem rgba(15, 23, 42, .22);
             overflow: hidden;
         }

         #appPopupModal .modal-header {
             align-items: center;
             border-bottom: 1px solid rgba(15, 23, 42, .08);
             min-height: 3.35rem;
             padding: .75rem 1rem;
         }

         #appPopupModal .modal-title {
             color: #1f2937;
             font-size: 1.05rem;
             font-weight: 600;
             line-height: 1.35;
         }

         #appPopupModal .modal-title i {
             font-size: 1.1rem;
         }

         #appPopupModal .modal-body {
             color: #4b5563;
             font-size: .94rem;
             line-height: 1.5;
             max-height: min(68vh, 34rem);
             overflow-y: auto;
             padding: 1rem;
         }

         #appPopupModal .modal-footer {
             border-top: 1px solid rgba(15, 23, 42, .08);
             gap: .5rem;
             justify-content: flex-end;
             padding: .7rem 1rem;
         }

         #appPopupModal .modal-footer .btn {
             border-radius: .4rem;
             font-size: .875rem;
             min-width: 5.25rem;
             padding: .4rem .85rem;
         }

         #appPopupModal .modal-footer .app-popup-cancel {
             background: #f3f4f6;
             border-color: #e5e7eb;
             color: #4b5563;
         }

         #appPopupModal .modal-footer .app-popup-cancel:hover {
             background: #e5e7eb;
             color: #1f2937;
         }

         #appPopupModal .close {
             font-size: 1.35rem;
             font-weight: 400;
             margin: -.35rem -.35rem -.35rem auto;
             opacity: .55;
             padding: .35rem;
         }

         #appPopupModal .close:hover {
             opacity: .85;
         }

         #appPopupModal .app-popup-loading {
             color: #16869a;
             font-size: .9rem;
         }

         #appPopupModal .app-popup-validation {
             font-size: .86rem;
         }

         @media (max-width: 575.98px) {
             #appPopupModal .app-popup-dialog,
             #appPopupModal .app-popup-dialog.app-popup-dialog-wide {
                 margin: .75rem;
                 max-width: none;
             }
         }

         }


         body.manual-preview-mode {
             background: var(--tre-soft) !important;
             overflow: auto !important;
         }

         body.manual-preview-mode .main-header,
         body.manual-preview-mode .main-sidebar,
         body.manual-preview-mode .main-footer,
         body.manual-preview-mode .sidebar-edge-toggle,
         body.manual-preview-mode .sidebar-mobile-backdrop {
             display: none !important;
         }

         body.manual-preview-mode .wrapper,
         body.manual-preview-mode .content-wrapper {
             margin-left: 0 !important;
             min-height: 100vh !important;
             padding-top: 0 !important;
             background: transparent !important;
         }

         body.manual-preview-mode .content-header {
             padding: 1rem 1.15rem 0 !important;
         }

         body.manual-preview-mode .content-header .row.mb-2 {
             margin-bottom: .75rem !important;
         }

         body.manual-preview-mode .content > .card {
             margin: 0 1.15rem 1.15rem !important;
         }

         body.manual-preview-mode [data-manual-order-help="true"] {
             cursor: help !important;
         }

         .manual-order-tooltip {
             background: #111827;
             border-radius: 12px;
             box-shadow: 0 16px 34px rgba(15, 23, 42, .24);
             color: #fff;
             font-size: .82rem;
             font-weight: 700;
             line-height: 1.45;
             max-width: 300px;
             opacity: 0;
             padding: .72rem .85rem;
             pointer-events: none;
             position: fixed;
             transform: translateY(8px);
             transition: opacity .16s ease, transform .16s ease;
             z-index: 5000;
         }

         .manual-order-tooltip.is-visible {
             opacity: 1;
             transform: translateY(0);
         }

         .tre-inline-combobox {
             position: relative;
         }

         .tre-inline-combobox.tre-inline-combobox-solo>.form-control {
             border-bottom-right-radius: 12px !important;
             border-top-right-radius: 12px !important;
          }

         .tre-inline-combobox-menu {
             background: #fff;
             border: 1px solid #b9d7ff;
             box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
             display: none;
             left: 0;
             max-height: 14rem;
             overflow-y: auto;
             position: absolute;
             right: 0;
             top: calc(100% - 1px);
             z-index: 1060;
         }

         .tre-inline-combobox.is-open .tre-inline-combobox-menu {
             display: block;
         }

         .tre-inline-combobox-option {
             color: #172033;
             cursor: pointer;
             padding: .62rem .9rem;
         }

         .tre-inline-combobox-option:hover,
         .tre-inline-combobox-option.is-active {
             background: #2d86f7;
             color: #fff;
         }

         .tre-inline-combobox-empty {
             color: #718096;
             padding: .62rem .9rem;
         }
     </style>
     <meta name="csrf-token" content="<?= csrf_hash() ?>">
     <meta name="csrf-token-name" content="<?= csrf_token() ?>">
     <meta name="csrf-header" content="<?= config('Security')->headerName ?>">
     <script src="<?= base_url() ?>plugins/jquery/jquery.min.js"></script>
     <script>
         (function($) {
             let currentCsrfToken = document.querySelector('meta[name="csrf-token"]').content;
             const csrfTokenName = document.querySelector('meta[name="csrf-token-name"]').content;
             const csrfHeader = document.querySelector('meta[name="csrf-header"]').content;

             function syncCsrfToken(xhr) {
                 if (!xhr || typeof xhr.getResponseHeader !== 'function') {
                     return;
                 }

                 const newCsrfToken = xhr.getResponseHeader(csrfHeader);
                 if (newCsrfToken) {
                     currentCsrfToken = newCsrfToken;
                     document.querySelector('meta[name="csrf-token"]').content = newCsrfToken;
                 }
             }

             $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
                 const method = (options.type || options.method || 'GET').toUpperCase();

                 // Callback success dapat langsung menjalankan AJAX berikutnya
                 // (contoh: simpan item lalu muat ulang draft). Sinkronkan token
                 // sebelum callback asli agar request lanjutan tidak memakai token lama.
                 if (typeof options.success === 'function') {
                     const originalSuccess = options.success;
                     options.success = function(data, textStatus, xhr) {
                         syncCsrfToken(xhr);
                         return originalSuccess.apply(this, arguments);
                     };
                 } else {
                     jqXHR.done(function(data, textStatus, xhr) {
                         syncCsrfToken(xhr);
                     });
                 }

                 if (!options.crossDomain && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
                     if (options.data instanceof FormData) {
                         options.data.set(csrfTokenName, currentCsrfToken);
                     } else if (typeof options.data === 'string') {
                         const contentType = String(options.contentType || '').toLowerCase();

                         if (contentType.includes('application/json')) {
                             try {
                                 const jsonData = JSON.parse(options.data || '{}');
                                 jsonData[csrfTokenName] = currentCsrfToken;
                                 options.data = JSON.stringify(jsonData);
                             } catch (error) {
                                 // Biarkan body asli jika isinya bukan JSON yang valid.
                             }
                         } else {
                             const requestData = new URLSearchParams(options.data);
                             requestData.set(csrfTokenName, currentCsrfToken);
                             options.data = requestData.toString();
                         }
                     } else if (options.data && typeof options.data === 'object') {
                         options.data[csrfTokenName] = currentCsrfToken;
                     }

                     jqXHR.setRequestHeader(csrfHeader, currentCsrfToken);
                 }
             });

             $(document).ajaxComplete(function(event, xhr) {
                 syncCsrfToken(xhr);
             });
         })(jQuery);
     </script>
     <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

 </head>

 <body class="hold-transition layout-fixed layout-navbar-fixed layout-footer-fixed sidebar-mini<?= $manualPreviewMode ? ' manual-preview-mode' : '' ?>">
     <script>
         (function() {
             try {
                 // "sidebar-collapse" (mode mini icon-rail) cuma konsep desktop.
                 // Jangan dipakai di layar sempit (HP), soalnya CSS mobile cuma
                 // ngerti sidebar-open/tertutup, bukan mode mini.
                 if (window.innerWidth >= 768 && localStorage.getItem('tre.sidebar.collapsed') === '1') {
                     document.body.classList.add('sidebar-collapse');
                     document.body.classList.remove('sidebar-open');
                 }
             } catch (error) {
                 // Abaikan kalau browser menolak akses localStorage.
             }
         })();
     </script>
     <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="navbar-user-chip" href="<?= site_url('utility/gantipassword') ?>" title="Profil pengguna">
                        <span class="navbar-user-avatar"><i class="fas fa-user"></i></span>
                        <span class="navbar-user-name"><?= esc(session()->namauser ?? '') ?></span>
                    </a>
                </li>
            </ul>
         </nav>

         <aside class="main-sidebar sidebar-dark-primary elevation-4">
             <a href="<?= base_url('dashboard/data') ?>" class="brand-link">
                 <img src="<?= base_url() ?>dist/img/logo-pt-tre.png" alt="TRE Logo" class="brand-image">
                 <span class="brand-text font-weight-light">Inventory</span>
             </a>

            <div class="sidebar">
                <div class="sidebar-menu-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="sidebarMenuSearch" placeholder="Cari menu..." autocomplete="off" aria-label="Cari menu">
                    <button type="button" class="sidebar-menu-search-clear" id="sidebarMenuSearchClear" title="Hapus pencarian" style="display:none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="sidebar-menu-search-empty" id="sidebarMenuSearchEmpty" style="display:none;">Menu tidak ditemukan.</p>
                <nav class="mt-2">
                     <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                         <?php if (\App\Libraries\AccessControl::can('dashboard.view')) : ?>
                             <li class="nav-header">DASHBOARD</li>
                             <li class="nav-item">
                                 <a href="<?= site_url('dashboard/data') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'dashboard') ? 'active' : '' ?>">
                                     <i class="nav-icon fas fa-home text-primary"></i>
                                     <p class="text">Dashboard</p>
                                 </a>
                             </li>
                         <?php endif ?>
                         <?php if (\App\Libraries\AccessControl::isDynamicEnabled()) : ?>
                             <?php foreach (\App\Libraries\AccessControl::menuSections() as $section) : ?>
                                 <li class="nav-header"><?= esc($section['label']) ?></li>
                                 <?php foreach ($section['features'] as $feature) : ?>
                                     <li class="nav-item">
                                         <a href="<?= site_url($feature['url']) ?>" class="nav-link <?= \App\Libraries\AccessControl::activeMenuClass($feature) ?>">
                                             <i class="nav-icon <?= esc($feature['icon']) ?>"></i>
                                             <p class="text"><?= esc($feature['label']) ?></p>
                                         </a>
                                     </li>
                                 <?php endforeach ?>
                             <?php endforeach ?>
                         <?php else : ?>
                         <?php if (in_array(session()->idlevel, [1, 4, 5])) :  ?>
                             <li class="nav-header">MASTER</li>
                             <li class="nav-item">
                                 <a href="<?= site_url('kategori/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'kategori') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-tasks text-primary"></i>
                                     <p class="text">Kategori</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('satuan/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'satuan') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-box text-warning"></i>
                                     <p class="text">Satuan</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('material/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'material') ? 'active' : '' ?>">
                                     <i class="nav-icon fa fa-truck-loading text-success"></i>
                                     <p class="text">Material</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('barang/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'barang') ? 'active' : '' ?>">
                                     <i class="nav-icon fa fa-truck-loading text-danger"></i>
                                     <p class="text">Produk</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('pelanggan/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'pelanggan') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-users text-success"></i>
                                     <p class="text">Pelanggan</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('supplier/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'supplier') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-users text-success"></i>
                                     <p class="text">Supplier</p>
                                 </a>
                             </li>
                             <li class="nav-header">TRANSAKSI PRODUK</li>
                             <li class="nav-item">
                                 <a href="<?= site_url('barangmasuk/data') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'barangmasuk') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-arrow-circle-down text-success"></i>
                                     <p class="text">Produk Masuk</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('barangkeluar/data') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'barangkeluar') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-history text-warning"></i>
                                     <p class="text">Pengiriman</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('permintaanBarangKirim/datakirim') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'permintaanBarangKirim' || current_url(true)->getSegment(1) == 'permintaanBarang') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-box text-primary"></i>
                                     <p class="text">Antar Gudang</p>
                                 </a>
                             </li>
                             <li class="nav-header">TRANSAKSI ORDER</li>
                             <li class="nav-item">
                                 <a href="<?= site_url('po/data') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'po') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-arrow-circle-down text-info"></i>
                                     <p class="text">PO Masuk</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('poKeluar/data') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'poKeluar') ? 'active' : '' ?>">
                                     <i class="nav-icon fa fa-arrow-circle-up text-danger"></i>
                                     <p class="text">PO Keluar</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('outstand/data') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'outstand') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-ban text-warning"></i>
                                     <p class="text">Outstanding</p>
                                 </a>
                             </li>
                             <!--
                                 Fallback menu statis ini (dipakai saat RBAC dinamis belum
                                 aktif) sengaja tidak menyertakan menu "Keuangan" (Invoice
                                 Hub -- gabungan Invoice Out/Invoice In/Reporting, data margin/
                                 harga modal sensitif). Saat RBAC dinamis aktif, menu "Keuangan"
                                 sudah muncul di section TRANSAKSI ORDER lewat
                                 AccessControl::sections() (feature key order.invoice_hub) dan
                                 hanya tampil untuk user yang diberi izin "order.invoice_hub.view"
                                 lewat Hak Akses User.
                             -->
                             <li class="nav-header">TRANSAKSI MATERIAL</li>
                             <li class="nav-item">
                                 <a href="<?= site_url('stokmaterial/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'stokmaterial') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-box text-primary"></i>
                                     <p class="text">Stok Material</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('kebutuhanmaterial/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'kebutuhanmaterial') ? 'active' : '' ?>">
                                     <i class="nav-icon fas fa-calculator text-info"></i>
                                     <p class="text">Kebutuhan Material</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('materialmasuk/data') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'materialmasuk') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-arrow-circle-down text-success"></i>
                                     <p class="text">Material Masuk</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('materialkeluar/data') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'materialkeluar') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-arrow-circle-up text-warning"></i>
                                     <p class="text">Pemakaian Material</p>
                                 </a>
                             </li>
                             <?php /* Menu "Data Raw Produk" disembunyikan dari sidebar, route/controller tetap ada
                             if (in_array(session()->idlevel, [1, 4, 5])) :  ?>
                                 <li class="nav-item">
                                     <a href="<?= site_url('ngdata/data') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'ngdata') ? 'active' : '' ?>">
                                         <i class=" nav-icon fa fa-truck-loading text-danger"></i>
                                         <p class="text">Data Raw Produk</p>
                                     </a>
                                 </li>
                             <?php endif */ ?>
                             <?php if (session()->idlevel == 5) : ?>
                                 <li class="nav-header">PACKAGING</li>
                                 <li class="nav-item">
                                     <a href="<?= site_url('packaging/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'packaging') ? 'active' : '' ?>">
                                         <i class=" nav-icon fa fa-box-open text-success"></i>
                                         <p class="text">Packaging</p>
                                     </a>
                                 </li>
                             <?php endif ?>
                             <?php if (session()->idlevel == 5) : ?>
                                 <li class="nav-header">UTILITY</li>
                                 <li class="nav-item">
                                     <a href="<?= site_url('users/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'users') ? 'active' : '' ?>">
                                         <i class=" nav-icon fa fa-user text-warning"></i>
                                         <p class="text">Management User</p>
                                     </a>
                                 </li>
                                 <?php /*
                                 Menu Laporan dinonaktifkan untuk semua user.
                                 <li class="nav-item">
                                     <a href="<?= site_url('laporan/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'laporan') ? 'active' : '' ?>">
                                         <i class=" nav-icon fa fa-file text-default"></i>
                                         <p class="text">Laporan</p>
                                     </a>
                                 </li>
                                 */ ?>
                                 <li class="nav-item">
                                     <a href="<?= site_url('utility/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'utility') ? 'active' : '' ?>">
                                         <i class=" nav-icon fa fa-database text-primary"></i>
                                         <p class="text">Backup DB</p>
                                     </a>
                                 </li>
                                 <li class="nav-item">
                                     <a href="<?= site_url('utility/gantipassword') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'utility' && current_url(true)->getSegment(2) == 'gantipassword') ? 'active' : '' ?>">
                                         <i class=" nav-icon fa fa-lock text-white"></i>
                                         <p class="text">Ganti Password</p>
                                     </a>
                                 </li>
                                 <li class="nav-item">
                                     <a href="<?= site_url('login/keluar') ?>" class="nav-link">
                                         <i class="nav-icon fa fa-sign-out-alt text-success"></i>
                                         <p class="text">Logout</p>
                                     </a>
                                 </li>
                             <?php endif ?>
                             <?php if (in_array(session()->idlevel, [1, 4])) :  ?>
                                 <li class="nav-header">UTILITY</li>
                                 <li class="nav-item">
                                     <a href="<?= site_url('users/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'users') ? 'active' : '' ?>">
                                         <i class=" nav-icon fa fa-users text-warning"></i>
                                         <p class="text">Management User</p>
                                     </a>
                                 </li>
                                 <li class="nav-item">
                                     <a href="<?= site_url('utility/gantipassword') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'utility' && current_url(true)->getSegment(2) == 'gantipassword') ? 'active' : '' ?>">
                                         <i class=" nav-icon fa fa-lock text-white"></i>
                                         <p class="text">Ganti Password</p>
                                     </a>
                                 </li>
                                 <li class="nav-item">
                                     <a href="<?= site_url('login/keluar') ?>" class="nav-link">
                                         <i class="nav-icon fa fa-sign-out-alt text-success"></i>
                                         <p class="text">Logout</p>
                                     </a>
                                 </li>
                             <?php endif ?>
                         <?php endif ?>

                         <?php if (session()->idlevel == 2) : ?>
                             <li class="nav-header">TRANSAKSI PRODUK</li>
                             <li class="nav-item">
                                 <a href="<?= site_url('barangmasuk/data') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'barangmasuk') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-arrow-circle-down text-success"></i>
                                     <p class="text">Produk Masuk</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('barangkeluar/data') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'barangkeluar') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-history text-warning"></i>
                                     <p class="text">Pengiriman</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('permintaanBarangKirim/datakirim') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'permintaanBarangKirim' || current_url(true)->getSegment(1) == 'permintaanBarang') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-box text-primary"></i>
                                     <p class="text">Antar Gudang</p>
                                 </a>
                             </li>
                             <li class="nav-header">TRANSAKSI MATERIAL</li>
                             <li class="nav-item">
                                 <a href="<?= site_url('stokmaterial/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'stokmaterial') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-box text-primary"></i>
                                     <p class="text">Stok Material</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('kebutuhanmaterial/index') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'kebutuhanmaterial') ? 'active' : '' ?>">
                                     <i class="nav-icon fas fa-calculator text-info"></i>
                                     <p class="text">Kebutuhan Material</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('materialmasuk/data') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'materialmasuk') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-arrow-circle-down text-success"></i>
                                     <p class="text">Material Masuk</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('materialkeluar/data') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'materialkeluar') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-arrow-circle-up text-warning"></i>
                                     <p class="text">Pemakaian Material</p>
                                 </a>
                             </li>
                             <li class="nav-header">UTILITY</li>
                             <li class="nav-item">
                                 <a href="<?= site_url('utility/gantipassword') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'utility' && current_url(true)->getSegment(2) == 'gantipassword') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-lock text-white"></i>
                                     <p class="text">Ganti Password</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('login/keluar') ?>" class="nav-link">
                                     <i class="nav-icon fa fa-sign-out-alt text-success"></i>
                                     <p class="text">Logout</p>
                                 </a>
                             </li>
                         <?php endif ?>

                         <?php if (session()->idlevel == 3) : ?>
                             <li class="nav-header">TRANSAKSI PRODUK</li>
                             <li class="nav-item">
                                 <a href="<?= site_url('barangkeluar/data') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'barangkeluar') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-history text-warning"></i>
                                     <p class="text">Pengiriman</p>
                                 </a>
                             </li>
                             <li class="nav-header">UTILITY</li>
                             <li class="nav-item">
                                 <a href="<?= site_url('utility/gantipassword') ?>" class="nav-link <?= (current_url(true)->getSegment(1) == 'utility' && current_url(true)->getSegment(2) == 'gantipassword') ? 'active' : '' ?>">
                                     <i class=" nav-icon fa fa-lock text-white"></i>
                                     <p class="text">Ganti Password</p>
                                 </a>
                             </li>
                             <li class="nav-item">
                                 <a href="<?= site_url('login/keluar') ?>" class="nav-link">
                                     <i class="nav-icon fa fa-sign-out-alt text-success"></i>
                                     <p class="text">Logout</p>
                                 </a>
                             </li>
                         <?php endif ?>
                         <?php endif ?>

                     </ul>
                 </nav>
                 <?php $logoutFeature = \App\Libraries\AccessControl::logoutFeature(); ?>
                 <?php $manualBookFeature = [
                     'key' => 'utility.panduan',
                     'label' => 'Manual Book',
                     'url' => 'panduan/index',
                     'icon' => 'fa fa-book-open text-info',
                     'active_patterns' => ['panduan', 'panduan/index'],
                 ]; ?>
                 <nav class="sidebar-bottom-menu" aria-label="Menu akun">
                     <ul class="nav nav-pills flex-column sidebar-bottom-list">
                         <?php if (session()->get('userid')) : ?>
                             <li class="nav-item sidebar-manual-item">
                                 <a href="<?= site_url($manualBookFeature['url']) ?>" class="nav-link <?= \App\Libraries\AccessControl::activeMenuClass($manualBookFeature) ?>">
                                     <i class="nav-icon <?= esc($manualBookFeature['icon']) ?>"></i>
                                     <p class="text"><?= esc($manualBookFeature['label']) ?></p>
                                 </a>
                             </li>
                         <?php endif ?>
                         <li class="nav-item sidebar-logout-item">
                             <a href="<?= site_url($logoutFeature['url']) ?>" class="nav-link <?= \App\Libraries\AccessControl::activeMenuClass($logoutFeature) ?>">
                                 <i class="nav-icon <?= esc($logoutFeature['icon']) ?>"></i>
                                 <p class="text"><?= esc($logoutFeature['label']) ?></p>
                             </a>
                         </li>
                     </ul>
                 </nav>
             </div>
         </aside>

         <div class="content-wrapper">
             <div class="sidebar-mobile-backdrop" data-widget="pushmenu" role="button" aria-label="Tutup menu"></div>
             <a href="#" class="sidebar-edge-toggle" data-widget="pushmenu" role="button" aria-label="Kecilkan sidebar" title="Kecilkan sidebar">
                 <i class="fas fa-chevron-left"></i>
             </a>
             <section class="content-header">
                 <div class="container-fluid">
                     <div class="row mb-2">
                         <div class="col-sm-6">
                             <h1>
                                 <?= $this->renderSection('judul'); ?>
                             </h1>
                         </div>
                         <div class="col-sm-6">
                             <ol class="breadcrumb float-sm-right">
                             </ol>
                         </div>
                     </div>
                 </div>
                 <?= $this->renderSection('main'); ?>
             </section>

             <section class="content">
             </section>
             <section class="content">
                 <div class="card">
                     <div class="card-header">
                         <h3 class="card-title">
                             <?= $this->renderSection('subjudul'); ?>
                         </h3>
                     </div>
                     <div class="card-body">
                         <?= $this->renderSection('isi'); ?>
                     </div>
                     <div class="card-footer">

                     </div>
                 </div>

             </section>
         </div>

         <footer class="main-footer">
             <div class="float-right d-none d-sm-block">
                 <b>Version</b> 1.0.1
             </div>
             <strong>Copyright &copy; 2023-<?php echo Date('Y'); ?> <a href="https://trisentosaraya.co.id">TRE</a>.</strong> All rights reserved.
         </footer>

         <aside class="control-sidebar control-sidebar-dark">
         </aside>
     </div>
     <script src="<?= base_url() ?>plugins/select2/js/select2.full.min.js"></script>
     <script>
         (function () {
             function wrapTables() {
                 document.querySelectorAll('.content table, .modal-body table').forEach(function (table) {
                     if (table.closest('.table-responsive, .dataTables_wrapper, .do-page, .print-page')) {
                         return;
                     }
                     var wrapper = document.createElement('div');
                     wrapper.className = 'table-responsive tre-auto-table-wrap';
                     table.parentNode.insertBefore(wrapper, table);
                     wrapper.appendChild(table);
                 });
             }

             function recalcTables() {
                 if (!window.jQuery || !jQuery.fn || !jQuery.fn.dataTable) {
                     return;
                 }
                 try {
                     jQuery.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
                 } catch (error) {
                     try {
                         jQuery.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                     } catch (ignored) {}
                 }
             }

             document.addEventListener('DOMContentLoaded', function () {
                 wrapTables();
                 window.setTimeout(recalcTables, 150);
                 window.setTimeout(recalcTables, 500);
             });

             window.addEventListener('resize', function () {
                 window.clearTimeout(window.treResponsiveResizeTimer);
                 window.treResponsiveResizeTimer = window.setTimeout(recalcTables, 120);
             }, { passive: true });

             if (window.jQuery) {
                 jQuery(document).on('shown.bs.modal shown.bs.tab', function () {
                     wrapTables();
                     window.setTimeout(recalcTables, 80);
                 });
             }
         }());
     </script>
     <script>
         window.treInitInlineCombobox = function(config) {
             const $box = $(config.box);
             const $input = $(config.input);
             const $hidden = $(config.hidden);
             const $menu = $(config.menu);
             const options = Array.isArray(config.options) ? config.options : [];
             let activeIndex = -1;
             let currentMatches = [];

             if (!$box.length || !$input.length || !$hidden.length || !$menu.length) {
                 return {
                     sync: function() {}
                 };
             }

             function normalize(value) {
                 return String(value || '').trim().toLowerCase();
             }

             function optionLabel(option) {
                 return String(option.text || '');
             }

             function optionValue(option) {
                 return String(option.value ?? option.text ?? '');
             }

             function render(query) {
                 const keyword = normalize(query);
                 currentMatches = options
                     .filter(function(option) {
                         return keyword === '' || normalize(optionLabel(option)).includes(keyword) || normalize(optionValue(option)).includes(keyword);
                     })
                     .slice(0, 50);
                 activeIndex = currentMatches.length ? 0 : -1;

                 if (currentMatches.length === 0) {
                     $menu.html('<div class="tre-inline-combobox-empty">Data tidak ditemukan</div>');
                     return;
                 }

                 $menu.html(currentMatches.map(function(option, index) {
                     return '<div class="tre-inline-combobox-option' + (index === activeIndex ? ' is-active' : '') + '" data-index="' + index + '">' + $('<div>').text(optionLabel(option)).html() + '</div>';
                 }).join(''));
             }

             function setActive(index) {
                 if (!currentMatches.length) {
                     activeIndex = -1;
                     return;
                 }

                 activeIndex = (index + currentMatches.length) % currentMatches.length;
                 $menu.find('.tre-inline-combobox-option')
                     .removeClass('is-active')
                     .eq(activeIndex)
                     .addClass('is-active');
             }

             function selectOption(option) {
                 if (!option) {
                     return;
                 }

                 $input.val(optionValue(option));
                 $hidden.val(option.id);
                 $box.removeClass('is-open');
                 if (typeof config.onSelect === 'function') {
                     config.onSelect(option);
                 }
             }

             function addOption(option, shouldSelect) {
                 if (!option || option.id === undefined || option.text === undefined) {
                     return;
                 }

                 const normalizedId = String(option.id);
                 const normalizedOption = {
                     id: normalizedId,
                     text: String(option.text),
                     value: option.value !== undefined ? String(option.value) : undefined
                 };
                 const exists = options.some(function(existingOption) {
                     return String(existingOption.id) === normalizedId;
                 });

                 if (!exists) {
                     options.push(normalizedOption);
                     options.sort(function(a, b) {
                         return optionLabel(a).localeCompare(optionLabel(b));
                     });
                 }

                 if (shouldSelect) {
                     selectOption(normalizedOption);
                 }
             }

             function syncExact() {
                 const keyword = normalize($input.val());
                 const match = options.find(function(option) {
                     return normalize(optionLabel(option)) === keyword || normalize(optionValue(option)) === keyword;
                 });

                 if (match) {
                     $hidden.val(match.id);
                 } else if (keyword === '') {
                     $hidden.val('');
                 } else if (!$hidden.val()) {
                     $hidden.val('');
                 }
             }

             $input
                 .attr('autocomplete', 'off')
                 .on('focus', function() {
                     render($input.val());
                     $box.addClass('is-open');
                 })
                 .on('input', function() {
                     $hidden.val('');
                     render($input.val());
                     $box.addClass('is-open');
                 })
                 .on('keydown', function(event) {
                     if (!$box.hasClass('is-open')) {
                         return;
                     }

                     if (event.key === 'ArrowDown') {
                         event.preventDefault();
                         setActive(activeIndex + 1);
                     } else if (event.key === 'ArrowUp') {
                         event.preventDefault();
                         setActive(activeIndex - 1);
                     } else if (event.key === 'Enter' && activeIndex >= 0) {
                         event.preventDefault();
                         selectOption(currentMatches[activeIndex]);
                     } else if (event.key === 'Escape') {
                         $box.removeClass('is-open');
                     }
                 })
                 .on('blur', function() {
                     window.setTimeout(function() {
                         syncExact();
                         $box.removeClass('is-open');
                     }, 120);
                 });

             $menu.on('mousedown', '.tre-inline-combobox-option', function(event) {
                 event.preventDefault();
                 selectOption(currentMatches[Number($(this).data('index'))]);
             });

             return {
                 addOption: addOption,
                 sync: syncExact
             };
         };

         // Merge-cell rowspan buat DataTables: satu baris "header" per grup
         // (kolom-kolom di `columns`) tampil sekali, baris lain dalam grup
         // yang sama disembunyikan selnya dan rowspan header-nya ditambah.
         // Dipanggil dari drawCallback -- pastikan tabel pakai ordering:false
         // (urutan grup harus sudah benar dari server) dan paging:false atau
         // serverSide:false (grup gak boleh kepotong halaman).
         window.treRenderMergedGroupRows = function(table, config) {
             const groupBy = config.groupBy;
             const columns = (config.columns || []).slice().sort(function(a, b) {
                 return a - b;
             });
             const removeColumns = columns.slice().sort(function(a, b) {
                 return b - a;
             });
             const nodes = table.rows({
                 page: 'current'
             }).nodes();
             const data = table.rows({
                 page: 'current'
             }).data();

             let lastKey = null;
             let groupStartNode = null;

             for (let i = 0; i < data.length; i++) {
                 const key = groupBy(data[i]);
                 const $cells = $(nodes[i]).children('td');

                 if (key !== null && key === lastKey && groupStartNode) {
                     const $startCells = $(groupStartNode).children('td');
                     columns.forEach(function(colIndex) {
                         const $startCell = $startCells.eq(colIndex);
                         const rowspan = parseInt($startCell.attr('rowspan') || '1', 10);
                         $startCell.attr('rowspan', rowspan + 1).css('vertical-align', 'top');
                     });
                     removeColumns.forEach(function(colIndex) {
                         $cells.eq(colIndex).remove();
                     });
                 } else {
                     columns.forEach(function(colIndex) {
                         $cells.eq(colIndex).removeAttr('rowspan').css('vertical-align', '');
                     });
                     groupStartNode = nodes[i];
                     lastKey = key;
                 }
             }
         };
     </script>
     <script>
         //Initialize Select2 Elements
         $(function() {
             //Initialize Select2 Elements
             $('.select2').select2({
                 width: '100%'
             })

             //Initialize Select2 Elements
             $('.select2bs4').select2({
                 theme: 'bootstrap4',
                 minimumResultsForSearch: 0,
                 width: '100%'
             })

             $(document).on('select2:open', function() {
                 window.setTimeout(function() {
                     const searchField = document.querySelector('.select2-container--open .select2-search__field');
                     if (searchField) {
                         searchField.focus();
                     }
                 }, 0);
             });
         })
     </script>
     <script>
         $(function() {
             const storageKey = 'tre.sidebar.openSections';
             const sidebarStateKey = 'tre.sidebar.collapsed';
             const $headers = $('.nav-sidebar > .nav-header');
             const sectionIcons = {
                 'dashboard': 'fas fa-home text-primary',
                 'master': 'fas fa-database text-primary',
                 'transaksi-produk': 'fas fa-boxes text-success',
                 'transaksi-order': 'fas fa-file-invoice text-info',
                 'transaksi-material': 'fas fa-dolly-flatbed text-warning',
                 'packaging': 'fas fa-box-open text-warning',
                 'utility': 'fas fa-tools text-danger'
             };
             let savedSections = [];

             try {
                 savedSections = JSON.parse(localStorage.getItem(storageKey) || '[]');
             } catch (error) {
                 savedSections = [];
             }

             function saveOpenSections() {
                 savedSections = $('.nav-sidebar > .nav-header.sidebar-section-toggle.is-open')
                     .map(function() {
                         return $(this).data('section-id');
                     })
                     .get();
                 localStorage.setItem(storageKey, JSON.stringify(savedSections));
             }

             function ensureSectionChevron($header) {
                 if ($header.find('.section-chevron').length === 0) {
                     $header.append('<i class="fas fa-chevron-right section-chevron"></i>');
                 }
             }

             function ensureRailIndicator() {
                 const $nav = $('.nav-sidebar');
                 let $indicator = $nav.children('.sidebar-rail-indicator');

                 if ($indicator.length === 0) {
                     $indicator = $('<li class="sidebar-rail-indicator" aria-hidden="true" role="presentation"></li>');
                     $nav.prepend($indicator);
                 }

                 return $indicator;
             }

             function moveRailIndicator($link, immediate) {
                 const $indicator = ensureRailIndicator();

                 if (!$link || $link.length === 0 || !$link.is(':visible')) {
                     $indicator.removeClass('is-visible is-moving');
                     return;
                 }

                 const $nav = $('.nav-sidebar');
                 const navTop = $nav.offset().top;
                 const linkTop = $link.offset().top;
                 const top = (linkTop - navTop) + ($link.outerHeight() / 2);

                 if (immediate) {
                     $indicator.css('transition', 'none');
                 }

                 $indicator
                     .css('top', top + 'px')
                     .addClass('is-visible is-moving');

                 if (immediate) {
                     $indicator[0].offsetHeight;
                     $indicator.css('transition', '');
                 }

                 window.clearTimeout($indicator.data('movingTimer'));
                 $indicator.data('movingTimer', window.setTimeout(function() {
                     $indicator.removeClass('is-moving');
                 }, 220));
             }

             function moveRailToActive(immediate) {
                 const $activeLink = $('.nav-sidebar .sidebar-section-item > .nav-link.active:visible').first();
                 moveRailIndicator($activeLink, immediate);
             }

             function setSectionState($header, isOpen, animate) {
                 ensureSectionChevron($header);
                 const $items = $header.nextUntil('.nav-header');
                 $header.toggleClass('is-open', isOpen);
                 $header.attr('aria-expanded', isOpen ? 'true' : 'false');

                 if (animate) {
                     isOpen ? $items.stop(true, true).slideDown(160, function() {
                         moveRailToActive(false);
                     }) : $items.stop(true, true).slideUp(160, function() {
                         moveRailToActive(false);
                     });
                 } else {
                     isOpen ? $items.show() : $items.hide();
                 }
             }

             function syncCollapsedSections() {
                 if ($('body').hasClass('sidebar-collapse')) {
                     $('.nav-sidebar > .nav-header.sidebar-section-toggle')
                         .removeClass('is-open')
                         .attr('aria-expanded', 'false')
                         .nextUntil('.nav-header')
                         .stop(true, true)
                         .hide();
                 } else {
                     $('.nav-sidebar > .nav-header.sidebar-section-toggle').each(function() {
                         const $header = $(this);
                         const shouldOpen = $header.nextUntil('.nav-header').find('.nav-link.active').length > 0;
                         setSectionState($header, shouldOpen, false);
                     });
                 }
             }

             function updateSidebarEdgeToggle() {
                 const $toggle = $('.sidebar-edge-toggle');

                 if (window.innerWidth < 768) {
                     const isOpen = $('body').hasClass('sidebar-open');
                     $toggle
                         .attr('aria-label', isOpen ? 'Tutup menu' : 'Buka menu')
                         .attr('title', isOpen ? 'Tutup menu' : 'Buka menu');
                     $toggle.find('i')
                         .toggleClass('fa-chevron-left fa-chevron-right', false)
                         .toggleClass('fa-times', isOpen)
                         .toggleClass('fa-bars', !isOpen);
                     return;
                 }

                 const isCollapsed = $('body').hasClass('sidebar-collapse');
                 $toggle
                     .attr('aria-label', isCollapsed ? 'Besarkan sidebar' : 'Kecilkan sidebar')
                     .attr('title', isCollapsed ? 'Besarkan sidebar' : 'Kecilkan sidebar');
                 $toggle.find('i')
                     .toggleClass('fa-times fa-bars', false)
                     .toggleClass('fa-chevron-left', !isCollapsed)
                     .toggleClass('fa-chevron-right', isCollapsed);
             }

             function saveSidebarCollapsedState() {
                 try {
                     localStorage.setItem(sidebarStateKey, $('body').hasClass('sidebar-collapse') ? '1' : '0');
                 } catch (error) {
                     // Abaikan kalau browser menolak akses localStorage.
                 }
             }

             let hasActiveSection = false;

             $headers.each(function(index) {
                 const $header = $(this);
                 const $items = $header.nextUntil('.nav-header');

                 if ($items.length === 0) {
                     return;
                 }

                 $items
                     .filter('.nav-item')
                     .addClass('sidebar-section-item')
                     .removeClass('section-first section-last')
                     .first()
                     .addClass('section-first')
                     .end()
                     .last()
                     .addClass('section-last');

                 const sectionTitle = $.trim($header.text());
                 const sectionName = sectionTitle.toLowerCase().replace(/[^a-z0-9]+/g, '-');
                 const sectionId = `${index}-${sectionName}`;
                 const isActiveSection = $items.find('.nav-link.active').length > 0;

                 if (isActiveSection) {
                     hasActiveSection = true;
                 }

                 $header
                     .addClass('sidebar-section-toggle')
                     .attr({
                         role: 'button',
                         tabindex: '0',
                         'aria-expanded': isActiveSection ? 'true' : 'false'
                     })
                     .data('section-id', sectionId);

                 if ($header.find('.section-label').length === 0) {
                     const iconClass = sectionIcons[sectionName] || 'fas fa-folder text-muted';
                     const $label = $('<span class="section-label"></span>');
                     $label.append($('<i class="section-icon"></i>').addClass(iconClass));
                     $label.append($('<span></span>').text(sectionTitle));
                     $header.empty().append($label);
                 }

                 ensureSectionChevron($header);

                 setSectionState($header, isActiveSection, false);
             });

             $('.nav-sidebar').on('click keydown', '.sidebar-section-toggle', function(event) {
                 if (event.type === 'keydown' && !['Enter', ' '].includes(event.key)) {
                     return;
                 }

                 event.preventDefault();

                 const $header = $(this);
                 const isOpen = !$header.hasClass('is-open');
                 $header.attr('aria-expanded', isOpen ? 'true' : 'false');
                 setSectionState($header, isOpen, true);
                 saveOpenSections();
             });

             $('.nav-sidebar')
                 .on('mouseenter focusin', '.sidebar-section-item > .nav-link', function() {
                     moveRailIndicator($(this), false);
                 })
                 .on('mouseleave focusout', '.sidebar-section-item > .nav-link', function() {
                     moveRailToActive(false);
                 })
                 .on('click', '.sidebar-section-item > .nav-link', function() {
                     moveRailIndicator($(this), false);
                 });

             $(document).on('collapsed.lte.pushmenu shown.lte.pushmenu', function() {
                 saveSidebarCollapsedState();
                 window.setTimeout(function() {
                     syncCollapsedSections();
                     updateSidebarEdgeToggle();
                     moveRailToActive(false);
                 }, 80);
             });

             if ($('body').hasClass('sidebar-collapse')) {
                 syncCollapsedSections();
             }
             updateSidebarEdgeToggle();
             moveRailToActive(true);

             const $menuSearch = $('#sidebarMenuSearch');
             const $menuSearchClear = $('#sidebarMenuSearchClear');
             const $menuSearchEmpty = $('#sidebarMenuSearchEmpty');

             function clearMenuSearch() {
                 $menuSearch.val('');
                 $menuSearchClear.hide();
                 $menuSearchEmpty.hide();
                 $('.nav-sidebar > .nav-header, .nav-sidebar > .nav-item').show();
                 syncCollapsedSections();
                 moveRailToActive(false);
             }

             $menuSearch.on('input', function() {
                 const term = $.trim($(this).val()).toLowerCase();
                 $menuSearchClear.toggle(term !== '');

                 if (term === '') {
                     clearMenuSearch();
                     return;
                 }

                 let anySectionMatched = false;

                 $('.nav-sidebar > .nav-header').each(function() {
                     const $header = $(this);
                     const $items = $header.nextUntil('.nav-header').filter('.nav-item');
                     let sectionMatched = false;

                     $items.each(function() {
                         const label = $.trim($(this).find('.text').text()).toLowerCase();
                         const matched = label.indexOf(term) !== -1;
                         $(this).toggle(matched);
                         if (matched) {
                             sectionMatched = true;
                         }
                     });

                     $header.toggle(sectionMatched);
                     if (sectionMatched) {
                         anySectionMatched = true;
                         setSectionState($header, true, false);
                     }
                 });

                 $menuSearchEmpty.toggle(!anySectionMatched);
             });

             $menuSearchClear.on('click', function() {
                 clearMenuSearch();
                 $menuSearch.trigger('focus');
             });
         });
     </script>
     <div class="modal fade" id="appPopupModal" tabindex="-1" role="dialog" aria-labelledby="appPopupModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered app-popup-dialog" role="document">
             <div class="modal-content app-popup-content">
                 <div class="modal-header">
                     <h5 class="modal-title" id="appPopupModalLabel"><span class="app-popup-title">Pesan</span></h5>
                     <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                 </div>
                 <div class="modal-body app-popup-body"></div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary app-popup-cancel">Batal</button>
                     <button type="button" class="btn btn-primary app-popup-confirm">OK</button>
                 </div>
             </div>
         </div>
     </div>
     <!-- Bootstrap -->
     <script src="<?= base_url() ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
     <script src="<?= base_url() ?>js/bootstrap-popup.js"></script>
     <script src="<?= base_url() ?>dist/js/adminlte.min.js"></script>
     <?php if (session()->getFlashdata('access_denied')) : ?>
     <script>
         $(function() {
             showBootstrapModal({
                 icon: 'warning',
                 title: 'Akses Ditolak',
                 text: <?= json_encode(session()->getFlashdata('access_denied')) ?>
             });
         });
     </script>
     <?php endif ?>
     <?php if ($manualPreviewMode) : ?>
     <script>
         document.addEventListener('DOMContentLoaded', function() {
             const previewKey = <?= json_encode($manualPreviewKey) ?>;

             function withManualPreview(url) {
                 try {
                     const nextUrl = new URL(url, window.location.origin);
                     if (nextUrl.origin !== window.location.origin) {
                         return url;
                     }
                     nextUrl.searchParams.set('manual_preview', previewKey);
                     return nextUrl.pathname + nextUrl.search + nextUrl.hash;
                 } catch (e) {
                     return url;
                 }
             }

             // Diekspos global buat halaman yang navigasinya lewat JS langsung
             // (mis. onSelect combobox di invoiceOut/create & invoiceIn/create
             // yang assign window.location.href sendiri, bukan lewat <a href>
             // atau button[onclick] biasa) -- tanpa ini, klik combobox itu
             // bikin mode preview keluar total di halaman berikutnya (sidebar
             // balik muncul, semua tooltip hilang).
             window.treApplyManualPreview = withManualPreview;

             function isNavigableHref(href) {
                 return href && href.charAt(0) !== '#' && !/^javascript:/i.test(href);
             }

             function getInlineLocationHref(inlineAction) {
                 const match = (inlineAction || '').match(/(?:window\.)?location\.href\s*=\s*\(?\s*['"]([^'"]+)['"]/i);
                 return match ? match[1] : '';
             }

             // Beberapa halaman list (po/data, materialmasuk/data, dll.) punya
             // tombol Edit yang onclick-nya manggil fungsi JS `edit('id')`
             // (bukan langsung location.href=...), jadi getInlineLocationHref()
             // di atas nggak nangkep itu, dan mode preview keluar total begitu
             // diklik. SENGAJA pakai whitelist controller -> nama action
             // (bukan generik dari segmen URL) -- soalnya nggak semua fungsi
             // `edit()` di app ini sesederhana location.href={segmen}/edit/{id}:
             // gudang pakai path "formedit", permintaanBarangKirim pakai
             // "editproses", material/viewdatamaterial.php malah manggil AJAX
             // dulu (bukan langsung pindah halaman). Kalau di-generik-in, itu
             // bakal salah dibajak jadi navigasi yang salah pas mode preview
             // aktif di halaman itu.
             const LIST_EDIT_SEGMENTS = {
                 'po': 'edit',
                 'materialmasuk': 'edit',
                 'permintaanbarangkirim': 'editproses',
                 'barangmasuk': 'edit',
                 'barangkeluar': 'edit',
                 'barang': 'edit'
             };
             // Tombol lain yang manggil fungsi JS bernama beda (bukan `edit`)
             // tapi tujuannya tetap navigasi location.href ke URL tetap,
             // nggak tergantung segmen halaman saat ini (mis. tombol "Edit"
             // pada baris produksi di tab "Dari Produksi" halaman Produk
             // Masuk, yang selalu ke /produksi/edit/{id} apa pun halamannya,
             // atau tombol "Lanjutkan Input Pengiriman" di List Permintaan
             // yang selalu ke /permintaanPengiriman/langsung/{id}).
             const NAMED_EDIT_TARGETS = [
                 { pattern: /\beditProduksi\s*\(\s*['"]([^'"]+)['"]\s*\)/i, prefix: '/produksi/edit/' },
                 { pattern: /\blanjutkanPengiriman\s*\(\s*['"]([^'"]+)['"]\s*\)/i, prefix: '/permintaanPengiriman/langsung/' },
                 { pattern: /\briwayat\s*\(\s*['"]([^'"]+)['"]\s*\)/i, prefix: '/barang/riwayat/' }
             ];
             function getListEditHref(inlineAction) {
                 for (let i = 0; i < NAMED_EDIT_TARGETS.length; i++) {
                     const namedMatch = (inlineAction || '').match(NAMED_EDIT_TARGETS[i].pattern);
                     if (namedMatch) {
                         return NAMED_EDIT_TARGETS[i].prefix + namedMatch[1];
                     }
                 }

                 const match = (inlineAction || '').match(/\bedit\s*\(\s*['"]([^'"]+)['"]\s*\)/i);
                 if (!match) {
                     return '';
                 }

                 const segments = window.location.pathname.split('/').filter(Boolean);
                 const action = segments.length ? LIST_EDIT_SEGMENTS[segments[0].toLowerCase()] : undefined;
                 if (!action) {
                     return '';
                 }

                 return '/' + segments[0] + '/' + action + '/' + match[1];
             }

             function syncManualPreviewNavigation(root) {
                 const scope = root && root.querySelectorAll ? root : document;

                 scope.querySelectorAll('a[href]').forEach(function(link) {
                     const href = link.getAttribute('href');
                     if (!link.target && isNavigableHref(href)) {
                         link.setAttribute('href', withManualPreview(href));
                     }
                 });

                 scope.querySelectorAll('button[onclick]').forEach(function(button) {
                     const inlineAction = button.getAttribute('onclick');
                     const nextHref = getInlineLocationHref(inlineAction) || getListEditHref(inlineAction);
                     if (!nextHref) {
                         return;
                     }
                     button.dataset.manualPreviewHref = withManualPreview(nextHref);
                     button.removeAttribute('onclick');
                 });

                 scope.querySelectorAll('form').forEach(function(form) {
                     const action = form.getAttribute('action') || window.location.href;
                     form.setAttribute('action', withManualPreview(action));
                     let input = form.querySelector('input[name="manual_preview"]');
                     if (!input) {
                         input = document.createElement('input');
                         input.type = 'hidden';
                         input.name = 'manual_preview';
                         form.appendChild(input);
                     }
                     input.value = previewKey;
                 });
             }

             document.addEventListener('click', function(event) {
                 const link = event.target.closest('a[href]');
                 if (link && !link.target && isNavigableHref(link.getAttribute('href'))) {
                     event.preventDefault();
                     event.stopImmediatePropagation();
                     window.location.href = withManualPreview(link.getAttribute('href'));
                     return;
                 }

                 const previewButton = event.target.closest('[data-manual-preview-href]');
                 if (previewButton) {
                     event.preventDefault();
                     event.stopImmediatePropagation();
                     window.location.href = previewButton.dataset.manualPreviewHref;
                     return;
                 }

                 const actionButton = event.target.closest('button[onclick]');
                 if (!actionButton) {
                     return;
                 }

                 const inlineAction = actionButton.getAttribute('onclick');
                 const nextHref = getInlineLocationHref(inlineAction) || getListEditHref(inlineAction);
                 if (!nextHref) {
                     return;
                 }

                 event.preventDefault();
                 event.stopImmediatePropagation();
                 window.location.href = withManualPreview(nextHref);
             }, true);

             syncManualPreviewNavigation(document);

             if ('MutationObserver' in window) {
                 const observer = new MutationObserver(function(mutations) {
                     mutations.forEach(function(mutation) {
                         mutation.addedNodes.forEach(function(node) {
                             if (node.nodeType === 1) {
                                 syncManualPreviewNavigation(node);
                             }
                         });
                     });
                 });
                 observer.observe(document.body, { childList: true, subtree: true });
             }
         });
     </script>
     <style>
         .tre-tour-backdrop { background: rgba(15, 23, 42, .5); display: none; inset: 0; position: fixed; z-index: 99989; }
         .tre-tour-highlight { border-radius: 8px; box-shadow: 0 0 0 4px #12879a, 0 0 0 6000px rgba(15, 23, 42, .55); display: none; position: fixed; z-index: 99991; }
         .tre-tour-callout { background: #fff; border-radius: 10px; box-shadow: 0 20px 50px rgba(15, 23, 42, .32); display: none; max-width: 320px; opacity: 0; padding: 16px 18px; position: fixed; transition: opacity .18s ease; z-index: 99992; }
         .tre-tour-callout.is-visible { opacity: 1; }
         .tre-tour-callout-title { color: #172033; font-size: 1rem; font-weight: 800; margin-bottom: 6px; }
         .tre-tour-callout-desc { color: #4b5563; font-size: .88rem; line-height: 1.4; margin-bottom: 14px; }
         .tre-tour-callout-actions { align-items: center; display: flex; justify-content: space-between; gap: 8px; }
         .tre-tour-callout-step { color: #94a3b8; font-size: .78rem; font-weight: 700; }
         .tre-tour-btn { border: 0; border-radius: 6px; cursor: pointer; font-size: .85rem; font-weight: 700; padding: 7px 14px; }
         .tre-tour-btn-close { background: #f1f5f9; color: #475569; }
         .tre-tour-btn-close:hover { background: #e2e8f0; }
         .tre-tour-btn-next { background: #12879a; color: #fff; }
         .tre-tour-btn-next:hover { background: #0e6d7d; }
     </style>
     <script>
         (function() {
             function getTour() {
                 try {
                     const raw = sessionStorage.getItem('treTourActive');
                     const tour = raw ? JSON.parse(raw) : null;
                     return (tour && Array.isArray(tour.steps) && tour.steps.length) ? tour : null;
                 } catch (e) {
                     return null;
                 }
             }

             function saveTour(tour) {
                 sessionStorage.setItem('treTourActive', JSON.stringify(tour));
             }

             function clearTour() {
                 sessionStorage.removeItem('treTourActive');
             }

             function samePage(url) {
                 try {
                     const target = new URL(url, window.location.origin);
                     return target.pathname.replace(/\/+$/, '') === window.location.pathname.replace(/\/+$/, '');
                 } catch (e) {
                     return false;
                 }
             }

             // Sebagian halaman (mis. Ubah PO) URL-nya punya id spesifik yang nggak bisa
             // ditebak di depan (/po/edit/{hash}). Buat step di halaman kayak gitu, dipakai
             // 'urlPattern' (prefix path) bukan 'url' -- match-nya ke SEMUA halaman yang
             // pathname-nya diawali prefix itu, apa pun id-nya.
             function stepMatchesLocation(step) {
                 if (step.urlPattern) {
                     try {
                         const prefix = new URL(step.urlPattern, window.location.origin).pathname;
                         return window.location.pathname.indexOf(prefix) === 0;
                     } catch (e) {
                         return false;
                     }
                 }
                 return samePage(step.url);
             }

             // Kalau user klik langsung tombol asli yang di-spotlight (bukan tombol
             // "Selanjutnya" di callout), browser tetap pindah halaman seperti biasa.
             // Supaya tur nggak "nyangkut" di step lama, di tiap load kita cek: kalau
             // step saat ini nggak cocok sama halaman ini, tapi ada step LEBIH JAUH
             // yang cocok, lompat ke situ otomatis.
             function resolveCurrentIndex(tour) {
                 if (stepMatchesLocation(tour.steps[tour.index])) return tour.index;
                 for (let i = tour.index + 1; i < tour.steps.length; i++) {
                     if (stepMatchesLocation(tour.steps[i])) return i;
                 }
                 return -1;
             }

             let backdropEl, highlightEl, calloutEl, repositionHandler;

             function buildOverlay() {
                 backdropEl = document.createElement('div');
                 backdropEl.className = 'tre-tour-backdrop';

                 highlightEl = document.createElement('div');
                 highlightEl.className = 'tre-tour-highlight';

                 calloutEl = document.createElement('div');
                 calloutEl.className = 'tre-tour-callout';
                 calloutEl.innerHTML =
                     '<div class="tre-tour-callout-title"></div>' +
                     '<div class="tre-tour-callout-desc"></div>' +
                     '<div class="tre-tour-callout-actions">' +
                         '<span class="tre-tour-callout-step"></span>' +
                         '<span>' +
                             '<button type="button" class="tre-tour-btn tre-tour-btn-close">Tutup</button> ' +
                             '<button type="button" class="tre-tour-btn tre-tour-btn-next">Selanjutnya</button>' +
                         '</span>' +
                     '</div>';

                 document.body.appendChild(backdropEl);
                 document.body.appendChild(highlightEl);
                 document.body.appendChild(calloutEl);

                 calloutEl.querySelector('.tre-tour-btn-close').addEventListener('click', endTour);
                 calloutEl.querySelector('.tre-tour-btn-next').addEventListener('click', advanceTour);
             }

             function positionOnElement(target) {
                 const rect = target.getBoundingClientRect();
                 const pad = 6;
                 backdropEl.style.display = 'none';
                 highlightEl.style.display = 'block';
                 highlightEl.style.top = (rect.top - pad) + 'px';
                 highlightEl.style.left = (rect.left - pad) + 'px';
                 highlightEl.style.width = (rect.width + pad * 2) + 'px';
                 highlightEl.style.height = (rect.height + pad * 2) + 'px';

                 // Normalnya callout ditempel di bawah/atas target. Tapi kalau target-nya
                 // gede banget (mis. nyorot satu section penuh) dan nggak muat mepet di
                 // bawah ATAUPUN di atas, jangan maksain nempel ke tepi (bisa kepental ke
                 // pojok) -- taruh melayang di tengah viewport aja, highlight-nya tetap
                 // nunjuk section itu.
                 const calloutRect = calloutEl.getBoundingClientRect();
                 const fitsBelow = rect.bottom + 14 + calloutRect.height <= window.innerHeight - 12;
                 const fitsAbove = rect.top - 14 - calloutRect.height >= 12;
                 let top, left;
                 if (fitsBelow) {
                     top = rect.bottom + 14;
                     left = rect.left;
                 } else if (fitsAbove) {
                     top = rect.top - calloutRect.height - 14;
                     left = rect.left;
                 } else {
                     top = (window.innerHeight - calloutRect.height) / 2;
                     left = (window.innerWidth - calloutRect.width) / 2;
                 }
                 if (left + calloutRect.width > window.innerWidth - 12) left = window.innerWidth - calloutRect.width - 12;
                 if (left < 12) left = 12;
                 if (top < 12) top = 12;
                 calloutEl.style.top = top + 'px';
                 calloutEl.style.left = left + 'px';
                 calloutEl.classList.add('is-visible');
             }

             function positionCentered() {
                 highlightEl.style.display = 'none';
                 backdropEl.style.display = 'block';
                 const calloutRect = calloutEl.getBoundingClientRect();
                 calloutEl.style.top = Math.max(12, (window.innerHeight - calloutRect.height) / 2) + 'px';
                 calloutEl.style.left = Math.max(12, (window.innerWidth - calloutRect.width) / 2) + 'px';
                 calloutEl.classList.add('is-visible');
             }

             let renderToken = 0;
             let currentStepTarget = null;

             // Beberapa field cuma muncul tergantung pilihan lain di form (mis. checkbox
             // "Tampilkan Kolom Material" cuma kepakai buat Jenis PO = Produk -- kalau PO
             // ini Jenis-nya Material, elemennya TETAP ADA di DOM tapi disembunyikan CSS,
             // jadi getBoundingClientRect()-nya nol semua dan callout numpuk di pojok kiri
             // atas. querySelector() doang nggak cukup, size-nya juga harus dicek.
             function isVisibleTarget(el) {
                 if (!el) return false;
                 const rect = el.getBoundingClientRect();
                 return rect.width > 0 && rect.height > 0;
             }

             function findVisibleTarget(selector) {
                 if (!selector) return null;
                 const el = document.querySelector(selector);
                 return isVisibleTarget(el) ? el : null;
             }

             function renderStep(tour) {
                 if (!calloutEl) buildOverlay();
                 const step = tour.steps[tour.index];
                 const myToken = ++renderToken;

                 calloutEl.classList.remove('is-visible');
                 calloutEl.querySelector('.tre-tour-callout-title').textContent = step.title || '';
                 calloutEl.querySelector('.tre-tour-callout-desc').textContent = step.desc || '';
                 calloutEl.querySelector('.tre-tour-callout-step').textContent = 'Langkah ' + (tour.index + 1) + ' dari ' + tour.steps.length;
                 const isLast = tour.index === tour.steps.length - 1;
                 calloutEl.querySelector('.tre-tour-btn-next').textContent = isLast ? 'Selesai' : 'Selanjutnya';
                 calloutEl.style.display = 'block';
                 currentStepTarget = null;

                 // Tampilin dulu versi centered SEKARANG JUGA (nggak nunggu retry di bawah
                 // selesai) -- soalnya kalau nunggu, callout nggak keliatan sama sekali
                 // (nggak ada class is-visible) sampai retry-nya kelar, dan itu kerasa
                 // kayak tur-nya macet/delay padahal cuma lagi nyoba nyari elemennya.
                 positionCentered();

                 // Beberapa halaman (mis. po/data) render tombol aksinya lewat DataTables
                 // ajax, jadi elemennya belum ada di DOM (atau belum keliatan) pas renderStep
                 // ini pertama jalan. Coba ulang singkat -- begitu ketemu, callout "lompat"
                 // dari posisi centered ke spotlight yang bener. Kalau emang nggak pernah
                 // ketemu (mis. field-nya kondisional dan syaratnya belum dipenuhi user),
                 // tetap di tampilan centered yang udah kepasang dari awal tadi.
                 function tryFindTarget(attemptsLeft) {
                     if (myToken !== renderToken) return;
                     const target = findVisibleTarget(step.selector);
                     if (target) {
                         currentStepTarget = target;
                         // Kalau target-nya lebih tinggi dari viewport (mis. nyorot satu
                         // section/card penuh), scroll ke TOP-nya (bukan center) -- biar
                         // tepi atas kotak sorotannya kelihatan di layar sebagai acuan
                         // visual, daripada user nyangka nggak ada yang disorot sama
                         // sekali karena lagi di "dalam" kotak yang gedenya ngelebihin
                         // satu layar penuh.
                         const preScrollRect = target.getBoundingClientRect();
                         const tooTallForViewport = preScrollRect.height > window.innerHeight - 80;
                         target.scrollIntoView({ block: tooTallForViewport ? 'start' : 'center' });
                         positionOnElement(target);
                         setTimeout(function() { if (myToken === renderToken) positionOnElement(target); }, 120);
                     } else if (attemptsLeft > 0) {
                         setTimeout(function() { tryFindTarget(attemptsLeft - 1); }, 200);
                     }
                 }
                 if (step.selector) tryFindTarget(20);

                 if (repositionHandler) {
                     window.removeEventListener('scroll', repositionHandler, true);
                     window.removeEventListener('resize', repositionHandler);
                 }
                 repositionHandler = function() {
                     const el = findVisibleTarget(step.selector);
                     if (el) positionOnElement(el); else positionCentered();
                 };
                 window.addEventListener('scroll', repositionHandler, true);
                 window.addEventListener('resize', repositionHandler);
             }

             function advanceTour() {
                 const tour = getTour();
                 if (!tour) return;
                 const nextIndex = tour.index + 1;
                 if (nextIndex >= tour.steps.length) {
                     endTour();
                     return;
                 }
                 const nextStep = tour.steps[nextIndex];
                 if (stepMatchesLocation(nextStep)) {
                     tour.index = nextIndex;
                     saveTour(tour);
                     renderStep(tour);
                 } else if (nextStep.url) {
                     tour.index = nextIndex;
                     saveTour(tour);
                     window.location.href = nextStep.url;
                 } else if (currentStepTarget) {
                     // Step berikutnya pakai urlPattern (target dinamis, id-nya belum
                     // ketahuan, mis. /po/edit/{id}) -- nggak ada URL pasti buat dituju
                     // lewat kode. Tapi elemen ASLI yang lagi disorot di step SEKARANG
                     // ini ada, dan itu justru elemen yang mestinya diklik buat sampai ke
                     // halaman berikutnya -- jadi tombol "Selanjutnya" diperlakukan sebagai
                     // "klik elemen itu buat saya", bukan diem aja (yang kerasa nge-stuck).
                     // Kalau elemen aslinya navigasi beneran, resolveCurrentIndex di
                     // halaman baru bakal nyambungin ke step yang cocok otomatis.
                     currentStepTarget.click();
                 }
             }

             function endTour() {
                 clearTour();
                 // Baik ditutup di tengah jalan (Tutup) maupun selesai sampai step
                 // terakhir (Selesai), user diarahkan balik ke tab "Alur TRE" di
                 // Manual Book -- bukan dibiarkan nyangkut di halaman fitur terakhir
                 // yang lagi di-spotlight.
                 window.location.href = '<?= site_url('panduan') ?>#alur';
             }

             document.addEventListener('DOMContentLoaded', function() {
                 const tour = getTour();
                 if (!tour) return;
                 const idx = resolveCurrentIndex(tour);
                 if (idx === -1) {
                     clearTour();
                     return;
                 }
                 tour.index = idx;
                 saveTour(tour);
                 renderStep(tour);
             });
         })();
     </script>
     <?php endif ?>
     <?php if (in_array($manualPreviewKey, ['po-masuk', 'po-keluar', 'outstanding', 'keuangan', 'stok-material', 'kebutuhan-material', 'material-terbuang', 'material-masuk', 'pemakaian-material', 'produk-masuk', 'pengiriman', 'antar-gudang', 'kategori', 'satuan', 'master-material', 'master-produk', 'master-pelanggan', 'master-supplier', 'management-user', 'log-aktivitas', 'ganti-password'], true)) : ?>
     <script>
         document.addEventListener('DOMContentLoaded', function() {
             const previewKey = <?= json_encode($manualPreviewKey) ?>;
             const tooltipCopy = {
                 'po-masuk': {
                     input: 'Input PO: membuka form untuk mencatat PO pelanggan secara manual.',
                     import: 'Import PO PDF: upload file PO agar data item terbaca otomatis lalu bisa dicek ulang.',
                     uploadFile: 'Upload File PO: tempat user memilih file PDF PO pelanggan sebelum sistem membaca isi dokumen.',
                     readPdf: 'Baca File PO: menjalankan pembacaan PDF agar No PO, tanggal, pelanggan, dan item bisa dipreview dulu.',
                     reviewPdf: 'Hasil baca PDF: cek ulang data hasil ekstraksi sebelum disimpan, terutama pelanggan dan kecocokan produk.',
                     headerData: 'Data Header PO: informasi utama PO yang terbaca dari PDF dan harus dicek sebelum disimpan.',
                     detailImport: 'Detail Item PO: daftar item hasil baca PDF yang perlu dicocokkan dengan master produk di sistem.',
                     pdfDescription: 'Deskripsi dari PDF: teks item asli dari dokumen PDF sebagai pembanding saat mencocokkan produk.',
                     systemProduct: 'Produk di Sistem: produk master yang dipasangkan ke item PDF. Jika belum cocok, pilih manual dari daftar.',
                     uom: 'UoM: satuan item yang terbaca dari PDF atau dipakai untuk transaksi ini.',
                     unitPrice: 'Harga Satuan: harga per item yang terbaca dari PDF dan masih bisa dicek sebelum PO disimpan.',
                     pdfCustomerHint: 'Terbaca dari PDF: nama pelanggan yang dikenali sistem dari file, dipakai sebagai petunjuk pencocokan.',
                     savePo: 'Simpan PO: menyimpan hasil import PDF menjadi PO Masuk final setelah semua data dicek.',
                     reupload: 'Upload Ulang: kembali memilih file PDF lain kalau hasil baca file sebelumnya belum sesuai.',
                     search: 'Search: mencari PO Masuk berdasarkan nomor PO, pelanggan, tanggal, harga, atau status progress.',
                     filterButton: 'Tombol filter: membuka pilihan periode tanggal dan jumlah data yang ditampilkan.',
                     filterPanel: 'Panel filter: pilih rentang tanggal, jumlah data, lalu tampilkan ulang daftar PO Masuk.',
                     dateRangeChip: 'Filter cepat tanggal: menampilkan PO Masuk dalam rentang waktu ini saja.',
                     filterApply: 'Tampilkan: menerapkan semua filter yang dipilih ke daftar PO Masuk.',
                     filterReset: 'Reset: mengembalikan semua filter dan pencarian ke kondisi awal.',
                     table: 'Daftar PO Masuk: ringkasan PO pelanggan yang sudah dicatat di sistem.',
                     noPo: 'No PO: nomor pesanan dari pelanggan sebagai identitas utama transaksi.',
                     tanggal: 'Tanggal: tanggal PO pelanggan dibuat atau dicatat.',
                     pelanggan: 'Pelanggan: nama customer yang membuat PO.',
                     totalBarang: 'Total Barang: total qty item dalam PO dalam satuan pcs.',
                     harga: 'Harga: total nilai PO berdasarkan item dan harga yang dicatat.',
                     progress: 'Progress: status pengiriman dan penagihan PO, misalnya belum dikirim, dikirim lengkap, atau belum lunas.',
                     aksi: 'Aksi: tombol untuk melihat detail, edit, hapus, cetak, close/reopen item, atau proses lanjutan sesuai kondisi PO.',
                     detail: 'Detail: melihat isi PO dan progress item tanpa mengubah data.',
                     edit: 'Edit: mengubah data yang masih boleh diedit atau melakukan koreksi yang disediakan sistem.',
                     delete: 'Hapus: menghapus PO yang belum terkunci oleh transaksi lanjutan.',
                     back: 'Kembali: kembali ke daftar PO Masuk.',
                     dateInput: 'Tanggal PO: tanggal pesanan pelanggan yang sedang diinput.',
                     poNumberInput: 'No. PO: nomor PO pelanggan yang akan dipakai sebagai referensi transaksi.',
                     customerInput: 'Pelanggan: pilih atau tambahkan pelanggan yang membuat PO.',
                     migration: 'PO sudah berjalan/migrasi: aktifkan kalau PO sudah berjalan sebelum dicatat di sistem, lalu isi qty terkirim/nilai ditagihkan awal.',
                     productCode: 'Kode Produk: pilih produk yang masuk ke PO pelanggan.',
                     productName: 'Nama Produk: otomatis terisi dari kode produk yang dipilih.',
                     weight: 'Berat Satuan: berat per pcs untuk menghitung subtotal kilogram.',
                     stock: 'Stok: stok produk saat ini sebagai informasi sebelum menerima pesanan.',
                     qty: 'Qty: jumlah pesanan produk yang dimasukkan ke PO.',
                     sentQty: 'QTY terkirim: qty yang sudah terkirim sebelum sistem dipakai atau sebelum koreksi dicatat.',
                     billedValue: 'Nilai sudah ditagihkan: nominal awal yang sudah pernah ditagihkan untuk item PO ini.',
                     saveItem: 'Tambah Item: menyimpan item sementara ke draft PO sebelum transaksi diselesaikan.',
                     resetItem: 'Reload/reset item: mengosongkan input item agar user bisa memilih ulang data produk.',
                     cancelItemEdit: 'Batal: membatalkan proses edit item yang sedang berjalan dan mengembalikan form ke mode tambah item baru, tanpa mengubah data yang sudah tersimpan.',
                     deleteDraftItem: 'Hapus Item Draft: menghapus salah satu baris item dari draft PO yang belum disimpan final.',
                     deleteSavedItem: 'Hapus Item: menghapus salah satu item dari PO ini. Beda dengan tombol Hapus di daftar PO yang menghapus keseluruhan PO.',
                     draft: 'Draft Item PO: daftar item yang sudah ditambahkan sebelum PO disimpan final.',
                     finish: 'Selesai Transaksi: menyimpan PO Masuk beserta semua item draft.',
                     progressPage: 'Progress PO: halaman untuk melihat tahapan PO dari dibuat, material siap, dikirim, ditagih, sampai lunas.',
                     poInfo: 'Informasi PO: ringkasan nomor PO, tanggal, dan pelanggan yang sedang dicek progressnya.',
                     progressSteps: 'Tahapan progress: menunjukkan posisi PO saat ini. Tanda centang berarti tahap selesai, angka biru berarti tahap sedang berjalan.',
                     materialProcurement: 'Pengadaan material: status material pendukung untuk memenuhi PO pelanggan.',
                     shippingCustomer: 'Pengiriman ke pelanggan: status dan riwayat surat jalan yang sudah dikirim ke pelanggan.',
                     closedMoved: 'Item Ditutup/Dipindah: catatan qty item yang ditutup, dikoreksi, atau dipindahkan supaya outstanding PO tetap benar.',
                     billingProgress: 'Penagihan: status invoice dan pembayaran atas PO ini.',
                     btbButton: 'Lihat BTB: melihat dokumen bukti terima barang untuk surat jalan terkait.',
                     lockNotice: 'Terkunci: PO sudah dipakai transaksi lanjutan, jadi data utama tidak boleh diedit langsung.',
                     closePo: 'Close PO/Tutup PO: menutup sisa qty yang tidak akan dilanjutkan supaya outstanding menjadi selesai.',
                     reopenClose: 'Buka Close: membuka kembali qty yang sebelumnya ditutup tanpa mengubah transaksi lama.',
                     statusBadge: 'Label status: menunjukkan kondisi terakhir dari bagian progress ini agar user cepat tahu langkah berikutnya.',
                    editPage: 'Ubah PO: halaman untuk melihat detail PO Masuk, mengubah data yang masih boleh diedit, atau menjalankan koreksi item sesuai aturan transaksi.',
                    editableNotice: 'Info edit: PO masih boleh diubah selama belum dipakai transaksi lanjutan. Kalau sudah dipakai, data utama dikunci.',
                    lockedPill: 'Terkunci/Read-only: bagian ini tidak bisa diubah karena PO sudah dipakai transaksi lanjutan.',
                    summaryNoPo: 'Card No. PO: nomor PO pelanggan yang menjadi identitas utama transaksi.',
                    summaryTanggal: 'Card Tanggal: tanggal PO pelanggan. Jika masih boleh, tombol Edit dipakai untuk koreksi tanggal.',
                    summaryPelanggan: 'Card Pelanggan: customer pemilik PO. Bisa diedit hanya kalau PO belum terkunci transaksi lanjutan.',
                    summaryTotalQty: 'Card Total Qty: total seluruh qty item di PO dalam satuan pcs.',
                    editNumberButton: 'Edit No. PO: mengubah nomor PO jika PO belum terkunci dan user punya akses.',
                    editDateButton: 'Edit Tanggal: mengubah tanggal PO tanpa mengubah item lain.',
                    editCustomerButton: 'Edit Pelanggan: mengganti pelanggan PO jika masih boleh diedit.',
                    saveFieldButton: 'Simpan perubahan field: menyimpan perubahan kecil seperti nomor PO, tanggal, atau pelanggan.',
                    cancelEdit: 'Batal: membatalkan perubahan yang belum disimpan dan mengembalikan tampilan semula.',
                    addItemSection: 'Tambah Barang: area untuk menambah atau mengubah item PO. Saat PO terkunci, form ini hanya tampil sebagai informasi dan tidak bisa dipakai input.',
                    tableItemList: 'Daftar Item PO: daftar item dalam PO beserta qty, qty terkirim, nilai ditagihkan, subtotal berat, harga, dan aksi yang tersedia.',
                    readOnlySection: 'Read-only/Terkunci: daftar item hanya bisa dilihat atau diproses lewat aksi yang tersedia, bukan diedit bebas.',
                    editItemButton: 'Edit Item: memuat item ke form agar qty, harga, atau data yang masih boleh dikoreksi bisa disimpan ulang.',
                    closeItemButton: 'Tutup Item: menutup sisa qty item yang tidak akan dilanjutkan, tanpa menghapus transaksi lama.',
                    correctionButton: 'Koreksi Qty: mengubah qty PO untuk item yang sudah dipakai transaksi, tanpa menghapus riwayat transaksi lama.',
                    closeHistoryButton: 'Riwayat: membuka catatan penutupan atau buka close untuk item ini.',
                    closeHistory: 'Riwayat penutupan item: catatan qty yang pernah ditutup atau dibuka kembali sebagai jejak koreksi.',
                    closedBadge: 'Ditutup: item ini memiliki sisa qty yang sengaja ditutup sehingga outstanding-nya tidak diproses lagi.',
                    subtotalWeight: 'Subtotal: total berat item, dihitung dari berat satuan dikali qty.',
                    itemActionColumn: 'Aksi item: tombol untuk hapus, tutup item, koreksi qty, melihat riwayat, atau buka close sesuai status item.',
                    rowNumber: 'No: nomor urut baris pada daftar PO Masuk.',
                    tipeAlokasi: 'Tipe Alokasi: pilih "Pindah (catatan)" kalau sisa qty nya sebenernya mau dicatat ke PO lain milik pelanggan yang sama, atau "Sesuaikan (hapus)" kalau qty ini memang salah input/tidak nyambung ke PO manapun.',
                    qtyAlokasi: 'QTY Alokasi: jumlah pcs dari sisa qty yang mau dialokasikan di baris ini. Total semua baris harus pas dengan sisa qty yang mau ditutup.',
                    koreksiQtyBaru: 'Qty Po yang Benar: angka qty pesanan yang seharusnya tercatat di sistem, dan qty ini tidak boleh kurang dari qty yang sudah terkirim.',
                    koreksiQtyAlasan : 'Alasan Koreksi: catatan kenapa qty PO dubah, wajib diisi supaya ada alasan kenapa qty tersebut diubah'
                 },
                 'po-keluar': {
                     input: 'Input PO Keluar: membuat PO ke supplier/vendor, termasuk PO Material, PO Produk, PO Jasa, titip proses, atau kirim langsung.',
                     search: 'Search: mencari PO Keluar berdasarkan nomor PO, supplier, jenis PO, transaksi, atau status penerimaan.',
                     filterButton: 'Tombol filter: membuka filter periode, status, jenis PO, transaksi, supplier, dan jumlah data PO Keluar.',
                     filterPanel: 'Panel filter: atur rentang tanggal, status, jenis PO, transaksi, supplier, dan jumlah data lalu tampilkan ulang daftar PO Keluar.',
                     table: 'Daftar PO Keluar: ringkasan pesanan ke supplier/vendor dan progress penerimaannya.',
                     tableInfo: 'Info tabel: menunjukkan jumlah data PO Keluar yang sedang tampil dan total data yang tersedia.',
                     pagination: 'Pagination: berpindah halaman jika data PO Keluar lebih dari satu halaman.',
                     sortColumn: 'Sort kolom: klik judul kolom untuk mengurutkan data naik atau turun.',
                     editPage: 'Edit PO Keluar: halaman untuk melihat dan mengubah bagian PO Keluar yang masih boleh diedit sesuai aturan transaksi.',
                     inputPage: 'Input PO Keluar: halaman untuk membuat PO baru ke supplier/vendor.',
                     detailPage: 'Detail PO Keluar: halaman ringkasan item PO Keluar, informasi PO, dan status penerimaannya.',
                     detailPoNumber: 'Nomor PO Keluar: identitas utama dokumen PO yang sedang dilihat.',
                     printPoButton: 'Cetak PO: membuka atau mencetak dokumen PO Keluar untuk supplier/vendor sesuai format cetak yang sudah diatur.',
                     summaryStatusPo: 'Status PO: menunjukkan apakah PO Keluar masih aktif atau sudah nonaktif.',
                     summaryPenerimaan: 'Penerimaan: status progress barang/material dari PO ini, apakah belum diterima, diterima sebagian, diterima lengkap, atau selesai.',
                     summarySupplier: 'Supplier: nama vendor atau pihak tujuan PO Keluar ini.',
                     summaryDate: 'Tanggal PO: tanggal dokumen PO Keluar dibuat.',
                     rincianItem: 'Rincian Item: daftar item yang dipesan, jumlah pesan, jumlah masuk, harga, dan subtotal.',
                     itemCount: 'Jumlah item: total baris item yang ada di PO Keluar ini.',
                     detailItemName: 'Nama Item: nama material/produk/jasa yang dipesan pada PO ini.',
                     satuan: 'Satuan: unit transaksi item, misalnya Kg, Pcs, Lot, atau Jasa.',
                     jumlahPesan: 'Jumlah Pesan: qty awal yang dipesan ke supplier/vendor.',
                     jumlahMasuk: 'Jumlah Masuk: qty yang sudah diterima atau tercatat masuk dari PO ini.',
                     hargaSatuan: 'Harga: harga satuan item sesuai PO.',
                     subtotal: 'Subtotal: nilai per item hasil qty dikali harga satuan.',
                     totalPo: 'Total: total nilai seluruh item pada PO Keluar.',
                     informasiPo: 'Informasi PO: ringkasan data header PO seperti nomor, tanggal, supplier, jenis PO, transaksi, dan referensi terkait.',
                     infoNoPo: 'No. PO: nomor dokumen PO Keluar.',
                     infoTanggalPo: 'Tanggal PO: tanggal PO Keluar dibuat.',
                     infoSupplierVendor: 'Supplier/Vendor: pihak tujuan PO Keluar.',
                     infoJenisPo: 'Jenis PO: kategori PO, misalnya Material, Produk, atau Jasa.',
                     infoJenisTransaksi: 'Jenis Transaksi: menunjukkan tujuan PO Keluar, misalnya Beli untuk pembelian biasa atau Titip Proses untuk pekerjaan vendor.',
                    infoProductionMaterial: 'Material Produksi: menunjukkan sumber material untuk PO Produk, apakah memakai material TRE atau material dari customer.',
                     materialColumnPrint: 'Tampilkan kolom Material di print: menentukan apakah kolom material ikut muncul pada cetakan PO Produk.',
                     infoPoAsal: 'PO Asal: referensi PO Keluar sebelumnya jika PO ini merupakan lanjutan proses.',
                     infoDirectSend: 'Kirim Langsung: menunjukkan apakah barang dikirim langsung ke pihak lain tanpa resmi masuk stok TRE.',
                     infoRelatedPoIn: 'PO Masuk Terkait: referensi PO pelanggan yang berhubungan dengan PO Keluar ini jika ada.',
                     infoDescription: 'Keterangan: catatan tambahan yang disimpan pada PO Keluar.',
                     progressPenerimaan: 'Progres Penerimaan: persentase dan jumlah barang/material yang sudah diterima dibanding total pesanan.',
                     progressReceivedQty: 'Sudah Diterima: jumlah barang/material yang sudah tercatat diterima dari PO ini.',
                     progressRemainingQty: 'Sisa Belum Diterima: jumlah qty yang masih belum diterima dari total pesanan PO.',
                     progressPaymentStatus: 'Status Payment: menunjukkan apakah PO ini sudah dibuatkan invoice/tagihan atau belum.',
                     progressTermin: 'Termin: jangka waktu pembayaran yang digunakan pada PO ini.',
                     progressShippingTo: 'Shipping To: lokasi tujuan pengiriman yang tercatat untuk PO ini.',
                     materialReceiptInfo: 'Keterangan Material Masuk: daftar surat jalan atau catatan penerimaan material yang sudah terhubung dengan PO Keluar ini.',
                     materialReceiptCount: 'Jumlah surat jalan: total surat jalan/material masuk yang tercatat untuk PO Keluar ini.',
                    materialReceiptRow: 'Baris material masuk: ringkasan surat jalan/material masuk yang terhubung dengan PO Keluar ini.',
                    materialReceiptNumber: 'Nomor surat jalan: nomor dokumen penerimaan material dari supplier/vendor.',
                    materialReceiptDate: 'Tanggal material masuk: tanggal material diterima atau dicatat masuk untuk PO ini.',
                    materialReceiptEmpty: 'Belum ada material masuk: area ini akan terisi setelah ada surat jalan/material masuk untuk PO ini.',
                    panelToggle: 'Buka/tutup panel: menampilkan atau menyembunyikan detail tambahan pada panel ini.',
                     back: 'Kembali: kembali ke daftar PO Keluar.',
                     lockNotice: 'Info terkunci: PO sudah dipakai transaksi lain, jadi nomor PO, supplier, jenis PO, dan item PO dikunci. Yang masih boleh diubah hanya keterangan dan info cetak.',
                     editableNotice: 'Info bisa diedit: PO Keluar belum dipakai transaksi lanjutan, jadi header dan item PO masih boleh diubah.',
                     usedBy: 'Dipakai di: menunjukkan transaksi lanjutan yang sudah memakai PO ini.',
                     usedReceptionQty: 'QTY Penerimaan Tercatat: jumlah penerimaan atau qty masuk yang sudah tercatat dari PO ini.',
                     usedMaterialReceipt: 'Penerimaan Material: jumlah transaksi material masuk yang sudah memakai PO ini.',
                     poNumberInput: 'No. PO Keluar: nomor dokumen PO yang dikirim ke supplier/vendor.',
                     dateInput: 'Tanggal PO: tanggal PO Keluar dibuat.',
                     supplierInput: 'Supplier/Vendor: pihak tujuan PO. Field ini dikunci jika PO sudah dipakai transaksi lanjutan.',
                     poTypeInput: 'Jenis PO: kategori PO, misalnya PO Material, PO Produk, atau PO Jasa.',
                     transactionTypeInput: 'Jenis Transaksi: tipe kebutuhan PO seperti beli atau titip proses.',
                     poOrigin: 'PO Asal: referensi PO Keluar sebelumnya jika transaksi ini merupakan lanjutan proses.',
                     relatedPoIn: 'PO Masuk Terkait: referensi PO pelanggan bila pembelian ini untuk kebutuhan pesanan tertentu.',
                     directSend: 'Kirim langsung: menandai barang dikirim langsung ke pihak lain dan tidak resmi masuk stok TRE.',
                     notes: 'Keterangan: catatan tambahan PO yang masih boleh diubah selama tidak mengganggu transaksi lanjutan.',
                     detailItemSection: 'Detail Item PO Keluar: daftar item yang dipesan ke supplier/vendor beserta qty, harga, dan subtotal.',
                     printInfo: 'Info cetak: data tambahan yang akan muncul pada lembar cetak PO.',
                     readonlyField: 'Field terkunci: data ini hanya bisa dilihat karena PO sudah dipakai transaksi lanjutan.',
                     labelDetail: 'Label Detail: judul kolom detail ukuran/info print yang tampil pada cetakan PO Keluar.',
                     labelUkuran: 'Label Ukuran: teks subjudul untuk kolom ukuran/info print pada lembar cetak PO Jasa.',
                     infoPrint: 'Info Print: detail ukuran atau keterangan item yang akan muncul pada lembar cetak PO.',
                     top: 'TOP: termin pembayaran yang dicetak di PO, misalnya 30 hari setelah invoice.',
                     systemPayment: 'System Payment: metode pembayaran yang akan ditampilkan di lembar cetak PO.',
                     shippingTo: 'Shipping To: lokasi atau tujuan pengiriman yang akan tercetak di PO.',
                     quoteNumber: 'Quot Number: nomor penawaran/quotation dari supplier jika ada.',
                     approvedBy: 'Approved By: nama pihak yang menyetujui PO untuk ditampilkan pada cetakan.',
                     discount: 'Discount: potongan nilai PO. Centang jika PO memakai diskon, lalu isi nominal/persentasenya sesuai kebutuhan.',
                     ppn: 'PPN 11%: centang jika harga item sudah termasuk PPN. Jika tidak dicentang, PPN ditambahkan saat print/invoice.',
                     pph23: 'PPH 23: centang jika PO memakai potongan PPH 23. Nilainya otomatis memakai 2%.',
                     notesCetak: 'Notes Cetak: catatan tambahan yang akan tampil di lembar cetak PO.',
                     saveChanges: 'Simpan Perubahan: menyimpan keterangan, info cetak, dan data lain yang masih boleh diubah sesuai status PO Keluar.',
                     rowNumber: 'No: nomor urut baris pada daftar PO Keluar.',
                     activeStatus: 'Status: menunjukkan apakah PO Keluar masih aktif atau sudah dinonaktifkan.',
                     noPo: 'No PO: nomor PO yang dikirim ke supplier/vendor.',
                     tanggal: 'Tanggal: tanggal PO Keluar dibuat.',
                     supplier: 'Supplier: pihak vendor/supplier tujuan PO.',
                     jenisPo: 'Jenis PO: membedakan PO Material, PO Produk, atau PO Jasa.',
                     poMaterial: 'PO Material: pesanan material ke supplier/vendor.',
                     poProduk: 'PO Produk: pesanan produk jadi ke supplier/vendor.',
                     poJasa: 'PO Jasa: pesanan jasa atau pekerjaan vendor, termasuk kebutuhan titip proses.',
                     transaksi: 'Transaksi: jenis kebutuhan PO, misalnya beli atau titip proses.',
                     beli: 'Beli: transaksi pembelian langsung ke supplier/vendor.',
                     titipProses: 'Titip Proses: item dikirim ke vendor untuk diproses, lalu progressnya dipantau dari penerimaan dan invoice.',
                     qty: 'QTY: total jumlah item yang dipesan ke supplier/vendor.',
                     nominal: 'Total Nominal: total nilai PO Keluar dari item dan harga yang dicatat.',
                     penerimaan: 'Status Penerimaan: progress material/produk yang sudah diterima, diterima sebagian, belum diterima, atau selesai.',
                     aksi: 'Aksi: tombol detail, edit, cetak, atau pembatalan sesuai status dan hak akses.',
                     detail: 'Detail: melihat rincian PO Keluar, item, dan progress penerimaan.',
                     viewButton: 'Lihat detail: membuka rincian PO Keluar beserta item dan progress penerimaannya.',
                     edit: 'Edit: mengubah data PO Keluar yang masih boleh diedit.',
                     editButton: 'Edit PO Keluar: membuka form perubahan untuk PO yang masih memenuhi aturan edit.',
                     cancel: 'Batal/nonaktif: membatalkan PO Keluar bila masih memenuhi aturan sistem.',
                     cancelButton: 'Batal/nonaktif PO: menonaktifkan PO Keluar sesuai aturan akses dan status transaksi.',
                     delete: 'Hapus: menghapus PO Keluar jika data belum terkunci oleh transaksi lanjutan.',
                     addItemRow: 'Tambah Item: menambahkan item yang dipilih ke daftar item PO ini. Item baru masuk ke tabel di bawah dan baru benar-benar tersimpan setelah PO disimpan.',
                     removeItemRow: 'Hapus Baris: menghapus item ini dari daftar sebelum PO disimpan/diperbarui. Beda dengan tombol Hapus di daftar PO yang menghapus keseluruhan PO.',
                     saveNewPo: 'Simpan PO Keluar: menyimpan PO baru ke supplier/vendor beserta semua item yang sudah ditambahkan ke daftar.',
                     dateRangeChip: 'Filter cepat tanggal: menampilkan PO Keluar dalam rentang waktu ini saja.',
                     filterApply: 'Tampilkan: menerapkan semua filter yang dipilih ke daftar PO Keluar.',
                     filterReset: 'Reset: mengembalikan semua filter dan pencarian ke kondisi awal.',
                     statusSelesai: 'Selesai: PO dianggap sudah selesai sesuai aturan penerimaan sistem.',
                     diterimaLengkap: 'Diterima Lengkap: seluruh qty pesanan sudah diterima.',
                     diterimaSebagian: 'Diterima Sebagian: baru sebagian qty yang diterima, sisanya masih perlu dipantau.',
                     belumDiterima: 'Belum Diterima: belum ada penerimaan material/produk untuk PO ini.',
                     statusBadge: 'Badge status PO Keluar: menunjukkan kondisi aktif/nonaktif, jenis PO, transaksi, atau progress penerimaan pada baris ini.'
                 },
                 'outstanding': {
                     search: 'Search: mencari outstanding berdasarkan nomor PO, kode produk, tanggal, atau status.',
                     filterButton: 'Filter Data: pilih status outstanding seperti belum selesai atau sudah selesai.',
                     filterToggle: 'Tombol filter: membuka pilihan rentang tanggal, status outstanding, dan pelanggan.',
                     dateRangeChip: 'Filter cepat tanggal: menampilkan outstanding dalam rentang waktu ini saja.',
                     filterApply: 'Tampilkan: menerapkan semua filter yang dipilih ke daftar outstanding.',
                     filterReset: 'Reset: mengembalikan semua filter dan pencarian ke kondisi awal.',
                     pelangganFilter: 'Pelanggan: batasi daftar outstanding untuk satu pelanggan tertentu saja.',
                     pageLength: 'Jumlah data: mengatur berapa banyak baris outstanding yang ditampilkan per halaman.',
                     printButton: 'Cetak: mencetak/export data outstanding sesuai status dan pelanggan yang dipilih.',
                     modalCancel: 'Batal: menutup jendela cetak tanpa mencetak apa pun.',
                     rowNumber: 'No: nomor urut baris pada daftar outstanding.',
                     table: 'Data Outstanding: daftar sisa pesanan dan sisa nilai tagihan yang perlu dipantau.',
                     noPo: 'No PO: nomor PO pelanggan yang masih punya progress pengiriman/tagihan.',
                     tanggal: 'Tgl. PO: tanggal PO pelanggan dibuat.',
                     productCode: 'Kode Produk: item produk yang masih dipantau sisa qty/tagihannya.',
                     qty: 'QTY: jumlah pesanan awal di PO.',
                     terkirim: 'Terkirim: qty yang sudah dikirim ke pelanggan.',
                     belumTerkirim: 'Belum Terkirim: sisa qty yang belum dikirim.',
                     nilaiTerkirim: 'Nilai Tagihan Terkirim: nominal dari qty yang sudah dikirim/ditagihkan.',
                     sisaTagihan: 'Sisa Nilai Tagihan: nominal yang masih perlu ditagihkan.',
                     action: 'Data ini dipakai untuk menentukan pengiriman berikutnya, invoice, dan pengecekan progress PO.'
                 },
                 'keuangan': {
                     invoiceOut: 'Invoice Out: kelola invoice tagihan ke pelanggan berdasarkan pengiriman atau surat jalan yang dipilih.',
                     invoiceIn: 'Invoice In: catat invoice dari supplier/vendor berdasarkan PO Keluar atau surat jalan/penerimaan terkait.',
                     reporting: 'Reporting: melihat laporan qty, harga jual, harga modal, dan margin untuk kebutuhan analisis keuangan.',
                     openButton: 'Buka: masuk ke fitur keuangan yang dipilih.',
                     back: 'Kembali: kembali ke halaman sebelumnya.',
                     aksi: 'Aksi: tombol untuk melihat detail, mencetak, mengubah status, atau memproses invoice ini.',

                     outBack: 'Kembali: kembali ke halaman hub Keuangan.',
                     outGenerateButton: 'Generate Invoice Out: membuat invoice tagihan baru ke pelanggan dari PO yang punya qty terkirim belum ditagih.',
                     outPembayaranButton: 'Catat Pembayaran: mencatat pembayaran yang masuk dari pelanggan dan mengalokasikannya ke invoice yang belum lunas.',
                     outTableNoInvoice: 'No. Invoice: nomor invoice tagihan ke pelanggan.',
                     outTableTanggal: 'Tanggal: tanggal invoice dibuat.',
                     outTableNoPo: 'No. PO: nomor PO pelanggan yang ditagih lewat invoice ini.',
                     outTablePelanggan: 'Pelanggan: nama customer yang ditagih.',
                     outTableGrandTotal: 'Grand Total: total nilai invoice setelah PPN, PPh 23, dan DP.',
                     outTableSudahBayar: 'Sudah Bayar: total pembayaran yang sudah dialokasikan ke invoice ini.',
                     outTableSisa: 'Sisa: sisa tagihan yang belum dibayar pelanggan.',
                     outTableStatus: 'Status: AKTIF (masih berjalan), SELESAI (sudah lunas), atau DIBATALKAN.',
                     outTableStatusBayar: 'Status Bayar: Belum Lunas, Dibayar Sebagian, atau Lunas.',
                     outTableNo: 'No: nomor urut baris pada daftar Invoice Out.',
                     outAksiLihat: 'Lihat: membuka detail invoice, rincian item, dan riwayat pembayaran.',
                     outAksiExtract: 'Extract File: mengunduh invoice dalam bentuk Print (PDF) atau Excel.',
                     outAksiPrint: 'Print: membuka/mencetak invoice ini dalam bentuk PDF.',
                     outAksiExcel: 'Excel: mengunduh rincian invoice ini dalam bentuk file Excel.',
                     outAksiTandaiLunas: 'Tandai Lunas: menandai invoice ini lunas tanpa mencatat pembayaran lewat menu Catat Pembayaran.',
                     outAksiBatalkan: 'Batalkan: membatalkan invoice supaya qty yang sudah ditagih bisa ditagihkan kembali lewat invoice lain.',
                     outAksiHapus: 'Hapus: menghapus permanen invoice yang sudah dibatalkan.',

                     outFormPilihPo: 'Pilih PO: cari PO pelanggan yang punya qty terkirim dan belum ditagihkan penuh.',
                     outFormSuratJalanTable: 'Daftar Surat Jalan: pilih satu atau beberapa surat jalan dari PO ini yang mau digabung jadi 1 invoice. Surat jalan yang qty-nya sudah full ditagihkan tidak muncul di sini.',
                     outFormTampilkanItem: 'Tampilkan Item Invoice: memuat item dari surat jalan yang dicentang supaya bisa dicek harganya sebelum invoice disimpan.',
                     outFormNoInvoice: 'No. Invoice: nomor invoice yang akan diterbitkan ke pelanggan.',
                     outFormTanggalInvoice: 'Tanggal Invoice: tanggal invoice ini diterbitkan.',
                     outFormSigner: 'Nama & Jabatan Penandatangan: identitas yang tercetak sebagai penandatangan pada lembar invoice.',
                     outFormPpn: 'PPN: centang kalau invoice ini kena PPN, lalu atur persentasenya.',
                     outFormPph: 'PPh 23: centang kalau invoice ini kena potongan PPh 23, lalu atur persentasenya.',
                     outFormDp: 'DP: centang kalau ada uang muka yang mengurangi tagihan invoice ini, lalu atur persentasenya.',
                     outFormBankInfo: 'Info Rekening: nama pemilik rekening, bank, nomor rekening, dan NPWP yang tercetak di invoice untuk pembayaran pelanggan.',
                     outFormHargaInput: 'Harga: harga satuan item, otomatis dari master produk tapi bisa diubah manual sebelum invoice disimpan.',
                     outFormSimpan: 'Simpan Invoice: menerbitkan invoice baru ke pelanggan berdasarkan item dan pengaturan yang sudah diisi.',

                     outDetailTandaiLunas: 'Tandai Lunas: menandai invoice ini lunas langsung tanpa mencatat pembayaran lewat menu Catat Pembayaran.',
                     outDetailPrint: 'Print: membuka/mencetak lembar invoice ini.',
                     outDetailRiwayatBayar: 'Riwayat Pembayaran: daftar pembayaran yang sudah dialokasikan ke invoice ini.',

                     outBayarPilihPelanggan: 'Pelanggan: pilih pelanggan yang mau dicatat pembayarannya. Hanya menampilkan pelanggan yang masih punya invoice belum lunas.',
                     outBayarTampilkan: 'Tampilkan Invoice: memuat daftar invoice belum lunas milik pelanggan yang dipilih.',
                     outBayarNoPembayaran: 'No. Pembayaran/Bukti: nomor referensi pembayaran. Kosongkan untuk dibuatkan otomatis oleh sistem.',
                     outBayarTanggalBayar: 'Tanggal Bayar: tanggal uang diterima dari pelanggan.',
                     outBayarNominal: 'Nominal Pembayaran: total uang yang diterima dari pelanggan, akan dibagi ke invoice yang dicentang di bawah.',
                     outBayarKeterangan: 'Keterangan: catatan tambahan pembayaran, misalnya rekening tujuan atau info bukti transfer.',
                     outBayarCheckbox: 'Centang invoice ini untuk dialokasikan sebagian dari nominal pembayaran di atas.',
                     outBayarAutoAlokasi: 'Auto Alokasi dari Nominal: membagi otomatis nominal pembayaran ke invoice-invoice yang dicentang, dari yang paling atas.',
                     outBayarAlokasiInput: 'Alokasi Bayar: nominal yang dialokasikan ke invoice ini. Total semua alokasi harus sama dengan Nominal Pembayaran.',
                     outBayarSimpan: 'Simpan Pembayaran: mencatat pembayaran dan mengurangi sisa tagihan pada invoice yang dialokasikan.',

                     inBack: 'Kembali: kembali ke halaman hub Keuangan.',
                     inCatatButton: 'Catat Invoice In: mencatat invoice tagihan dari supplier/vendor berdasarkan PO Keluar atau transaksi material/produk masuk.',
                     inTableNoInvoice: 'No. Invoice Supplier: nomor invoice yang diterbitkan oleh supplier/vendor.',
                     inTableTanggal: 'Tanggal: tanggal invoice supplier dicatat.',
                     inTableSupplier: 'Supplier: pihak vendor/supplier yang menerbitkan invoice.',
                     inTableSumber: 'Sumber: transaksi asal invoice ini, dari PO Keluar atau dari material/produk masuk (termasuk surat jalannya kalau ada).',
                     inTableGrandTotal: 'Grand Total: total nilai invoice setelah PPN, PPh 23, dan DP.',
                     inTableStatus: 'Status: AKTIF, Lunas, atau DIBATALKAN.',
                     inTableNo: 'No: nomor urut baris pada daftar Invoice In.',
                     inAksiLihat: 'Lihat: membuka detail invoice, rincian item, file invoice, dan bukti transfer.',
                     inAksiBatalkan: 'Batalkan: membatalkan pencatatan invoice ini.',
                     inAksiHapus: 'Hapus: menghapus permanen invoice yang sudah dibatalkan.',

                     inFormPilihSumber: 'Pilih Sumber: cari transaksi penerimaan (material/produk masuk) atau PO Keluar dari supplier/vendor yang mau dibuatkan invoice.',
                     inFormSuratJalanTable: 'Daftar Surat Jalan: pilih satu atau beberapa surat jalan dari PO Keluar ini yang mau digabung jadi 1 Invoice In. Surat jalan yang sudah punya Invoice In tidak muncul lagi di sini.',
                     inFormTampilkanItem: 'Tampilkan Item Invoice: memuat item dari surat jalan/transaksi yang dipilih supaya bisa dicek harganya sebelum invoice disimpan.',
                     inFormNoInvoice: 'No. Invoice Supplier: nomor invoice sesuai dokumen dari supplier/vendor.',
                     inFormTanggalInvoice: 'Tanggal Invoice: tanggal invoice yang tertera di dokumen supplier.',
                     inFormUploadFile: 'Upload File Invoice: lampiran file invoice dari supplier/vendor. Opsional, bisa diupload belakangan lewat halaman detail.',
                     inFormPpn: 'PPN: centang kalau invoice ini kena PPN, lalu atur persentasenya.',
                     inFormPph: 'PPh 23: centang kalau invoice ini kena potongan PPh 23, lalu atur persentasenya.',
                     inFormDp: 'DP: centang kalau supplier sudah menerima uang muka yang mengurangi tagihan invoice ini.',
                     inFormHargaInput: 'Harga Satuan: harga beli per item. Untuk PO Keluar otomatis terisi dari PO, untuk transaksi masuk harus diisi manual sesuai invoice supplier.',
                     inFormSimpan: 'Simpan Invoice In: mencatat invoice dari supplier/vendor berdasarkan item dan pengaturan yang sudah diisi.',

                     inDetailStatus: 'Status: AKTIF, Lunas, atau DIBATALKAN.',
                     inDetailTanggalLunas: 'Tanggal Lunas: waktu invoice ini ditandai lunas, biasanya otomatis saat bukti transfer diupload.',
                     inDetailFileInvoice: 'File Invoice: lampiran dokumen invoice dari supplier. Lihat file yang sudah ada, atau Upload/Ganti kalau belum ada/mau diperbarui.',
                     inDetailHapusFile: 'Hapus: menghapus file invoice yang sudah diupload. Data Invoice In tetap tersimpan.',
                     inDetailBuktiTransfer: 'Bukti Transfer: lampiran bukti pembayaran ke supplier. Upload bukti ini akan otomatis menandai invoice Lunas.',
                     inDetailSumberInfo: 'Sumber & No. Transaksi: transaksi asal invoice ini (PO Keluar atau material/produk masuk beserta surat jalannya).',
                     inDetailLihatFile: 'Lihat: membuka file invoice yang sudah diupload di tab baru.',
                     inDetailGantiFile: 'Upload/Ganti: mengupload file invoice baru, menggantikan yang lama kalau sudah ada.',
                     inDetailLihatBukti: 'Lihat: membuka bukti transfer yang sudah diupload di tab baru.',
                     inDetailUploadBukti: 'Upload/Ganti: mengupload bukti transfer ke supplier. Setelah disimpan, status invoice otomatis jadi Lunas.',

                     inUploadFileInfo: 'Ringkasan invoice yang akan dilampiri file ini.',
                     inUploadFileInput: 'File Invoice: pilih file invoice dari supplier untuk dilampirkan. Format PDF/JPG/JPEG/PNG, maksimal 10 MB.',
                     inUploadFileSubmit: 'Upload File Invoice: menyimpan file yang dipilih sebagai lampiran invoice ini.',

                     inUploadBuktiInfo: 'Ringkasan invoice yang akan dilampiri bukti transfer ini.',
                     inUploadBuktiInput: 'Bukti Transfer: pilih file bukti transfer ke supplier. Format PDF/JPG/JPEG/PNG, maksimal 5 MB. Setelah disimpan, status invoice otomatis Lunas.',
                     inUploadBuktiSubmit: 'Upload & Tandai Lunas: menyimpan bukti transfer dan otomatis mengubah status invoice ini jadi Lunas.',

                     repTanggalAwal: 'Tanggal Awal: batas awal periode laporan.',
                     repTanggalAkhir: 'Tanggal Akhir: batas akhir periode laporan.',
                     repPelanggan: 'Pelanggan: batasi laporan untuk satu pelanggan tertentu saja, atau biarkan kosong untuk semua pelanggan.',
                     repTampilkan: 'Tampilkan: memuat ulang laporan sesuai periode dan filter yang dipilih.',
                     repMarginTab: 'Tab Margin: laporan qty tertagih, harga modal, harga jual, dan margin per produk pada periode yang dipilih.',
                     repMarginSummary: 'Ringkasan: total produk, total qty (pcs/kg), dan total margin pada periode yang dipilih.',
                     repMarginMaterial: 'Material: harga modal material per pcs. Otomatis terkunci begitu harganya lengkap dari Invoice In, tapi bisa diubah manual untuk simulasi.',
                     repMarginMaterialReset: 'Reset: mengembalikan nilai Material ke hasil hitungan otomatis sistem.',
                     repMarginJasa: 'Jasa: biaya jasa per pcs, diisi manual kalau ada biaya jasa produksi/vendor.',
                     repMarginTrpoh: 'Transport & OH: biaya transport dan overhead per pcs, diisi manual sebagai simulasi tambahan.',
                     repMarginTotalModal: 'Total Modal: penjumlahan Material + Jasa + Transport & OH, otomatis terhitung tapi bisa ditimpa manual untuk simulasi.',
                     repMarginTotalModalReset: 'Reset: mengembalikan Total Modal ke hasil penjumlahan otomatis Material + Jasa + Transport & OH.',
                     repMarginHargaJual: 'Jual / Pcs: harga jual per pcs, otomatis dari Invoice Out tapi bisa diubah manual untuk simulasi margin.',
                     repMarginHargaJualReset: 'Reset: mengembalikan Jual / Pcs ke harga jual asli dari Invoice Out.',
                     repMarginUnit: 'Margin / Pcs: selisih Jual / Pcs dikurangi Total Modal.',
                     repMarginProsentase: 'Prosentase: margin per pcs dibagi Total Modal, dalam persen.',
                     repMarginTotal: 'Total Margin: Margin / Pcs dikali Qty (Pcs) produk ini.',
                     repPiutangTab: 'Tab Piutang: daftar invoice ke pelanggan yang belum lunas pada periode yang dipilih.',
                     repPiutangSummary: 'Ringkasan: jumlah invoice belum lunas dan total sisa piutang pada periode ini.',
                     repPiutangNo: 'No: nomor urut baris pada daftar piutang.',
                     repPiutangNoInvoice: 'No Invoice: nomor invoice tagihan ke pelanggan yang masih punya sisa piutang.',
                     repPiutangTanggal: 'Tanggal: tanggal invoice dibuat.',
                     repPiutangNoPo: 'No PO: nomor PO pelanggan yang ditagih lewat invoice ini.',
                     repPiutangPelanggan: 'Pelanggan: nama customer yang ditagih.',
                     repPiutangGrandTotal: 'Grand Total: total nilai invoice setelah PPN, PPh 23, dan DP.',
                     repPiutangSudahBayar: 'Sudah Bayar: total pembayaran yang sudah dialokasikan ke invoice ini.',
                     repPiutangSisa: 'Sisa: sisa piutang yang belum dibayar pelanggan.',
                     repPiutangStatus: 'Status: status pembayaran invoice ini (Belum Lunas atau Dibayar Sebagian).',
                     repHutangTab: 'Tab Hutang: daftar invoice dari supplier/vendor yang belum lunas pada periode yang dipilih.',
                     repHutangSummary: 'Ringkasan: jumlah invoice supplier belum lunas dan total sisa hutang pada periode ini.',
                     repHutangNo: 'No: nomor urut baris pada daftar hutang.',
                     repHutangNoInvoice: 'No Invoice: nomor invoice dari supplier/vendor yang masih punya sisa hutang.',
                     repHutangTanggal: 'Tanggal: tanggal invoice supplier dicatat.',
                     repHutangSumber: 'Sumber: transaksi asal invoice ini, dari PO Keluar atau dari material/produk masuk.',
                     repHutangSupplier: 'Supplier/Vendor: pihak yang menerbitkan invoice.',
                     repHutangGrandTotal: 'Grand Total: total nilai invoice setelah PPN, PPh 23, dan DP.',
                     repHutangStatus: 'Status: status invoice ini (AKTIF, belum dibayar/dilunasi).',
                     repCashflowTab: 'Tab Cashflow: pergerakan kas masuk (pembayaran dari pelanggan) dan kas keluar (pembayaran ke supplier) pada periode yang dipilih.',
                     repCashflowSummary: 'Ringkasan: total kas masuk, kas keluar, dan selisihnya (net cashflow) pada periode ini.',
                     repCashflowNo: 'No: nomor urut baris pada daftar cashflow.',
                     repCashflowTanggal: 'Tanggal: tanggal pembayaran diterima/dikeluarkan.',
                     repCashflowJenis: 'Jenis: Masuk (pembayaran dari pelanggan) atau Keluar (pembayaran ke supplier).',
                     repCashflowNomor: 'No Bukti/Invoice: nomor referensi pembayaran atau invoice terkait.',
                     repCashflowPihak: 'Pihak: pelanggan atau supplier/vendor terkait transaksi ini.',
                     repCashflowKeterangan: 'Keterangan: catatan tambahan pada transaksi pembayaran ini.',
                     repCashflowNominal: 'Nominal: jumlah uang yang masuk atau keluar pada transaksi ini.',
                     repOmzetTab: 'Tab Omzet per Surat Jalan: rekap omzet pengiriman per minggu dan per kategori dalam satu periode/bulan.',
                     repOmzetPeriodePrev: 'Sebelumnya: pindah ke periode sebelumnya.',
                     repOmzetPeriodeNext: 'Berikutnya: pindah ke periode berikutnya.',
                     repOmzetWeekTotal: 'Total omzet pengiriman pada minggu ini.',
                     repOmzetKategoriTotal: 'Rekap omzet dikelompokkan per kategori produk untuk tahap ini.',
                     repOmzetGrandTotal: 'Total seluruh tagihan pengiriman pada periode/bulan ini.'
                 },
                 'stok-material': {
                     table: 'Data Stok Material: ringkasan stok material yang tersedia per gudang.',
                     search: 'Search: mencari material berdasarkan kode material.',
                     pageLength: 'Show entries: mengatur jumlah baris yang ditampilkan per halaman.',
                     rowNumber: 'No: nomor urut baris.',
                     kodeMaterial: 'Kode Material: kode unik material di sistem.',
                     stokCikarang: 'Stok Cikarang: jumlah stok material yang ada di gudang Cikarang.',
                     stokCirebon: 'Stok Cirebon: jumlah stok material yang ada di gudang Cirebon.',
                     totalStok: 'Total Stok: penjumlahan stok dari semua gudang.'
                 },
                 'kebutuhan-material': {
                     filterPelanggan: 'Pelanggan: batasi forecast untuk satu pelanggan tertentu saja.',
                     filterProduk: 'Produk: batasi forecast untuk satu produk tertentu saja.',
                     filterMaterial: 'Material: batasi forecast untuk satu material tertentu saja.',
                     filterStatus: 'Status: pilih Aman/Kurang/Data Belum Lengkap untuk menyaring hasil.',
                     btnReset: 'Reset: mengembalikan semua filter ke kondisi awal dan menghitung ulang.',
                     btnCetak: 'Cetak: membuka hasil forecast dalam format cetak sesuai filter yang aktif.',
                     btnTampilkan: 'Hitung Kebutuhan: menjalankan forecast kebutuhan material berdasarkan outstanding PO dan stok produk saat ini.',
                     waktuHitung: 'Waktu perhitungan terakhir kali forecast ini dijalankan.',
                     ringkasanTotal: 'Material Dibutuhkan: jumlah jenis material yang dibutuhkan untuk memenuhi outstanding PO.',
                     ringkasanKurang: 'Stok Kurang: jumlah material yang stoknya tidak cukup untuk memenuhi kebutuhan.',
                     ringkasanBelum: 'Peringatan Data: jumlah produk yang datanya belum lengkap sehingga kebutuhan materialnya belum bisa dihitung akurat.',
                     ringkasanVendor: 'Material Customer: jumlah produk yang materialnya disediakan customer, bukan dari stok TRE.',
                     ringkasanFullBeliJadi: 'Beli Jadi dari Vendor: jumlah produk yang dibeli jadi dari vendor sehingga tidak butuh material.',
                     peringatanList: 'Data yang perlu dilengkapi: daftar produk yang datanya (berat material, wise, dll.) belum lengkap sehingga hasil forecast-nya belum akurat.',
                     vendorSupplyList: 'Produk dengan material dari customer: daftar produk yang material produksinya disediakan customer, jadi tidak dihitung dari stok TRE.',
                     fullBeliJadiList: 'Produk beli jadi dari vendor: daftar produk yang dibeli jadi, jadi tidak butuh material apa pun.',
                     colNo: 'No: nomor urut baris.',
                     colKodeMaterial: 'Kode Material: kode unik material di sistem.',
                     colNamaMaterial: 'Nama Material: nama material yang dibutuhkan.',
                     colSatuan: 'Satuan: satuan ukur material ini.',
                     colKebutuhan: 'Kebutuhan: total material yang dibutuhkan untuk memenuhi outstanding PO dan stok minimum.',
                     colStokCikarang: 'Stok Cikarang: stok material saat ini di gudang Cikarang.',
                     colStokCirebon: 'Stok Cirebon: stok material saat ini di gudang Cirebon.',
                     colTotalStok: 'Total Stok: penjumlahan stok dari semua gudang.',
                     colStokMinimum: 'Stok Minimum: batas stok minimum yang harus selalu tersedia untuk material ini.',
                     colKekurangan: 'Kekurangan: selisih kebutuhan dikurangi total stok. Kalau lebih besar dari 0, berarti stok belum cukup.',
                     colSaranBeli: 'Saran Beli: jumlah material yang disarankan untuk dibeli supaya kebutuhan dan stok minimum terpenuhi.',
                     colStatus: 'Status: Aman (stok cukup), Kurang (stok tidak cukup), atau Data Belum Lengkap.',
                     colDetail: 'Detail: melihat asal perhitungan kebutuhan material ini, per produk dan per PO.',
                     modalDetail: 'Detail perhitungan: rincian outstanding, stok produk, dan kebutuhan material per produk yang menyusun angka kebutuhan di baris ini.'
                 },
                 'material-terbuang': {
                     filterPelanggan: 'Pelanggan: batasi estimasi waste untuk satu pelanggan tertentu saja.',
                     filterProduk: 'Produk: batasi estimasi waste untuk satu produk tertentu saja.',
                     filterMaterial: 'Material: batasi estimasi waste untuk satu material tertentu saja.',
                     btnReset: 'Reset: mengembalikan semua filter ke kondisi awal dan menghitung ulang.',
                     btnTampilkan: 'Hitung Waste: menjalankan estimasi material waste berdasarkan outstanding PO, Berat Material Terpakai, dan Wise per produk.',
                     waktuHitung: 'Waktu perhitungan terakhir kali estimasi ini dijalankan.',
                     ringkasanTotalWaste: 'Total Estimasi Waste: total perkiraan material yang terbuang (Kg) untuk memenuhi outstanding PO saat ini.',
                     ringkasanBelum: 'Peringatan Data: jumlah produk yang datanya (berat material, Wise) belum lengkap sehingga estimasi waste-nya belum akurat.',
                     ringkasanVendor: 'Material Customer: jumlah produk yang materialnya disediakan customer, jadi tidak dihitung wastenya di sini.',
                     ringkasanFullBeliJadi: 'Beli Jadi dari Vendor: jumlah produk yang dibeli jadi dari vendor sehingga tidak ada waste material.',
                     peringatanList: 'Data yang perlu dilengkapi: daftar produk yang datanya belum lengkap sehingga estimasi waste-nya belum akurat.',
                     vendorSupplyList: 'Produk dengan material dari customer: daftar produk yang material produksinya disediakan customer.',
                     fullBeliJadiList: 'Produk beli jadi dari vendor: daftar produk yang dibeli jadi, jadi tidak ada waste material.',
                     colNo: 'No: nomor urut baris.',
                     colKodeMaterial: 'Kode Material: kode unik material di sistem.',
                     colNamaMaterial: 'Nama Material: nama material yang diperkirakan terbuang.',
                     colSatuan: 'Satuan: satuan ukur material ini.',
                     colKebutuhan: 'Kebutuhan Material: total material yang dibutuhkan untuk memenuhi outstanding PO (sebelum dikurangi waste).',
                     colWaste: 'Estimasi Waste: perkiraan jumlah material yang terbuang, dihitung dari kebutuhan dikali persentase Wise produk.',
                     colPersenWaste: '% Waste: persentase Wise (perkiraan susut produksi) yang dipakai untuk menghitung estimasi waste.',
                     colDetail: 'Detail: melihat asal perhitungan waste material ini, per produk dan per PO.',
                     modalDetail: 'Detail perhitungan: rincian perlu produksi, Wise, dan estimasi waste per produk yang menyusun angka waste di baris ini.'
                 },
                 'material-masuk': {
                     back: 'Kembali: kembali ke daftar Material Masuk.',
                     inputButton: 'Input Transaksi Material Masuk: mencatat material yang diterima dari supplier, konsinyasi, PO Keluar, atau adjustment stok.',
                     search: 'Search: mencari transaksi material masuk.',
                     filterToggle: 'Tombol filter: membuka pilihan periode tanggal dan jumlah data.',
                     filterChip: 'Filter cepat tanggal: menampilkan transaksi dalam rentang waktu ini saja.',
                     filterApply: 'Tampilkan: menerapkan filter tanggal ke daftar material masuk.',
                     filterReset: 'Reset: mengembalikan semua filter dan pencarian ke kondisi awal.',
                     pageLength: 'Show Data: mengatur jumlah baris yang ditampilkan per halaman.',
                     colNo: 'No: nomor urut baris.',
                     colNoInvoice: 'No. Invoice: nomor invoice dari supplier untuk transaksi ini (kalau ada).',
                     colNoSuratJalan: 'No Surat Jalan: nomor surat jalan pengiriman material dari supplier/sumbernya.',
                     colTanggal: 'Tanggal: tanggal material ini diterima/dicatat.',
                     colSupplierSumber: 'Supplier/Sumber: pihak asal material -- supplier, pelanggan (konsinyasi), atau adjustment stok.',
                     colTotalBerat: 'Total Berat/Ukuran: total kuantitas material yang masuk pada transaksi ini.',
                     colGudang: 'Gudang: lokasi gudang tempat material ini disimpan.',
                     aksiEdit: 'Edit: mengubah item atau melengkapi No. Invoice transaksi material masuk ini.',
                     aksiHapus: 'Hapus: menghapus transaksi material masuk ini beserta seluruh itemnya.',
                     formTanggal: 'Tanggal: tanggal material ini diterima.',
                     formNoInvoice: 'No. Invoice: nomor invoice dari supplier (opsional, bisa dilengkapi belakangan lewat Edit).',
                     formNoDo: 'No Surat Jalan: nomor surat jalan pengiriman. Wajib diisi kecuali sumbernya Adjustment Stok.',
                     formSumberMaterial: 'Sumber Material: asal material -- Beli dari Supplier, Adjustment Stok (koreksi tanpa transaksi), atau Konsinyasi dari Pelanggan (dititipkan, bukan dibeli).',
                     formSupplier: 'Cari Supplier: pilih supplier yang mengirim material ini (untuk sumber Beli dari Supplier).',
                     formPelanggan: 'Pelanggan (Sumber Konsinyasi): pelanggan yang menitipkan material ini untuk diproses.',
                     formGudang: 'Lokasi Gudang: gudang tempat material ini akan disimpan.',
                     formPoKeluar: 'Pilih PO Keluar: hubungkan material masuk ini dengan PO Keluar material terkait (opsional), supaya progres penerimaan PO ikut terupdate.',
                     itemPoKeluarTable: 'Item PO Keluar: klik salah satu baris untuk mengisi form item di bawah, lalu isi Qty yang datang (boleh sebagian dari sisa).',
                     formKodeMaterial: 'Kode Material: pilih material yang diterima.',
                     formNamaMaterial: 'Nama Material: otomatis terisi dari kode material yang dipilih.',
                     formStok: 'Stok: stok material saat ini di gudang yang dipilih, sebagai informasi sebelum menambah qty.',
                     formQty: 'Qty: jumlah material yang diterima pada baris item ini.',
                     tombolSimpanItem: 'Simpan Item: menambahkan item ke draft transaksi material masuk sebelum transaksi diselesaikan.',
                     tombolReload: 'Reload Data: mengosongkan input item supaya bisa memilih ulang data material.',
                     draftTable: 'Draft Item Material Masuk: daftar item yang sudah ditambahkan sebelum transaksi disimpan final.',
                     tombolSelesaiTransaksi: 'Selesai Transaksi: menyimpan transaksi material masuk beserta semua item draft.',
                     editNoInvoice: 'No. Invoice: lengkapi atau ubah nomor invoice supplier untuk transaksi ini.',
                     editSimpanInvoice: 'Simpan No. Invoice: menyimpan perubahan nomor invoice.',
                     editHeaderInfo: 'Informasi transaksi: No Surat Jalan, tanggal, supplier, dan gudang tujuan material masuk ini.',
                     editCariMaterial: 'Cari Material: mencari dan memilih material lain untuk item ini.',
                     editTombolEditItem: 'Simpan: menyimpan perubahan qty/material pada item yang sedang diedit.',
                     editTombolBatal: 'Batal: membatalkan proses edit item yang sedang berjalan dan mengembalikan form ke mode tambah item baru.',
                     editItemTable: 'Daftar Item: item material yang sudah tercatat pada transaksi ini, beserta qty dan aksinya.'
                 },
                 'pemakaian-material': {
                     info: 'Halaman ini otomatis mencatat material yang kepakai untuk Produksi begitu ada transaksi "Selesai Produksi" -- tidak perlu input manual di sini.',
                     search: 'Search: mencari pemakaian material berdasarkan produksi, produk, atau material.',
                     filterToggle: 'Tombol filter: membuka pilihan periode tanggal dan jumlah data.',
                     filterChip: 'Filter cepat tanggal: menampilkan pemakaian dalam rentang waktu ini saja.',
                     filterApply: 'Tampilkan: menerapkan filter tanggal ke daftar pemakaian material.',
                     filterReset: 'Reset: mengembalikan semua filter dan pencarian ke kondisi awal.',
                     pageLength: 'Show Data: mengatur jumlah baris yang ditampilkan per halaman.',
                     colNo: 'No: nomor urut baris.',
                     colNoProduksi: 'No. Produksi: nomor transaksi produksi yang memakai material ini.',
                     colTanggal: 'Tanggal: tanggal produksi/pemakaian material dicatat.',
                     colProdukDibuat: 'Produk yang Dibuat: produk hasil produksi yang memakai material ini.',
                     colKodeMaterial: 'Kode Material: kode unik material yang dipakai.',
                     colNamaMaterial: 'Nama Material: nama material yang dipakai untuk produksi ini.',
                     colQtyTerpakai: 'Qty Terpakai: jumlah material yang keluar/dipakai pada transaksi produksi ini.',
                     colSatuan: 'Satuan: satuan ukur material yang dipakai.',
                     colGudang: 'Gudang: lokasi gudang asal material yang dipakai.'
                 },
                 'produk-masuk': {
                     inputButton: 'Input Transaksi Produk Masuk: mencatat produk jadi yang masuk ke stok, baik dari pembelian supplier/PO Keluar, produksi, maupun adjustment stok.',
                     tabStok: 'Tab Stok Saat Ini: melihat stok produk per gudang beserta sisa PO, qty terkirim, dan selisih kekurangan/kelebihan produksi.',
                     tabSupplier: 'Tab Supplier / PO Out: daftar transaksi produk masuk yang dibeli dari supplier atau diterima dari PO Keluar.',
                     tabAdjustment: 'Tab Adjustment: daftar koreksi stok produk yang bukan dari transaksi pembelian/produksi normal.',
                     tabProduksi: 'Tab Dari Produksi: daftar produk yang masuk ke stok hasil produksi (memakai material).',
                     filterKategori: 'Kategori: batasi tabel stok untuk satu kategori produk saja.',
                     filterMaterialStok: 'Material: batasi tabel stok untuk produk yang memakai material tertentu.',
                     filterPelangganStok: 'Pelanggan: batasi tabel stok untuk produk milik pelanggan tertentu.',
                     btnCetakStok: 'Cetak Laporan: mengunduh laporan stok sesuai filter kategori/material/pelanggan yang aktif.',
                     search: 'Search: mencari data pada tabel di tab ini.',
                     filterToggle: 'Tombol filter: membuka pilihan filter dan jumlah data.',
                     filterChip: 'Filter cepat tanggal: menampilkan data dalam rentang waktu ini saja.',
                     filterApply: 'Tampilkan: menerapkan filter yang dipilih ke tabel.',
                     filterReset: 'Reset: mengembalikan semua filter dan pencarian ke kondisi awal.',
                     pageLength: 'Show entries: mengatur jumlah baris yang ditampilkan per halaman.',
                     colNo: 'No: nomor urut baris.',
                     colKodeBarang: 'Kode Barang: kode unik produk di sistem.',
                     colStokCikarang: 'Stok Cikarang: jumlah stok produk yang ada di gudang Cikarang.',
                     colStokCirebon: 'Stok Cirebon: jumlah stok produk yang ada di gudang Cirebon.',
                     colTotalStok: 'Total Stok: penjumlahan stok dari semua gudang.',
                     colTotalSisaPo: 'Total Sisa PO: sisa qty PO pelanggan untuk produk ini yang belum terkirim.',
                     colQtyTerkirim: 'QTY Terkirim: total qty produk ini yang sudah dikirim ke pelanggan.',
                     colKekuranganProduksi: 'Kekurangan Produksi: qty yang masih perlu diproduksi karena stok belum cukup untuk memenuhi sisa PO.',
                     colKelebihanProduksi: 'Kelebihan Produksi: qty stok yang melebihi kebutuhan sisa PO saat ini.',
                     colNoPo: 'No PO: nomor PO Keluar sumber produk masuk ini.',
                     colTanggal: 'Tanggal: tanggal produk ini dicatat masuk.',
                     colSupplier: 'Supplier: pihak yang mengirim produk ini.',
                     colQtyPcs: 'QTY: jumlah produk yang masuk pada transaksi ini.',
                     colTotalBerat: 'Total Berat: total berat/ukuran produk yang masuk pada transaksi ini.',
                     colGudangTujuan: 'Gudang Tujuan: lokasi gudang tempat produk ini disimpan.',
                     colNoAdjustment: 'No Adjustment: nomor transaksi koreksi stok ini.',
                     colNoProduksi: 'No. Produksi: nomor transaksi produksi yang menghasilkan produk ini.',
                     colKodeProduk: 'Kode Produk: kode unik produk hasil produksi.',
                     colNamaProduk: 'Nama Produk: nama produk hasil produksi ini.',
                     colQtyDiproduksi: 'Qty Diproduksi: jumlah produk yang dihasilkan pada transaksi produksi ini.',
                     colGudang: 'Gudang: lokasi gudang tempat produk hasil produksi disimpan.',
                     colKeterangan: 'Keterangan: catatan tambahan transaksi produksi ini.',
                     aksiEdit: 'Edit: mengubah item transaksi produk masuk ini.',
                     aksiHapus: 'Hapus: menghapus transaksi produk masuk ini beserta seluruh itemnya.',
                     aksiCetak: 'Cetak Faktur: mencetak dokumen faktur transaksi produk masuk ini.',
                     aksiEditProduksi: 'Edit: membuka halaman ubah data transaksi produksi ini.',
                     aksiHapusProduksi: 'Hapus: menghapus transaksi produksi ini. Stok material yang tadi dipakai dikembalikan, stok produk hasil produksi dikurangi lagi.',
                     formSumberProduk: 'Sumber Produk: asal produk masuk -- Beli dari Supplier (dari PO Keluar), Adjustment Stok (koreksi), Produksi dari Material, atau Produksi Material Pelanggan.',
                     formTanggalTransaksi: 'Tanggal Transaksi: tanggal produk ini dicatat masuk.',
                     formNoPo: 'No PO: pilih PO Keluar yang produknya sudah datang. Wajib diisi untuk sumber Beli dari Supplier.',
                     formSupplier: 'Cari Supplier: pilih supplier yang mengirim produk ini.',
                     formGudangTujuan: 'Gudang Tujuan: gudang tempat produk ini akan disimpan.',
                     itemPoKeluarTable: 'Item PO Keluar: klik salah satu baris untuk mengisi form item di bawah, lalu isi Qty yang datang (boleh sebagian dari sisa).',
                     formKodeProdukItem: 'Kode Produk: pilih produk yang diterima.',
                     formNamaProdukItem: 'Nama Produk: otomatis terisi dari kode produk yang dipilih.',
                     formBeratItem: 'Berat/Ukuran: berat atau ukuran satuan produk ini.',
                     formStokItem: 'Stok: stok produk saat ini di gudang yang dipilih, sebagai informasi sebelum menambah qty.',
                     formQtyItem: 'Qty: jumlah produk yang diterima pada baris item ini.',
                     tombolSimpanItem: 'Simpan Item: menambahkan item ke draft transaksi produk masuk sebelum transaksi diselesaikan.',
                     tombolReload: 'Reload Data: mengosongkan input item supaya bisa memilih ulang data produk.',
                     draftTable: 'Draft Item Produk Masuk: daftar item yang sudah ditambahkan sebelum transaksi disimpan final.',
                     tombolSelesaiTransaksi: 'Selesai Transaksi: menyimpan transaksi produk masuk beserta semua item draft.',
                     formKodeProdukProduksi: 'Kode Produk: pilih produk yang dihasilkan dari produksi ini.',
                     formStokProduksi: 'Stok: stok produk ini saat ini, sebagai informasi sebelum menambah qty produksi.',
                     formQtyDiproduksi: 'Qty Diproduksi: jumlah produk yang dihasilkan dari produksi ini.',
                     tombolTambahDaftarProduksi: 'Tambah ke Daftar: menambahkan produk ke daftar produksi lokal di layar ini. Belum tersimpan ke server sampai Selesai Transaksi diklik.',
                     tombolResetProduksi: 'Reset Form: mengosongkan form input produk produksi supaya bisa memilih ulang.',
                     daftarProduksiTable: 'Daftar Produk yang Diproduksi: daftar produk yang sudah ditambahkan sebelum transaksi produksi disimpan final.',
                     tombolSelesaiProduksi: 'Selesai Transaksi: menyimpan semua produk di daftar sekaligus. Stok material yang dipakai berkurang, stok produk hasil produksi bertambah.',
                     editHeaderInfo: 'Informasi transaksi: No PO, tanggal, supplier, dan gudang tujuan produk masuk ini.',
                     editTombolEditItem: 'Simpan: menyimpan perubahan qty/produk pada item yang sedang diedit.',
                     editTombolBatal: 'Batal: membatalkan proses edit item yang sedang berjalan dan mengembalikan form ke mode tambah item baru.',
                     editItemTable: 'Daftar Item: item produk yang sudah tercatat pada transaksi ini, beserta qty dan aksinya.'
                 },
                 'pengiriman': {
                     inputPengirimanButton: 'Input Pengiriman: mencatat pengiriman barang langsung ke pelanggan tanpa membuat permintaan pengiriman terlebih dahulu.',
                     buatPermintaanButton: 'Buat Permintaan: membuat permintaan pengiriman terlebih dahulu, untuk diproses/dikirim belakangan.',
                     tabDaftar: 'Tab List Pengiriman: daftar surat jalan yang sudah dibuat, beserta progres qty rencana, terkirim, dan sisa belum terkirim.',
                     tabPermintaan: 'Tab List Permintaan: daftar permintaan pengiriman yang belum/sedang diproses jadi surat jalan.',
                     tabRiwayat: 'Tab Cetak Surat Jalan: daftar surat jalan yang sudah resmi terbit dan siap dicetak.',
                     search: 'Search: mencari data pada tabel di tab ini.',
                     filterToggle: 'Tombol filter: membuka pilihan filter dan jumlah data.',
                     filterChip: 'Filter cepat tanggal: menampilkan data dalam rentang waktu ini saja.',
                     filterApply: 'Tampilkan: menerapkan filter yang dipilih ke tabel.',
                     filterReset: 'Reset: mengembalikan semua filter dan pencarian ke kondisi awal.',
                     pageLength: 'Show entries: mengatur jumlah baris yang ditampilkan per halaman.',
                     colNo: 'No: nomor urut baris.',
                     colTanggal: 'Tanggal: tanggal surat jalan/pengiriman dibuat.',
                     colStatus: 'Status: status progres pengiriman ini.',
                     colNoSuratJalan: 'No Surat Jalan: nomor dokumen surat jalan pengiriman.',
                     colNoPo: 'No. PO: nomor PO pelanggan yang dikirim lewat surat jalan ini.',
                     colTotal: 'Total: total qty yang diminta/direncanakan untuk dikirim.',
                     colRencana: 'Rencana: qty yang direncanakan dikirim pada surat jalan ini.',
                     colTerkirim: 'Terkirim: qty yang sudah benar-benar dikirim.',
                     colBelum: 'Belum: sisa qty yang belum dikirim.',
                     colUser: 'User: pengguna yang membuat/memproses pengiriman ini.',
                     colKeterangan: 'Keterangan: catatan tambahan pada pengiriman ini.',
                     colTotalProduk: 'Total Produk (Pcs): total qty produk pada permintaan pengiriman ini.',
                     colJenis: 'Jenis: jenis pengiriman, misalnya reguler atau langsung.',
                     colPelanggan: 'Pelanggan: customer tujuan pengiriman.',
                     colGudangAsal: 'Gudang Asal: lokasi gudang asal barang yang dikirim.',
                     aksiLanjutkan: 'Lanjutkan: melanjutkan proses permintaan ini menjadi surat jalan/pengiriman resmi.',
                     aksiPrintDo: 'Print Delivery Order: mencetak surat jalan. Tombol nonaktif kalau surat jalan resmi belum terbit.',
                     aksiEditPengiriman: 'Edit Pengiriman: mengubah item pada surat jalan ini. Tombol nonaktif kalau surat jalan belum ada.',
                     aksiHapusSuratJalan: 'Hapus: menghapus surat jalan ini dan mengembalikan stoknya. Surat jalan lain dalam sesi input yang sama tidak ikut terhapus.',
                     btbButton: 'Dokumen BTB: melihat atau melengkapi No BTB dan file Bukti Terima Barang untuk item pengiriman ini.',
                     btbNoInput: 'No BTB: nomor Bukti Terima Barang dari pelanggan untuk pengiriman ini.',
                     btbFileInput: 'Upload File BTB: lampiran dokumen BTB dari pelanggan.'
                 },
                 'antar-gudang': {
                     inputTransferButton: 'Input Transfer: membuat permintaan perpindahan produk/material dari satu gudang ke gudang lain.',
                     printButton: 'Print: mencetak permintaan transfer yang seluruh barangnya sudah terkirim, sesuai periode dan gudang asal yang dipilih.',
                     search: 'Search: mencari transfer antar gudang.',
                     filterToggle: 'Tombol filter: membuka pilihan jumlah data yang ditampilkan.',
                     filterApply: 'Tampilkan: menerapkan filter ke daftar transfer.',
                     filterReset: 'Reset: mengembalikan semua filter dan pencarian ke kondisi awal.',
                     pageLength: 'Show Data: mengatur jumlah baris yang ditampilkan per halaman.',
                     printTglAwal: 'Tanggal Awal: batas awal periode transfer yang mau dicetak.',
                     printTglAkhir: 'Tanggal Akhir: batas akhir periode transfer yang mau dicetak.',
                     printGudang: 'Asal Gudang: gudang pengirim yang transfernya mau dicetak.',
                     colNo: 'No: nomor urut baris.',
                     colNoSuratJalan: 'No Surat Jalan: nomor dokumen transfer antar gudang ini.',
                     colTanggal: 'Tanggal: tanggal transfer dicatat.',
                     colUser: 'User: pengguna yang membuat transfer ini.',
                     colTotalProduk: 'Total Produk (Pcs): total qty produk yang ditransfer.',
                     colJenisPengiriman: 'Jenis Pengiriman: cara pengiriman transfer ini, misalnya nama kendaraan/ekspedisi.',
                     colPicPengirim: 'PIC Pengirim: nama penanggung jawab yang mengirim barang transfer ini.',
                     colNominal: 'Nominal: biaya transfer/pengiriman antar gudang ini (kalau ada).',
                     colGudangKeluar: 'Gudang Keluar: lokasi gudang asal barang yang ditransfer.',
                     aksiEdit: 'Edit: mengubah item atau info pengiriman transfer ini.',
                     aksiHapus: 'Hapus: menghapus transaksi transfer ini beserta seluruh itemnya.',
                     editNoSuratJalan: 'No Surat Jalan: nomor dokumen transfer ini.',
                     editUser: 'User: pengguna yang membuat transfer ini.',
                     editGudangKeluar: 'Gudang Keluar: lokasi gudang asal barang yang ditransfer.',
                     editJenisPengiriman: 'Jenis Pengiriman: cara pengiriman transfer ini, bisa diedit lewat Edit Info Pengiriman.',
                     editPicPengirim: 'PIC Pengirim: nama penanggung jawab pengiriman, bisa diedit lewat Edit Info Pengiriman.',
                     editNominal: 'Nominal: biaya transfer ini, bisa diedit lewat Edit Info Pengiriman.',
                     editHeaderButton: 'Edit Info Pengiriman: mengubah Jenis Pengiriman, PIC Pengirim, dan Nominal transfer ini.',
                     editSaveHeaderButton: 'Simpan: menyimpan perubahan info pengiriman.',
                     editCancelHeaderButton: 'Batal: membatalkan perubahan info pengiriman yang belum disimpan.',
                     formJenisItem: 'Jenis Item: pilih Produk atau Material yang mau ditransfer.',
                     formKodeItem: 'Kode Produk/Material: pilih item yang mau ditransfer, sesuai Jenis Item yang dipilih.',
                     formNamaItem: 'Nama Item: otomatis terisi dari kode item yang dipilih.',
                     formGudangAsal: 'Gudang Asal: gudang sumber item yang akan ditransfer/dikurangi stoknya.',
                     formStokItem: 'Stok: stok item saat ini di gudang asal yang dipilih.',
                     formQtyItem: 'Qty: jumlah item yang ditransfer pada baris ini.',
                     tombolSimpanItem: 'Simpan Item: menambahkan item baru langsung ke transfer ini, memotong stok gudang asal begitu disimpan.',
                     tombolEditItem: 'Simpan: menyimpan perubahan qty/item pada baris yang sedang diedit.',
                     tombolBatal: 'Batal: membatalkan proses edit item yang sedang berjalan dan mengembalikan form ke mode tambah item baru.',
                     itemTable: 'Daftar Item: item produk/material yang sudah tercatat pada transfer ini, beserta qty dan aksinya.'
                 },
                 'kategori': {
                     back: 'Kembali: kembali ke daftar Kategori.',
                     tambahButton: 'Tambah Data Kategori: membuat kategori baru untuk mengelompokkan produk dan material.',
                     search: 'Search: mencari kategori berdasarkan nama.',
                     filterToggle: 'Tombol filter: membuka pilihan jumlah data yang ditampilkan.',
                     filterReset: 'Reset: mengembalikan pencarian dan jumlah data ke kondisi awal.',
                     pageLength: 'Show Data: mengatur jumlah baris yang ditampilkan per halaman.',
                     colNo: 'No: nomor urut baris.',
                     colNama: 'Nama Kategori: nama kategori yang dipakai untuk mengelompokkan produk dan material.',
                     aksiEdit: 'Edit Data: mengubah nama kategori ini. Sistem menampilkan dulu di mana saja kategori ini dipakai sebelum masuk ke form edit -- mengganti nama aman, data yang memakainya otomatis ikut menampilkan nama baru.',
                     aksiHapus: 'Hapus Data: menghapus kategori ini. Hanya bisa dihapus kalau belum dipakai di data produk/material manapun.'
                 },
                 'satuan': {
                     back: 'Kembali: kembali ke daftar Satuan.',
                     tambahButton: 'Tambah Data Satuan: membuat satuan baru untuk dipakai pada produk, material, atau transaksi.',
                     search: 'Search: mencari satuan berdasarkan nama.',
                     filterToggle: 'Tombol filter: membuka pilihan jumlah data yang ditampilkan.',
                     filterReset: 'Reset: mengembalikan pencarian dan jumlah data ke kondisi awal.',
                     pageLength: 'Show Data: mengatur jumlah baris yang ditampilkan per halaman.',
                     colNo: 'No: nomor urut baris.',
                     colNama: 'Nama Satuan: nama satuan ukur yang dipakai pada produk, material, atau transaksi.',
                     aksiEdit: 'Edit Data: mengubah nama satuan ini. Sistem menampilkan dulu di mana saja satuan ini dipakai sebelum masuk ke form edit.',
                     aksiHapus: 'Hapus Data: menghapus satuan ini. Hanya bisa dihapus kalau belum dipakai di data manapun.'
                 },
                 'master-material': {
                     back: 'Kembali: kembali ke daftar Material.',
                     tambahButton: 'Tambah Data Material: mendaftarkan material baru beserta kategori, satuan, stok minimum, dan data pendukungnya.',
                     search: 'Search: mencari material berdasarkan kode atau nama.',
                     filterToggle: 'Tombol filter: membuka pilihan jumlah data yang ditampilkan.',
                     filterReset: 'Reset: mengembalikan pencarian dan jumlah data ke kondisi awal.',
                     pageLength: 'Show Data: mengatur jumlah baris yang ditampilkan per halaman.',
                     colNo: 'No: nomor urut baris.',
                     colKode: 'Kode Material: kode unik material di sistem.',
                     colNama: 'Nama Material: nama material ini.',
                     colKategori: 'Kategori: kategori pengelompokan material ini.',
                     aksiEdit: 'Edit Data: mengubah data material ini (nama, kategori, satuan, stok minimum, dsb). Sistem menampilkan dulu di mana saja material ini dipakai sebelum masuk ke form edit.',
                     aksiLabelSupplier: 'Label Nama per Supplier: mengatur nama alias material ini per supplier, yang otomatis dipakai saat membuat PO Keluar ke supplier tersebut.',
                     aksiHapus: 'Hapus Data: menghapus material ini. Hanya bisa dihapus kalau belum dipakai di data manapun.',
                     formKategori: 'Nama Kategori: kategori pengelompokan material ini.',
                     formNamaMaterial: 'Nama Material: nama material ini.',
                     formKodeMaterial: 'Kode Material: kode unik material di sistem. Tidak boleh sama dengan material lain.',
                     formSatuan: 'Satuan Material: satuan ukur material ini.',
                     formMinStok: 'Minimal Stok Material: batas stok minimum yang harus selalu tersedia untuk material ini.',
                     formSimpan: 'Simpan: menyimpan data material ini.',
                     formReset: 'Reset: mengosongkan/mengembalikan form ke kondisi awal.',
                     lsMaterialInfo: 'Material yang sedang diatur label namanya, dan daftar Label Nama yang sudah ada untuk material ini.',
                     lsSupplier: 'Supplier: pilih supplier yang mau diberi label nama alias untuk material ini.',
                     lsLabelNama: 'Label Nama: nama alias material ini yang akan tercantum di PO Keluar khusus untuk supplier yang dipilih.',
                     lsSimpan: 'Simpan: menyimpan label nama untuk supplier yang dipilih.',
                     lsHapus: 'Hapus: menghapus label nama ini untuk supplier terkait.',
                     lsTutup: 'Tutup: menutup jendela ini.'
                 },
                 'master-produk': {
                     back: 'Kembali: kembali ke daftar Produk.',
                     tambahButton: 'Tambah Data Produk: mendaftarkan produk jadi baru beserta material, berat, harga, dan stok minimumnya.',
                     search: 'Search: mencari produk berdasarkan kode atau nama.',
                     filterToggle: 'Tombol filter: membuka pilihan jumlah data yang ditampilkan.',
                     filterReset: 'Reset: mengembalikan pencarian dan jumlah data ke kondisi awal.',
                     pageLength: 'Show Data: mengatur jumlah baris yang ditampilkan per halaman.',
                     colNo: 'No: nomor urut baris.',
                     colKode: 'Kode Produk: kode unik produk di sistem.',
                     colNama: 'Nama Produk: nama produk jadi ini.',
                     colMaterial: 'Material: material utama yang dipakai untuk membuat produk ini.',
                     colKategori: 'Kategori: kategori pengelompokan produk ini.',
                     colSatuan: 'Satuan: satuan ukur/jual produk ini.',
                     aksiEdit: 'Edit Data: mengubah data produk ini (material, berat, harga, Wise, stok minimum, dsb). Sistem menampilkan dulu di mana saja produk ini dipakai sebelum masuk ke form edit.',
                     aksiRiwayat: 'Riwayat Perubahan: melihat histori perubahan data produk ini.',
                     aksiHapus: 'Hapus Data: menghapus produk ini. Hanya bisa dihapus kalau belum dipakai di data manapun.',
                     formKodeProduk: 'Kode Produk: kode unik produk di sistem. Tidak boleh sama dengan produk lain.',
                     formNamaProduk: 'Nama Produk: nama produk jadi ini.',
                     formPelangganProduk: 'Pelanggan: pelanggan pemilik produk ini, kalau produk ini eksklusif untuk satu pelanggan tertentu.',
                     formKategoriProduk: 'Kategori: kategori pengelompokan produk ini.',
                     formSatuanProduk: 'Satuan: satuan ukur/jual produk ini.',
                     formJasaCheckbox: 'Produk jasa / tanpa berat: centang kalau produk ini berupa jasa atau tidak punya berat fisik (tidak butuh perhitungan material/berat).',
                     formSumberMaterial: 'Sumber Material Produksi: asal material untuk membuat produk ini -- Material TRE (dari stok TRE) atau material dari customer.',
                     formHargaProduk: 'Harga Produk: harga jual default produk ini.',
                     formMinStokProduk: 'Minimal Stok Produk: batas stok minimum yang harus selalu tersedia untuk produk ini.',
                     formSimpan: 'Simpan: menyimpan data produk ini.',
                     formReset: 'Reset: mengosongkan/mengembalikan form ke kondisi awal.'
                 },
                 'master-pelanggan': {
                     back: 'Kembali: kembali ke daftar Pelanggan.',
                     tambahButton: 'Tambah Data Pelanggan: mendaftarkan pelanggan baru beserta kontak dan gudang defaultnya.',
                     search: 'Search: mencari pelanggan berdasarkan nama, PIC, atau kontak lainnya.',
                     filterToggle: 'Tombol filter: membuka pilihan jumlah data yang ditampilkan.',
                     filterReset: 'Reset: mengembalikan pencarian dan jumlah data ke kondisi awal.',
                     pageLength: 'Show Data: mengatur jumlah baris yang ditampilkan per halaman.',
                     colNo: 'No: nomor urut baris.',
                     colNama: 'Nama Pelanggan: nama customer ini.',
                     colPic: 'PIC: nama penanggung jawab/kontak utama pelanggan ini.',
                     colEmail: 'Email: alamat email pelanggan untuk korespondensi/invoice.',
                     colAlamat: 'Alamat: alamat pengiriman/penagihan pelanggan ini.',
                     colTelp: 'No Telp / Handphone: nomor kontak pelanggan ini.',
                     colFax: 'Fax: nomor fax pelanggan (opsional).',
                     colTo: 'To: bagian/penerima tujuan surat-menyurat pada pelanggan ini (opsional), misalnya "Bag. Keuangan".',
                     colGudang: 'Gudang: gudang default yang terkait dengan pelanggan ini.',
                     aksiEdit: 'Edit Data: mengubah data kontak pelanggan ini lewat modal, tanpa pindah halaman.',
                     aksiHapus: 'Hapus Data: menghapus pelanggan ini. Hanya bisa dihapus kalau belum dipakai di transaksi manapun.',
                     editNama: 'Nama Pelanggan: nama customer ini.',
                     editPic: 'Nama PIC: nama penanggung jawab/kontak utama pelanggan ini.',
                     editEmail: 'Email: alamat email pelanggan untuk korespondensi/invoice.',
                     editAlamat: 'Alamat: alamat pengiriman/penagihan pelanggan ini.',
                     editTelp: 'No Telp / Handphone: nomor kontak pelanggan ini.',
                     editFax: 'Fax: nomor fax pelanggan (opsional, kosongkan kalau tidak ada).',
                     editTo: 'To / Bagian Penerima: bagian/penerima tujuan surat-menyurat (opsional), misalnya "Bag. Keuangan".',
                     editGudang: 'Gudang: gudang default yang terkait dengan pelanggan ini.',
                     editUpdate: 'Update: menyimpan perubahan data pelanggan ini.'
                 },
                 'master-supplier': {
                     back: 'Kembali: kembali ke daftar Supplier.',
                     tambahButton: 'Tambah Data Supplier: mendaftarkan supplier/vendor baru beserta kontaknya.',
                     search: 'Search: mencari supplier berdasarkan nama, PIC, atau kontak lainnya.',
                     filterToggle: 'Tombol filter: membuka pilihan jumlah data yang ditampilkan.',
                     filterReset: 'Reset: mengembalikan pencarian dan jumlah data ke kondisi awal.',
                     pageLength: 'Show Data: mengatur jumlah baris yang ditampilkan per halaman.',
                     colNo: 'No: nomor urut baris.',
                     colNama: 'Supplier: nama supplier/vendor ini.',
                     colPic: 'Nama PIC: nama penanggung jawab/kontak utama supplier ini.',
                     colEmail: 'Email: alamat email supplier untuk korespondensi/invoice.',
                     colTelp: 'No Telp / HP: nomor kontak supplier ini.',
                     colAlamat: 'Alamat: alamat supplier/vendor ini.',
                     aksiEdit: 'Edit Data: mengubah data kontak supplier ini lewat modal, tanpa pindah halaman.',
                     aksiHapus: 'Hapus Data: menghapus supplier ini. Hanya bisa dihapus kalau belum dipakai di transaksi manapun.',
                     editNama: 'Nama Supplier: nama supplier/vendor ini.',
                     editPic: 'Nama PIC: nama penanggung jawab/kontak utama supplier ini.',
                     editEmail: 'Email: alamat email supplier untuk korespondensi/invoice.',
                     editTelp: 'No Telp / Handphone: nomor kontak supplier ini.',
                     editAlamat: 'Alamat: alamat supplier/vendor ini.',
                     editUpdate: 'Update: menyimpan perubahan data supplier ini.'
                 },
                 'management-user': {
                     tambahUserButton: 'Tambah User Baru: mendaftarkan akun user baru beserta role dan hak aksesnya.',
                     haqAksesButton: 'Hak Akses User: mengatur menu dan aksi apa saja yang boleh diakses tiap role.',
                     tambahRoleButton: 'Tambah Role: membuat role/level baru untuk dipakai saat mendaftarkan user.',
                     colNo: 'No: nomor urut baris.',
                     colIdUser: 'ID User: username yang dipakai untuk login.',
                     colNamaUser: 'Nama User: nama lengkap pemilik akun ini.',
                     colRole: 'Role: level/role akun ini, menentukan menu dan aksi yang boleh diakses.',
                     colStatus: 'Status: Active/Non Active. User Non Active tidak bisa login.',
                     aksiView: 'View: membuka detail user ini untuk mengubah nama, role, status, atau reset password.',
                     tambahIdUser: 'ID User: username yang akan dipakai user ini untuk login. Password awal dibuatkan otomatis oleh sistem.',
                     tambahNamaLengkap: 'Nama Lengkap: nama lengkap pemilik akun ini.',
                     tambahLevel: 'Level User: role/level akun ini, menentukan default hak akses menu dan aksi yang boleh dipakai.',
                     tambahHakAkses: 'Hak Akses: sesuaikan menu dan aksi yang boleh diakses user ini, di luar default dari Level User yang dipilih.',
                     tambahSimpan: 'Simpan: mendaftarkan user baru ini.',
                     editIdUser: 'ID User: username akun ini (tidak bisa diubah).',
                     editNamaLengkap: 'Nama Lengkap: nama lengkap pemilik akun ini.',
                     editLevel: 'Level User: role/level akun ini, menentukan menu dan aksi yang boleh diakses.',
                     editStatus: 'Status User: aktifkan/nonaktifkan akun ini. User Non Active tidak bisa login.',
                     editResetPassword: 'Reset Password: membuatkan password baru secara acak untuk user ini. Password lama langsung tidak berlaku.',
                     editHapus: 'Hapus: menghapus akun user ini secara permanen.',
                     editSimpan: 'Update: menyimpan perubahan nama lengkap atau role user ini.',
                     roleNama: 'Nama Role: nama role/level baru, misalnya "SUPERVISOR".',
                     roleSimpan: 'Simpan: menyimpan role baru ini, supaya bisa dipilih saat mendaftarkan user.'
                 },
                 'log-aktivitas': {
                     tabAktivitas: 'Tab Aktivitas: daftar aktivitas user yang tercatat di sistem, untuk audit dan pengecekan.',
                     tabMemory: 'Tab Memory: pengaturan dan riwayat arsip Log Aktivitas yang sudah dirapikan jadi file Excel supaya tabel utama tetap ringan.',
                     search: 'Search: mencari log aktivitas berdasarkan keterangan, user, atau lokasi.',
                     filterToggle: 'Tombol filter: membuka pilihan User, Lokasi, Jenis Aksi, rentang tanggal, dan jumlah data.',
                     filterUser: 'User: batasi log untuk satu user tertentu saja.',
                     filterLokasi: 'Lokasi: batasi log untuk satu halaman/fitur tertentu saja.',
                     filterAksi: 'Jenis Aksi: batasi log untuk satu jenis aksi saja, misalnya Tambah, Edit, atau Hapus.',
                     filterTglAwal: 'Tanggal Awal: batas awal periode log yang ditampilkan.',
                     filterTglAkhir: 'Tanggal Akhir: batas akhir periode log yang ditampilkan.',
                     filterReset: 'Reset: mengembalikan semua filter dan pencarian ke kondisi awal.',
                     pageLength: 'Show Data: mengatur jumlah baris yang ditampilkan per halaman.',
                     colNo: 'No: nomor urut baris.',
                     colWaktu: 'Waktu: kapan aktivitas ini terjadi.',
                     colUser: 'User: user yang melakukan aktivitas ini.',
                     colAksi: 'Aksi: jenis aksi yang dilakukan, misalnya Tambah, Edit, atau Hapus.',
                     colLokasi: 'Lokasi: halaman/fitur tempat aktivitas ini terjadi.',
                     colKeterangan: 'Keterangan: ringkasan singkat aktivitas yang dilakukan.',
                     colDetail: 'Detail: melihat rincian lengkap data sebelum/sesudah perubahan pada aktivitas ini.',
                     emailPenerima: 'Email Penerima Laporan: alamat email yang menerima file arsip Log Aktivitas tiap kali diarsipkan. Kosongkan kalau tidak mau ada yang menerima email.',
                     intervalHari: 'Kirim Tiap Berapa Hari: interval pengarsipan Log Aktivitas jadi file Excel, misalnya 7 untuk mingguan.',
                     simpanEmailButton: 'Simpan Pengaturan: menyimpan email penerima dan interval pengarsipan Log Aktivitas.',
                     colNamaFile: 'Nama File: nama file Excel hasil arsip Log Aktivitas.',
                     colJumlahBaris: 'Jumlah Baris: jumlah baris log yang dirapikan ke file arsip ini.',
                     colPeriode: 'Periode: rentang tanggal log yang tercakup dalam arsip ini.',
                     colWaktuDiarsipkan: 'Waktu Diarsipkan: kapan arsip ini dibuat.',
                     aksiDownload: 'Download: mengunduh file Excel arsip Log Aktivitas ini.'
                 },
                 'ganti-password': {
                     passwordLama: 'Password Lama: password akun kamu saat ini, untuk verifikasi sebelum diganti.',
                     passwordBaru: 'Password Baru: password baru yang mau dipakai untuk login selanjutnya.',
                     confirmPasswordBaru: 'Confirm Password Baru: ketik ulang Password Baru untuk memastikan tidak salah ketik.',
                     simpanButton: 'Ganti Password: menyimpan password baru. Kamu akan otomatis logout dan perlu login ulang pakai password baru ini.'
                 }
             };

             const copy = tooltipCopy[previewKey];
             if (!copy) {
                 return;
             }

             const tooltip = document.createElement('div');
             tooltip.className = 'manual-order-tooltip';
             document.body.appendChild(tooltip);

             function placeTooltip(target, pointerEvent) {
                 const tooltipRect = tooltip.getBoundingClientRect();
                 const gap = 14;
                 let left;
                 let top;

                 if (pointerEvent && typeof pointerEvent.clientX === 'number') {
                     left = pointerEvent.clientX + gap;
                     top = pointerEvent.clientY + gap;
                 } else {
                     const rect = target.getBoundingClientRect();
                     left = rect.left + gap;
                     top = rect.top + gap;
                 }

                 if (left + tooltipRect.width > window.innerWidth - 12) {
                     left = (pointerEvent && typeof pointerEvent.clientX === 'number')
                         ? pointerEvent.clientX - tooltipRect.width - gap
                         : window.innerWidth - tooltipRect.width - 12;
                 }
                 if (left < 12) left = 12;

                 if (top + tooltipRect.height > window.innerHeight - 12) {
                     top = (pointerEvent && typeof pointerEvent.clientY === 'number')
                         ? pointerEvent.clientY - tooltipRect.height - gap
                         : window.innerHeight - tooltipRect.height - 12;
                 }
                 if (top < 12) top = 12;

                 tooltip.style.left = left + 'px';
                 tooltip.style.top = top + 'px';
             }

             function attachTooltip(element, message) {
                 if (!element || !message) {
                     return;
                 }
                 element.dataset.manualOrderMessage = message;
                 if (element.dataset.manualOrderHelp === 'true') {
                     return;
                 }
                 element.dataset.manualOrderHelp = 'true';
                 element.addEventListener('mouseenter', function(event) {
                     // Kalau tur Alur TRE lagi jalan (coach-mark), jangan ikut nongolin
                     // tooltip hover ini -- dua-duanya sama-sama nunjuk manual_preview,
                     // jadi kalau nggak di-skip, hover kepencet nggak sengaja pas tur
                     // aktif bakal numpuk dua penjelasan sekaligus di layar.
                     if (sessionStorage.getItem('treTourActive')) {
                         return;
                     }
                     tooltip.textContent = element.dataset.manualOrderMessage || message;
                     tooltip.classList.add('is-visible');
                     requestAnimationFrame(function() {
                         placeTooltip(element, event);
                     });
                 });
                 element.addEventListener('mousemove', function(event) {
                     placeTooltip(element, event);
                 });
                 element.addEventListener('mouseleave', function() {
                     tooltip.classList.remove('is-visible');
                 });
             }

             window.addEventListener('scroll', function() {
                 tooltip.classList.remove('is-visible');
             }, true);

             function normalizedText(element) {
                 return (element.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
             }

             function attachByText(selector, words, message, closestSelector) {
                 document.querySelectorAll(selector).forEach(function(element) {
                     const text = normalizedText(element);
                     if (!words.some(function(word) { return text.indexOf(word) !== -1; })) {
                         return;
                     }
                     attachTooltip(closestSelector ? (element.closest(closestSelector) || element) : element, message);
                 });
             }

             function attachHeader(words, message) {
                 attachByText('th', words, message);
             }

             function attachExactHeader(words, message) {
                 document.querySelectorAll('th').forEach(function(element) {
                     if (words.indexOf(normalizedText(element)) === -1) {
                         return;
                     }
                     attachTooltip(element, message);
                 });
             }

             function attachLabel(words, message) {
                 attachByText('label, .form-group > label, .form-check-label, h5, h6, .card-title, .po-filter-label', words, message, '.form-group, .form-check, .po-filter-section, label');
             }

             function attachIconButton(iconSelector, message) {
                 document.querySelectorAll(iconSelector).forEach(function(icon) {
                     attachTooltip(icon.closest('a, button, .btn') || icon, message);
                 });
             }

             function buttonDescriptor(button) {
                 const iconClass = Array.from(button.querySelectorAll('i, svg, span'))
                     .map(function(icon) { return icon.className && icon.className.baseVal ? icon.className.baseVal : icon.className; })
                     .join(' ');
                 return [
                     normalizedText(button),
                     button.getAttribute('title') || '',
                     button.getAttribute('aria-label') || '',
                     button.getAttribute('data-original-title') || '',
                     button.className || '',
                     iconClass || ''
                 ].join(' ').toLowerCase();
             }

             function fallbackButtonTooltip(button) {
                 const descriptor = buttonDescriptor(button);

                 if (descriptor.indexOf('fa-eye') !== -1 || descriptor.indexOf('lihat') !== -1 || descriptor.indexOf('detail') !== -1) {
                     return copy.viewButton || copy.detail || 'Lihat detail: membuka rincian data tanpa mengubah transaksi.';
                 }
                 if (descriptor.indexOf('fa-edit') !== -1 || descriptor.indexOf('fa-pencil') !== -1 || descriptor.indexOf('edit') !== -1 || descriptor.indexOf('ubah') !== -1) {
                     return copy.editButton || copy.edit || 'Edit: membuka form perubahan untuk data yang masih boleh diedit.';
                 }
                 if (descriptor.indexOf('fa-trash') !== -1 || descriptor.indexOf('hapus') !== -1 || descriptor.indexOf('delete') !== -1) {
                     return copy.delete || 'Hapus: menghapus data jika transaksi masih memenuhi aturan sistem.';
                 }
                 if (descriptor.indexOf('fa-filter') !== -1 || descriptor.indexOf('sliders') !== -1 || descriptor.indexOf('filter') !== -1) {
                     return copy.filterButton || 'Filter: membuka pilihan untuk membatasi data yang ditampilkan.';
                 }
                 if (descriptor.indexOf('fa-search') !== -1 || descriptor.indexOf('search') !== -1) {
                     return copy.search || 'Cari: mencari data berdasarkan kata kunci.';
                 }
                 if (descriptor.indexOf('fa-file-upload') !== -1 || descriptor.indexOf('import') !== -1 || descriptor.indexOf('pdf') !== -1) {
                     return copy.import || copy.uploadFile || 'Import/upload: memasukkan file agar data bisa diproses sistem.';
                 }
                 if (descriptor.indexOf('fa-save') !== -1 || descriptor.indexOf('simpan') !== -1) {
                     return copy.saveChanges || copy.savePo || copy.saveItem || copy.finish || 'Simpan: menyimpan data yang sudah diisi user.';
                 }
                 if (descriptor.indexOf('fa-plus') !== -1 || descriptor.indexOf('tambah') !== -1) {
                     return copy.input || copy.saveItem || 'Tambah: menambahkan data atau item baru ke transaksi.';
                 }
                 if (descriptor.indexOf('fa-sync') !== -1 || descriptor.indexOf('fa-redo') !== -1 || descriptor.indexOf('fa-undo') !== -1 || descriptor.indexOf('reload') !== -1 || descriptor.indexOf('reset') !== -1) {
                     return copy.resetItem || copy.filterButton || 'Reset/reload: mengembalikan pilihan atau input ke kondisi awal.';
                 }
                 if (descriptor.indexOf('fa-print') !== -1 || descriptor.indexOf('fa-file-invoice') !== -1 || descriptor.indexOf('fa-money-check') !== -1 || descriptor.indexOf('cetak') !== -1 || descriptor.indexOf('invoice') !== -1 || descriptor.indexOf('bayar') !== -1) {
                     return copy.aksi || copy.action || 'Tombol proses lanjutan: dipakai untuk cetak, invoice, pembayaran, atau aksi transaksi terkait.';
                 }
                 if (descriptor.indexOf('fa-ban') !== -1 || descriptor.indexOf('cancel') !== -1 || descriptor.indexOf('batal') !== -1 || descriptor.indexOf('nonaktif') !== -1) {
                     return copy.cancelButton || copy.cancel || copy.aksi || 'Batal/nonaktif: membatalkan atau menonaktifkan data sesuai aturan sistem.';
                 }
                 if (button.closest('td') || button.closest('th')) {
                     return copy.aksi || copy.action || 'Aksi: tombol cepat untuk memproses data pada baris ini.';
                 }

                 return copy.aksi || copy.action || 'Tombol aksi: gunakan untuk menjalankan proses pada halaman ini.';
             }

             // Dipakai buat tebakan generik berbasis ikon/fallback -- BEDA
             // dari attachTooltip() yang selalu nimpa. Ini sengaja nggak
             // nimpa kalau elemennya udah ada penjelasan spesifik (misal dari
             // attachByText berdasarkan teks tombol), soalnya tebakan
             // berbasis ikon doang gampang salah kalau ikonnya dipakai ulang
             // buat tombol lain (contoh: fa-undo dipakai baik buat "Reset
             // Item" maupun tombol "Kembali").
             function attachTooltipIfEmpty(element, message) {
                 if (!element || !message || element.dataset.manualOrderMessage) {
                     return;
                 }
                 attachTooltip(element, message);
             }

             function attachAllButtons() {
                 document.querySelectorAll('button, a.btn, .btn').forEach(function(button) {
                     attachTooltipIfEmpty(button, fallbackButtonTooltip(button));
                 });
             }

             // Grup 'keuangan' dulu return lebih awal di sini (sebelum
             // attachDynamicPreviewTooltips()/MutationObserver pernah
             // didefinisikan), jadi tab Reporting yang kontennya dimuat via
             // AJAX nggak pernah dapat tooltip sama sekali setelah scan
             // pertama. Sekarang logic khusus 'keuangan' dipindah ke
             // attachKeuanganTooltips() (dipanggil dari dalam
             // attachDynamicPreviewTooltips() di bawah) supaya ikut ke-rescan
             // tiap ada perubahan DOM, sama seperti grup lain.

             attachByText('a, button', ['input po', 'input transaksi', 'tambah po'], copy.input, '.btn, a, button');
             attachByText('a, button', ['import po', 'pdf'], copy.import, '.btn, a, button');
             attachByText('a, button', ['kembali'], copy.back, '.btn, a, button');
             attachByText('a, button', ['tambah item', 'simpan item'], copy.saveItem, '.btn, a, button');
             attachByText('a, button', ['selesai transaksi'], copy.finish, '.btn, a, button');
             attachByText('a, button', ['baca file po'], copy.readPdf, '.btn, a, button');
             attachByText('a, button', ['simpan po'], copy.savePo || copy.finish, '.btn, a, button');
             attachByText('a, button', ['upload ulang'], copy.reupload, '.btn, a, button');
             attachByText('h5, h6, .card-header, strong', ['upload file po'], copy.uploadFile);
             attachByText('.alert, .callout, .info-box, p', ['cek ulang hasil baca pdf', 'upload file pdf po'], copy.reviewPdf || copy.uploadFile, '.alert, .callout, .info-box, p');
             attachByText('.card-header, strong, h5, h6', ['data header po'], copy.headerData);
             attachByText('.card-header, strong, h5, h6', ['detail item po'], copy.detailImport || copy.table);
             attachByText('small, .text-muted', ['terbaca dari pdf'], copy.pdfCustomerHint, 'small, .text-muted');

             document.querySelectorAll('.po-search-input, input[type="search"], .dataTables_filter input').forEach(function(element) {
                 attachTooltip(element, copy.search || copy.filterButton);
             });
             document.querySelectorAll('.po-filter-toggle, .manual-filter-trigger, [data-filter-toggle]').forEach(function(element) {
                 attachTooltip(element, copy.filterButton || copy.search);
             });
             document.querySelectorAll('.po-filter-panel, .manual-filter-panel').forEach(function(element) {
                 attachTooltip(element, copy.filterPanel || copy.filterButton);
             });
             document.querySelectorAll('.po-filter-chip').forEach(function(element) {
                 attachTooltip(element, copy.dateRangeChip);
             });
             document.querySelectorAll('.po-filter-apply').forEach(function(element) {
                 attachTooltip(element, copy.filterApply);
             });
             document.querySelectorAll('.po-filter-reset').forEach(function(element) {
                 attachTooltip(element, copy.filterReset);
             });
             document.querySelectorAll('select').forEach(function(element) {
                 attachTooltip(element, copy.filterPanel || copy.filterButton || copy.search);
             });

             // Halaman Outstanding pakai prefix class 'outstanding-filter-*'
             // sendiri (bukan 'po-filter-*'), jadi nggak kena aturan generik
             // di atas -- di-set eksplisit di sini. attachTooltip() SENGAJA
             // dipanggil belakangan biar menang atas aturan <select> generik
             // di atas (yang kalau tidak ditimpa bakal nyamain tooltip
             // #pelanggan/#outstandingPageLength dengan #filter_kekurangan).
             document.querySelectorAll('.outstanding-filter-toggle').forEach(function(element) {
                 attachTooltip(element, copy.filterToggle || copy.filterButton);
             });
             document.querySelectorAll('.outstanding-filter-panel').forEach(function(element) {
                 attachTooltip(element, copy.filterButton);
             });
             document.querySelectorAll('.outstanding-filter-chip').forEach(function(element) {
                 attachTooltip(element, copy.dateRangeChip);
             });
             document.querySelectorAll('.outstanding-filter-apply').forEach(function(element) {
                 attachTooltip(element, copy.filterApply);
             });
             document.querySelectorAll('.outstanding-filter-reset').forEach(function(element) {
                 attachTooltip(element, copy.filterReset);
             });
             document.querySelectorAll('#filter_kekurangan').forEach(function(element) {
                 attachTooltip(element, copy.filterButton);
             });
             document.querySelectorAll('#pelanggan, #pelangganCetak').forEach(function(element) {
                 attachTooltip(element, copy.pelangganFilter);
             });
             document.querySelectorAll('#outstandingPageLength').forEach(function(element) {
                 attachTooltip(element, copy.pageLength);
             });
             document.querySelectorAll('#outstandingBtnCetak, #modalCetakOutstanding .btn-primary').forEach(function(element) {
                 attachTooltip(element, copy.printButton);
             });
             document.querySelectorAll('#modalCetakOutstanding .btn-secondary').forEach(function(element) {
                 attachTooltip(element, copy.modalCancel);
             });

             attachExactHeader(['no'], copy.rowNumber || copy.noPo);
             attachHeader(['no po', 'no. po', 'purchase order'], copy.noPo);
             attachHeader(['tanggal', 'tgl. po', 'tgl po'], copy.tanggal);
             attachHeader(['pelanggan'], copy.pelanggan);
             attachHeader(['supplier', 'vendor'], copy.supplier);
             attachHeader(['jenis po'], copy.jenisPo);
             attachHeader(['transaksi'], copy.transaksi);
             attachHeader(['total barang'], copy.totalBarang);
             attachHeader(['total nominal'], copy.nominal);
             attachHeader(['harga'], copy.harga);
             attachHeader(['harga satuan'], copy.unitPrice || copy.harga);
             attachHeader(['deskripsi dari pdf'], copy.pdfDescription);
             attachHeader(['produk di sistem'], copy.systemProduct);
             attachHeader(['uom'], copy.uom);
             attachHeader(['progress'], copy.progress);
             // Urutan di bawah ini SENGAJA: pola pendek/generik ('status',
             // 'terkirim') dipasang duluan, baru pola yang lebih spesifik
             // ('status penerimaan', 'belum terkirim', 'nilai tagihan
             // terkirim') -- soalnya attachHeader() lewat attachTooltip()
             // yang SELALU nimpa, dan pola generik itu substring dari yang
             // spesifik (mis. "status penerimaan".indexOf('status') !== -1),
             // jadi kalau generiknya dipasang belakangan dia bakal nimpa
             // balik header yang udah benar dari pola spesifik.
             attachHeader(['status'], copy.activeStatus || copy.statusBadge);
             attachHeader(['status penerimaan'], copy.penerimaan);
             attachHeader(['kode produk'], copy.productCode);
             attachHeader(['qty', 'jumlah'], copy.qty);
             attachHeader(['terkirim'], copy.terkirim || copy.sentQty);
             attachHeader(['belum terkirim'], copy.belumTerkirim);
             attachHeader(['nilai tagihan terkirim'], copy.nilaiTerkirim);
             attachHeader(['sisa nilai tagihan'], copy.sisaTagihan);
             attachHeader(['aksi'], copy.aksi || copy.action);

             attachLabel(['tanggal po'], copy.dateInput || copy.tanggal);
             attachLabel(['no. po', 'no po'], copy.poNumberInput || copy.noPo);
             attachLabel(['pelanggan'], copy.customerInput || copy.pelanggan);
             attachLabel(['file po pdf'], copy.uploadFile);
             attachLabel(['po sudah berjalan', 'migrasi'], copy.migration);
             attachLabel(['kode produk'], copy.productCode);
             attachLabel(['nama produk'], copy.productName);
             attachLabel(['berat satuan'], copy.weight);
             attachLabel(['stok'], copy.stock);
             attachLabel(['qty terkirim'], copy.sentQty || copy.terkirim);
             attachLabel(['qty'], copy.qty);
             attachLabel(['nilai sudah ditagihkan'], copy.billedValue);
             attachLabel(['draft item po'], copy.draft);

             function attachStatusBadges() {
                 document.querySelectorAll('.badge, .label, .status-badge, .badge-pill, .po-badge, .po-status-badge, [class*="po-badge"]').forEach(function(element) {
                     let message = copy.statusBadge || 'Label status ini menunjukkan progress transaksi saat ini agar user cepat tahu data mana yang perlu diproses.';
                     const text = normalizedText(element);

                     if (previewKey === 'po-keluar') {
                         if (text.indexOf('qty penerimaan tercatat') !== -1) {
                             message = copy.usedReceptionQty || message;
                         } else if (text.indexOf('penerimaan material') !== -1) {
                             message = copy.usedMaterialReceipt || message;
                         } else if (text.indexOf('dipakai di') !== -1) {
                             message = copy.usedBy || copy.lockNotice || message;
                         } else if (text.indexOf('terkunci') !== -1) {
                             message = copy.lockNotice || message;
                         } else if (text.indexOf('aktif') !== -1) {
                             message = copy.activeStatus || message;
                         } else if (text.indexOf('po material') !== -1) {
                             message = copy.poMaterial || copy.jenisPo || message;
                         } else if (text.indexOf('po produk') !== -1) {
                             message = copy.poProduk || copy.jenisPo || message;
                         } else if (text.indexOf('po jasa') !== -1) {
                             message = copy.poJasa || copy.jenisPo || message;
                         } else if (text.indexOf('titip proses') !== -1) {
                             message = copy.titipProses || copy.transaksi || message;
                         } else if (text.indexOf('beli') !== -1) {
                             message = copy.beli || copy.transaksi || message;
                         } else if (text.indexOf('diterima lengkap') !== -1) {
                             message = copy.diterimaLengkap || copy.penerimaan || message;
                         } else if (text.indexOf('diterima sebagian') !== -1) {
                             message = copy.diterimaSebagian || copy.penerimaan || message;
                         } else if (text.indexOf('belum diterima') !== -1) {
                             message = copy.belumDiterima || copy.penerimaan || message;
                         } else if (text.indexOf('selesai') !== -1) {
                             message = copy.statusSelesai || copy.penerimaan || message;
                         }
                     }

                     attachTooltip(element, message);
                 });
             }

             function attachProgressTooltips() {
                 if (previewKey !== 'po-masuk') {
                     return;
                 }

                 attachByText('h1, h2, h3, .content-header h1, .card-header, strong', ['progress po'], copy.progressPage || copy.progress);
                 attachByText('table tr, table th, table td', ['no. po', 'tanggal po', 'pelanggan'], copy.poInfo, 'tr');
                 attachByText('.po-progress-steps, .step, .step-label', ['po dibuat', 'material siap', 'dikirim', 'ditagih', 'lunas'], copy.progressSteps, '.po-progress-steps');
                 attachByText('.detail-row span, .detail-row div', ['pengadaan material'], copy.materialProcurement, '.detail-row');
                 attachByText('.detail-row span, .detail-row div', ['pengiriman ke pelanggan'], copy.shippingCustomer, '.detail-row');
                 attachByText('.detail-row span, .detail-row div', ['item ditutup', 'dipindah'], copy.closedMoved, '.detail-row');
                 attachByText('.detail-row span, .detail-row div', ['penagihan'], copy.billingProgress, '.detail-row');
                 attachByText('a, button', ['lihat btb'], copy.btbButton, '.btn, a, button');
                 attachByText('a, button', ['close po', 'tutup po'], copy.closePo, '.btn, a, button');
                 attachByText('a, button', ['buka close', 'reopen'], copy.reopenClose, '.btn, a, button');
                 attachByText('.alert, .callout, .info-box, p', ['terkunci', 'dipakai transaksi lanjutan'], copy.lockNotice, '.alert, .callout, .info-box, p');
             }

             function attachClosePoModalTooltips() {
                if (previewKey !== 'po-masuk') {
                    return;
                }

                document.querySelectorAll('.alokasi-tipe').forEach(function(el) { attachTooltip(el, copy.tipeAlokasi); });
                document.querySelectorAll('.alokasi-qty').forEach(function(el) { attachTooltip(el, copy.qtyAlokasi); });
             }

             function attachKoreksiModalTooltips() {
                if (previewKey !== 'po-masuk') {
                    return;
                }

                document.querySelectorAll('#koreksiQtyBaru').forEach(function(el) { attachTooltip(el, copy.koreksiQtyBaru); });
                document.querySelectorAll('#koreksiQtyAlasan').forEach(function(el) { attachTooltip(el, copy.koreksiQtyAlasan); });
             }

             function attachPoMasukEditTooltips() {
                if (previewKey !== 'po-masuk') {
                    return;
                }

                const isEditPage = document.querySelector('.po-summary-grid, #nopoDisplay, #tanggalDisplay, .po-section-card') && /ubah po|edit po/i.test(document.body.textContent || '');
                if (!isEditPage) {
                    return;
                }

                function attachFirst(selector, message) {
                    const element = document.querySelector(selector);
                    if (element && message) {
                        attachTooltip(element, message);
                    }
                }

                function attachEach(selector, message) {
                    if (!message) {
                        return;
                    }
                    document.querySelectorAll(selector).forEach(function(element) {
                        attachTooltip(element, message);
                    });
                }

                function attachField(selector, message) {
                    attachEach(selector, message);
                    document.querySelectorAll(selector).forEach(function(element) {
                        const group = element.closest('.form-group, .input-group, .po-field, .col-md-2, .col-md-3, .col-md-4, .col-md-6');
                        if (group) {
                            attachTooltip(group, message);
                        }
                    });
                }

                function attachSummaryCard(keyText, message) {
                    document.querySelectorAll('.po-summary-card').forEach(function(card) {
                        if (normalizedText(card).indexOf(keyText) !== -1) {
                            attachTooltip(card, message);
                        }
                    });
                }

                function attachHeaderCell(headerText, message) {
                    document.querySelectorAll('.tampilDataDetail th, #datadetail th').forEach(function(header) {
                        if (normalizedText(header).indexOf(headerText) !== -1) {
                            attachTooltip(header, message);
                            const table = header.closest('table');
                            const index = Array.from(header.parentElement.children).indexOf(header);
                            if (table && index >= 0) {
                                table.querySelectorAll('tbody tr').forEach(function(row) {
                                    const cell = row.children[index];
                                    if (cell) {
                                        attachTooltip(cell, message);
                                    }
                                });
                            }
                        }
                    });
                }

                attachByText('h1, h2, .content-header h1', ['ubah po', 'edit po'], copy.editPage || copy.edit);
                attachEach('.po-soft-alert.is-info', copy.editableNotice || copy.lockNotice);
                attachEach('.po-soft-alert:not(.is-info)', copy.lockNotice);
                attachEach('.po-lock-pill, .po-readonly-pill', copy.lockedPill || copy.lockNotice);
                attachByText('button, a', ['tutup po', 'close po'], copy.closePo, '.btn, button, a');
                attachByText('button, a', ['kembali'], copy.back, '.btn, button, a');

                attachSummaryCard('no. po', copy.summaryNoPo || copy.noPo);
                attachSummaryCard('tanggal', copy.summaryTanggal || copy.tanggal);
                attachSummaryCard('pelanggan', copy.summaryPelanggan || copy.pelanggan);
                attachSummaryCard('total qty', copy.summaryTotalQty || copy.totalBarang);
                attachField('#nopoDisplay, #nopoText, #nopoInput', copy.summaryNoPo || copy.noPo);
                attachField('#tanggalDisplay, #tanggalText, #tanggalInput', copy.summaryTanggal || copy.dateInput);
                attachField('#pelangganDisplay, #pelangganText, #pelangganSelect', copy.summaryPelanggan || copy.customerInput);
                attachField('#lbTotalBerat', copy.summaryTotalQty || copy.totalBarang);
                attachFirst('#editNopoBtn', copy.editNumberButton || copy.edit);
                attachFirst('#editTanggalBtn', copy.editDateButton || copy.edit);
                attachFirst('#editPelangganBtn', copy.editCustomerButton || copy.edit);
                attachEach('#saveNopoBtn, #saveTanggalBtn, #savePelangganBtn', copy.saveFieldButton || copy.savePo);
                attachEach('#cancelNopoBtn, #cancelTanggalBtn, #cancelPelangganBtn, #tombolBatal', copy.cancelEdit || copy.back);

                attachByText('.po-section-card, .po-section-card h5, .po-section-card p', ['tambah barang'], copy.addItemSection || copy.saveItem, '.po-section-card');
                attachByText('.po-section-card, .po-section-card h5, .po-section-card p', ['daftar item po'], copy.tableItemList || copy.draft, '.po-section-card');
                attachField('#kodebarang', copy.productCode);
                attachField('#namabarang', copy.productName);
                attachField('#berat', copy.weight);
                attachField('#stok', copy.stock);
                attachField('#harga', copy.harga);
                attachField('#jml', copy.qty);
                attachField('#terkirim_awal', copy.sentQty);
                attachField('#invoice_awal', copy.billedValue);
                attachEach('#tombolSimpanItem', copy.saveItem || copy.addItemSection);
                attachEach('#tombolEditItem', copy.editItemButton || copy.edit);

                attachEach('.tampilDataDetail table, #datadetail', copy.tableItemList || copy.table);
                attachHeaderCell('no', copy.rowNumber);
                attachHeaderCell('kode produk', copy.productCode);
                attachHeaderCell('nama produk', copy.productName);
                attachHeaderCell('berat satuan', copy.weight);
                attachHeaderCell('jumlah', copy.qty);
                attachHeaderCell('qty terkirim', copy.sentQty);
                attachHeaderCell('nilai sudah ditagihkan', copy.billedValue);
                attachHeaderCell('subtotal', copy.subtotalWeight);
                attachHeaderCell('harga', copy.harga);
                attachHeaderCell('aksi', copy.itemActionColumn || copy.aksi);
                attachEach('.po-close-badge', copy.closedBadge || copy.statusBadge);
                attachEach('.po-close-history-toggle', copy.closeHistoryButton || copy.closeHistory);
                attachEach('.po-close-history-popover', copy.closeHistory);
                attachEach('.btn-reopen-close', copy.reopenClose);
                attachEach('.btn-close-item', copy.closeItemButton || copy.closePo);
                attachEach('.btn-koreksi-qty', copy.correctionButton || copy.editItemButton);
            }
            function attachPoKeluarTooltips() {
                 if (previewKey !== 'po-keluar') {
                     return;
                 }

                 function headerMatches(text, matcher) {
                     return matcher.exact ? text === matcher.text : text.indexOf(matcher.text) !== -1;
                 }

                 function attachColumnByHeader(matchers, message) {
                     document.querySelectorAll('table').forEach(function(table) {
                         const headers = Array.from(table.querySelectorAll('thead th'));
                         if (!headers.length) {
                             return;
                         }

                         headers.forEach(function(header, index) {
                             const headerText = normalizedText(header);
                             if (!matchers.some(function(matcher) { return headerMatches(headerText, matcher); })) {
                                 return;
                             }

                             attachTooltip(header, message || copy.sortColumn);
                             table.querySelectorAll('tbody tr').forEach(function(row) {
                                 const cell = row.children[index];
                                 if (cell) {
                                     attachTooltip(cell, message);
                                 }
                             });
                         });
                     });
                 }

                 function attachFieldByText(words, message) {
                     if (!message) {
                         return;
                     }

                     document.querySelectorAll('label, .form-group > label, .form-check-label, h5, h6, .card-title, .card-header, strong').forEach(function(label) {
                         const text = normalizedText(label);
                         if (!words.some(function(word) { return text.indexOf(word) !== -1; })) {
                             return;
                         }

                         const container = label.closest('.form-group, .form-check, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-8, .col-md-12, [class*="col-"]') || label;
                         attachTooltip(container, message);
                         container.querySelectorAll('input, select, textarea, .form-control, .custom-select').forEach(function(input) {
                             attachTooltip(input, message);
                         });
                     });
                 }


                 function attachCompactTextTooltip(selector, words, message, closestSelector) {
                     if (!message) {
                         return;
                     }
                     document.querySelectorAll(selector).forEach(function(element) {
                         const text = normalizedText(element);
                         if (!words.some(function(word) { return text.indexOf(word) !== -1; })) {
                             return;
                         }
                         const target = closestSelector ? (element.closest(closestSelector) || element) : element;
                         if (normalizedText(target).length > 220 && target !== element) {
                             attachTooltip(element, message);
                             return;
                         }
                         attachTooltip(target, message);
                         attachTooltip(element, message);
                     });
                 }
                 function attachCardByHeading(words, message) {
                     if (!message) {
                         return;
                     }
                     document.querySelectorAll('.card, .info-box, .small-box, .summary-card, [class*="card"], [class*="panel"]').forEach(function(card) {
                         const text = normalizedText(card);
                         if (!words.some(function(word) { return text.indexOf(word) !== -1; })) {
                             return;
                         }
                         attachTooltip(card, message);
                         card.querySelectorAll('h1, h2, h3, h4, h5, h6, .card-title, .card-header, button, a, i, svg, strong, p, small, span, .progress, .progress-bar, [class*="toggle"], [class*="chevron"], [class*="progress"], [class*="metric"], [class*="stat"], [class*="badge"]').forEach(function(child) {
                             attachTooltip(child, message);
                         });
                     });
                 }

                 function attachPoKeluarDetailViewTooltips() {
                     const detailRoot = document.querySelector('.po-detail-grid');
                     if (!detailRoot) {
                         return;
                     }

                     document.querySelectorAll('.po-detail-subtitle').forEach(function(element) {
                         attachTooltip(element, copy.detailPoNumber);
                     });
                     document.querySelectorAll('.po-detail-actions .btn-outline-secondary, .po-detail-actions a[href*="poKeluar/data"]').forEach(function(element) {
                         attachTooltip(element, copy.back);
                     });
                     document.querySelectorAll('.po-detail-actions .btn-info, .po-detail-actions a[href*="poKeluar/cetak"]').forEach(function(element) {
                         attachTooltip(element, copy.printPoButton);
                     });

                     const statMessages = {
                         'status po': copy.summaryStatusPo,
                         'penerimaan': copy.summaryPenerimaan,
                         'supplier': copy.summarySupplier,
                         'tanggal po': copy.summaryDate
                     };
                     document.querySelectorAll('.po-stat-card').forEach(function(card) {
                         const label = normalizedText(card.querySelector('.po-stat-label') || card);
                         Object.keys(statMessages).forEach(function(key) {
                             if (label.indexOf(key) !== -1) {
                                 attachTooltip(card, statMessages[key]);
                                 card.querySelectorAll('.po-stat-icon, .po-stat-label, .po-stat-value, i').forEach(function(child) {
                                     attachTooltip(child, statMessages[key]);
                                 });
                             }
                         });
                     });

                     document.querySelectorAll('.po-card-header').forEach(function(header) {
                         const text = normalizedText(header);
                         if (text.indexOf('rincian item') !== -1) {
                             attachTooltip(header.closest('.po-card') || header, copy.rincianItem);
                             attachTooltip(header, copy.rincianItem);
                         } else if (text.indexOf('informasi po') !== -1) {
                             attachTooltip(header.closest('.po-card') || header, copy.informasiPo);
                             attachTooltip(header, copy.informasiPo);
                         } else if (text.indexOf('progres penerimaan') !== -1) {
                             attachTooltip(header.closest('.po-card') || header, copy.progressPenerimaan);
                             attachTooltip(header, copy.progressPenerimaan);
                         } else if (text.indexOf('keterangan material masuk') !== -1) {
                             attachTooltip(header.closest('.po-card') || header, copy.materialReceiptInfo);
                             attachTooltip(header, copy.materialReceiptInfo);
                         }
                         header.querySelectorAll('.po-card-toggle-icon, i, svg').forEach(function(icon) {
                             attachTooltip(icon, copy.panelToggle);
                         });
                     });

                     document.querySelectorAll('.po-card-badge').forEach(function(badge) {
                         const cardText = normalizedText(badge.closest('.po-card') || badge);
                         attachTooltip(badge, cardText.indexOf('keterangan material masuk') !== -1 ? copy.materialReceiptCount : copy.itemCount);
                     });
                     document.querySelectorAll('.po-item-total-row, .po-item-total-row .label, .po-item-total-row .value').forEach(function(element) {
                         attachTooltip(element, copy.totalPo);
                     });

                     const infoMessages = {
                         'no. po': copy.infoNoPo,
                         'tanggal po': copy.infoTanggalPo,
                         'supplier / vendor': copy.infoSupplierVendor,
                         'jenis po': copy.infoJenisPo,
                         'jenis transaksi': copy.infoJenisTransaksi,
                         'material produksi': copy.infoProductionMaterial,
                         'po asal': copy.infoPoAsal,
                         'kirim langsung': copy.infoDirectSend,
                         'po masuk terkait': copy.infoRelatedPoIn,
                         'keterangan': copy.infoDescription
                     };
                     document.querySelectorAll('.po-info-list .po-info-row').forEach(function(row) {
                         const label = normalizedText(row.querySelector('.po-info-label') || row);
                         Object.keys(infoMessages).forEach(function(key) {
                             if (label.indexOf(key) !== -1) {
                                 attachTooltip(row, infoMessages[key]);
                                 row.querySelectorAll('.po-info-label, .po-info-value, span').forEach(function(child) {
                                     attachTooltip(child, infoMessages[key]);
                                 });
                             }
                         });
                     });

                     document.querySelectorAll('.po-progress-bar-track, .po-progress-bar-fill, .po-progress-caption').forEach(function(element) {
                         attachTooltip(element, copy.progressPenerimaan);
                     });
                     document.querySelectorAll('.po-progress-split-box').forEach(function(box) {
                         const text = normalizedText(box);
                         const message = text.indexOf('sudah diterima') !== -1 ? copy.progressReceivedQty : copy.progressRemainingQty;
                         attachTooltip(box, message);
                         box.querySelectorAll('span').forEach(function(child) {
                             attachTooltip(child, message);
                         });
                     });
                     const progressMessages = {
                         'status payment': copy.progressPaymentStatus,
                         'termin': copy.progressTermin,
                         'shipping to': copy.progressShippingTo || copy.shippingTo
                     };
                     document.querySelectorAll('.po-progress-info-row').forEach(function(row) {
                         const label = normalizedText(row.querySelector('.po-info-label') || row);
                         Object.keys(progressMessages).forEach(function(key) {
                             if (label.indexOf(key) !== -1) {
                                 attachTooltip(row, progressMessages[key]);
                                 row.querySelectorAll('.po-info-label, .po-info-value, .po-badge, span').forEach(function(child) {
                                     attachTooltip(child, progressMessages[key]);
                                 });
                             }
                         });
                     });

                     document.querySelectorAll('.po-empty-state').forEach(function(element) {
                         attachTooltip(element, copy.materialReceiptEmpty || copy.materialReceiptInfo);
                     });
                     document.querySelectorAll('.po-material-masuk-list .po-info-row').forEach(function(row) {
                         attachTooltip(row, copy.materialReceiptRow || copy.materialReceiptInfo);
                         const label = row.querySelector('.po-info-label');
                         const value = row.querySelector('.po-info-value');
                         if (label) attachTooltip(label, copy.materialReceiptNumber || copy.materialReceiptRow);
                         if (value) attachTooltip(value, copy.materialReceiptDate || copy.materialReceiptRow);
                     });
                 }
                 function attachPoKeluarEditFormTooltips() {
                     const pageTitle = normalizedText(document.querySelector('h1, h2, h3, .content-header h1') || document.body);
                     if (pageTitle.indexOf('edit po keluar') === -1 && pageTitle.indexOf('ubah po keluar') === -1) {
                         return;
                     }

                     attachByText('.alert.alert-success, .alert-success', ['belum dipakai transaksi lain'], copy.editableNotice || copy.lockNotice, '.alert, .alert-success');
                     attachByText('.alert.alert-info, .alert-info, .alert.alert-warning, .alert-warning', ['sudah dipakai transaksi lain', 'item po dikunci', 'bisa diubah cuma keterangan'], copy.lockNotice, '.alert, .alert-info, .alert-warning');
                     attachByText('.alert .mt-2, .alert .small, .alert span, .alert div', ['dipakai di'], copy.usedBy || copy.lockNotice, '.mt-2, .small, .alert div, span');
                     attachByText('.badge, .label, span, small', ['qty penerimaan tercatat'], copy.usedReceptionQty, '.badge, .label, span, small');
                     attachByText('.badge, .label, span, small', ['penerimaan material'], copy.usedMaterialReceipt, '.badge, .label, span, small');
                     attachByText('a, button', ['simpan perubahan'], copy.saveChanges || copy.savePo || copy.finish, '.btn, a, button');
                     attachByText('a, button', ['tambah item'], copy.saveItem || copy.detailItemSection, '.btn, a, button');

                     attachFieldByText(['no. po keluar', 'no po keluar'], copy.poNumberInput || copy.noPo);
                     attachFieldByText(['tanggal po'], copy.dateInput || copy.tanggal);
                     attachFieldByText(['supplier/vendor', 'supplier', 'vendor'], copy.supplierInput || copy.supplier);
                     attachFieldByText(['jenis po'], copy.poTypeInput || copy.jenisPo);
                     attachFieldByText(['jenis transaksi'], copy.transactionTypeInput || copy.transaksi);
                     attachFieldByText(['po asal'], copy.poOrigin || copy.infoPoAsal);
                     attachFieldByText(['po masuk terkait'], copy.relatedPoIn || copy.infoRelatedPoIn);
                     attachFieldByText(['material produksi'], copy.infoProductionMaterial);
                     attachFieldByText(['kirim langsung'], copy.directSend || copy.infoDirectSend);
                     attachFieldByText(['keterangan'], copy.notes || copy.infoDescription);
                     attachFieldByText(['label detail'], copy.labelDetail || copy.labelUkuran);
                     attachFieldByText(['label ukuran'], copy.labelUkuran || copy.labelDetail);
                     attachFieldByText(['info print'], copy.infoPrint);
                     attachFieldByText(['top'], copy.top);
                     attachFieldByText(['system payment'], copy.systemPayment);
                     attachFieldByText(['shipping to'], copy.shippingTo);
                     attachFieldByText(['quot number', 'quote number'], copy.quoteNumber);
                     attachFieldByText(['approved by'], copy.approvedBy);
                     attachFieldByText(['discount'], copy.discount);
                     attachFieldByText(['ppn 11%', 'harga sudah termasuk ppn'], copy.ppn);
                     attachFieldByText(['pph 23', 'potong pph 23'], copy.pph23);
                     attachFieldByText(['notes cetak'], copy.notesCetak);
                     attachByText('small, .form-text', ['dipakai sebagai label detail item', 'label detail item pada print', 'label kolom material'], copy.labelDetail || copy.labelUkuran, '.form-group, small, .form-text');
                     attachByText('.form-check, .custom-control, label, small, .form-text', ['tampilkan kolom material', 'kolom material tidak muncul'], copy.materialColumnPrint || copy.infoProductionMaterial, '.form-group, .form-check, .custom-control, label, small, .form-text');

                     attachColumnByHeader([{ text: 'no', exact: true }], copy.rowNumber);
                     attachColumnByHeader([{ text: 'item', exact: true }], copy.detailItemName || copy.detailItemSection);
                     attachColumnByHeader([{ text: 'info print' }], copy.infoPrint);
                     attachColumnByHeader([{ text: 'qty', exact: true }], copy.qty);
                     attachColumnByHeader([{ text: 'harga', exact: true }], copy.hargaSatuan || copy.nominal);
                     attachColumnByHeader([{ text: 'subtotal' }], copy.subtotal || copy.nominal);
                     attachColumnByHeader([{ text: '#', exact: true }], copy.aksi);
                 }
                 function attachReadonlyFields() {
                     document.querySelectorAll('input[readonly], input:disabled, select:disabled, textarea:disabled, .form-control:disabled, .form-control[readonly]').forEach(function(element) {
                         if (element.dataset.manualOrderMessage) {
                             return;
                         }
                         attachTooltip(element, copy.readonlyField || copy.lockNotice || copy.table);
                     });
                 }

                 attachByText('h1, h2, h3, .content-header h1', ['data po keluar'], copy.table);
                 attachByText('h1, h2, h3, .content-header h1', ['edit po keluar'], copy.editPage || copy.edit);
                 attachByText('h1, h2, h3, .content-header h1', ['ubah po keluar'], copy.editPage || copy.edit);
                 attachByText('h1, h2, h3, .content-header h1', ['input po keluar'], copy.inputPage || copy.input);
                 attachByText('h1, h2, h3, .content-header h1', ['detail po keluar'], copy.detailPage || copy.detail);
                 attachByText('a, button', ['cetak po'], copy.printPoButton, '.btn, a, button');
                 attachByText('code, .text-muted, p, span, .breadcrumb, .page-subtitle', ['test po aja ini', 'po-'], copy.detailPoNumber, 'code, .text-muted, p, span, .breadcrumb, .page-subtitle');
                 attachByText('.card, .info-box, .small-box, .summary-card, .col-md-3, .col-md-4', ['status po'], copy.summaryStatusPo, '.card, .info-box, .small-box, .summary-card, .col-md-3, .col-md-4');
                 attachByText('.card, .info-box, .small-box, .summary-card, .col-md-3, .col-md-4', ['penerimaan'], copy.summaryPenerimaan, '.card, .info-box, .small-box, .summary-card, .col-md-3, .col-md-4');
                 attachByText('.card, .info-box, .small-box, .summary-card, .col-md-3, .col-md-4', ['supplier'], copy.summarySupplier, '.card, .info-box, .small-box, .summary-card, .col-md-3, .col-md-4');
                 attachByText('.card, .info-box, .small-box, .summary-card, .col-md-3, .col-md-4', ['tanggal po'], copy.summaryDate, '.card, .info-box, .small-box, .summary-card, .col-md-3, .col-md-4');
                 attachByText('.card-header, h5, h6, strong', ['rincian item'], copy.rincianItem, '.card, .card-header, h5, h6, strong');
                 attachByText('.badge, .label, span, small', ['item'], copy.itemCount, '.badge, .label, span, small');
                 attachByText('.card-header, h5, h6, strong', ['informasi po'], copy.informasiPo, '.card, .card-header, h5, h6, strong');
                 attachByText('dt, th, td, strong, .font-weight-bold, .fw-bold', ['no. po'], copy.infoNoPo, 'tr, .row, dt, th, td');
                 attachByText('dt, th, td, strong, .font-weight-bold, .fw-bold', ['tanggal po'], copy.infoTanggalPo, 'tr, .row, dt, th, td');
                 attachByText('dt, th, td, strong, .font-weight-bold, .fw-bold', ['supplier / vendor', 'supplier/vendor'], copy.infoSupplierVendor, 'tr, .row, dt, th, td');
                 attachByText('dt, th, td, strong, .font-weight-bold, .fw-bold', ['jenis po'], copy.infoJenisPo, 'tr, .row, dt, th, td');
                 attachByText('dt, th, td, strong, .font-weight-bold, .fw-bold', ['jenis transaksi'], copy.infoJenisTransaksi, 'tr, .row, dt, th, td');
                 attachByText('dt, th, td, strong, .font-weight-bold, .fw-bold', ['po asal'], copy.infoPoAsal, 'tr, .row, dt, th, td');
                 attachByText('.card-header, h5, h6, strong', ['detail item po keluar'], copy.detailItemSection || copy.detail);
                 attachByText('.card-header, h5, h6, strong', ['pengaturan cetak po'], copy.printInfo || copy.aksi);
                 attachByText('.alert, .callout, .info-box, .alert-info, .alert-warning', ['sudah dipakai transaksi lain', 'item po dikunci', 'bisa diubah cuma keterangan', 'dipakai di'], copy.lockNotice);
                 attachByText('.badge, .label, span, small', ['qty penerimaan tercatat'], copy.usedReceptionQty);
                 attachByText('.badge, .label, span, small', ['penerimaan material'], copy.usedMaterialReceipt);
                 attachByText('.badge, .label, span, small', ['dipakai di'], copy.usedBy || copy.lockNotice);
                 attachByText('a, button', ['input po keluar'], copy.input, '.btn, a, button');
                 attachByText('a, button', ['kembali'], copy.back, '.btn, a, button');
                 attachByText('a, button', ['detail'], copy.viewButton || copy.detail, '.btn, a, button');
                 attachByText('a, button', ['edit'], copy.editButton || copy.edit, '.btn, a, button');
                 attachByText('a, button', ['batalkan', 'batal', 'nonaktif'], copy.cancelButton || copy.cancel, '.btn, a, button');
                 attachByText('a, button', ['hapus'], copy.delete || copy.aksi, '.btn, a, button');

                 attachFieldByText(['no. po keluar', 'no po keluar'], copy.poNumberInput || copy.noPo);
                 attachFieldByText(['tanggal po'], copy.dateInput || copy.tanggal);
                 attachFieldByText(['supplier/vendor', 'supplier', 'vendor'], copy.supplierInput || copy.supplier);
                 attachFieldByText(['jenis po'], copy.poTypeInput || copy.jenisPo);
                 attachFieldByText(['jenis transaksi'], copy.transactionTypeInput || copy.transaksi);
                 attachFieldByText(['po asal'], copy.poOrigin);
                 attachFieldByText(['po masuk terkait'], copy.relatedPoIn);
                 attachFieldByText(['kirim langsung'], copy.directSend);
                 attachFieldByText(['keterangan'], copy.notes);
                 attachFieldByText(['label ukuran'], copy.labelUkuran);
                 attachFieldByText(['info print'], copy.infoPrint);
                 attachFieldByText(['top'], copy.top);
                 attachFieldByText(['system payment'], copy.systemPayment);
                 attachFieldByText(['shipping to'], copy.shippingTo);
                 attachFieldByText(['quot number', 'quote number'], copy.quoteNumber);
                 attachFieldByText(['approved by'], copy.approvedBy);
                 attachFieldByText(['discount'], copy.discount);
                 attachFieldByText(['ppn 11%', 'harga sudah termasuk ppn'], copy.ppn);
                 attachFieldByText(['pph 23', 'potong pph 23'], copy.pph23);
                 attachFieldByText(['notes cetak'], copy.notesCetak);
                 attachByText('.form-check, .custom-control, label, strong, span, small', ['harga sudah termasuk ppn'], copy.ppn, '.form-check, .custom-control, label, span, small');
                 attachByText('.form-check, .custom-control, label, strong, span, small', ['potong pph 23'], copy.pph23, '.form-check, .custom-control, label, span, small');
                 attachByText('td, th', ['po material'], copy.poMaterial || copy.jenisPo);
                 attachByText('td, th', ['po produk'], copy.poProduk || copy.jenisPo);
                 attachByText('td, th', ['po jasa'], copy.poJasa || copy.jenisPo);
                 attachByText('td, th', ['titip proses'], copy.titipProses || copy.transaksi);
                 attachByText('td, th', ['diterima lengkap'], copy.diterimaLengkap || copy.penerimaan);
                 attachByText('td, th', ['diterima sebagian'], copy.diterimaSebagian || copy.penerimaan);
                 attachByText('td, th', ['belum diterima'], copy.belumDiterima || copy.penerimaan);
                 attachByText('td, th', ['selesai'], copy.statusSelesai || copy.penerimaan);

                 attachColumnByHeader([{ text: 'no', exact: true }], copy.rowNumber);
                 attachColumnByHeader([{ text: 'status', exact: true }], copy.activeStatus || copy.statusBadge);
                 attachColumnByHeader([{ text: 'jenis po' }], copy.jenisPo);
                 attachColumnByHeader([{ text: 'no. po' }, { text: 'no po' }, { text: 'no. po keluar' }, { text: 'no po keluar' }], copy.noPo);
                 attachColumnByHeader([{ text: 'tanggal' }, { text: 'tanggal po' }], copy.tanggal);
                 attachColumnByHeader([{ text: 'supplier' }, { text: 'vendor' }], copy.supplier);
                 attachColumnByHeader([{ text: 'transaksi' }, { text: 'jenis transaksi' }], copy.transaksi);
                 attachColumnByHeader([{ text: 'qty', exact: true }, { text: 'jumlah', exact: true }], copy.qty);
                 attachColumnByHeader([{ text: 'total nominal' }, { text: 'subtotal' }], copy.nominal);
                 attachColumnByHeader([{ text: 'status penerimaan' }], copy.penerimaan);
                 attachColumnByHeader([{ text: 'aksi', exact: true }, { text: '#', exact: true }], copy.aksi);
                 attachColumnByHeader([{ text: 'kode' }, { text: 'kode item' }], copy.detailItemSection);
                 attachColumnByHeader([{ text: 'nama item' }, { text: 'item' }], copy.detailItemSection);
                 attachColumnByHeader([{ text: 'nama item' }], copy.detailItemName || copy.detailItemSection);
                 attachColumnByHeader([{ text: 'satuan' }], copy.satuan);
                 attachColumnByHeader([{ text: 'jumlah pesan' }], copy.jumlahPesan || copy.qty);
                 attachColumnByHeader([{ text: 'jumlah masuk' }], copy.jumlahMasuk || copy.penerimaan);
                 attachColumnByHeader([{ text: 'harga', exact: true }], copy.hargaSatuan || copy.nominal);
                 attachColumnByHeader([{ text: 'subtotal' }], copy.subtotal || copy.nominal);
                 attachColumnByHeader([{ text: 'info print' }], copy.infoPrint);
                 attachColumnByHeader([{ text: 'harga' }], copy.nominal);


                 attachCompactTextTooltip('a, button', ['cetak po'], copy.printPoButton, '.btn, a, button');
                 attachCompactTextTooltip('.card, .info-box, .small-box, .summary-card, [class*="summary"], [class*="stat"]', ['status po'], copy.summaryStatusPo);
                 attachCompactTextTooltip('.card, .info-box, .small-box, .summary-card, [class*="summary"], [class*="stat"]', ['penerimaan'], copy.summaryPenerimaan);
                 attachCompactTextTooltip('.card, .info-box, .small-box, .summary-card, [class*="summary"], [class*="stat"]', ['supplier'], copy.summarySupplier);
                 attachCompactTextTooltip('.card, .info-box, .small-box, .summary-card, [class*="summary"], [class*="stat"]', ['tanggal po'], copy.summaryDate);
                 attachCompactTextTooltip('h5, h6, strong, .card-header, .card-title', ['informasi po'], copy.informasiPo, '.card, .card-header, .card-title, h5, h6, strong');
                 attachCompactTextTooltip('dt, dd, th, td, strong, span, small, label, .row div, .d-flex div, .list-group-item, p', ['no. po'], copy.infoNoPo, 'tr, .row, .d-flex, .list-group-item, dt, dd, th, td');
                 attachCompactTextTooltip('dt, dd, th, td, strong, span, small, label, .row div, .d-flex div, .list-group-item, p', ['tanggal po'], copy.infoTanggalPo, 'tr, .row, .d-flex, .list-group-item, dt, dd, th, td');
                 attachCompactTextTooltip('dt, dd, th, td, strong, span, small, label, .row div, .d-flex div, .list-group-item, p', ['supplier / vendor', 'supplier/vendor', 'supplier'], copy.infoSupplierVendor, 'tr, .row, .d-flex, .list-group-item, dt, dd, th, td');
                 attachCompactTextTooltip('dt, dd, th, td, strong, span, small, label, .row div, .d-flex div, .list-group-item, p', ['jenis po'], copy.infoJenisPo, 'tr, .row, .d-flex, .list-group-item, dt, dd, th, td');
                 attachCompactTextTooltip('dt, dd, th, td, strong, span, small, label, .row div, .d-flex div, .list-group-item, p', ['jenis transaksi'], copy.infoJenisTransaksi, 'tr, .row, .d-flex, .list-group-item, dt, dd, th, td');
                 attachCompactTextTooltip('dt, dd, th, td, strong, span, small, label, .row div, .d-flex div, .list-group-item, p', ['po asal'], copy.infoPoAsal, 'tr, .row, .d-flex, .list-group-item, dt, dd, th, td');
                 attachCompactTextTooltip('dt, dd, th, td, strong, span, small, label, .row div, .d-flex div, .list-group-item, p', ['kirim langsung'], copy.infoDirectSend, 'tr, .row, .d-flex, .list-group-item, dt, dd, th, td');
                 attachCompactTextTooltip('dt, dd, th, td, strong, span, small, label, .row div, .d-flex div, .list-group-item, p', ['po masuk terkait'], copy.infoRelatedPoIn, 'tr, .row, .d-flex, .list-group-item, dt, dd, th, td');
                 attachCompactTextTooltip('dt, dd, th, td, strong, span, small, label, .row div, .d-flex div, .list-group-item, p', ['keterangan'], copy.infoDescription, 'tr, .row, .d-flex, .list-group-item, dt, dd, th, td');
                 attachCompactTextTooltip('h5, h6, strong, .card-header, .card-title, p, small, span', ['progres penerimaan', '% diterima'], copy.progressPenerimaan, '.card, .card-header, .card-title, p, small, span');
                 attachCardByHeading(['progres penerimaan'], copy.progressPenerimaan);
                 attachCompactTextTooltip('dt, dd, th, td, strong, span, small, label, .row div, .d-flex div, .list-group-item, p, [class*="metric"], [class*="stat"]', ['sudah diterima'], copy.progressReceivedQty, 'tr, .row, .d-flex, .list-group-item, dt, dd, th, td, [class*="metric"], [class*="stat"]');
                 attachCompactTextTooltip('dt, dd, th, td, strong, span, small, label, .row div, .d-flex div, .list-group-item, p, [class*="metric"], [class*="stat"]', ['sisa belum diterima'], copy.progressRemainingQty, 'tr, .row, .d-flex, .list-group-item, dt, dd, th, td, [class*="metric"], [class*="stat"]');
                 attachCompactTextTooltip('dt, dd, th, td, strong, span, small, label, .row div, .d-flex div, .list-group-item, p', ['status payment'], copy.progressPaymentStatus, 'tr, .row, .d-flex, .list-group-item, dt, dd, th, td');
                 attachCompactTextTooltip('dt, dd, th, td, strong, span, small, label, .row div, .d-flex div, .list-group-item, p', ['termin'], copy.progressTermin, 'tr, .row, .d-flex, .list-group-item, dt, dd, th, td');
                 attachCompactTextTooltip('dt, dd, th, td, strong, span, small, label, .row div, .d-flex div, .list-group-item, p', ['shipping to'], copy.progressShippingTo || copy.shippingTo, 'tr, .row, .d-flex, .list-group-item, dt, dd, th, td');
                 attachCardByHeading(['keterangan material masuk'], copy.materialReceiptInfo);
                 attachCompactTextTooltip('h5, h6, strong, .card-header, .card-title, p, small, span, [class*="card"], [class*="panel"]', ['keterangan material masuk'], copy.materialReceiptInfo, '.card, [class*="card"], [class*="panel"], h5, h6, strong');
                 attachCompactTextTooltip('span, small, strong, p, .badge, [class*="badge"], [class*="pill"]', ['surat jalan'], copy.materialReceiptCount, '.badge, [class*="badge"], [class*="pill"], span, small, strong, p');
                 attachPoKeluarDetailViewTooltips();
                 attachReadonlyFields();
                 attachPoKeluarEditFormTooltips();

                 // Ikon yang sama dipakai ulang dengan arti beda di form Input/Edit PO
                 // Keluar (fa-plus-circle = "Tambah Item" ke tabel, bukan bikin PO baru;
                 // fa-trash-alt di baris tabel item = hapus 1 baris, bukan hapus PO;
                 // tombol submit "Simpan PO Keluar" di form Input beda konteks dari
                 // "Simpan Perubahan" di form Edit) -- tebakan generik gampang salah
                 // pilih copy key, jadi di-set eksplisit di sini dan SENGAJA selalu
                 // nimpa (attachTooltip, bukan attachTooltipIfEmpty).
                 document.querySelectorAll('#tambahItem, #addPoItem').forEach(function(el) {
                     attachTooltip(el, copy.addItemRow);
                 });
                 document.querySelectorAll('#tableDetailPoKeluar .btn-danger, #poEditItemsTable .remove-row').forEach(function(el) {
                     attachTooltip(el, copy.removeItemRow);
                 });
                 document.querySelectorAll('#formPoKeluar button[type="submit"]').forEach(function(el) {
                     attachTooltip(el, copy.saveNewPo);
                 });

                 document.querySelectorAll('.dataTables_info').forEach(function(element) {
                     attachTooltip(element, copy.tableInfo || copy.table);
                 });
                 document.querySelectorAll('.dataTables_length, .dataTables_length select').forEach(function(element) {
                     attachTooltip(element, copy.filterPanel || copy.tableInfo || copy.table);
                 });
                 document.querySelectorAll('.dataTables_paginate, .paginate_button, .pagination, .page-link').forEach(function(element) {
                     attachTooltip(element, copy.pagination || copy.tableInfo || copy.table);
                 });
             }

             function attachKeuanganTooltips() {
                 if (previewKey !== 'keuangan') {
                     return;
                 }

                 const path = (window.location.pathname || '').toLowerCase();
                 function pathIs(fragment) {
                     return path.indexOf(fragment.toLowerCase()) !== -1;
                 }

                 function attachHeaderText(words, message, exact) {
                     document.querySelectorAll('th').forEach(function(header) {
                         const text = normalizedText(header);
                         const match = exact ? words.indexOf(text) !== -1 : words.some(function(word) { return text.indexOf(word) !== -1; });
                         if (match) {
                             attachTooltip(header, message);
                         }
                     });
                 }

                 // ---- Hub Keuangan (3 card: Invoice Out/Invoice In/Reporting) ----
                 if (path === '/invoicehub' || path === '/invoicehub/') {
                     attachByText('.card, .card-body', ['invoice out'], copy.invoiceOut, '.card');
                     attachByText('.card, .card-body', ['invoice in'], copy.invoiceIn, '.card');
                     attachByText('.card, .card-body', ['reporting'], copy.reporting, '.card');
                     attachByText('a, button', ['buka'], copy.openButton, '.btn, a, button');
                 }

                 // ---- Invoice Out: daftar (invoiceOut/data) ----
                 if (pathIs('/invoiceout/data')) {
                     attachByText('a, button', ['generate invoice out'], copy.outGenerateButton, '.btn, a, button');
                     attachByText('a, button', ['catat pembayaran'], copy.outPembayaranButton, '.btn, a, button');
                     attachHeaderText(['no'], copy.outTableNo, true);
                     attachHeaderText(['no. invoice'], copy.outTableNoInvoice);
                     attachHeaderText(['tanggal'], copy.outTableTanggal);
                     attachHeaderText(['no. po'], copy.outTableNoPo);
                     attachHeaderText(['pelanggan'], copy.outTablePelanggan);
                     attachHeaderText(['grand total'], copy.outTableGrandTotal);
                     attachHeaderText(['sudah bayar'], copy.outTableSudahBayar);
                     attachHeaderText(['sisa'], copy.outTableSisa);
                     attachHeaderText(['status'], copy.outTableStatus);
                     attachHeaderText(['status bayar'], copy.outTableStatusBayar);
                     attachHeaderText(['aksi'], copy.aksi);
                     document.querySelectorAll('#invoiceOutTable .fa-eye').forEach(function(icon) { attachTooltip(icon.closest('a, button') || icon, copy.outAksiLihat); });
                     document.querySelectorAll('#invoiceOutTable .fa-file-export').forEach(function(icon) { attachTooltip(icon.closest('a, button') || icon, copy.outAksiExtract); });
                     document.querySelectorAll('#invoiceOutTable .dropdown-menu').forEach(function(menu) { attachTooltip(menu, copy.outAksiExtract); });
                     // Item "Print"/"Excel" DI DALAM dropdown itu sendiri (bukan cuma
                     // container-nya) perlu di-override eksplisit -- soalnya ikon
                     // fa-print di dalamnya sudah kena aturan generik
                     // attachIconButton('.fa-print, ...') duluan yang nempel
                     // langsung ke elemen <a> ini (bukan ke container dropdown-nya),
                     // jadi override di atas (yang cuma nyentuh containernya) nggak
                     // ke-apply pas hover langsung di baris "Print"-nya.
                     document.querySelectorAll('#invoiceOutTable a[href*="invoiceOut/cetak/"]').forEach(function(el) { attachTooltip(el, copy.outAksiPrint); });
                     document.querySelectorAll('#invoiceOutTable a[href*="invoiceOut/cetakExcel/"]').forEach(function(el) { attachTooltip(el, copy.outAksiExcel); });
                     document.querySelectorAll('#invoiceOutTable .fa-money-check-alt').forEach(function(icon) { attachTooltip(icon.closest('a, button') || icon, copy.outAksiTandaiLunas); });
                     document.querySelectorAll('#invoiceOutTable .fa-ban').forEach(function(icon) { attachTooltip(icon.closest('a, button') || icon, copy.outAksiBatalkan); });
                     document.querySelectorAll('#invoiceOutTable .fa-trash').forEach(function(icon) { attachTooltip(icon.closest('a, button') || icon, copy.outAksiHapus); });
                 }

                 // ---- Invoice Out: form generate (invoiceOut/create) ----
                 if (pathIs('/invoiceout/create')) {
                     attachLabel(['pilih po yang memiliki'], copy.outFormPilihPo);
                     document.querySelectorAll('#pilihPoCombobox, #pilihPoText').forEach(function(el) { attachTooltip(el, copy.outFormPilihPo); });
                     attachByText('.card, .card-header', ['list surat jalan'], copy.outFormSuratJalanTable, '.card');
                     attachByText('a, button', ['tampilkan item invoice'], copy.outFormTampilkanItem, '.btn, a, button');
                     attachLabel(['no. invoice'], copy.outFormNoInvoice);
                     attachLabel(['tanggal invoice'], copy.outFormTanggalInvoice);
                     attachLabel(['nama penandatangan', 'jabatan penandatangan'], copy.outFormSigner);
                     attachLabel(['ppn'], copy.outFormPpn);
                     attachLabel(['pph 23'], copy.outFormPph);
                     attachLabel(['dp'], copy.outFormDp);
                     attachByText('.card, .card-header', ['pengaturan invoice'], copy.outFormBankInfo, '.card');
                     attachLabel(['pemilik rekening', 'nama bank', 'no rekening', 'npwp'], copy.outFormBankInfo);
                     document.querySelectorAll('.input-harga').forEach(function(el) { attachTooltip(el, copy.outFormHargaInput); });
                     document.querySelectorAll('#btnSimpanInvoice').forEach(function(el) { attachTooltip(el, copy.outFormSimpan); });
                 }

                 // ---- Invoice Out: detail (invoiceOut/detail) ----
                 if (pathIs('/invoiceout/detail')) {
                     attachByText('a, button', ['tandai lunas'], copy.outDetailTandaiLunas, '.btn, a, button');
                     attachByText('a, button', ['print'], copy.outDetailPrint, '.btn, a, button');
                     attachByText('h5', ['riwayat pembayaran'], copy.outDetailRiwayatBayar);
                 }

                 // ---- Invoice Out: catat pembayaran (invoiceOut/pembayaran) ----
                 if (pathIs('/invoiceout/pembayaran')) {
                     attachLabel(['pelanggan dengan invoice belum lunas'], copy.outBayarPilihPelanggan);
                     attachByText('a, button', ['tampilkan invoice'], copy.outBayarTampilkan, '.btn, a, button');
                     attachLabel(['no. pembayaran'], copy.outBayarNoPembayaran);
                     attachLabel(['tanggal bayar'], copy.outBayarTanggalBayar);
                     attachLabel(['nominal pembayaran'], copy.outBayarNominal);
                     attachLabel(['keterangan'], copy.outBayarKeterangan);
                     document.querySelectorAll('.check-invoice').forEach(function(el) { attachTooltip(el, copy.outBayarCheckbox); });
                     document.querySelectorAll('#btnAutoAlokasi').forEach(function(el) { attachTooltip(el, copy.outBayarAutoAlokasi); });
                     document.querySelectorAll('.input-alokasi').forEach(function(el) { attachTooltip(el, copy.outBayarAlokasiInput); });
                     document.querySelectorAll('#btnSimpanPembayaran').forEach(function(el) { attachTooltip(el, copy.outBayarSimpan); });
                 }

                 // ---- Invoice In: daftar (invoiceIn/data) ----
                 if (pathIs('/invoicein/data')) {
                     attachByText('a, button', ['catat invoice in'], copy.inCatatButton, '.btn, a, button');
                     attachHeaderText(['no'], copy.inTableNo, true);
                     attachHeaderText(['tanggal'], copy.inTableTanggal);
                     // 'supplier' generik dipasang duluan, baru 'no. invoice
                     // supplier' yang lebih spesifik -- soalnya headernya
                     // ("No. Invoice Supplier") mengandung substring
                     // "supplier", jadi kalau urutannya kebalik si generik
                     // bakal nimpa balik yang spesifik (attachTooltip selalu
                     // nimpa, yang belakangan menang).
                     attachHeaderText(['supplier'], copy.inTableSupplier);
                     attachHeaderText(['no. invoice supplier'], copy.inTableNoInvoice);
                     attachHeaderText(['sumber'], copy.inTableSumber);
                     attachHeaderText(['grand total'], copy.inTableGrandTotal);
                     attachHeaderText(['status'], copy.inTableStatus);
                     attachHeaderText(['aksi'], copy.aksi);
                     document.querySelectorAll('#invoiceInTable .fa-eye').forEach(function(icon) { attachTooltip(icon.closest('a, button') || icon, copy.inAksiLihat); });
                     document.querySelectorAll('#invoiceInTable .fa-ban').forEach(function(icon) { attachTooltip(icon.closest('a, button') || icon, copy.inAksiBatalkan); });
                     document.querySelectorAll('#invoiceInTable .fa-trash-alt').forEach(function(icon) { attachTooltip(icon.closest('a, button') || icon, copy.inAksiHapus); });
                 }

                 // ---- Invoice In: form catat (invoiceIn/create) ----
                 if (pathIs('/invoicein/create')) {
                     attachLabel(['pilih transaksi penerimaan'], copy.inFormPilihSumber);
                     document.querySelectorAll('#pilihSumberCombobox, #pilihSumberText').forEach(function(el) { attachTooltip(el, copy.inFormPilihSumber); });
                     attachByText('.card, .card-header', ['list surat jalan'], copy.inFormSuratJalanTable, '.card');
                     attachByText('a, button', ['tampilkan item invoice'], copy.inFormTampilkanItem, '.btn, a, button');
                     attachLabel(['no. invoice supplier'], copy.inFormNoInvoice);
                     attachLabel(['tanggal invoice'], copy.inFormTanggalInvoice);
                     attachLabel(['upload file invoice supplier'], copy.inFormUploadFile);
                     attachLabel(['ppn'], copy.inFormPpn);
                     attachLabel(['pph 23'], copy.inFormPph);
                     attachLabel(['dp'], copy.inFormDp);
                     document.querySelectorAll('.harga').forEach(function(el) { attachTooltip(el, copy.inFormHargaInput); });
                     attachByText('a, button', ['simpan invoice in'], copy.inFormSimpan, '.btn, a, button');
                 }

                 // ---- Invoice In: detail (invoiceIn/detail) ----
                 if (pathIs('/invoicein/detail')) {
                     attachHeaderText(['status'], copy.inDetailStatus);
                     attachByText('th', ['tanggal lunas'], copy.inDetailTanggalLunas);
                     attachByText('th', ['file invoice'], copy.inDetailFileInvoice);
                     attachByText('th', ['bukti transfer'], copy.inDetailBuktiTransfer);
                     attachByText('th', ['sumber', 'no. transaksi'], copy.inDetailSumberInfo);
                     attachByText('a, button', ['hapus'], copy.inDetailHapusFile, '.btn, a, button');
                     // Tombol "Lihat"/"Upload"/"Ganti" di baris File Invoice & Bukti
                     // Transfer sebelumnya jatuh ke fallback generik "Aksi" (soalnya
                     // ada di dalam <td>) -- di-scope pakai pola href biar akurat
                     // per-tombol, nggak tergantung teksnya "Upload" atau "Ganti".
                     document.querySelectorAll('a[href*="invoiceIn/file/"]').forEach(function(el) { attachTooltip(el, copy.inDetailLihatFile); });
                     document.querySelectorAll('a[href*="invoiceIn/uploadFileInvoice/"]').forEach(function(el) { attachTooltip(el, copy.inDetailGantiFile); });
                     document.querySelectorAll('a[href*="invoiceIn/buktiTransfer/"]').forEach(function(el) { attachTooltip(el, copy.inDetailLihatBukti); });
                     document.querySelectorAll('a[href*="invoiceIn/uploadBuktiTransfer/"]').forEach(function(el) { attachTooltip(el, copy.inDetailUploadBukti); });
                 }

                 // ---- Invoice In: upload file invoice ----
                 if (pathIs('/invoicein/uploadfileinvoice')) {
                     document.querySelectorAll('.table-borderless').forEach(function(el) { attachTooltip(el, copy.inUploadFileInfo); });
                     document.querySelectorAll('input[name="invoice_file"]').forEach(function(el) { attachTooltip(el, copy.inUploadFileInput); });
                     attachByText('a, button', ['upload file invoice'], copy.inUploadFileSubmit, '.btn, a, button');
                 }

                 // ---- Invoice In: upload bukti transfer ----
                 if (pathIs('/invoicein/uploadbuktitransfer')) {
                     document.querySelectorAll('.table-borderless').forEach(function(el) { attachTooltip(el, copy.inUploadBuktiInfo); });
                     document.querySelectorAll('input[name="bukti_transfer"]').forEach(function(el) { attachTooltip(el, copy.inUploadBuktiInput); });
                     attachByText('a, button', ['upload & tandai lunas'], copy.inUploadBuktiSubmit, '.btn, a, button');
                 }

                 // ---- Reporting (invoiceHub/reporting, 5 tab) ----
                 if (pathIs('/invoicehub/reporting')) {
                     document.querySelectorAll('.reporting-tabs .nav-link').forEach(function(tab) {
                         const text = normalizedText(tab);
                         let message = null;
                         if (text.indexOf('margin') !== -1) message = copy.repMarginTab;
                         else if (text.indexOf('piutang') !== -1) message = copy.repPiutangTab;
                         else if (text.indexOf('hutang') !== -1) message = copy.repHutangTab;
                         else if (text.indexOf('cashflow') !== -1) message = copy.repCashflowTab;
                         else if (text.indexOf('omzet') !== -1) message = copy.repOmzetTab;
                         if (message) attachTooltip(tab, message);
                     });
                     document.querySelectorAll('.reporting-tab-filter').forEach(function(form) {
                         form.querySelectorAll('input[type="date"]').forEach(function(el, idx) {
                             attachTooltip(el, idx === 0 ? copy.repTanggalAwal : copy.repTanggalAkhir);
                         });
                         form.querySelectorAll('select[name="pelanggan"]').forEach(function(el) { attachTooltip(el, copy.repPelanggan); });
                         form.querySelectorAll('button[type="submit"]').forEach(function(el) { attachTooltip(el, copy.repTampilkan); });
                     });
                     document.querySelectorAll('#omzetPeriodePrev').forEach(function(el) { attachTooltip(el, copy.repOmzetPeriodePrev); });
                     document.querySelectorAll('#omzetPeriodeNext').forEach(function(el) { attachTooltip(el, copy.repOmzetPeriodeNext); });

                     document.querySelectorAll('#tabMarginContent .reporting-summary-grid').forEach(function(el) { attachTooltip(el, copy.repMarginSummary); });
                     document.querySelectorAll('.input-material').forEach(function(el) { attachTooltip(el, copy.repMarginMaterial); });
                     document.querySelectorAll('.reset-material').forEach(function(el) { attachTooltip(el, copy.repMarginMaterialReset); });
                     document.querySelectorAll('.input-jasa').forEach(function(el) { attachTooltip(el, copy.repMarginJasa); });
                     document.querySelectorAll('.input-trpoh').forEach(function(el) { attachTooltip(el, copy.repMarginTrpoh); });
                     document.querySelectorAll('.input-total-modal').forEach(function(el) { attachTooltip(el, copy.repMarginTotalModal); });
                     document.querySelectorAll('.reset-total-modal').forEach(function(el) { attachTooltip(el, copy.repMarginTotalModalReset); });
                     document.querySelectorAll('.input-harga-jual').forEach(function(el) { attachTooltip(el, copy.repMarginHargaJual); });
                     document.querySelectorAll('.reset-harga-jual').forEach(function(el) { attachTooltip(el, copy.repMarginHargaJualReset); });
                     document.querySelectorAll('.margin-unit').forEach(function(el) { attachTooltip(el, copy.repMarginUnit); });
                     document.querySelectorAll('.prosentase').forEach(function(el) { attachTooltip(el, copy.repMarginProsentase); });
                     document.querySelectorAll('.margin-total, #grandTotalMargin').forEach(function(el) { attachTooltip(el, copy.repMarginTotal); });

                     document.querySelectorAll('#tabPiutangContent .reporting-summary-grid').forEach(function(el) { attachTooltip(el, copy.repPiutangSummary); });
                     document.querySelectorAll('#tabHutangContent .reporting-summary-grid').forEach(function(el) { attachTooltip(el, copy.repHutangSummary); });
                     document.querySelectorAll('#tabCashflowContent .reporting-summary-grid').forEach(function(el) { attachTooltip(el, copy.repCashflowSummary); });

                     // Header tabel data di tiap tab -- di-scope per container
                     // (bukan attachHeaderText global) soalnya nama kolom
                     // (No, Tanggal, Status, Grand Total, dst.) muncul ulang
                     // di beberapa tab dengan arti yang beda-beda.
                     function attachHeaderTextIn(containerSelector, words, message, exact) {
                         document.querySelectorAll(containerSelector + ' th').forEach(function(header) {
                             const text = normalizedText(header);
                             const match = exact ? words.indexOf(text) !== -1 : words.some(function(word) { return text.indexOf(word) !== -1; });
                             if (match) {
                                 attachTooltip(header, message);
                             }
                         });
                     }

                     attachHeaderTextIn('#tabPiutangContent', ['no'], copy.repPiutangNo, true);
                     attachHeaderTextIn('#tabPiutangContent', ['no invoice'], copy.repPiutangNoInvoice);
                     attachHeaderTextIn('#tabPiutangContent', ['tanggal'], copy.repPiutangTanggal);
                     attachHeaderTextIn('#tabPiutangContent', ['no po'], copy.repPiutangNoPo);
                     attachHeaderTextIn('#tabPiutangContent', ['pelanggan'], copy.repPiutangPelanggan);
                     attachHeaderTextIn('#tabPiutangContent', ['grand total'], copy.repPiutangGrandTotal);
                     attachHeaderTextIn('#tabPiutangContent', ['sudah bayar'], copy.repPiutangSudahBayar);
                     attachHeaderTextIn('#tabPiutangContent', ['sisa'], copy.repPiutangSisa);
                     attachHeaderTextIn('#tabPiutangContent', ['status'], copy.repPiutangStatus);

                     attachHeaderTextIn('#tabHutangContent', ['no'], copy.repHutangNo, true);
                     attachHeaderTextIn('#tabHutangContent', ['no invoice'], copy.repHutangNoInvoice);
                     attachHeaderTextIn('#tabHutangContent', ['tanggal'], copy.repHutangTanggal);
                     attachHeaderTextIn('#tabHutangContent', ['sumber'], copy.repHutangSumber);
                     attachHeaderTextIn('#tabHutangContent', ['supplier/vendor', 'supplier'], copy.repHutangSupplier);
                     attachHeaderTextIn('#tabHutangContent', ['grand total'], copy.repHutangGrandTotal);
                     attachHeaderTextIn('#tabHutangContent', ['status'], copy.repHutangStatus);

                     attachHeaderTextIn('#tabCashflowContent', ['no'], copy.repCashflowNo, true);
                     attachHeaderTextIn('#tabCashflowContent', ['tanggal'], copy.repCashflowTanggal);
                     attachHeaderTextIn('#tabCashflowContent', ['jenis'], copy.repCashflowJenis);
                     attachHeaderTextIn('#tabCashflowContent', ['no bukti/invoice'], copy.repCashflowNomor);
                     attachHeaderTextIn('#tabCashflowContent', ['pihak'], copy.repCashflowPihak);
                     attachHeaderTextIn('#tabCashflowContent', ['keterangan'], copy.repCashflowKeterangan);
                     attachHeaderTextIn('#tabCashflowContent', ['nominal'], copy.repCashflowNominal);

                     document.querySelectorAll('.report-bulanan-week').forEach(function(el) { attachTooltip(el, copy.repOmzetWeekTotal); });
                     document.querySelectorAll('.report-bulanan-kategori').forEach(function(el) { attachTooltip(el, copy.repOmzetKategoriTotal); });
                     document.querySelectorAll('.report-bulanan-grand-total').forEach(function(el) { attachTooltip(el, copy.repOmzetGrandTotal); });
                 }
             }

             function attachTransaksiMaterialTooltips() {
                 const groups = ['stok-material', 'kebutuhan-material', 'material-terbuang', 'material-masuk', 'pemakaian-material'];
                 if (groups.indexOf(previewKey) === -1) {
                     return;
                 }

                 function attachHeaderIn(containerSelector, matcher, message) {
                     document.querySelectorAll(containerSelector + ' thead th, ' + containerSelector + ' th').forEach(function(header, index) {
                         if (matcher(normalizedText(header), index)) {
                             attachTooltip(header, message);
                         }
                     });
                 }

                 // ---- Stok Material ----
                 if (previewKey === 'stok-material') {
                     document.querySelectorAll('#materialStockSearchInput').forEach(function(el) { attachTooltip(el, copy.search); });
                     document.querySelectorAll('#materialStockPageLength').forEach(function(el) { attachTooltip(el, copy.pageLength); });
                     attachHeaderIn('#datastokmaterial', function(text, idx) { return idx === 0; }, copy.rowNumber);
                     attachHeaderIn('#datastokmaterial', function(text) { return text.indexOf('kode material') !== -1; }, copy.kodeMaterial);
                     attachHeaderIn('#datastokmaterial', function(text) { return text.indexOf('stok cikarang') !== -1; }, copy.stokCikarang);
                     attachHeaderIn('#datastokmaterial', function(text) { return text.indexOf('stok cirebon') !== -1; }, copy.stokCirebon);
                     attachHeaderIn('#datastokmaterial', function(text) { return text.indexOf('total stok') !== -1; }, copy.totalStok);
                 }

                 // ---- Kebutuhan Material & Material Terbuang (struktur mirip) ----
                 if (previewKey === 'kebutuhan-material' || previewKey === 'material-terbuang') {
                     document.querySelectorAll('#filterPelangganInput, #pelangganCombobox').forEach(function(el) { attachTooltip(el, copy.filterPelanggan); });
                     document.querySelectorAll('#filterProdukInput, #produkCombobox').forEach(function(el) { attachTooltip(el, copy.filterProduk); });
                     document.querySelectorAll('#filterMaterialInput, #materialCombobox').forEach(function(el) { attachTooltip(el, copy.filterMaterial); });
                     document.querySelectorAll('#btnReset').forEach(function(el) { attachTooltip(el, copy.btnReset); });
                     document.querySelectorAll('#btnTampilkan').forEach(function(el) { attachTooltip(el, copy.btnTampilkan); });
                     document.querySelectorAll('#waktuHitung').forEach(function(el) { attachTooltip(el, copy.waktuHitung); });
                     document.querySelectorAll('#ringkasanBelum').forEach(function(el) { attachTooltip(el.closest('.forecast-card') || el, copy.ringkasanBelum); });
                     document.querySelectorAll('#ringkasanVendor').forEach(function(el) { attachTooltip(el.closest('.forecast-card') || el, copy.ringkasanVendor); });
                     document.querySelectorAll('#ringkasanFullBeliJadi').forEach(function(el) { attachTooltip(el.closest('.forecast-card') || el, copy.ringkasanFullBeliJadi); });
                     document.querySelectorAll('#peringatanWrapper').forEach(function(el) { attachTooltip(el, copy.peringatanList); });
                     document.querySelectorAll('#vendorSupplyWrapper').forEach(function(el) { attachTooltip(el, copy.vendorSupplyList); });
                     document.querySelectorAll('#fullBeliJadiWrapper').forEach(function(el) { attachTooltip(el, copy.fullBeliJadiList); });
                     document.querySelectorAll('#modalDetail .modal-body').forEach(function(el) { attachTooltip(el, copy.modalDetail); });
                 }

                 if (previewKey === 'kebutuhan-material') {
                     document.querySelectorAll('#filterStatus').forEach(function(el) { attachTooltip(el, copy.filterStatus); });
                     document.querySelectorAll('#btnCetak').forEach(function(el) { attachTooltip(el, copy.btnCetak); });
                     document.querySelectorAll('#ringkasanTotal').forEach(function(el) { attachTooltip(el.closest('.forecast-card') || el, copy.ringkasanTotal); });
                     document.querySelectorAll('#ringkasanKurang').forEach(function(el) { attachTooltip(el.closest('.forecast-card') || el, copy.ringkasanKurang); });
                     attachHeaderIn('#tabelKebutuhan', function(text) { return text === 'no'; }, copy.colNo);
                     attachHeaderIn('#tabelKebutuhan', function(text) { return text.indexOf('kode material') !== -1; }, copy.colKodeMaterial);
                     attachHeaderIn('#tabelKebutuhan', function(text) { return text.indexOf('nama material') !== -1; }, copy.colNamaMaterial);
                     attachHeaderIn('#tabelKebutuhan', function(text) { return text === 'satuan'; }, copy.colSatuan);
                     attachHeaderIn('#tabelKebutuhan', function(text) { return text === 'kebutuhan'; }, copy.colKebutuhan);
                     attachHeaderIn('#tabelKebutuhan', function(text) { return text.indexOf('stok cikarang') !== -1; }, copy.colStokCikarang);
                     attachHeaderIn('#tabelKebutuhan', function(text) { return text.indexOf('stok cirebon') !== -1; }, copy.colStokCirebon);
                     attachHeaderIn('#tabelKebutuhan', function(text) { return text.indexOf('total stok') !== -1; }, copy.colTotalStok);
                     attachHeaderIn('#tabelKebutuhan', function(text) { return text.indexOf('stok minimum') !== -1; }, copy.colStokMinimum);
                     attachHeaderIn('#tabelKebutuhan', function(text) { return text === 'kekurangan'; }, copy.colKekurangan);
                     attachHeaderIn('#tabelKebutuhan', function(text) { return text.indexOf('saran beli') !== -1; }, copy.colSaranBeli);
                     attachHeaderIn('#tabelKebutuhan', function(text) { return text === 'status'; }, copy.colStatus);
                     attachHeaderIn('#tabelKebutuhan', function(text) { return text === 'detail'; }, copy.colDetail);
                 }

                 if (previewKey === 'material-terbuang') {
                     document.querySelectorAll('#ringkasanTotalWaste').forEach(function(el) { attachTooltip(el.closest('.forecast-card') || el, copy.ringkasanTotalWaste); });
                     attachHeaderIn('#tabelWaste', function(text) { return text === 'no'; }, copy.colNo);
                     attachHeaderIn('#tabelWaste', function(text) { return text.indexOf('kode material') !== -1; }, copy.colKodeMaterial);
                     attachHeaderIn('#tabelWaste', function(text) { return text.indexOf('nama material') !== -1; }, copy.colNamaMaterial);
                     attachHeaderIn('#tabelWaste', function(text) { return text === 'satuan'; }, copy.colSatuan);
                     attachHeaderIn('#tabelWaste', function(text) { return text.indexOf('kebutuhan material') !== -1; }, copy.colKebutuhan);
                     attachHeaderIn('#tabelWaste', function(text) { return text.indexOf('estimasi waste') !== -1; }, copy.colWaste);
                     attachHeaderIn('#tabelWaste', function(text) { return text.indexOf('% waste') !== -1; }, copy.colPersenWaste);
                     attachHeaderIn('#tabelWaste', function(text) { return text === 'detail'; }, copy.colDetail);
                 }

                 // ---- Material Masuk (list/input/edit) ----
                 if (previewKey === 'material-masuk') {
                     const path = (window.location.pathname || '').toLowerCase();
                     function pathIs(fragment) { return path.indexOf(fragment.toLowerCase()) !== -1; }

                     if (pathIs('/materialmasuk/data')) {
                         attachByText('a, button', ['input transaksi material masuk'], copy.inputButton, '.btn, a, button');
                         document.querySelectorAll('#mmSearchInput').forEach(function(el) { attachTooltip(el, copy.search); });
                         document.querySelectorAll('.mm-filter-toggle').forEach(function(el) { attachTooltip(el, copy.filterToggle); });
                         document.querySelectorAll('.mm-filter-chip').forEach(function(el) { attachTooltip(el, copy.filterChip); });
                         document.querySelectorAll('.mm-filter-apply').forEach(function(el) { attachTooltip(el, copy.filterApply); });
                         document.querySelectorAll('.mm-filter-reset').forEach(function(el) { attachTooltip(el, copy.filterReset); });
                         document.querySelectorAll('#mmPageLength').forEach(function(el) { attachTooltip(el, copy.pageLength); });
                         attachHeaderIn('#datamaterialmasuk', function(text, idx) { return idx === 0; }, copy.colNo);
                         attachHeaderIn('#datamaterialmasuk', function(text) { return text.indexOf('no. invoice') !== -1; }, copy.colNoInvoice);
                         attachHeaderIn('#datamaterialmasuk', function(text) { return text.indexOf('no surat jalan') !== -1; }, copy.colNoSuratJalan);
                         attachHeaderIn('#datamaterialmasuk', function(text) { return text === 'tanggal'; }, copy.colTanggal);
                         attachHeaderIn('#datamaterialmasuk', function(text) { return text.indexOf('supplier/sumber') !== -1; }, copy.colSupplierSumber);
                         attachHeaderIn('#datamaterialmasuk', function(text) { return text.indexOf('total berat') !== -1; }, copy.colTotalBerat);
                         attachHeaderIn('#datamaterialmasuk', function(text) { return text === 'gudang'; }, copy.colGudang);
                         attachHeaderIn('#datamaterialmasuk', function(text) { return text === '#'; }, copy.aksi);
                         document.querySelectorAll('#datamaterialmasuk .fa-edit').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiEdit); });
                         document.querySelectorAll('#datamaterialmasuk .fa-trash-alt').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiHapus); });
                     }

                     if (pathIs('/materialmasuk/input')) {
                         attachLabel(['tanggal'], copy.formTanggal);
                         attachLabel(['no. invoice'], copy.formNoInvoice);
                         attachLabel(['no surat jalan'], copy.formNoDo);
                         attachLabel(['sumber material'], copy.formSumberMaterial);
                         attachLabel(['cari supplier'], copy.formSupplier);
                         attachLabel(['pelanggan (sumber konsinyasi)'], copy.formPelanggan);
                         attachLabel(['lokasi gudang'], copy.formGudang);
                         attachLabel(['pilih po keluar'], copy.formPoKeluar);
                         attachLabel(['kode material'], copy.formKodeMaterial);
                         attachLabel(['nama material'], copy.formNamaMaterial);
                         attachLabel(['stok'], copy.formStok);
                         attachLabel(['qty'], copy.formQty);
                         document.querySelectorAll('#tabelItemPoKeluar').forEach(function(el) { attachTooltip(el, copy.itemPoKeluarTable); });
                         document.querySelectorAll('#tombolSimpanItem').forEach(function(el) { attachTooltip(el, copy.tombolSimpanItem); });
                         document.querySelectorAll('#tombolReload').forEach(function(el) { attachTooltip(el, copy.tombolReload); });
                         document.querySelectorAll('.tampilDataTemp').forEach(function(el) { attachTooltip(el, copy.draftTable); });
                         document.querySelectorAll('#tombolSelesaiTransaksi').forEach(function(el) { attachTooltip(el, copy.tombolSelesaiTransaksi); });
                     }

                     if (pathIs('/materialmasuk/edit')) {
                         document.querySelectorAll('#no_invoice').forEach(function(el) { attachTooltip(el, copy.editNoInvoice); });
                         document.querySelectorAll('#tombolSimpanInvoice').forEach(function(el) { attachTooltip(el, copy.editSimpanInvoice); });
                         document.querySelectorAll('table.table-striped.table-sm').forEach(function(el) { attachTooltip(el, copy.editHeaderInfo); });
                         document.querySelectorAll('#tombolCariMaterial').forEach(function(el) { attachTooltip(el, copy.editCariMaterial); });
                         attachLabel(['nama material'], copy.formNamaMaterial);
                         attachLabel(['stok'], copy.formStok);
                         attachLabel(['qty'], copy.formQty);
                         document.querySelectorAll('#tombolSimpanItem').forEach(function(el) { attachTooltip(el, copy.tombolSimpanItem); });
                         document.querySelectorAll('#tombolEditItem').forEach(function(el) { attachTooltip(el, copy.editTombolEditItem); });
                         document.querySelectorAll('#tombolBatal').forEach(function(el) { attachTooltip(el, copy.editTombolBatal); });
                         document.querySelectorAll('.tampilDataDetail').forEach(function(el) { attachTooltip(el, copy.editItemTable); });
                     }
                 }

                 // ---- Pemakaian Material (list saja, read-only) ----
                 if (previewKey === 'pemakaian-material') {
                     document.querySelectorAll('.alert-info').forEach(function(el) { attachTooltip(el, copy.info); });
                     document.querySelectorAll('#mkSearchInput').forEach(function(el) { attachTooltip(el, copy.search); });
                     document.querySelectorAll('.mk-filter-toggle').forEach(function(el) { attachTooltip(el, copy.filterToggle); });
                     document.querySelectorAll('.mk-filter-chip').forEach(function(el) { attachTooltip(el, copy.filterChip); });
                     document.querySelectorAll('.mk-filter-apply').forEach(function(el) { attachTooltip(el, copy.filterApply); });
                     document.querySelectorAll('.mk-filter-reset').forEach(function(el) { attachTooltip(el, copy.filterReset); });
                     document.querySelectorAll('#mkPageLength').forEach(function(el) { attachTooltip(el, copy.pageLength); });
                     attachHeaderIn('#datamaterialkeluar', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#datamaterialkeluar', function(text) { return text.indexOf('no. produksi') !== -1; }, copy.colNoProduksi);
                     attachHeaderIn('#datamaterialkeluar', function(text) { return text === 'tanggal'; }, copy.colTanggal);
                     attachHeaderIn('#datamaterialkeluar', function(text) { return text.indexOf('produk yang dibuat') !== -1; }, copy.colProdukDibuat);
                     attachHeaderIn('#datamaterialkeluar', function(text) { return text.indexOf('kode material') !== -1; }, copy.colKodeMaterial);
                     attachHeaderIn('#datamaterialkeluar', function(text) { return text.indexOf('nama material') !== -1; }, copy.colNamaMaterial);
                     attachHeaderIn('#datamaterialkeluar', function(text) { return text.indexOf('qty terpakai') !== -1; }, copy.colQtyTerpakai);
                     attachHeaderIn('#datamaterialkeluar', function(text) { return text === 'satuan'; }, copy.colSatuan);
                     attachHeaderIn('#datamaterialkeluar', function(text) { return text === 'gudang'; }, copy.colGudang);
                 }
             }

             function attachTransaksiProdukTooltips() {
                 const groups = ['produk-masuk', 'pengiriman', 'antar-gudang'];
                 if (groups.indexOf(previewKey) === -1) {
                     return;
                 }

                 function attachHeaderIn(containerSelector, matcher, message) {
                     document.querySelectorAll(containerSelector + ' thead th, ' + containerSelector + ' th').forEach(function(header, index) {
                         if (matcher(normalizedText(header), index)) {
                             attachTooltip(header, message);
                         }
                     });
                 }

                 // ---- Produk Masuk (barangmasuk/data, /input, /edit) ----
                 if (previewKey === 'produk-masuk') {
                     attachByText('a, button', ['input transaksi produk masuk'], copy.inputButton, '.btn, a, button');
                     document.querySelectorAll('#tab-stok-link').forEach(function(el) { attachTooltip(el, copy.tabStok); });
                     document.querySelectorAll('#tab-supplier-link').forEach(function(el) { attachTooltip(el, copy.tabSupplier); });
                     document.querySelectorAll('#tab-adjustment-link').forEach(function(el) { attachTooltip(el, copy.tabAdjustment); });
                     document.querySelectorAll('#tab-produksi-link').forEach(function(el) { attachTooltip(el, copy.tabProduksi); });

                     document.querySelectorAll('#kategoriStok').forEach(function(el) { attachTooltip(el, copy.filterKategori); });
                     document.querySelectorAll('#materialStok').forEach(function(el) { attachTooltip(el, copy.filterMaterialStok); });
                     document.querySelectorAll('#pelangganStok').forEach(function(el) { attachTooltip(el, copy.filterPelangganStok); });
                     document.querySelectorAll('button[name="btnCetak"]').forEach(function(el) { attachTooltip(el, copy.btnCetakStok); });
                     document.querySelectorAll('.dpm-search-input').forEach(function(el) { attachTooltip(el, copy.search); });
                     document.querySelectorAll('.dpm-filter-toggle').forEach(function(el) { attachTooltip(el, copy.filterToggle); });
                     document.querySelectorAll('.dpm-filter-chip').forEach(function(el) { attachTooltip(el, copy.filterChip); });
                     document.querySelectorAll('.dpm-filter-apply').forEach(function(el) { attachTooltip(el, copy.filterApply); });
                     document.querySelectorAll('.dpm-filter-reset').forEach(function(el) { attachTooltip(el, copy.filterReset); });
                     document.querySelectorAll('[id$="PageLength"]').forEach(function(el) { attachTooltip(el, copy.pageLength); });

                     attachHeaderIn('#datastok', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#datastok', function(text) { return text.indexOf('kode barang') !== -1; }, copy.colKodeBarang);
                     attachHeaderIn('#datastok', function(text) { return text.indexOf('stok cikarang') !== -1; }, copy.colStokCikarang);
                     attachHeaderIn('#datastok', function(text) { return text.indexOf('stok cirebon') !== -1; }, copy.colStokCirebon);
                     attachHeaderIn('#datastok', function(text) { return text.indexOf('total stok') !== -1; }, copy.colTotalStok);
                     attachHeaderIn('#datastok', function(text) { return text.indexOf('total sisa po') !== -1; }, copy.colTotalSisaPo);
                     attachHeaderIn('#datastok', function(text) { return text.indexOf('qty terkirim') !== -1; }, copy.colQtyTerkirim);
                     attachHeaderIn('#datastok', function(text) { return text.indexOf('kekurangan produksi') !== -1; }, copy.colKekuranganProduksi);
                     attachHeaderIn('#datastok', function(text) { return text.indexOf('kelebihan produksi') !== -1; }, copy.colKelebihanProduksi);

                     attachHeaderIn('#databarangmasuk', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#databarangmasuk', function(text) { return text.indexOf('no po') !== -1; }, copy.colNoPo);
                     attachHeaderIn('#databarangmasuk', function(text) { return text === 'tanggal'; }, copy.colTanggal);
                     attachHeaderIn('#databarangmasuk', function(text) { return text === 'supplier'; }, copy.colSupplier);
                     attachHeaderIn('#databarangmasuk', function(text) { return text.indexOf('qty') !== -1; }, copy.colQtyPcs);
                     attachHeaderIn('#databarangmasuk', function(text) { return text.indexOf('total berat') !== -1; }, copy.colTotalBerat);
                     attachHeaderIn('#databarangmasuk', function(text) { return text.indexOf('gudang tujuan') !== -1; }, copy.colGudangTujuan);
                     attachHeaderIn('#databarangmasuk', function(text) { return text === '#'; }, copy.aksi);
                     document.querySelectorAll('#databarangmasuk .fa-edit').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiEdit); });
                     document.querySelectorAll('#databarangmasuk .fa-trash-alt').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiHapus); });

                     attachHeaderIn('#dataadjustment', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#dataadjustment', function(text) { return text.indexOf('no adjustment') !== -1; }, copy.colNoAdjustment);
                     attachHeaderIn('#dataadjustment', function(text) { return text === 'tanggal'; }, copy.colTanggal);
                     attachHeaderIn('#dataadjustment', function(text) { return text.indexOf('qty') !== -1; }, copy.colQtyPcs);
                     attachHeaderIn('#dataadjustment', function(text) { return text.indexOf('total berat') !== -1; }, copy.colTotalBerat);
                     attachHeaderIn('#dataadjustment', function(text) { return text.indexOf('gudang tujuan') !== -1; }, copy.colGudangTujuan);
                     attachHeaderIn('#dataadjustment', function(text) { return text === '#'; }, copy.aksi);
                     document.querySelectorAll('#dataadjustment .fa-edit').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiEdit); });
                     document.querySelectorAll('#dataadjustment .fa-trash-alt').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiHapus); });

                     attachHeaderIn('#dataproduksi', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#dataproduksi', function(text) { return text.indexOf('no. produksi') !== -1; }, copy.colNoProduksi);
                     attachHeaderIn('#dataproduksi', function(text) { return text === 'tanggal'; }, copy.colTanggal);
                     attachHeaderIn('#dataproduksi', function(text) { return text.indexOf('kode produk') !== -1; }, copy.colKodeProduk);
                     attachHeaderIn('#dataproduksi', function(text) { return text.indexOf('nama produk') !== -1; }, copy.colNamaProduk);
                     attachHeaderIn('#dataproduksi', function(text) { return text.indexOf('qty diproduksi') !== -1; }, copy.colQtyDiproduksi);
                     attachHeaderIn('#dataproduksi', function(text) { return text === 'gudang'; }, copy.colGudang);
                     attachHeaderIn('#dataproduksi', function(text) { return text.indexOf('keterangan') !== -1; }, copy.colKeterangan);
                     attachHeaderIn('#dataproduksi', function(text) { return text === '#'; }, copy.aksi);
                     document.querySelectorAll('#dataproduksi .fa-edit').forEach(function(icon) { attachTooltip(icon.closest('button, a') || icon, copy.aksiEditProduksi); });
                     document.querySelectorAll('#dataproduksi .fa-trash-alt').forEach(function(icon) { attachTooltip(icon.closest('button, a') || icon, copy.aksiHapusProduksi); });

                     attachLabel(['sumber produk'], copy.formSumberProduk);
                     attachLabel(['tanggal transaksi'], copy.formTanggalTransaksi);
                     attachLabel(['no po'], copy.formNoPo);
                     attachLabel(['cari supplier'], copy.formSupplier);
                     attachLabel(['gudang tujuan'], copy.formGudangTujuan);
                     document.querySelectorAll('#tabelItemPoKeluar').forEach(function(el) { attachTooltip(el, copy.itemPoKeluarTable); });
                     attachLabel(['kode produk'], copy.formKodeProdukItem);
                     attachLabel(['nama produk'], copy.formNamaProdukItem);
                     attachLabel(['berat/ukuran'], copy.formBeratItem);
                     attachLabel(['stok'], copy.formStokItem);
                     attachLabel(['qty'], copy.formQtyItem);
                     document.querySelectorAll('#tombolSimpanItem').forEach(function(el) { attachTooltip(el, copy.tombolSimpanItem); });
                     document.querySelectorAll('#tombolReload').forEach(function(el) { attachTooltip(el, copy.tombolReload); });
                     document.querySelectorAll('.tampilDataTemp').forEach(function(el) { attachTooltip(el, copy.draftTable); });
                     document.querySelectorAll('#tombolSelesaiTransaksi').forEach(function(el) { attachTooltip(el, copy.tombolSelesaiTransaksi); });

                     attachLabel(['kode produk'], copy.formKodeProdukProduksi);
                     attachLabel(['qty diproduksi'], copy.formQtyDiproduksi);
                     document.querySelectorAll('#tombolSimpanProduksi').forEach(function(el) { attachTooltip(el, copy.tombolTambahDaftarProduksi); });
                     document.querySelectorAll('#tombolResetProduksi').forEach(function(el) { attachTooltip(el, copy.tombolResetProduksi); });
                     document.querySelectorAll('#tabelDaftarProduk').forEach(function(el) { attachTooltip(el, copy.daftarProduksiTable); });
                     document.querySelectorAll('#tombolSelesaiProduksi').forEach(function(el) { attachTooltip(el, copy.tombolSelesaiProduksi); });

                     document.querySelectorAll('table.table-striped.table-sm').forEach(function(el) { attachTooltip(el, copy.editHeaderInfo); });
                     document.querySelectorAll('#tombolEditItem').forEach(function(el) { attachTooltip(el, copy.editTombolEditItem); });
                     document.querySelectorAll('#tombolBatal').forEach(function(el) { attachTooltip(el, copy.editTombolBatal); });
                     document.querySelectorAll('.tampilDataDetail').forEach(function(el) { attachTooltip(el, copy.editItemTable); });
                 }

                 // ---- Pengiriman (barangkeluar/data) ----
                 if (previewKey === 'pengiriman') {
                     attachByText('a, button', ['input pengiriman'], copy.inputPengirimanButton, '.btn, a, button');
                     attachByText('a, button', ['buat permintaan'], copy.buatPermintaanButton, '.btn, a, button');
                     document.querySelectorAll('#tab-daftar-link').forEach(function(el) { attachTooltip(el, copy.tabDaftar); });
                     document.querySelectorAll('#tab-permintaan-link').forEach(function(el) { attachTooltip(el, copy.tabPermintaan); });
                     document.querySelectorAll('#tab-riwayat-link').forEach(function(el) { attachTooltip(el, copy.tabRiwayat); });

                     document.querySelectorAll('.pgr-search-input').forEach(function(el) { attachTooltip(el, copy.search); });
                     document.querySelectorAll('.pgr-filter-toggle').forEach(function(el) { attachTooltip(el, copy.filterToggle); });
                     document.querySelectorAll('.pgr-filter-chip').forEach(function(el) { attachTooltip(el, copy.filterChip); });
                     document.querySelectorAll('.pgr-filter-apply').forEach(function(el) { attachTooltip(el, copy.filterApply); });
                     document.querySelectorAll('.pgr-filter-reset').forEach(function(el) { attachTooltip(el, copy.filterReset); });
                     document.querySelectorAll('[id$="PageLength"]').forEach(function(el) { attachTooltip(el, copy.pageLength); });

                     attachHeaderIn('#dataPengirimanGabungan', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#dataPengirimanGabungan', function(text) { return text === 'tanggal'; }, copy.colTanggal);
                     attachHeaderIn('#dataPengirimanGabungan', function(text) { return text === 'status'; }, copy.colStatus);
                     attachHeaderIn('#dataPengirimanGabungan', function(text) { return text.indexOf('no surat jalan') !== -1; }, copy.colNoSuratJalan);
                     attachHeaderIn('#dataPengirimanGabungan', function(text) { return text.indexOf('no. po') !== -1; }, copy.colNoPo);
                     attachHeaderIn('#dataPengirimanGabungan', function(text) { return text === 'total'; }, copy.colTotal);
                     attachHeaderIn('#dataPengirimanGabungan', function(text) { return text === 'rencana'; }, copy.colRencana);
                     attachHeaderIn('#dataPengirimanGabungan', function(text) { return text === 'terkirim'; }, copy.colTerkirim);
                     attachHeaderIn('#dataPengirimanGabungan', function(text) { return text === 'belum'; }, copy.colBelum);
                     attachHeaderIn('#dataPengirimanGabungan', function(text) { return text === 'user'; }, copy.colUser);
                     attachHeaderIn('#dataPengirimanGabungan', function(text) { return text.indexOf('keterangan') !== -1; }, copy.colKeterangan);
                     attachHeaderIn('#dataPengirimanGabungan', function(text) { return text === 'aksi'; }, copy.aksi);

                     attachHeaderIn('#dataPermintaanPengiriman', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#dataPermintaanPengiriman', function(text) { return text === 'tanggal'; }, copy.colTanggal);
                     attachHeaderIn('#dataPermintaanPengiriman', function(text) { return text === 'user'; }, copy.colUser);
                     attachHeaderIn('#dataPermintaanPengiriman', function(text) { return text.indexOf('total produk') !== -1; }, copy.colTotalProduk);
                     attachHeaderIn('#dataPermintaanPengiriman', function(text) { return text === 'status'; }, copy.colStatus);
                     attachHeaderIn('#dataPermintaanPengiriman', function(text) { return text === 'aksi'; }, copy.aksi);

                     attachHeaderIn('#databarangkeluar', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#databarangkeluar', function(text) { return text.indexOf('no surat jalan') !== -1; }, copy.colNoSuratJalan);
                     attachHeaderIn('#databarangkeluar', function(text) { return text.indexOf('no. po') !== -1; }, copy.colNoPo);
                     attachHeaderIn('#databarangkeluar', function(text) { return text === 'tanggal'; }, copy.colTanggal);
                     attachHeaderIn('#databarangkeluar', function(text) { return text === 'jenis'; }, copy.colJenis);
                     attachHeaderIn('#databarangkeluar', function(text) { return text === 'pelanggan'; }, copy.colPelanggan);
                     attachHeaderIn('#databarangkeluar', function(text) { return text.indexOf('terkirim') !== -1; }, copy.colTerkirim);
                     attachHeaderIn('#databarangkeluar', function(text) { return text.indexOf('gudang asal') !== -1; }, copy.colGudangAsal);
                     attachHeaderIn('#databarangkeluar', function(text) { return text === '#'; }, copy.aksi);

                     // Ikon fa-edit/fa-print/fa-trash-alt dipakai ulang lintas
                     // baris dengan arti beda (Lanjutkan Input Pengiriman vs
                     // Lanjutkan Proses Permintaan vs Edit Pengiriman; Print DO
                     // vs disabled). SENGAJA attachTooltip (selalu nimpa) di sini
                     // dan di-scope per title attribute biar akurat.
                     document.querySelectorAll('[title="Lanjutkan Input Pengiriman"], [title="Lanjutkan Proses Permintaan"]').forEach(function(el) { attachTooltip(el, copy.aksiLanjutkan); });
                     document.querySelectorAll('[title="Print Delivery Order"], [title="Belum ada surat jalan resmi"], [title="Belum ada surat jalan"]').forEach(function(el) { attachTooltip(el, copy.aksiPrintDo); });
                     document.querySelectorAll('[title="Edit Pengiriman"]').forEach(function(el) { attachTooltip(el, copy.aksiEditPengiriman); });
                     document.querySelectorAll('[title="Hapus Surat Jalan ini"], [title="Hapus (seluruh permintaan ini)"]').forEach(function(el) { attachTooltip(el, copy.aksiHapusSuratJalan); });

                     document.querySelectorAll('#noBtbPengiriman').forEach(function(el) {
                         attachTooltip(el, copy.btbNoInput);
                         const modal = el.closest('.modal');
                         if (modal) {
                             attachTooltip(modal, copy.btbButton);
                         }
                     });
                     document.querySelectorAll('#fileBtbPengiriman').forEach(function(el) { attachTooltip(el, copy.btbFileInput); });
                 }

                 // ---- Antar Gudang (permintaanBarangKirim/datakirim, editproses) ----
                 if (previewKey === 'antar-gudang') {
                     attachByText('a, button', ['input transfer'], copy.inputTransferButton, '.btn, a, button');
                     document.querySelectorAll('[data-target="#modalCetakPeriode"]').forEach(function(el) { attachTooltip(el, copy.printButton); });
                     document.querySelectorAll('#tanggalPrintAwal').forEach(function(el) { attachTooltip(el, copy.printTglAwal); });
                     document.querySelectorAll('#tanggalPrintAkhir').forEach(function(el) { attachTooltip(el, copy.printTglAkhir); });
                     document.querySelectorAll('#gudangPrint').forEach(function(el) { attachTooltip(el, copy.printGudang); });

                     document.querySelectorAll('#kirimSearchInput, .ag-search-input').forEach(function(el) { attachTooltip(el, copy.search); });
                     document.querySelectorAll('.ag-filter-toggle').forEach(function(el) { attachTooltip(el, copy.filterToggle); });
                     document.querySelectorAll('.ag-filter-apply').forEach(function(el) { attachTooltip(el, copy.filterApply); });
                     document.querySelectorAll('.ag-filter-reset').forEach(function(el) { attachTooltip(el, copy.filterReset); });
                     document.querySelectorAll('[id$="PageLength"]').forEach(function(el) { attachTooltip(el, copy.pageLength); });

                     attachHeaderIn('#datareqkirim', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#datareqkirim', function(text) { return text.indexOf('no surat jalan') !== -1; }, copy.colNoSuratJalan);
                     attachHeaderIn('#datareqkirim', function(text) { return text === 'tanggal'; }, copy.colTanggal);
                     attachHeaderIn('#datareqkirim', function(text) { return text === 'user'; }, copy.colUser);
                     attachHeaderIn('#datareqkirim', function(text) { return text.indexOf('total produk') !== -1; }, copy.colTotalProduk);
                     attachHeaderIn('#datareqkirim', function(text) { return text.indexOf('jenis pengiriman') !== -1; }, copy.colJenisPengiriman);
                     attachHeaderIn('#datareqkirim', function(text) { return text.indexOf('pic pengirim') !== -1; }, copy.colPicPengirim);
                     attachHeaderIn('#datareqkirim', function(text) { return text === 'nominal'; }, copy.colNominal);
                     attachHeaderIn('#datareqkirim', function(text) { return text.indexOf('gudang keluar') !== -1; }, copy.colGudangKeluar);
                     attachHeaderIn('#datareqkirim', function(text) { return text === 'aksi'; }, copy.aksi);
                     document.querySelectorAll('#datareqkirim .fa-edit').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiEdit); });
                     document.querySelectorAll('#datareqkirim .fa-trash-alt').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiHapus); });

                     attachByText('td', ['no surat jalan'], copy.editNoSuratJalan);
                     attachByText('td', ['user'], copy.editUser);
                     attachByText('td', ['gudang keluar'], copy.editGudangKeluar);
                     document.querySelectorAll('#jenisPengirimanText, #jenisPengirimanInput').forEach(function(el) { attachTooltip(el, copy.editJenisPengiriman); });
                     document.querySelectorAll('#picPengirimText, #picPengirimInput').forEach(function(el) { attachTooltip(el, copy.editPicPengirim); });
                     document.querySelectorAll('#nominalText, #nominalInput').forEach(function(el) { attachTooltip(el, copy.editNominal); });
                     document.querySelectorAll('#editHeaderBtn').forEach(function(el) { attachTooltip(el, copy.editHeaderButton); });
                     document.querySelectorAll('#saveHeaderBtn').forEach(function(el) { attachTooltip(el, copy.editSaveHeaderButton); });
                     document.querySelectorAll('#cancelHeaderBtn').forEach(function(el) { attachTooltip(el, copy.editCancelHeaderButton); });

                     attachLabel(['jenis item'], copy.formJenisItem);
                     document.querySelectorAll('#labelKodeItemProses').forEach(function(el) { attachTooltip(el.closest('.form-group') || el, copy.formKodeItem); });
                     attachLabel(['nama item'], copy.formNamaItem);
                     attachLabel(['gudang asal'], copy.formGudangAsal);
                     attachLabel(['stok'], copy.formStokItem);
                     attachLabel(['qty'], copy.formQtyItem);
                     document.querySelectorAll('#tombolSimpanItem').forEach(function(el) { attachTooltip(el, copy.tombolSimpanItem); });
                     document.querySelectorAll('#tombolEditItem').forEach(function(el) { attachTooltip(el, copy.tombolEditItem); });
                     document.querySelectorAll('#tombolBatal').forEach(function(el) { attachTooltip(el, copy.tombolBatal); });
                     document.querySelectorAll('.tampilDataDetail').forEach(function(el) { attachTooltip(el, copy.itemTable); });
                 }
             }

             function attachMasterTooltips() {
                 const groups = ['kategori', 'satuan', 'master-material', 'master-produk', 'master-pelanggan', 'master-supplier'];
                 if (groups.indexOf(previewKey) === -1) {
                     return;
                 }

                 function attachHeaderIn(containerSelector, matcher, message) {
                     document.querySelectorAll(containerSelector + ' thead th, ' + containerSelector + ' th').forEach(function(header, index) {
                         if (matcher(normalizedText(header), index)) {
                             attachTooltip(header, message);
                         }
                     });
                 }

                 attachByText('a, button', ['tambah data'], copy.tambahButton, '.btn, a, button');
                 document.querySelectorAll('input[type="search"]').forEach(function(el) { attachTooltip(el, copy.search); });
                 document.querySelectorAll('[id$="FilterToggle"]').forEach(function(el) { attachTooltip(el, copy.filterToggle); });
                 document.querySelectorAll('[id$="FilterReset"]').forEach(function(el) { attachTooltip(el, copy.filterReset); });
                 document.querySelectorAll('[id$="PageLength"]').forEach(function(el) { attachTooltip(el, copy.pageLength); });

                 if (previewKey === 'kategori') {
                     attachHeaderIn('#datakategori', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#datakategori', function(text) { return text.indexOf('nama kategori') !== -1; }, copy.colNama);
                     document.querySelectorAll('#datakategori .fa-edit').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiEdit); });
                     document.querySelectorAll('#datakategori .fa-trash-alt').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiHapus); });
                 }

                 if (previewKey === 'satuan') {
                     attachHeaderIn('#datasatuan', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#datasatuan', function(text) { return text.indexOf('nama satuan') !== -1; }, copy.colNama);
                     document.querySelectorAll('#datasatuan .fa-edit').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiEdit); });
                     document.querySelectorAll('#datasatuan .fa-trash-alt').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiHapus); });
                 }

                 if (previewKey === 'master-material') {
                     attachHeaderIn('#datamaterial', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#datamaterial', function(text) { return text.indexOf('kode material') !== -1; }, copy.colKode);
                     attachHeaderIn('#datamaterial', function(text) { return text.indexOf('nama material') !== -1; }, copy.colNama);
                     attachHeaderIn('#datamaterial', function(text) { return text === 'kategori'; }, copy.colKategori);
                     document.querySelectorAll('#datamaterial .fa-edit').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiEdit); });
                     document.querySelectorAll('#datamaterial .fa-tag').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiLabelSupplier); });
                     document.querySelectorAll('#datamaterial .fa-trash-alt').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiHapus); });

                     // Form Tambah/Edit Data Material (field id-nya sama persis
                     // di kedua halaman).
                     attachLabel(['nama kategori'], copy.formKategori);
                     attachLabel(['nama material'], copy.formNamaMaterial);
                     attachLabel(['kode material'], copy.formKodeMaterial);
                     attachLabel(['satuan material'], copy.formSatuan);
                     attachLabel(['minimal stok material'], copy.formMinStok);
                     document.querySelectorAll('#tombolSimpanItem, input[type="submit"][value="Simpan"]').forEach(function(el) { attachTooltip(el, copy.formSimpan); });
                     document.querySelectorAll('#tombolReload').forEach(function(el) { attachTooltip(el, copy.formReset); });

                     // Modal Bootstrap "Label Nama per Supplier" -- elemennya
                     // cuma ada di DOM selagi dialog ini kebuka, jadi selector
                     // ID di sini aman (no-op kalau dialog belum/nggak dibuka).
                     document.querySelectorAll('#labelSupplierDaftar').forEach(function(el) { attachTooltip(el, copy.lsMaterialInfo); });
                     document.querySelectorAll('#labelSupplierSupplierId').forEach(function(el) {
                         attachTooltip(el, copy.lsSupplier);
                         const cancelBtn = document.querySelector('.app-popup-cancel');
                         if (cancelBtn) {
                             attachTooltip(cancelBtn, copy.lsTutup);
                         }
                     });
                     document.querySelectorAll('#labelSupplierNama').forEach(function(el) { attachTooltip(el, copy.lsLabelNama); });
                     document.querySelectorAll('#btnSimpanLabelSupplier').forEach(function(el) { attachTooltip(el, copy.lsSimpan); });
                     document.querySelectorAll('#labelSupplierDaftar .fa-trash-alt').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.lsHapus); });
                 }

                 if (previewKey === 'master-produk') {
                     attachHeaderIn('#databarang', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#databarang', function(text) { return text.indexOf('kode produk') !== -1; }, copy.colKode);
                     attachHeaderIn('#databarang', function(text) { return text.indexOf('nama produk') !== -1; }, copy.colNama);
                     attachHeaderIn('#databarang', function(text) { return text === 'material'; }, copy.colMaterial);
                     attachHeaderIn('#databarang', function(text) { return text === 'kategori'; }, copy.colKategori);
                     attachHeaderIn('#databarang', function(text) { return text === 'satuan'; }, copy.colSatuan);
                     document.querySelectorAll('#databarang .fa-edit').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiEdit); });
                     document.querySelectorAll('#databarang .fa-history').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiRiwayat); });
                     document.querySelectorAll('#databarang .fa-trash-alt').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiHapus); });

                     // Form Tambah/Edit Data Produk (field id-nya sama persis
                     // di kedua halaman). Label material/berat/Wise di kartu
                     // "Material & Berat" udah punya teks bantuan + toggle
                     // "Info" sendiri, jadi nggak di-timpa di sini.
                     attachLabel(['kode produk'], copy.formKodeProduk);
                     attachLabel(['nama produk'], copy.formNamaProduk);
                     attachLabel(['pelanggan'], copy.formPelangganProduk);
                     attachLabel(['kategori'], copy.formKategoriProduk);
                     attachLabel(['satuan'], copy.formSatuanProduk);
                     attachLabel(['produk jasa'], copy.formJasaCheckbox);
                     attachLabel(['sumber material produksi'], copy.formSumberMaterial);
                     attachLabel(['harga produk'], copy.formHargaProduk);
                     attachLabel(['minimal stok produk'], copy.formMinStokProduk);
                     document.querySelectorAll('button[type="submit"]').forEach(function(el) {
                         if (normalizedText(el) === 'simpan') attachTooltip(el, copy.formSimpan);
                     });
                     document.querySelectorAll('button[type="reset"]').forEach(function(el) {
                         if (normalizedText(el) === 'reset') attachTooltip(el, copy.formReset);
                     });
                 }

                 if (previewKey === 'master-pelanggan') {
                     attachHeaderIn('#datapelanggan', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#datapelanggan', function(text) { return text.indexOf('nama pelanggan') !== -1; }, copy.colNama);
                     attachHeaderIn('#datapelanggan', function(text) { return text === 'pic'; }, copy.colPic);
                     attachHeaderIn('#datapelanggan', function(text) { return text === 'email'; }, copy.colEmail);
                     attachHeaderIn('#datapelanggan', function(text) { return text === 'alamat'; }, copy.colAlamat);
                     attachHeaderIn('#datapelanggan', function(text) { return text.indexOf('no telp') !== -1; }, copy.colTelp);
                     attachHeaderIn('#datapelanggan', function(text) { return text === 'fax'; }, copy.colFax);
                     attachHeaderIn('#datapelanggan', function(text) { return text === 'to'; }, copy.colTo);
                     attachHeaderIn('#datapelanggan', function(text) { return text === 'gudang'; }, copy.colGudang);
                     document.querySelectorAll('#datapelanggan .fa-edit').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiEdit); });
                     document.querySelectorAll('#datapelanggan .fa-trash-alt').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiHapus); });

                     attachLabel(['nama pelanggan'], copy.editNama);
                     attachLabel(['nama pic'], copy.editPic);
                     attachLabel(['email'], copy.editEmail);
                     attachLabel(['alamat'], copy.editAlamat);
                     attachLabel(['no telp'], copy.editTelp);
                     attachLabel(['fax'], copy.editFax);
                     attachLabel(['to / bagian penerima'], copy.editTo);
                     attachLabel(['gudang'], copy.editGudang);
                     document.querySelectorAll('#modalEditPelanggan button[onclick="update()"]').forEach(function(el) { attachTooltip(el, copy.editUpdate); });
                 }

                 if (previewKey === 'master-supplier') {
                     attachHeaderIn('#datasupplier', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#datasupplier', function(text) { return text === 'supplier'; }, copy.colNama);
                     attachHeaderIn('#datasupplier', function(text) { return text.indexOf('nama pic') !== -1; }, copy.colPic);
                     attachHeaderIn('#datasupplier', function(text) { return text === 'email'; }, copy.colEmail);
                     attachHeaderIn('#datasupplier', function(text) { return text.indexOf('no telp') !== -1; }, copy.colTelp);
                     attachHeaderIn('#datasupplier', function(text) { return text === 'alamat'; }, copy.colAlamat);
                     document.querySelectorAll('#datasupplier .fa-edit').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiEdit); });
                     document.querySelectorAll('#datasupplier .fa-trash-alt').forEach(function(icon) { attachTooltip(icon.closest('button') || icon, copy.aksiHapus); });

                     attachLabel(['nama supplier'], copy.editNama);
                     attachLabel(['nama pic'], copy.editPic);
                     attachLabel(['email'], copy.editEmail);
                     attachLabel(['no telp'], copy.editTelp);
                     attachLabel(['alamat'], copy.editAlamat);
                     document.querySelectorAll('#modalEditSupplier button[onclick*="update"]').forEach(function(el) { attachTooltip(el, copy.editUpdate); });
                 }
             }

             // Modal "Form Input Pelanggan" (pelanggan/modaltambah.php) dan
             // "Form Input Supplier" (supplier/modaltambah.php) dimuat lewat
             // AJAX dan dipakai ulang dari banyak tempat (Master Pelanggan/
             // Supplier sendiri, form Produk, form PO Masuk, dst.) -- teksnya
             // SENGAJA di-hardcode di sini (bukan lewat tooltipCopy[previewKey])
             // supaya tetap muncul di halaman manapun modal ini dibuka,
             // bukan cuma waktu previewKey-nya kebetulan 'master-pelanggan'/
             // 'master-supplier'.
             function attachSharedContactModalTooltips() {
                 const pelangganFields = {
                     '#namapel': 'Nama Pelanggan: nama customer yang mau didaftarkan.',
                     '#namapic': 'Nama PIC: nama penanggung jawab/kontak utama pelanggan ini.',
                     '#email': 'Email: alamat email pelanggan untuk korespondensi/invoice.',
                     '#alamat': 'Alamat: alamat pengiriman/penagihan pelanggan ini.',
                     '#telp': 'Telp / Handphone: nomor kontak pelanggan ini.',
                     '#fax': 'Fax: nomor fax pelanggan (opsional, kosongkan kalau tidak ada).',
                     '#to': 'To / Bagian Penerima: bagian/penerima tujuan surat-menyurat (opsional), misalnya "Bag. Keuangan".',
                     '#gdgid': 'Gudang: gudang default yang terkait dengan pelanggan ini.'
                 };
                 Object.keys(pelangganFields).forEach(function(selector) {
                     document.querySelectorAll('#modaltambahpelanggan ' + selector).forEach(function(el) {
                         attachTooltip(el, pelangganFields[selector]);
                     });
                 });
                 document.querySelectorAll('#modaltambahpelanggan #tombolsimpan').forEach(function(el) {
                     attachTooltip(el, 'Simpan: menyimpan pelanggan baru ini.');
                 });

                 const supplierFields = {
                     '#namasup': 'Nama Supplier: nama supplier/vendor yang mau didaftarkan.',
                     '#namapic': 'Nama PIC: nama penanggung jawab/kontak utama supplier ini.',
                     '#email': 'Email: alamat email supplier untuk korespondensi/invoice.',
                     '#telp': 'Telp / Handphone: nomor kontak supplier ini.',
                     '#alamat': 'Alamat: alamat supplier/vendor ini.'
                 };
                 Object.keys(supplierFields).forEach(function(selector) {
                     document.querySelectorAll('#modaltambahsupplier ' + selector).forEach(function(el) {
                         attachTooltip(el, supplierFields[selector]);
                     });
                 });
                 document.querySelectorAll('#modaltambahsupplier #tombolsimpan').forEach(function(el) {
                     attachTooltip(el, 'Simpan: menyimpan supplier baru ini.');
                 });
             }

             function attachUtilityTooltips() {
                 function attachHeaderIn(containerSelector, matcher, message) {
                     document.querySelectorAll(containerSelector + ' thead th, ' + containerSelector + ' th').forEach(function(header, index) {
                         if (matcher(normalizedText(header), index)) {
                             attachTooltip(header, message);
                         }
                     });
                 }

                 if (previewKey === 'management-user') {
                     attachByText('a, button', ['tambah user baru'], copy.tambahUserButton, '.btn, a, button');
                     attachByText('a, button', ['hak akses user'], copy.haqAksesButton, '.btn, a, button');
                     attachByText('a, button', ['tambah role'], copy.tambahRoleButton, '.btn, a, button');

                     attachHeaderIn('#datauser', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#datauser', function(text) { return text.indexOf('id user') !== -1; }, copy.colIdUser);
                     attachHeaderIn('#datauser', function(text) { return text.indexOf('nama user') !== -1; }, copy.colNamaUser);
                     attachHeaderIn('#datauser', function(text) { return text === 'role'; }, copy.colRole);
                     attachHeaderIn('#datauser', function(text) { return text === 'status'; }, copy.colStatus);
                     document.querySelectorAll('#datauser button').forEach(function(el) {
                         if (normalizedText(el) === 'view') attachTooltip(el, copy.aksiView);
                     });

                     // Modal "Tambah User" -- cuma field identitas dasar yang
                     // di-cover di sini. Daftar checkbox hak akses per menu
                     // di dalamnya sengaja tidak di-cover satu-satu (terlalu
                     // banyak & berubah-ubah ikut daftar fitur), sama seperti
                     // halaman Hak Akses User yang juga sengaja dilewati.
                     document.querySelectorAll('#modaltambah #iduser').forEach(function(el) { attachTooltip(el, copy.tambahIdUser); });
                     document.querySelectorAll('#modaltambah #namalengkap').forEach(function(el) { attachTooltip(el, copy.tambahNamaLengkap); });
                     document.querySelectorAll('#modaltambah #levelDisplay').forEach(function(el) { attachTooltip(el, copy.tambahLevel); });
                     document.querySelectorAll('#modaltambah .btnsimpan').forEach(function(el) { attachTooltip(el, copy.tambahSimpan); });
                     attachByText('#modaltambah label', ['hak akses'], copy.tambahHakAkses, '.form-group');

                     // Modal "View Data User" (edit/reset password/status/hapus).
                     document.querySelectorAll('#modaledit #userid').forEach(function(el) { attachTooltip(el, copy.editIdUser); });
                     document.querySelectorAll('#modaledit #namalengkap').forEach(function(el) { attachTooltip(el, copy.editNamaLengkap); });
                     document.querySelectorAll('#modaledit #level').forEach(function(el) { attachTooltip(el, copy.editLevel); });
                     document.querySelectorAll('#modaledit .chStatus').forEach(function(el) { attachTooltip(el, copy.editStatus); });
                     document.querySelectorAll('#modaledit .btnreset').forEach(function(el) { attachTooltip(el, copy.editResetPassword); });
                     document.querySelectorAll('#modaledit .btnhapus').forEach(function(el) { attachTooltip(el, copy.editHapus); });
                     document.querySelectorAll('#modaledit .btnsimpan').forEach(function(el) { attachTooltip(el, copy.editSimpan); });

                     // Modal "Tambah Role".
                     document.querySelectorAll('#modaltambahlevel #levelnama').forEach(function(el) { attachTooltip(el, copy.roleNama); });
                     document.querySelectorAll('#modaltambahlevel .btnsimpanlevel').forEach(function(el) { attachTooltip(el, copy.roleSimpan); });
                 }

                 if (previewKey === 'log-aktivitas') {
                     document.querySelectorAll('#logTabs a[href="#tabAktivitas"]').forEach(function(el) { attachTooltip(el, copy.tabAktivitas); });
                     document.querySelectorAll('#logTabs a[href="#tabMemory"]').forEach(function(el) { attachTooltip(el, copy.tabMemory); });

                     document.querySelectorAll('#logSearchInput').forEach(function(el) { attachTooltip(el, copy.search); });
                     document.querySelectorAll('#logFilterToggle').forEach(function(el) { attachTooltip(el, copy.filterToggle); });
                     document.querySelectorAll('#fUserid').forEach(function(el) { attachTooltip(el, copy.filterUser); });
                     document.querySelectorAll('#fLokasi').forEach(function(el) { attachTooltip(el, copy.filterLokasi); });
                     document.querySelectorAll('#fAksi').forEach(function(el) { attachTooltip(el, copy.filterAksi); });
                     document.querySelectorAll('#fTglAwal').forEach(function(el) { attachTooltip(el, copy.filterTglAwal); });
                     document.querySelectorAll('#fTglAkhir').forEach(function(el) { attachTooltip(el, copy.filterTglAkhir); });
                     document.querySelectorAll('#logFilterReset').forEach(function(el) { attachTooltip(el, copy.filterReset); });
                     document.querySelectorAll('#logPageLength').forEach(function(el) { attachTooltip(el, copy.pageLength); });

                     attachHeaderIn('#tabelLog', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#tabelLog', function(text) { return text === 'waktu'; }, copy.colWaktu);
                     attachHeaderIn('#tabelLog', function(text) { return text === 'user'; }, copy.colUser);
                     attachHeaderIn('#tabelLog', function(text) { return text === 'aksi'; }, copy.colAksi);
                     attachHeaderIn('#tabelLog', function(text) { return text === 'lokasi'; }, copy.colLokasi);
                     attachHeaderIn('#tabelLog', function(text) { return text === 'keterangan'; }, copy.colKeterangan);
                     attachHeaderIn('#tabelLog', function(text) { return text === 'detail'; }, copy.colDetail);
                     document.querySelectorAll('.btn-lihat-detail-log').forEach(function(el) { attachTooltip(el, copy.colDetail); });

                     document.querySelectorAll('#inputEmailPenerima').forEach(function(el) { attachTooltip(el, copy.emailPenerima); });
                     document.querySelectorAll('#inputIntervalHari').forEach(function(el) { attachTooltip(el, copy.intervalHari); });
                     document.querySelectorAll('#btnSimpanEmailPenerima').forEach(function(el) { attachTooltip(el, copy.simpanEmailButton); });

                     attachHeaderIn('#tabelMemory', function(text, idx) { return idx === 0; }, copy.colNo);
                     attachHeaderIn('#tabelMemory', function(text) { return text.indexOf('nama file') !== -1; }, copy.colNamaFile);
                     attachHeaderIn('#tabelMemory', function(text) { return text.indexOf('jumlah baris') !== -1; }, copy.colJumlahBaris);
                     attachHeaderIn('#tabelMemory', function(text) { return text === 'periode'; }, copy.colPeriode);
                     attachHeaderIn('#tabelMemory', function(text) { return text.indexOf('waktu diarsipkan') !== -1; }, copy.colWaktuDiarsipkan);
                     attachHeaderIn('#tabelMemory', function(text) { return text === 'aksi'; }, copy.aksiDownload);
                     document.querySelectorAll('#tabelMemory .fa-download').forEach(function(icon) { attachTooltip(icon.closest('a, button') || icon, copy.aksiDownload); });
                 }

                 if (previewKey === 'ganti-password') {
                     attachLabel(['password lama'], copy.passwordLama);
                     // 'password baru' generik dipasang duluan, baru 'confirm
                     // password baru' yang lebih spesifik -- soalnya labelnya
                     // ("Confirm Password Baru") mengandung substring "password
                     // baru", jadi kalau urutannya kebalik si generik bakal
                     // nimpa balik yang spesifik.
                     attachLabel(['password baru'], copy.passwordBaru);
                     attachLabel(['confirm password baru'], copy.confirmPasswordBaru);
                     document.querySelectorAll('.btnsimpan').forEach(function(el) { attachTooltip(el, copy.simpanButton); });
                 }
             }

             function attachDynamicPreviewTooltips() {
                 attachIconButton('.fa-eye', copy.viewButton || copy.detail || copy.aksi || copy.action);
                 attachIconButton('.fa-edit, .fa-pencil-alt', copy.editButton || copy.edit || copy.aksi || copy.action);
                 attachIconButton('.fa-trash, .fa-trash-alt', copy.delete || copy.aksi || copy.action);
                 attachIconButton('.fa-ban, .fa-times-circle', copy.cancelButton || copy.cancel || copy.aksi || copy.action);
                 // fa-undo juga dipakai tombol "Kembali" di hampir semua
                 // halaman -- pakai attachTooltipIfEmpty biar nggak nimpa
                 // penjelasan "Kembali" yang udah benar dari attachByText.
                 document.querySelectorAll('.fa-sync, .fa-redo, .fa-undo').forEach(function(icon) {
                     attachTooltipIfEmpty(icon.closest('a, button, .btn') || icon, copy.resetItem || copy.filterButton || copy.aksi);
                 });
                 attachIconButton('.fa-print, .fa-file-invoice, .fa-money-check-alt', copy.aksi || copy.action);
                 attachAllButtons();
                 attachStatusBadges();
                 attachProgressTooltips();
                 attachPoKeluarTooltips();
                attachPoMasukEditTooltips();
                attachClosePoModalTooltips();
                attachKoreksiModalTooltips();
                attachKeuanganTooltips();
                attachTransaksiMaterialTooltips();
                attachTransaksiProdukTooltips();
                attachMasterTooltips();
                attachSharedContactModalTooltips();
                attachUtilityTooltips();

                 document.querySelectorAll('.po-search-input, input[type="search"], .dataTables_filter input').forEach(function(element) {
                     attachTooltip(element, copy.search || copy.filterButton);
                 });
                 document.querySelectorAll('.po-filter-toggle, .manual-filter-trigger, [data-filter-toggle]').forEach(function(element) {
                     attachTooltip(element, copy.filterButton || copy.search);
                 });
                 document.querySelectorAll('.po-filter-panel, .manual-filter-panel').forEach(function(element) {
                     attachTooltip(element, copy.filterPanel || copy.filterButton);
                 });

                 // Ikon-ikon ini dipakai ulang lintas halaman po-masuk dengan arti
                 // beda-beda per halaman (fa-save = "Tambah Item" di Input PO tapi
                 // "Simpan PO" pas import PDF; fa-trash-alt = hapus 1 item draft/item
                 // tersimpan di sini, padahal di daftar PO artinya hapus SATU PO utuh;
                 // fa-sync-alt = "Reload Data" di Input PO tapi "Batalkan edit item" di
                 // Ubah PO) -- tebakan generik attachAllButtons()/fallbackButtonTooltip()
                 // gampang salah pilih copy key. attachTooltip() di sini SENGAJA selalu
                 // nimpa (bukan attachTooltipIfEmpty) biar menang dari tebakan generik itu.
                 document.querySelectorAll('#tombolSimpanItem').forEach(function(el) {
                     attachTooltip(el, copy.saveItem);
                 });
                 document.querySelectorAll('#tombolBatal').forEach(function(el) {
                     attachTooltip(el, copy.cancelItemEdit || copy.resetItem);
                 });
                 document.querySelectorAll('.po-draft-table .btn-danger').forEach(function(el) {
                     attachTooltip(el, copy.deleteDraftItem || copy.delete);
                 });
                 document.querySelectorAll('#datadetail .btn-danger').forEach(function(el) {
                     attachTooltip(el, copy.deleteSavedItem || copy.delete);
                 });
             }

             attachDynamicPreviewTooltips();

             let tooltipScanTimer = null;
             function scheduleTooltipScan() {
                 clearTimeout(tooltipScanTimer);
                 tooltipScanTimer = setTimeout(attachDynamicPreviewTooltips, 80);
             }

             if ('MutationObserver' in window) {
                 new MutationObserver(scheduleTooltipScan).observe(document.body, {
                     childList: true,
                     subtree: true
                 });
             }

             if (window.jQuery) {
                 window.jQuery(document).on('ajaxComplete draw.dt', scheduleTooltipScan);
             }
         });
     </script>
     <?php endif ?>
     <script>
         // Notifikasi global: kalau ada request AJAX yang ditolak karena hak akses
         // (401 sesi habis, 403 tidak punya izin), tampilkan popup-nya di sini
         // sekali buat semua halaman, daripada ngandelin tiap form nangkep sendiri.
         //
         // 403 itu bisa dari 2 sumber yang beda banget akar masalahnya, tapi
         // dulu nunjukin pesan yang PERSIS SAMA ("Anda tidak punya akses ke
         // fitur ini.") jadi nggak kelihatan bedanya:
         //   1. AccessControl beneran nolak izin -- responsnya punya field
         //      `error` (lihat FilterAdmin/FilterKasir/dkk).
         //   2. Token CSRF kadaluarsa (CodeIgniter\Security\Exceptions\
         //      SecurityException) -- responsnya PUNYA `title` tapi TIDAK
         //      punya `error`, jadi sebelumnya jatuh ke pesan default yang
         //      sama kayak poin 1, padahal solusinya beda (refresh halaman,
         //      bukan minta ditambahin izin).
         $(document).ajaxError(function(event, jqXHR) {
             if (jqXHR.status !== 401 && jqXHR.status !== 403) {
                 return;
             }

             let title = 'Akses Ditolak';
             let message = jqXHR.status === 401 ?
                 'Sesi login sudah habis. Silakan login ulang.' :
                 'Anda tidak punya akses ke fitur ini.';

             try {
                 const data = JSON.parse(jqXHR.responseText);
                 if (data && typeof data.error === 'string') {
                     message = data.error;
                 } else if (jqXHR.status === 403 && typeof data?.title === 'string' && data.title.includes('SecurityException')) {
                     title = 'Token Keamanan Kadaluarsa';
                     message = 'Sesi/token keamanan halaman ini sudah kadaluarsa (bukan soal izin akses). Silakan refresh halaman lalu coba lagi.';
                 }
             } catch (e) {}

             showBootstrapModal({
                 icon: 'warning',
                 title: title,
                 text: message
             });
         });
     </script>
 </body>

 </html>
