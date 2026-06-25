<?php

/** @var yii\web\View $this */
/** @var string $theme  emerald | indigo | amber  (kept for back-compat) */

$css = <<<CSS
/* Local Enterprise tokens (mirror inventoryV2/sub-stock/issue.php standard) */
.list-setting-card {
    --ls-ink-1: #1a202c;
    --ls-ink-2: #4a5568;
    --ls-ink-3: #718096;
    --ls-ink-4: #a0aec0;
    --ls-surface: #ffffff;
    --ls-surface-2: #f7f9fc;
    --ls-surface-3: #eef2f7;
    --ls-surface-hover: #f1f5f9;
    --ls-line: rgba(15, 23, 42, 0.08);
    --ls-line-strong: rgba(15, 23, 42, 0.14);
    --ls-primary: #0d6efd;
    --ls-primary-ink: #0a58ca;
    --ls-primary-soft: rgba(13, 110, 253, 0.08);
    --ls-primary-line: rgba(13, 110, 253, 0.22);
    --ls-success: #15803d;
    --ls-success-soft: rgba(21, 128, 61, 0.08);
    --ls-success-line: rgba(21, 128, 61, 0.22);
    --ls-warning: #b45309;
    --ls-warning-soft: rgba(180, 83, 9, 0.08);
    --ls-warning-line: rgba(180, 83, 9, 0.22);
    --ls-danger: #b91c1c;
    --ls-danger-soft: rgba(185, 28, 28, 0.08);
    --ls-danger-line: rgba(185, 28, 28, 0.22);

    --ls-radius: 10px;
    --ls-radius-sm: 8px;
    --ls-radius-xs: 6px;
    --ls-shadow-1: 0 1px 2px rgba(15,23,42,.04), 0 1px 1px rgba(15,23,42,.03);

    --ls-ease: cubic-bezier(0.16, 1, 0.3, 1);
    --ls-t-fast: 120ms var(--ls-ease);
    --ls-t-mid: 180ms var(--ls-ease);

    border-radius: var(--ls-radius);
    border: 1px solid var(--ls-line) !important;
    box-shadow: var(--ls-shadow-1) !important;
    background: var(--ls-surface);
    color: var(--ls-ink-1);
}

/* Flat enterprise header (replace gradient) */
.list-setting-header,
.list-setting-header--emerald,
.list-setting-header--indigo,
.list-setting-header--amber {
    background: linear-gradient(180deg, #fafbfd 0%, #ffffff 100%) !important;
    border-bottom: 1px solid var(--ls-line);
    color: var(--ls-ink-1);
    padding: 0.85rem 1.1rem !important;
}
.list-setting-header.text-white { color: var(--ls-ink-1) !important; }
.list-setting-header .text-white,
.list-setting-header h5.text-white,
.list-setting-header .fw-semibold { color: var(--ls-ink-1) !important; font-weight: 600 !important; }
.list-setting-header h5 { font-size: 0.98rem; letter-spacing: -0.005em; }
.list-setting-header .text-white-50 { color: var(--ls-ink-3) !important; }
.list-setting-header small { font-size: 0.78rem; }

/* Icon box: theme-colored tint */
.list-setting-icon {
    width: 2.1rem; height: 2.1rem;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid var(--ls-line);
    background: var(--ls-surface-3);
    color: var(--ls-ink-2);
    border-radius: var(--ls-radius-sm);
}
.list-setting-header--emerald .list-setting-icon {
    color: var(--ls-success); background: var(--ls-success-soft); border-color: var(--ls-success-line);
}
.list-setting-header--indigo  .list-setting-icon {
    color: var(--ls-primary-ink); background: var(--ls-primary-soft); border-color: var(--ls-primary-line);
}
.list-setting-header--amber   .list-setting-icon {
    color: var(--ls-warning); background: var(--ls-warning-soft); border-color: var(--ls-warning-line);
}
.list-setting-icon i { width: 1.05rem !important; height: 1.05rem !important; }

/* Primary CTA in header (override btn-light from index pages) */
.list-setting-header .btn-light {
    background: var(--ls-primary) !important;
    border-color: var(--ls-primary) !important;
    color: #ffffff !important;
    font-weight: 500;
    transition: background-color var(--ls-t-fast), border-color var(--ls-t-fast);
}
.list-setting-header .btn-light:hover,
.list-setting-header .btn-light:active,
.list-setting-header .btn-light:focus {
    background: var(--ls-primary-ink) !important;
    border-color: var(--ls-primary-ink) !important;
    color: #ffffff !important;
}
.list-setting-header .btn-light:focus-visible {
    box-shadow: 0 0 0 3px var(--ls-primary-soft);
    outline: none;
}

/* Table */
.list-setting-table thead th {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ls-ink-2);
    background: var(--ls-surface-2);
    border-bottom: 1px solid var(--ls-line);
    text-transform: none;
    letter-spacing: 0;
    padding-top: 0.7rem; padding-bottom: 0.7rem;
}
.list-setting-table tbody td {
    vertical-align: middle;
    color: var(--ls-ink-1);
    border-color: var(--ls-line);
}
.list-setting-table tbody tr.list-setting-row {
    opacity: 0;
    transform: translateY(3px);
    animation: listSettingRowIn 180ms var(--ls-ease) forwards;
    animation-delay: calc(var(--i, 0) * 24ms);
    transition: background-color var(--ls-t-fast);
}
.list-setting-table tbody tr.list-setting-row:hover {
    background-color: var(--ls-surface-hover);
}
@keyframes listSettingRowIn { to { opacity: 1; transform: none; } }

/* Deep semantic badges (Enterprise palette) */
.bg-emerald-soft { background-color: var(--ls-success-soft) !important; }
.text-emerald    { color: var(--ls-success) !important; }
.bg-indigo-soft  { background-color: var(--ls-primary-soft) !important; }
.text-indigo     { color: var(--ls-primary-ink) !important; }
.bg-amber-soft   { background-color: var(--ls-warning-soft) !important; }
.text-amber      { color: var(--ls-warning) !important; }

.list-setting-table .badge {
    font-weight: 600;
    padding: 0.22rem 0.55rem;
    font-size: 0.75rem;
    border: 1px solid transparent;
    letter-spacing: 0;
}
.list-setting-table .bg-emerald-soft { border-color: var(--ls-success-line); }
.list-setting-table .bg-indigo-soft  { border-color: var(--ls-primary-line); }
.list-setting-table .bg-amber-soft   { border-color: var(--ls-warning-line); }
.list-setting-table .bg-secondary-subtle { border-color: var(--ls-line); }

.status-dot {
    display: inline-block;
    width: 0.4rem; height: 0.4rem;
    border-radius: 50%;
    vertical-align: middle;
    box-shadow: none;
}
.status-dot--emerald { background: var(--ls-success); }
.status-dot--indigo  { background: var(--ls-primary-ink); }
.status-dot--amber   { background: var(--ls-warning); }
.status-dot--muted   { background: var(--ls-ink-4); }

/* Action buttons in table */
.list-setting-table .btn-group-sm > .btn {
    border-color: var(--ls-line);
    color: var(--ls-ink-2);
    background: var(--ls-surface);
    transition: background-color var(--ls-t-fast), border-color var(--ls-t-fast), color var(--ls-t-fast);
    padding: 0.28rem 0.48rem;
}
.list-setting-table .btn-group-sm > .btn:hover {
    background: var(--ls-surface-hover);
    border-color: var(--ls-line-strong);
    color: var(--ls-ink-1);
}
.list-setting-table .btn-outline-primary { color: var(--ls-primary); }
.list-setting-table .btn-outline-primary:hover {
    background: var(--ls-primary-soft); border-color: var(--ls-primary-line); color: var(--ls-primary-ink);
}
.list-setting-table .btn-outline-danger { color: var(--ls-danger); }
.list-setting-table .btn-outline-danger:hover {
    background: var(--ls-danger-soft); border-color: var(--ls-danger-line); color: var(--ls-danger);
}
.list-setting-table .btn-group-sm > .btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px var(--ls-primary-soft);
    border-color: var(--ls-primary-line);
}

/* Empty state */
.list-setting-empty {
    padding: 3rem 1.5rem;
    text-align: center;
}
.list-setting-empty__icon {
    width: 3rem; height: 3rem;
    margin: 0 auto 0.9rem;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid var(--ls-line);
    background: var(--ls-surface-3);
    color: var(--ls-ink-3);
    border-radius: var(--ls-radius);
}
.list-setting-empty__icon i { width: 1.5rem; height: 1.5rem; }
.list-setting-empty__icon--emerald { background: var(--ls-success-soft); color: var(--ls-success); border-color: var(--ls-success-line); }
.list-setting-empty__icon--indigo  { background: var(--ls-primary-soft); color: var(--ls-primary-ink); border-color: var(--ls-primary-line); }
.list-setting-empty__icon--amber   { background: var(--ls-warning-soft); color: var(--ls-warning); border-color: var(--ls-warning-line); }

.list-setting-empty h6 {
    color: var(--ls-ink-1);
    font-weight: 600;
    font-size: 0.95rem;
    margin: 0 0 0.25rem;
}
.list-setting-empty p {
    color: var(--ls-ink-3);
    font-size: 0.85rem;
    max-width: 32rem;
    margin: 0 auto 1rem;
}

/* Form */
.list-setting-form .form-label,
.list-setting-form .control-label {
    font-weight: 500; color: var(--ls-ink-2); font-size: 0.86rem; margin-bottom: 0.25rem;
}
.list-setting-form .form-control,
.list-setting-form .form-select {
    border-color: var(--ls-line-strong);
    color: var(--ls-ink-1);
    transition: border-color var(--ls-t-fast), box-shadow var(--ls-t-fast);
}
.list-setting-form .form-control:focus,
.list-setting-form .form-select:focus {
    border-color: var(--ls-primary-line);
    box-shadow: 0 0 0 3px var(--ls-primary-soft);
}
.list-setting-form .form-control::placeholder { color: var(--ls-ink-4); }

/* Pagination */
.list-setting-card .pagination .page-link {
    color: var(--ls-ink-2);
    border-color: var(--ls-line);
}
.list-setting-card .pagination .page-item.active .page-link {
    background: var(--ls-primary);
    border-color: var(--ls-primary);
    color: #ffffff;
}
.list-setting-card .pagination .page-link:focus {
    box-shadow: 0 0 0 3px var(--ls-primary-soft);
}

@media (prefers-reduced-motion: reduce) {
    .list-setting-table tbody tr.list-setting-row {
        opacity: 1; transform: none; animation: none;
    }
    .list-setting-header .btn-light,
    .list-setting-table .btn-group-sm > .btn,
    .list-setting-form .form-control,
    .list-setting-form .form-select { transition: none; }
}
CSS;

$this->registerCss($css, [], 'list-setting-styles');
