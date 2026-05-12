<?php
use yii\helpers\Url;

?>
  <style>
    /* ───────────────────────────────────
       DESIGN TOKENS
    ─────────────────────────────────── */
    :root {
      --navy-950: #1a3d6b;
      --navy-900: #255FAE;
      --navy-800: #2868ba;
      --navy-700: #255FAE;
      --navy-600: #255FAE;
      --navy-500: #2d72cc;
      --navy-400: #4a8fd6;
      --navy-300: #7ab3e3;
      --navy-100: #d0e4f5;
      --navy-50:  #eef5fc;

      --gold:     #f59e0b;
      --gold-light: #fef3c7;

      --surface:  #f8fafc;
      --card-bg:  #ffffff;
      --border:   #e2e8f0;
      --border-soft: #f1f5f9;

      --text-primary:   #0f172a;
      --text-secondary: #475569;
      --text-muted:     #94a3b8;

      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      --radius-xl: 20px;

      --shadow-sm: 0 1px 3px rgba(15,23,42,.06), 0 1px 2px rgba(15,23,42,.04);
      --shadow-md: 0 4px 16px rgba(15,23,42,.08), 0 2px 6px rgba(15,23,42,.05);
      --shadow-lg: 0 12px 40px rgba(15,23,42,.12);
      --shadow-glow: 0 0 0 3px rgba(59,130,246,.25);
    }

    * { box-sizing: border-box; }

    body {
      background: var(--surface);
      color: var(--text-primary);
      min-height: 100vh;
      font-size: 15px;
    }

    /* ───────────────────────────────────
       TOP BAR (brand strip)
    ─────────────────────────────────── */
    #topbar {
      background: var(--navy-900);
      height: 56px;
      display: flex;
      align-items: center;
      padding: 0 24px;
      gap: 16px;
      position: sticky;
      top: 0;
      z-index: 1000;
      border-bottom: 1px solid rgba(255,255,255,.06);
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      flex-shrink: 0;
    }
    .brand-logo {
      width: 36px;
      height: 36px;
      background: var(--navy-600);
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
    }
    .brand-text-main {
      font-size: 15px;
      font-weight: 700;
      color: #fff;
      line-height: 1.1;
    }
    .brand-text-sub {
      font-size: 10px;
      color: var(--navy-300);
      font-weight: 400;
      letter-spacing: .6px;
      text-transform: uppercase;
    }

    .topbar-spacer { flex: 1; }

    .topbar-actions {
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .topbar-icon-btn {
      width: 36px;
      height: 36px;
      border-radius: var(--radius-sm);
      border: none;
      background: transparent;
      color: var(--navy-300);
      font-size: 17px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background .15s, color .15s;
      position: relative;
    }
    .topbar-icon-btn:hover { background: rgba(255,255,255,.08); color: #fff; }
    .topbar-badge {
      position: absolute;
      top: 5px; right: 5px;
      width: 8px; height: 8px;
      background: #ef4444;
      border-radius: 50%;
      border: 1.5px solid var(--navy-900);
    }

    .topbar-divider {
      width: 1px; height: 24px;
      background: rgba(255,255,255,.1);
      margin: 0 8px;
    }

    .avatar-btn {
      display: flex;
      align-items: center;
      gap: 10px;
      background: rgba(255,255,255,.07);
      border: none;
      border-radius: var(--radius-sm);
      padding: 5px 12px 5px 6px;
      cursor: pointer;
      transition: background .15s;
      color: #fff;
    }
    .avatar-btn:hover { background: rgba(255,255,255,.12); }
    .avatar-circle {
      width: 30px; height: 30px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--navy-500), var(--navy-700));
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: 700;
      color: #fff;
      flex-shrink: 0;
    }
    .avatar-name {
      font-size: 13px;
      font-weight: 500;
      color: #fff;
      white-space: nowrap;
    }
    .avatar-role {
      font-size: 10px;
      color: var(--navy-300);
    }

    /* ───────────────────────────────────
       MAIN NAV (mega-menu strip)
    ─────────────────────────────────── */
    #mainnav {
      background: var(--card-bg);
      border-bottom: 1px solid var(--border);
      position: sticky;
      top: 62px;
      z-index: 999;
      box-shadow: var(--shadow-sm);
    }

    .nav-container {
      max-width: 1600px;
      margin: 0 auto;
      padding: 0 24px;
      display: flex;
      align-items: stretch;
      height: 52px;
      gap: 2px;
    }

    .nav-item {
      position: relative;
      display: flex;
      align-items: center;
    }

    .nav-link {
      display: flex;
      align-items: center;
      gap: 7px;
      padding: 0 14px;
      height: 52px;
      text-decoration: none;
      color: var(--text-secondary);
      font-size: 13.5px;
      font-weight: 500;
      white-space: nowrap;
      border-bottom: 2px solid transparent;
      transition: color .15s, border-color .15s, background .15s;
      cursor: pointer;
      background: transparent;
      border-top: none;
      border-left: none;
      border-right: none;
    }
    .nav-link i { font-size: 16px; opacity: .7; transition: opacity .15s; }
    .nav-link:hover { color: var(--navy-500); background: var(--navy-50); }
    .nav-link:hover i { opacity: 1; }
    .nav-link.active {
      color: var(--navy-600);
      border-bottom-color: var(--navy-600);
      font-weight: 600;
    }
    .nav-link.active i { opacity: 1; }
    .nav-link .nav-caret {
      font-size: 11px;
      opacity: .5;
      margin-left: 2px;
      transition: transform .2s;
    }
    .nav-item:hover .nav-caret { transform: rotate(180deg); }
    .nav-item.open .nav-caret { transform: rotate(180deg); }

    /* Mega-menu dropdown */
    .mega-menu {
      position: absolute;
      top: calc(100% + 1px);
      left: 0;
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 0 var(--radius-lg) var(--radius-lg) var(--radius-lg);
      box-shadow: var(--shadow-lg);
      padding: 20px 24px;
      min-width: 340px;
      display: none;
      z-index: 1050;
      animation: megaFadeIn .15s ease;
    }
    .mega-menu.wide { min-width: 560px; }
    @keyframes megaFadeIn {
      from { opacity: 0; transform: translateY(-6px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    /* Desktop: hover opens menu */
    @media (hover: hover) {
      .nav-item:hover .mega-menu { display: block; }
    }
    /* Touch/JS controlled: .open class opens menu */
    .nav-item.open .mega-menu { display: block; }

    .mega-section-title {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: var(--text-muted);
      margin-bottom: 8px;
      margin-top: 16px;
    }
    .mega-section-title:first-child { margin-top: 0; }

    .mega-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2px;
    }
    .mega-grid.cols-3 { grid-template-columns: 1fr 1fr 1fr; }

    .mega-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 10px;
      border-radius: var(--radius-sm);
      text-decoration: none;
      color: var(--text-primary);
      font-size: 13.5px;
      transition: background .12s, color .12s;
      cursor: pointer;
    }
    .mega-item:hover { background: var(--navy-50); color: var(--navy-600); }
    .mega-item-icon {
      width: 30px; height: 30px;
      border-radius: 7px;
      background: var(--navy-50);
      color: var(--navy-500);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 15px;
      flex-shrink: 0;
      transition: background .12s;
    }
    .mega-item:hover .mega-item-icon { background: var(--navy-100); }
    .mega-item-label { font-weight: 500; }
    .mega-item-desc { font-size: 11.5px; color: var(--text-muted); line-height: 1.3; }

    /* ───────────────────────────────────
       PAGE LAYOUT
    ─────────────────────────────────── */
    .page-wrapper {
      max-width: 1600px;
      margin: 0 auto;
      padding: 28px 24px 60px;
    }

    /* breadcrumb */
    .breadcrumb-row {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 24px;
      font-size: 13px;
      color: var(--text-muted);
    }
    .breadcrumb-row a { color: var(--text-secondary); text-decoration: none; }
    .breadcrumb-row a:hover { color: var(--navy-500); }
    .breadcrumb-row .sep { color: var(--border); }
    .breadcrumb-row .current { color: var(--text-primary); font-weight: 500; }

    /* ───────────────────────────────────
       PROFILE HERO CARD
    ─────────────────────────────────── */
    .hero-card {
      background: linear-gradient(135deg, #1a3d6b 0%, #255FAE 55%, #2d72cc 100%);
      border-radius: var(--radius-xl);
      padding: 32px 36px;
      position: relative;
      overflow: hidden;
      color: #fff;
      margin-bottom: 24px;
    }
    .hero-card::before {
      content: '';
      position: absolute;
      top: -60px; right: -60px;
      width: 280px; height: 280px;
      border-radius: 50%;
      background: rgba(255,255,255,.04);
    }
    .hero-card::after {
      content: '';
      position: absolute;
      bottom: -80px; right: 120px;
      width: 200px; height: 200px;
      border-radius: 50%;
      background: rgba(255,255,255,.03);
    }

    .hero-inner {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: center;
      gap: 28px;
      flex-wrap: wrap;
    }

    .hero-avatar {
      width: 72px; height: 72px;
      border-radius: 50%;
      background: rgba(255,255,255,.15);
      border: 3px solid rgba(255,255,255,.3);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 26px;
      font-weight: 700;
      color: #fff;
      flex-shrink: 0;
    }

    .hero-info { flex: 1; min-width: 180px; }
    .hero-name {
      font-size: 22px;
      font-weight: 700;
      margin-bottom: 4px;
    }
    .hero-title {
      font-size: 13.5px;
      color: rgba(255,255,255,.7);
      margin-bottom: 10px;
    }
    .hero-badges {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }
    .hero-badge {
      display: flex;
      align-items: center;
      gap: 5px;
      background: rgba(255,255,255,.12);
      border-radius: 20px;
      padding: 4px 12px;
      font-size: 12px;
      font-weight: 500;
    }
    .hero-badge.gold { background: rgba(245,158,11,.2); color: #fcd34d; }
    .hero-badge i { font-size: 13px; }

    .hero-stats {
      display: flex;
      gap: 28px;
      flex-wrap: wrap;
    }
    .hero-stat { text-align: center; }
    .hero-stat-val {
      font-size: 26px;
      font-weight: 700;
      line-height: 1;
      margin-bottom: 4px;
    }
    .hero-stat-label {
      font-size: 11.5px;
      color: rgba(255,255,255,.6);
      white-space: nowrap;
    }
    .hero-stat-divider {
      width: 1px;
      background: rgba(255,255,255,.15);
      align-self: stretch;
      margin: 4px 0;
    }

    .hero-timer-block {
      background: rgba(0,0,0,.25);
      border-radius: var(--radius-md);
      padding: 16px 22px;
      text-align: center;
      flex-shrink: 0;
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,.1);
    }
    .hero-timer-label { font-size: 11px; color: rgba(255,255,255,.55); margin-bottom: 4px; }
    .hero-timer {
      font-size: 30px;
      font-weight: 700;
      font-variant-numeric: tabular-nums;
      letter-spacing: 1px;
      color: #fff;
      font-family: 'Courier New', monospace;
    }
    .hero-timer-sublabel { font-size: 11px; color: rgba(255,255,255,.5); margin-top: 4px; }
    .hero-timer-btns {
      display: flex;
      gap: 6px;
      margin-top: 10px;
    }
    .btn-timer {
      flex: 1;
      padding: 6px 0;
      font-size: 12.5px;
      font-weight: 600;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      font-family: 'Sarabun', sans-serif;
      transition: opacity .15s;
    }
    .btn-timer:hover { opacity: .85; }
    .btn-timer-in { background: rgba(255,255,255,.9); color: var(--navy-800); }
    .btn-timer-out { background: rgba(255,255,255,.15); color: #fff; border: 1px solid rgba(255,255,255,.25); }

    /* ───────────────────────────────────
       MAIN GRID LAYOUT
    ─────────────────────────────────── */
    .dashboard-grid {
      display: grid;
      grid-template-columns: 1fr 300px;
      gap: 24px;
      align-items: start;
    }

    /* ───────────────────────────────────
       CARDS
    ─────────────────────────────────── */
    .card-panel {
      background: var(--card-bg);
      border-radius: var(--radius-lg);
      border: 1px solid var(--border);
      box-shadow: var(--shadow-sm);
      overflow: hidden;
    }
    .card-panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 22px 14px;
      border-bottom: 1px solid var(--border-soft);
    }
    .card-panel-title {
      font-size: 15px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .card-panel-title i { color: var(--navy-500); font-size: 17px; }
    .card-panel-action {
      font-size: 12.5px;
      color: var(--navy-500);
      text-decoration: none;
      font-weight: 500;
      padding: 4px 10px;
      border-radius: var(--radius-sm);
      transition: background .12s;
      cursor: pointer;
    }
    .card-panel-action:hover { background: var(--navy-50); }
    .card-panel-body { padding: 18px 22px; }

    /* ───────────────────────────────────
       QUICK ACTIONS
    ─────────────────────────────────── */
    .quick-actions-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 10px;
    }
    .quick-action-btn {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      padding: 16px 10px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      text-decoration: none;
      color: var(--text-primary);
      font-size: 12.5px;
      font-weight: 500;
      text-align: center;
      cursor: pointer;
      transition: all .2s ease;
      line-height: 1.3;
    }
    .quick-action-btn:hover {
      background: var(--navy-50);
      border-color: var(--navy-300);
      color: var(--navy-600);
      box-shadow: var(--shadow-sm);
      transform: translateY(-1px);
    }
    .qa-icon {
      width: 42px; height: 42px;
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      transition: background .2s;
    }
    .qa-blue   { background: #d0e4f5; color: #255FAE; }
    .qa-green  { background: #dcfce7; color: #15803d; }
    .qa-amber  { background: #fef3c7; color: #d97706; }
    .qa-purple { background: #ede9fe; color: #7c3aed; }
    .qa-rose   { background: #ffe4e6; color: #be123c; }
    .qa-teal   { background: #ccfbf1; color: #0f766e; }
    .qa-orange { background: #ffedd5; color: #c2410c; }
    .qa-slate  { background: #f1f5f9; color: #334155; }

    /* ───────────────────────────────────
       KPI STATS ROW
    ─────────────────────────────────── */
    .kpi-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
      margin-bottom: 24px;
    }
    .kpi-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 20px 22px;
      box-shadow: var(--shadow-sm);
      position: relative;
      overflow: hidden;
    }
    .kpi-card::after {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      border-radius: 3px 3px 0 0;
    }
    .kpi-card.blue::after   { background: var(--navy-500); }
    .kpi-card.gold::after   { background: var(--gold); }
    .kpi-card.green::after  { background: #22c55e; }

    .kpi-label {
      font-size: 12px;
      color: var(--text-muted);
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: .5px;
      margin-bottom: 8px;
    }
    .kpi-value {
      font-size: 28px;
      font-weight: 700;
      line-height: 1;
      margin-bottom: 6px;
    }
    .kpi-value.blue   { color: var(--navy-600); }
    .kpi-value.gold   { color: #d97706; }
    .kpi-value.green  { color: #16a34a; }
    .kpi-sub {
      font-size: 12px;
      color: var(--text-muted);
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .kpi-sub .badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 3px;
      background: #dcfce7;
      color: #15803d;
      border-radius: 20px;
      padding: 1px 8px;
      font-size: 11px;
      font-weight: 600;
    }
    .kpi-sub .badge-pill.gold { background: #fef3c7; color: #d97706; }

    /* Progress bar */
    .progress-custom {
      background: var(--border-soft);
      border-radius: 4px;
      height: 6px;
      overflow: hidden;
      margin-top: 10px;
    }
    .progress-custom-bar {
      height: 100%;
      border-radius: 4px;
      background: linear-gradient(90deg, var(--navy-500), var(--navy-400));
      transition: width .8s ease;
    }
    .progress-custom-bar.gold { background: linear-gradient(90deg, #f59e0b, #fbbf24); }

    /* ───────────────────────────────────
       DOCUMENTS LIST
    ─────────────────────────────────── */
    .doc-tabs {
      display: flex;
      gap: 2px;
      background: var(--surface);
      border-radius: var(--radius-sm);
      padding: 3px;
      margin-bottom: 16px;
    }
    .doc-tab {
      flex: 1;
      padding: 7px 12px;
      font-size: 12.5px;
      font-weight: 500;
      border-radius: 6px;
      border: none;
      background: transparent;
      color: var(--text-secondary);
      cursor: pointer;
      transition: all .15s;
      font-family: 'Sarabun', sans-serif;
      text-align: center;
    }
    .doc-tab.active {
      background: var(--card-bg);
      color: var(--navy-600);
      box-shadow: var(--shadow-sm);
      font-weight: 600;
    }

    .doc-item {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      padding: 14px 0;
      border-bottom: 1px solid var(--border-soft);
      cursor: pointer;
      transition: background .1s;
      border-radius: var(--radius-sm);
    }
    .doc-item:last-child { border-bottom: none; padding-bottom: 0; }
    .doc-item:hover { background: var(--surface); }
    .doc-icon {
      width: 36px; height: 36px;
      background: var(--navy-50);
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 17px;
      color: var(--navy-500);
      flex-shrink: 0;
    }
    .doc-icon.urgent { background: #fff1f2; color: #e11d48; }
    .doc-body { flex: 1; min-width: 0; }
    .doc-title {
      font-size: 13.5px;
      font-weight: 500;
      margin-bottom: 3px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .doc-meta {
      font-size: 11.5px;
      color: var(--text-muted);
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }
    .doc-tag {
      display: inline-flex;
      align-items: center;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 500;
    }
    .doc-tag.new    { background: #d0e4f5; color: #255FAE; }
    .doc-tag.urgent { background: #fff1f2; color: #be123c; }
    .doc-tag.done   { background: #dcfce7; color: #15803d; }
    .doc-tag.wait   { background: #fef3c7; color: #b45309; }

    /* ───────────────────────────────────
       SUBORDINATE PANEL
    ─────────────────────────────────── */
    .sub-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 0;
      border-bottom: 1px solid var(--border-soft);
    }
    .sub-item:last-child { border-bottom: none; padding-bottom: 0; }
    .sub-avatar {
      width: 38px; height: 38px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      font-weight: 700;
      color: #fff;
      flex-shrink: 0;
    }
    .sub-info { flex: 1; min-width: 0; }
    .sub-name {
      font-size: 13px;
      font-weight: 500;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .sub-role { font-size: 11.5px; color: var(--text-muted); }
    .sub-status {
      width: 8px; height: 8px;
      border-radius: 50%;
      flex-shrink: 0;
    }
    .sub-status.online { background: #22c55e; }
    .sub-status.away   { background: var(--gold); }
    .sub-status.offline { background: var(--border); }

    /* ───────────────────────────────────
       APPRECIATION WALL
    ─────────────────────────────────── */
    .appreciation-card {
      background: linear-gradient(135deg, #fdf4ff, #fce7f3);
      border: 1px solid #f0abfc;
      border-radius: var(--radius-lg);
      padding: 20px 22px;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .appreciation-icon {
      font-size: 32px;
      flex-shrink: 0;
    }
    .appreciation-body { flex: 1; }
    .appreciation-title {
      font-size: 14px;
      font-weight: 600;
      color: #86198f;
      margin-bottom: 3px;
    }
    .appreciation-desc {
      font-size: 12.5px;
      color: #a21caf;
    }
    .appreciation-cta {
      background: #a21caf;
      color: #fff;
      border: none;
      border-radius: var(--radius-sm);
      padding: 8px 16px;
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      font-family: 'Sarabun', sans-serif;
      white-space: nowrap;
      transition: opacity .15s;
      flex-shrink: 0;
    }
    .appreciation-cta:hover { opacity: .85; }

    /* ───────────────────────────────────
       SIDEBAR PANELS (right column)
    ─────────────────────────────────── */
    .right-col {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    /* Gamification rank card */
    .rank-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 20px;
      box-shadow: var(--shadow-sm);
    }
    .rank-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 14px;
    }
    .rank-title { font-size: 13px; font-weight: 600; color: var(--text-secondary); }
    .rank-badge {
      display: flex;
      align-items: center;
      gap: 5px;
      background: var(--gold-light);
      color: #b45309;
      border-radius: 20px;
      padding: 3px 10px;
      font-size: 11.5px;
      font-weight: 700;
    }
    .rank-badge i { font-size: 13px; color: var(--gold); }
    .rank-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 8px;
    }
    .rank-score { font-size: 13px; color: var(--text-secondary); }
    .rank-score-val { font-size: 22px; font-weight: 700; color: var(--navy-700); }
    .rank-points { font-size: 13px; color: var(--text-secondary); }
    .rank-points-val {
      font-size: 22px;
      font-weight: 700;
      color: #d97706;
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .rank-points-val i { font-size: 18px; color: var(--gold); }

    /* mini calendar */
    .mini-cal {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 18px 20px;
      box-shadow: var(--shadow-sm);
    }
    .mini-cal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 14px;
    }
    .mini-cal-month { font-size: 14px; font-weight: 600; }
    .mini-cal-nav {
      display: flex;
      gap: 4px;
    }
    .mini-cal-nav button {
      width: 26px; height: 26px;
      border: none;
      background: var(--surface);
      border-radius: 6px;
      cursor: pointer;
      font-size: 13px;
      color: var(--text-secondary);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .mini-cal-nav button:hover { background: var(--navy-50); color: var(--navy-600); }
    .mini-cal-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 2px;
      text-align: center;
    }
    .cal-day-header { font-size: 11px; font-weight: 600; color: var(--text-muted); padding: 4px 0; }
    .cal-day {
      font-size: 12.5px;
      padding: 5px 0;
      border-radius: 6px;
      cursor: pointer;
      transition: background .1s;
      color: var(--text-primary);
    }
    .cal-day:hover { background: var(--navy-50); color: var(--navy-600); }
    .cal-day.today { background: var(--navy-600); color: #fff; font-weight: 600; }
    .cal-day.other { color: var(--text-muted); }
    .cal-day.has-event { position: relative; }
    .cal-day.has-event::after {
      content: '';
      display: block;
      width: 4px; height: 4px;
      background: var(--gold);
      border-radius: 50%;
      margin: 1px auto 0;
    }

    /* footer */
    .page-footer {
      text-align: center;
      font-size: 12px;
      color: var(--text-muted);
      padding: 20px 0 0;
      border-top: 1px solid var(--border-soft);
      margin-top: 12px;
    }

    /* ───────────────────────────────────
       HAMBURGER BUTTON
    ─────────────────────────────────── */
    #hamburger-btn {
      display: none;
      width: 36px; height: 36px;
      border-radius: var(--radius-sm);
      border: none;
      background: transparent;
      color: rgba(255,255,255,.8);
      font-size: 20px;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background .15s;
      flex-shrink: 0;
    }
    #hamburger-btn:hover { background: rgba(255,255,255,.12); color: #fff; }

    /* ───────────────────────────────────
       MOBILE DRAWER
    ─────────────────────────────────── */
    #mobile-drawer {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      z-index: 2000;
      display: none;
    }
    #mobile-drawer.open { display: block; }

    #drawer-backdrop {
      position: absolute;
      inset: 0;
      background: rgba(0,0,0,.45);
      backdrop-filter: blur(2px);
    }

    #drawer-panel {
      position: absolute;
      top: 0; left: 0; bottom: 0;
      width: 300px;
      background: var(--card-bg);
      overflow-y: auto;
      box-shadow: var(--shadow-lg);
      transform: translateX(-100%);
      transition: transform .25s ease;
      display: flex;
      flex-direction: column;
    }
    #mobile-drawer.open #drawer-panel { transform: translateX(0); }

    .drawer-header {
      background: #255FAE;
      padding: 18px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-shrink: 0;
    }
    .drawer-brand {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .drawer-logo {
      width: 36px; height: 36px;
      background: rgba(255,255,255,.2);
      border-radius: var(--radius-sm);
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
    }
    .drawer-brand-name { font-size: 14px; font-weight: 700; color: #fff; }
    .drawer-brand-sub  { font-size: 10px; color: rgba(255,255,255,.65); }
    .drawer-close {
      width: 32px; height: 32px;
      background: rgba(255,255,255,.15);
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 18px;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
    }
    .drawer-close:hover { background: rgba(255,255,255,.25); }

    .drawer-user {
      padding: 16px 20px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      gap: 12px;
      background: var(--surface);
    }
    .drawer-avatar {
      width: 42px; height: 42px;
      border-radius: 50%;
      background: linear-gradient(135deg, #255FAE, #2d72cc);
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 14px; color: #fff;
      flex-shrink: 0;
    }
    .drawer-user-name { font-size: 14px; font-weight: 600; }
    .drawer-user-role { font-size: 12px; color: var(--text-muted); }

    .drawer-nav { flex: 1; padding: 8px 0; }

    .drawer-section-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .7px;
      color: var(--text-muted);
      padding: 12px 20px 4px;
    }

    .drawer-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 20px;
      color: var(--text-primary);
      font-size: 14px;
      font-weight: 500;
      text-decoration: none;
      cursor: pointer;
      transition: background .12s;
      border: none;
      background: transparent;
      width: 100%;
      font-family: 'Sarabun', sans-serif;
    }
    .drawer-item:hover { background: var(--navy-50); color: #255FAE; }
    .drawer-item.active { background: var(--navy-50); color: #255FAE; }
    .drawer-item i { font-size: 18px; width: 22px; text-align: center; color: #255FAE; opacity: .8; }
    .drawer-item-caret {
      margin-left: auto;
      font-size: 12px;
      color: var(--text-muted);
      transition: transform .2s;
    }
    .drawer-item.sub-open .drawer-item-caret { transform: rotate(90deg); }

    .drawer-subitems {
      display: none;
      background: var(--surface);
      border-top: 1px solid var(--border-soft);
      border-bottom: 1px solid var(--border-soft);
    }
    .drawer-subitems.open { display: block; }
    .drawer-subitem {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 20px 10px 54px;
      font-size: 13px;
      color: var(--text-secondary);
      text-decoration: none;
      transition: background .1s;
      cursor: pointer;
    }
    .drawer-subitem:hover { background: var(--navy-50); color: #255FAE; }
    .drawer-subitem i { font-size: 14px; color: #255FAE; opacity: .7; }

    /* ───────────────────────────────────
       RESPONSIVE
    ─────────────────────────────────── */
    @media (max-width: 1200px) {
      .dashboard-grid { grid-template-columns: 1fr; }
      .right-col { flex-direction: row; flex-wrap: wrap; }
      .right-col > * { flex: 1; min-width: 260px; }
      .quick-actions-grid { grid-template-columns: repeat(4, 1fr); }
    }

    @media (max-width: 768px) {
      #hamburger-btn { display: flex; }
      #mainnav { display: none; }
      #topbar { padding: 0 14px; }
      .brand-text-main { font-size: 13px; }
      .avatar-name, .avatar-role { display: none; }
      .nav-container { padding: 0 12px; overflow-x: auto; }
      .nav-link { padding: 0 10px; font-size: 12.5px; }
      .page-wrapper { padding: 16px 14px 40px; }
      .hero-card { padding: 22px 20px; }
      .hero-name { font-size: 18px; }
      .hero-stats { gap: 18px; }
      .hero-stat-val { font-size: 20px; }
      .hero-timer { font-size: 24px; }
      .quick-actions-grid { grid-template-columns: repeat(4, 1fr); gap: 8px; }
      .kpi-row { grid-template-columns: 1fr; }
      .right-col { flex-direction: column; }
      .right-col > * { min-width: unset; }
    }

    @media (max-width: 480px) {
      .quick-actions-grid { grid-template-columns: repeat(3, 1fr); }
      .hero-inner { flex-direction: column; align-items: flex-start; gap: 16px; }
      .hero-timer-block { width: 100%; }
      .hero-stats { width: 100%; justify-content: space-around; }
      .nav-link span { display: none; }
      .nav-link i { font-size: 18px; }
    }

    /* ───────────────────────────────────
       TWEAKS PANEL
    ─────────────────────────────────── */
    #tweaks-panel {
      position: fixed;
      bottom: 24px; right: 24px;
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-lg);
      padding: 20px;
      width: 240px;
      z-index: 2000;
      display: none;
      font-size: 13px;
    }
    #tweaks-panel.open { display: block; animation: megaFadeIn .2s ease; }
    .tweak-title {
      font-weight: 700;
      font-size: 14px;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .tweak-title button {
      width: 24px; height: 24px;
      border: none; background: none;
      cursor: pointer;
      font-size: 16px;
      color: var(--text-muted);
      display: flex; align-items: center; justify-content: center;
      border-radius: 4px;
    }
    .tweak-title button:hover { background: var(--surface); }
    .tweak-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 12px;
    }
    .tweak-label { color: var(--text-secondary); }
    .tweak-toggle {
      width: 36px; height: 20px;
      border-radius: 10px;
      background: var(--border);
      border: none;
      cursor: pointer;
      position: relative;
      transition: background .2s;
      flex-shrink: 0;
    }
    .tweak-toggle.on { background: var(--navy-500); }
    .tweak-toggle::after {
      content: '';
      position: absolute;
      top: 2px; left: 2px;
      width: 16px; height: 16px;
      background: #fff;
      border-radius: 50%;
      transition: left .2s;
      box-shadow: 0 1px 3px rgba(0,0,0,.15);
    }
    .tweak-toggle.on::after { left: 18px; }

    .tweak-select {
      font-family: 'Sarabun', sans-serif;
      font-size: 12px;
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 4px 8px;
      background: var(--surface);
      color: var(--text-primary);
      cursor: pointer;
    }
  </style>
<nav id="mainnav">
  <div class="nav-container">

    <!-- Dashboard -->
    <div class="nav-item">
      <a class="nav-link active" href="#">
        <i class="bi bi-grid-1x2-fill"></i>
        <span>Dashboard</span>
      </a>
    </div>

    <!-- บุคลากร -->
    <div class="nav-item">
      <button class="nav-link">
            <i class="bi bi-people-fill"></i>
        <span>งานบุคคล</span>
        <i class="bi bi-chevron-down nav-caret"></i>
      </button>
      <div class="mega-menu wide border-start-3">
        <div class="mega-section-title">ข้อมูลบุคลากร</div>
        <div class="mega-grid">
          <a class="mega-item" href="<?= Url::to(['/hr/employees']) ?>">
            <div class="mega-item-icon"><i class="bi bi-person-lines-fill"></i></div>
            <div><div class="mega-item-label">ทะเบียนบุคลากร</div><div class="mega-item-desc">ข้อมูลส่วนตัว สัญญาจ้าง</div></div>
          </a>
          <a class="mega-item" href="<?= Url::to(['/hr/organization/diagram']) ?>?>">
            <div class="mega-item-icon"><i class="bi bi-diagram-3-fill"></i></div>
            <div><div class="mega-item-label">โครงสร้างองค์กร</div><div class="mega-item-desc">สายบังคับบัญชา แผนก</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-card-list"></i></div>
            <div><div class="mega-item-label">ประวัติส่วนตัว</div><div class="mega-item-desc">ประวัติการศึกษา อบรม</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-star-fill"></i></div>
            <div><div class="mega-item-label">การประเมินผล</div><div class="mega-item-desc">KPI รายปี / รายไตรมาส</div></div>
          </a>
        </div>
      </div>
    </div>

    <!-- ข้อมูลทดสอบ -->
    <div class="nav-item">
      <button class="nav-link">
        <i class="bi bi-file-earmark-text-fill"></i>
        <span>ข้อมูลทดสอบ</span>
        <i class="bi bi-chevron-down nav-caret"></i>
      </button>
      <div class="mega-menu">
        <div class="mega-section-title">ใบรับรองและเอกสาร</div>
        <div class="mega-grid">
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-file-earmark-medical-fill"></i></div>
            <div><div class="mega-item-label">ผลทดสอบสุขภาพ</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-clipboard2-pulse-fill"></i></div>
            <div><div class="mega-item-label">ตรวจสุขภาพประจำปี</div></div>
          </a>
        </div>
      </div>
    </div>

    <!-- ขอรถ -->
    <div class="nav-item">
      <button class="nav-link">
        <i class="bi bi-truck-front-fill"></i>
        <span>ขอรถ</span>
        <i class="bi bi-chevron-down nav-caret"></i>
      </button>
      <div class="mega-menu">
        <div class="mega-section-title">ระบบยานพาหนะ</div>
        <div class="mega-grid">
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-calendar-plus-fill"></i></div>
            <div><div class="mega-item-label">จองรถ</div><div class="mega-item-desc">ขอใช้ยานพาหนะ</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-list-check"></i></div>
            <div><div class="mega-item-label">ประวัติการใช้รถ</div></div>
          </a>
        </div>
      </div>
    </div>

    <!-- คลังพัสดุ -->
    <div class="nav-item">
      <button class="nav-link">
        <i class="bi bi-boxes"></i>
        <span>คลังพัสดุ</span>
        <i class="bi bi-chevron-down nav-caret"></i>
      </button>
      <div class="mega-menu wide">
        <div class="mega-section-title">การจัดการพัสดุ</div>
        <div class="mega-grid">
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-plus-circle-fill"></i></div>
            <div><div class="mega-item-label">เบิกพัสดุ</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-arrow-return-left"></i></div>
            <div><div class="mega-item-label">คืนพัสดุ</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-graph-up"></i></div>
            <div><div class="mega-item-label">รายงานสต็อก</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-truck"></i></div>
            <div><div class="mega-item-label">สั่งซื้อ/จัดซื้อ</div></div>
          </a>
        </div>
      </div>
    </div>

    <!-- งานราชการ -->
    <div class="nav-item">
      <button class="nav-link">
        <i class="bi bi-briefcase-fill"></i>
        <span>งานราชการ</span>
        <i class="bi bi-chevron-down nav-caret"></i>
      </button>
      <div class="mega-menu wide">
        <div class="mega-section-title">หนังสือและเอกสาร</div>
        <div class="mega-grid">
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-envelope-fill"></i></div>
            <div><div class="mega-item-label">หนังสือรับ</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-envelope-paper-fill"></i></div>
            <div><div class="mega-item-label">หนังสือส่ง</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-file-earmark-check-fill"></i></div>
            <div><div class="mega-item-label">คำสั่ง/ประกาศ</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-folder2-open"></i></div>
            <div><div class="mega-item-label">สารบรรณดิจิทัล</div></div>
          </a>
        </div>
      </div>
    </div>

    <!-- เพิ่มเติม -->
    <div class="nav-item">
      <button class="nav-link">
        <i class="bi bi-grid-3x3-gap-fill"></i>
        <span>เพิ่มเติม</span>
        <i class="bi bi-chevron-down nav-caret"></i>
      </button>
      <div class="mega-menu wide">
        <div class="mega-section-title">โมดูลอื่นๆ</div>
        <div class="mega-grid cols-3">
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-calendar3"></i></div>
            <div><div class="mega-item-label">ห้องประชุม</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-shield-fill-check"></i></div>
            <div><div class="mega-item-label">ระบบยา</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-heart-pulse-fill"></i></div>
            <div><div class="mega-item-label">อบรม/สุขภาพ</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-tools"></i></div>
            <div><div class="mega-item-label">ซ่อมบำรุง</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-bar-chart-fill"></i></div>
            <div><div class="mega-item-label">รายงาน</div></div>
          </a>
          <a class="mega-item" href="#">
            <div class="mega-item-icon"><i class="bi bi-gear-wide-connected"></i></div>
            <div><div class="mega-item-label">ตั้งค่าระบบ</div></div>
          </a>
        </div>
      </div>
    </div>

  </div>
</nav>