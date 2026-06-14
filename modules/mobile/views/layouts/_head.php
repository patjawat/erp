<?php
/**
 * Head section: viewport meta, custom CSS.
 * Bootstrap 5 และ Lucide โหลดจาก AppAsset (ไม่ใช้ CDN).
 */
use yii\bootstrap5\Html;
?>

<meta charset="<?= Yii::$app->charset ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no, viewport-fit=cover">
<?php $this->registerCsrfMetaTags() ?>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<title><?= Html::encode($this->title) ?></title>
<?php $this->head() ?>
<style>
    :root {
        --mobile-primary: #0d6efd;
        --mobile-primary-dark: #0a58ca;
        --mobile-primary-soft: rgba(13, 110, 253, 0.06);
        --mobile-primary-soft-border: rgba(13, 110, 253, 0.15);
        --mobile-primary-soft-strong: rgba(13, 110, 253, 0.25);

        /* 4pt spacing scale — drive all margins, gaps, paddings off these. */
        --space-2xs: 0.25rem;  /*  4px — chip gap */
        --space-xs:  0.5rem;   /*  8px — tight grouping */
        --space-sm:  0.75rem;  /* 12px — sibling rows */
        --space-md:  1rem;     /* 16px — card padding, default gap */
        --space-lg:  1.5rem;   /* 24px — between subsections */
        --space-xl:  2rem;     /* 32px — between major sections */
        --space-2xl: 3rem;     /* 48px — page-level breathing */

        /* Type scale — 1.2 ratio, fixed rem per product register guidance. */
        --fs-2xs: 0.6875rem; /* 11px — micro labels (rare) */
        --fs-xs:  0.75rem;   /* 12px — captions, badges */
        --fs-sm:  0.875rem;  /* 14px — secondary body */
        --fs-md:  1rem;      /* 16px — body */
        --fs-lg:  1.125rem;  /* 18px — section titles */
        --fs-xl:  1.375rem;  /* 22px — page title */
        --fs-2xl: 1.75rem;   /* 28px — hero/greeting name */
        --fs-3xl: 2.25rem;   /* 36px — feature numbers */

        /* Ink scale — slate ramp for text. All pass WCAG AA on --surface. */
        --ink:    #0f172a; /* slate-900 — primary headings */
        --ink-2:  #334155; /* slate-700 — form labels */
        --ink-3:  #475569; /* slate-600 — section headings, body-2 */
        --ink-4:  #64748b; /* slate-500 — captions */
        --ink-5:  #94a3b8; /* slate-400 — meta, disabled */
        --ink-line: rgba(15, 23, 42, 0.06); /* hairline dividers */

        /* Surface scale */
        --surface:   #fff;     /* card / page */
        --surface-2: #f5f7fa;  /* tinted page (drawer body, modal body) */
        --surface-3: #f1f3f5;  /* pill-group bg, filter bar */

        /* Semantic palette — solid + soft pair, 4.5:1 on white verified */
        --danger:        #ef4444;
        --danger-strong: #b91c1c;
        --danger-soft:   rgba(239, 68, 68, 0.13);
        --success:       #15803d;
        --success-soft:  rgba(34, 197, 94, 0.13);
        --warning:       #b54708;
        --warning-soft:  rgba(255, 159, 28, 0.13);

        /* Elevation — semantic shadow scale */
        --shadow-sm: 0 1px 4px rgba(15, 23, 42, 0.04);
        --shadow-md: 0 2px 12px rgba(15, 23, 42, 0.06);
        --shadow-lg: 0 4px 24px rgba(15, 23, 42, 0.08);

        /* Z-index scale — semantic, not arbitrary */
        --z-sticky:  100;
        --z-overlay: 1000;
        --z-modal:   1050;
        --z-toast:   1080;

        /* Category palette — OKLCH harmonised at L 0.58, C 0.14-0.18.
           Each service gets a distinct hue for wayfinding on dense menus
           (the LINE / Grab pattern). Surfaces stay restrained; only the
           icon medallion is coloured. Foreground tones are darker (L 0.40)
           for 4.5:1+ contrast against the soft background tint. */
        --cat-attendance-fg:    oklch(0.45 0.14 75);    /* warm amber — clock/time */
        --cat-attendance-bg:    oklch(0.95 0.04 75);
        --cat-vehicle-fg:       oklch(0.45 0.12 200);   /* teal — transport */
        --cat-vehicle-bg:       oklch(0.95 0.04 200);
        --cat-meeting-fg:       oklch(0.45 0.16 290);   /* violet — calendar */
        --cat-meeting-bg:       oklch(0.95 0.05 290);
        --cat-leave-fg:         oklch(0.50 0.16 15);    /* rose — personal time */
        --cat-leave-bg:         oklch(0.96 0.04 15);
        --cat-maintenance-fg:   oklch(0.45 0.10 30);    /* terracotta — repair */
        --cat-maintenance-bg:   oklch(0.95 0.03 30);
        --cat-asset-fg:         oklch(0.45 0.13 155);   /* emerald — inventory */
        --cat-asset-bg:         oklch(0.95 0.04 155);
        --cat-supply-fg:        oklch(0.45 0.13 260);   /* indigo — supplies */
        --cat-supply-bg:        oklch(0.96 0.04 260);
        --cat-document-fg:      oklch(0.45 0.10 250);   /* steel-blue — paperwork */
        --cat-document-bg:      oklch(0.96 0.03 250);
        --cat-issue-fg:         oklch(0.50 0.16 25);    /* red — escalation */
        --cat-issue-bg:         oklch(0.96 0.04 25);
        --cat-approval-fg:      oklch(0.45 0.16 250);   /* cobalt — approval (kin to primary) */
        --cat-approval-bg:      oklch(0.95 0.05 250);
    }

    /* Category medallion — circle around an icon, sized to match the icon utility. */
    .cat-medallion {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        width: 2.75rem;
        height: 2.75rem;
        flex-shrink: 0;
    }
    .cat-medallion svg { stroke: currentColor; }
    .cat-attendance  { background: var(--cat-attendance-bg);  color: var(--cat-attendance-fg); }
    .cat-vehicle     { background: var(--cat-vehicle-bg);     color: var(--cat-vehicle-fg); }
    .cat-meeting     { background: var(--cat-meeting-bg);     color: var(--cat-meeting-fg); }
    .cat-leave       { background: var(--cat-leave-bg);       color: var(--cat-leave-fg); }
    .cat-maintenance { background: var(--cat-maintenance-bg); color: var(--cat-maintenance-fg); }
    .cat-asset       { background: var(--cat-asset-bg);       color: var(--cat-asset-fg); }
    .cat-supply      { background: var(--cat-supply-bg);      color: var(--cat-supply-fg); }
    .cat-document    { background: var(--cat-document-bg);    color: var(--cat-document-fg); }
    .cat-issue       { background: var(--cat-issue-bg);       color: var(--cat-issue-fg); }
    .cat-approval    { background: var(--cat-approval-bg);    color: var(--cat-approval-fg); }
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        background-color: #f0f2f5;
        min-height: 100vh;
        padding-bottom: env(safe-area-inset-bottom);
    }
    .mobile-app-content {
        padding-bottom: calc(env(safe-area-inset-bottom) + 5.5rem);
        min-height: 100vh;
    }
    /* Shared card shell — every mobile card variant. Add the class name here, skip the per-view block. */
    .mobile-card,
    .leave-card, .booking-card, .maint-card, .vehicle-card,
    .meeting-booking-card, .meeting-calendar, .meeting-view-card,
    .home-greeting-card, .home-quick-action, .service-menu-card,
    .room-card, .room-manage-card,
    .notif-card, .profile-card, .asset-card, .req-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    }
    /* Shared form-control radius inside any mobile-* card */
    .mobile-card .form-control, .mobile-card .form-select,
    .leave-card .form-control, .leave-card .form-select,
    .booking-card .form-control, .booking-card .form-select,
    .maint-card .form-control, .maint-card .form-select,
    .meeting-booking-card .form-control, .meeting-booking-card .form-select {
        border-radius: 12px;
        padding: 0.75rem 1rem;
    }
    .leave-card .form-label,
    .booking-card .form-label,
    .maint-card .form-label,
    .meeting-booking-card .form-label {
        font-weight: 500;
    }
    /* Sticky page header — drenched blue matching the home-hero so the brand
       carries across every mobile screen. Text overrides force white over the
       Bootstrap text-dark / text-body-secondary classes used in _header.php. */
    .mobile-header {
        background: linear-gradient(180deg, var(--mobile-primary) 0%, var(--mobile-primary-dark) 100%);
        color: #fff;
        box-shadow: 0 2px 12px rgba(13, 110, 253, 0.18);
        padding: calc(env(safe-area-inset-top, 0px) + var(--space-sm)) var(--space-md) var(--space-sm);
    }
    .mobile-header h1,
    .mobile-header .h1, .mobile-header .h2, .mobile-header .h3,
    .mobile-header .h4, .mobile-header .h5, .mobile-header .h6 {
        color: #fff !important;
    }
    .mobile-header p,
    .mobile-header .text-body-secondary,
    .mobile-header .text-muted {
        color: rgba(255, 255, 255, 0.85) !important;
    }
    .mobile-header a { color: #fff; }
    .mobile-header a:hover, .mobile-header a:focus { color: rgba(255, 255, 255, 0.85); }

    /* Stacked page rhythm — major sections separated by --space-xl, content within --space-md */
    .mobile-stack { display: flex; flex-direction: column; gap: var(--space-xl); }
    .mobile-stack-tight { display: flex; flex-direction: column; gap: var(--space-md); }

    /* Section heading — distinct from card heading, no card wrapper needed */
    .section-title {
        display: flex; align-items: center; gap: var(--space-xs);
        font-size: var(--fs-lg); font-weight: 700; color: #1a1f2c;
        margin: 0 0 var(--space-sm); line-height: 1.2; letter-spacing: -0.01em;
    }
    .section-title svg { color: var(--mobile-primary); width: 1.25rem; height: 1.25rem; flex-shrink: 0; }
    .section-action {
        margin-left: auto; font-size: var(--fs-sm); font-weight: 500;
        color: var(--mobile-primary); text-decoration: none;
    }
    .section-action:hover { color: var(--mobile-primary-dark); }

    /* Segmented pill group — native-feeling 2-3 option selector (replaces select for short lists) */
    .pill-group {
        display: flex;
        gap: var(--space-2xs);
        padding: var(--space-2xs);
        background: #f1f3f5;
        border-radius: 12px;
    }
    .pill-group input[type="radio"] {
        position: absolute; opacity: 0; pointer-events: none;
    }
    .pill-option {
        flex: 1; min-height: 2.5rem;
        display: flex; align-items: center; justify-content: center;
        text-align: center; padding: var(--space-xs) var(--space-sm);
        font-size: var(--fs-sm); font-weight: 500; color: #495057;
        background: transparent; border: 0; border-radius: 10px;
        cursor: pointer; user-select: none;
        transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    }
    .pill-option:hover { color: var(--mobile-primary); }
    .pill-option.is-active,
    .pill-group input[type="radio"]:checked + .pill-option {
        background: #fff; color: var(--mobile-primary); font-weight: 600;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .pill-option:focus-visible { outline: 2px solid var(--mobile-primary); outline-offset: -2px; }

    /* Fiscal-year filter used by mobile list pages. */
    .mobile-year-filter {
        display: flex;
        align-items: center;
        gap: var(--space-sm);
        margin: 0;
    }
    .mobile-year-filter-label {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2xs);
        flex-shrink: 0;
        margin: 0;
        color: var(--ink-3);
        font-size: var(--fs-xs);
        font-weight: 700;
        line-height: 1.2;
    }
    .mobile-year-filter-label svg {
        width: 1rem;
        height: 1rem;
        color: var(--mobile-primary);
    }
    .mobile-year-filter-select {
        flex: 1 1 auto;
        min-width: 0;
        min-height: 2.75rem;
        border: 1px solid var(--ink-line);
        border-radius: 12px;
        background: var(--surface);
        color: var(--ink);
        padding: 0 var(--space-md);
        font-size: var(--fs-sm);
        font-weight: 700;
        transition: border-color 160ms cubic-bezier(0.16, 1, 0.3, 1),
                    box-shadow 160ms cubic-bezier(0.16, 1, 0.3, 1),
                    background-color 160ms cubic-bezier(0.16, 1, 0.3, 1);
    }
    .mobile-year-filter-select:focus {
        outline: 0;
        border-color: var(--mobile-primary);
        background-color: var(--surface);
        box-shadow: 0 0 0 3px var(--mobile-primary-soft-border);
    }
    @media (max-width: 360px) {
        .mobile-year-filter {
            align-items: stretch;
            flex-direction: column;
            gap: var(--space-xs);
        }
    }

    /* Stat box — compact metric tile for summary readouts */
    .stat-box {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        padding: var(--space-sm);
        background: var(--mobile-primary-soft);
        border: 1px solid var(--mobile-primary-soft-border);
        border-radius: 12px;
        text-align: center;
    }
    .stat-box .stat-value { font-size: var(--fs-xl); font-weight: 700; color: var(--mobile-primary); line-height: 1; }
    .stat-box .stat-value.is-warn { color: #b54708; }
    .stat-box .stat-label { font-size: var(--fs-xs); color: #6c757d; margin-top: var(--space-2xs); }

    /* Sticky bottom action bar — primary submit always reachable above safe area */
    .mobile-form-actions {
        position: sticky;
        bottom: 0;
        margin-inline: calc(var(--space-md) * -1);
        padding: var(--space-sm) var(--space-md) calc(env(safe-area-inset-bottom, 0px) + var(--space-sm));
        background: #fff;
        box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.06);
        z-index: 10;
    }
    .mobile-form-actions .btn { width: 100%; min-height: 3rem; font-size: var(--fs-md); font-weight: 600; border-radius: 12px; }

    /* Inline list row — replaces "card per item" for low-priority items */
    .row-list { display: flex; flex-direction: column; gap: 0; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06); }
    .row-item {
        display: flex; align-items: center; gap: var(--space-sm);
        padding: var(--space-sm) var(--space-md);
        text-decoration: none; color: inherit;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        transition: background 0.15s ease;
    }
    .row-item:last-child { border-bottom: 0; }
    .row-item:hover, .row-item:active { background: rgba(0, 0, 0, 0.03); color: inherit; }
    .row-item .row-title { font-size: var(--fs-md); font-weight: 500; color: #1a1f2c; flex-grow: 1; min-width: 0; }
    .row-item .row-desc { font-size: var(--fs-xs); color: #6c757d; }
    .row-item .row-chevron { color: #adb5bd; flex-shrink: 0; }

    /* Icon sizing utilities — replaces inline `style="width:...; height:...;"` */
    .mi-xs { width: 1rem;    height: 1rem;    flex-shrink: 0; }
    .mi-sm { width: 1.125rem; height: 1.125rem; flex-shrink: 0; }
    .mi-md { width: 1.25rem; height: 1.25rem; flex-shrink: 0; }
    .mi-lg { width: 1.75rem; height: 1.75rem; flex-shrink: 0; }
    .mi-xl { width: 2rem;    height: 2rem;    flex-shrink: 0; }
    .mi-baseline { vertical-align: -0.2em; }

    /* Keyboard focus — visible ring on every interactive surface in mobile module */
    .mobile-nav-item:focus-visible,
    .home-quick-action:focus-visible,
    .service-menu-card:focus-visible,
    .check-type-btn:focus-visible,
    .btn-attach:focus-visible,
    .maint-btn-camera:focus-visible,
    .maint-btn-gallery:focus-visible,
    .vehicle-card:focus-visible,
    .room-card:focus-visible,
    .home-request-item:focus-visible {
        outline: 2px solid var(--mobile-primary);
        outline-offset: -2px;
        border-radius: 12px;
    }

    /* Reduced motion — disable transitions/animations for users who request it */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }

    /* ---- Loading page ---- */
    .mobile-loading-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: #f5f5f9;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        transition: opacity 0.25s ease, visibility 0.25s ease;
        padding: env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left);
    }
    .mobile-loading-overlay.mobile-loading-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
    .mobile-loading-spinner {
        width: 2.75rem;
        height: 2.75rem;
        border: 3px solid rgba(13, 110, 253, 0.2);
        border-top-color: var(--mobile-primary);
        border-radius: 50%;
        animation: mobile-loading-spin 0.7s linear infinite;
    }
    .mobile-loading-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin: 0;
    }
    @keyframes mobile-loading-spin {
        to { transform: rotate(360deg); }
    }

    /* ── Navigation progress bar — instant tap-feedback ──────────────────
       Sits above everything (including loading overlay) so the user sees
       an immediate response the moment a link is tapped. The bar grows
       quickly at first, then decelerates as it nears 100%, mimicking the
       browser's own progress indicator. Pairs with mobile-shared.js
       initNavLoading: bar paints instantly, full overlay appears after
       250ms if navigation is still in flight. */
    #mobile-nav-progress {
        position: fixed;
        top: 0; left: 0; right: 0;
        height: 3px;
        z-index: 10000; /* above mobile-loading-overlay (9999) */
        background: transparent;
        pointer-events: none;
        overflow: hidden;
    }
    #mobile-nav-progress::after {
        content: '';
        display: block;
        width: 100%; height: 100%;
        background: linear-gradient(90deg,
            var(--mobile-primary) 0%,
            var(--mobile-primary-dark) 100%);
        transform: scaleX(0);
        transform-origin: 0 50%;
        box-shadow: 0 0 6px rgba(13, 110, 253, 0.6);
    }
    #mobile-nav-progress.is-loading::after {
        animation: mobile-nav-progress 6s cubic-bezier(0, 0.4, 0.6, 1) forwards;
    }
    #mobile-nav-progress.is-done::after {
        animation: mobile-nav-progress-end 240ms cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    @keyframes mobile-nav-progress {
        0%   { transform: scaleX(0); }
        30%  { transform: scaleX(0.4); }
        60%  { transform: scaleX(0.7); }
        90%  { transform: scaleX(0.92); }
        100% { transform: scaleX(0.97); }
    }
    @keyframes mobile-nav-progress-end {
        0%   { transform: scaleX(0.97); opacity: 1; }
        50%  { transform: scaleX(1); opacity: 1; }
        100% { transform: scaleX(1); opacity: 0; }
    }
    @media (prefers-reduced-motion: reduce) {
        #mobile-nav-progress::after,
        #mobile-nav-progress.is-loading::after,
        #mobile-nav-progress.is-done::after { animation: none !important; transition: none !important; }
        #mobile-nav-progress.is-loading::after { transform: scaleX(0.7); opacity: 1; }
        #mobile-nav-progress.is-done::after { opacity: 0; }
    }

    /* ─────────────────────────────────────────────────────────────────
       Shared "fixed shell" pattern — drenched blue Hero + optional
       overlay card. Used by index.php, news.php, booking-vehicle.php
       and any future page that wants a pinned header.

       Render via the _partials/_hero_shell.php partial, then wrap your
       page body in <div class="app-scroll">. mobile-shared.js detects
       .app-shell and writes its height to --shell-h so .app-scroll
       offsets correctly.
       ───────────────────────────────────────────────────────────────── */
    .app-shell {
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 100; /* --z-sticky */
        background: transparent;
        pointer-events: none;
    }
    .app-shell > * { pointer-events: auto; }

    .app-scroll {
        padding-top: var(--shell-h, 13rem);
    }
    .app-scroll.has-stats,
    .app-scroll.has-overlay {
        padding-top: calc(var(--shell-h, 13rem) + var(--space-md));
    }

    /* ── Hero (drenched blue) ────────────────────────────────────── */
    .app-hero {
        position: relative;
        padding: calc(env(safe-area-inset-top, 0px) + 4.25rem) var(--space-md) calc(var(--space-2xl) + var(--space-sm));
        color: #fff;
        background: linear-gradient(180deg, var(--mobile-primary) 0%, var(--mobile-primary-dark) 100%);
        border-bottom-left-radius: 28px;
        border-bottom-right-radius: 28px;
        overflow: hidden;
    }
    .app-hero::before, .app-hero::after {
        content: ''; position: absolute; border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
        pointer-events: none;
    }
    .app-hero::before { top: -80px; right: -50px; width: 220px; height: 220px; animation: app-hero-blob 9s ease-in-out infinite; }
    .app-hero::after  { bottom: -50px; left: -40px; width: 170px; height: 170px; background: rgba(255, 255, 255, 0.06); animation: app-hero-blob 11s ease-in-out infinite reverse; }

    .app-hero-row {
        display: flex; align-items: center; gap: var(--space-md);
        position: relative; z-index: 1;
    }
    .app-hero-icon {
        width: 3.25rem; height: 3.25rem; border-radius: 18px;
        background: rgba(255, 255, 255, 0.18);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .app-hero-icon svg { width: 1.5rem; height: 1.5rem; }
    .app-hero-title {
        font-size: var(--fs-xl); font-weight: 700; color: #fff;
        margin: 0; line-height: 1.2; letter-spacing: -0.015em;
    }
    .app-hero-sub {
        font-size: var(--fs-sm); color: rgba(255, 255, 255, 0.88);
        margin: 4px 0 0; font-weight: 500;
    }

    /* ── Stats overlay (overlaps hero curve) ─────────────────────── */
    .app-stats {
        position: relative; z-index: 2;
        margin: -1.75rem var(--space-md) 0;
        display: grid;
        gap: var(--space-2xs);
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(13, 110, 253, 0.12), 0 2px 6px rgba(15, 23, 42, 0.04);
        padding: var(--space-xs);
    }
    .app-stats[data-cols="2"] { grid-template-columns: repeat(2, 1fr); }
    .app-stats[data-cols="3"] { grid-template-columns: repeat(3, 1fr); }
    .app-stats[data-cols="4"] { grid-template-columns: repeat(4, 1fr); }
    .app-stat {
        display: flex; flex-direction: column; align-items: center;
        gap: 2px;
        padding: var(--space-sm) var(--space-2xs);
        text-decoration: none; color: inherit;
        background: transparent; border: 0; /* reset for button tag */
        border-radius: 12px;
        position: relative;
        text-align: center;
        cursor: pointer;
        transition: background 160ms cubic-bezier(0.16, 1, 0.3, 1);
    }
    button.app-stat { font: inherit; -webkit-appearance: none; appearance: none; }
    .app-stat + .app-stat::before {
        content: ''; position: absolute; left: 0; top: 18%; bottom: 18%;
        width: 1px; background: rgba(15, 23, 42, 0.07);
    }
    .app-stat:hover { background: rgba(13, 110, 253, 0.04); color: inherit; }
    .app-stat.is-active { background: var(--mobile-primary-soft); }
    .app-stat-num {
        font-size: var(--fs-xl); font-weight: 800;
        line-height: 1; letter-spacing: -0.02em; color: var(--ink);
    }
    .app-stat-lbl {
        font-size: 0.6875rem; color: var(--ink-3);
        font-weight: 500; margin-top: 4px;
    }
    .app-stat[data-tone="primary"] .app-stat-num { color: var(--mobile-primary); }
    .app-stat[data-tone="warning"] .app-stat-num { color: var(--warning); }
    .app-stat[data-tone="success"] .app-stat-num { color: var(--success); }
    .app-stat[data-tone="danger"]  .app-stat-num { color: var(--danger-strong); }

    @keyframes app-hero-blob {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50%      { transform: translate(-10px, 6px) scale(1.06); }
    }
    @media (prefers-reduced-motion: reduce) {
        .app-hero::before, .app-hero::after { animation: none !important; }
        .app-stat { transition: none !important; }
        .mobile-year-filter-select { transition: none !important; }
    }
</style>
