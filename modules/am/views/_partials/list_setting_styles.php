<?php

/** @var yii\web\View $this */
/** @var string $theme  emerald | indigo | amber */

$css = <<<CSS
.list-setting-card {
    border-radius: 0.75rem;
}
.list-setting-header {
    border-bottom: 1px solid rgba(255,255,255,.18);
}
.list-setting-header--emerald { background: linear-gradient(135deg, #059669 0%, #047857 100%); }
.list-setting-header--indigo  { background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); }
.list-setting-header--amber   { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); }
.list-setting-icon {
    width: 2.5rem;
    height: 2.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,.16);
    border-radius: 0.6rem;
    color: #fff;
}

.list-setting-table thead th {
    font-size: 0.78rem;
    font-weight: 600;
    color: #4b5563;
    letter-spacing: 0.01em;
    border-bottom: 1px solid #e5e7eb;
    background: #f8fafc;
}
.list-setting-table tbody td { vertical-align: middle; }
.list-setting-table tbody tr.list-setting-row {
    opacity: 0;
    transform: translateY(4px);
    animation: listSettingRowIn .35s cubic-bezier(.22,.61,.36,1) forwards;
    animation-delay: calc(var(--i, 0) * 24ms);
}
.list-setting-table tbody tr.list-setting-row:hover {
    background-color: rgba(15,23,42,.025);
}

@keyframes listSettingRowIn {
    to { opacity: 1; transform: none; }
}

/* Soft theme badges */
.bg-emerald-soft { background-color: rgba(16,185,129,.12) !important; }
.text-emerald    { color: #047857 !important; }
.bg-indigo-soft  { background-color: rgba(79,70,229,.12) !important; }
.text-indigo     { color: #4338ca !important; }
.bg-amber-soft   { background-color: rgba(217,119,6,.14) !important; }
.text-amber      { color: #b45309 !important; }

.status-dot {
    display: inline-block;
    width: 0.45rem;
    height: 0.45rem;
    border-radius: 50%;
    vertical-align: middle;
    box-shadow: 0 0 0 2px rgba(255,255,255,.6);
}
.status-dot--emerald { background: #10b981; }
.status-dot--indigo  { background: #4f46e5; }
.status-dot--amber   { background: #d97706; }
.status-dot--muted   { background: #94a3b8; }

.list-setting-empty {
    padding: 3.5rem 1.5rem;
    text-align: center;
}
.list-setting-empty__icon {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 1rem;
}
.list-setting-empty__icon--emerald { background: rgba(16,185,129,.10); color: #047857; }
.list-setting-empty__icon--indigo  { background: rgba(79,70,229,.10);  color: #4338ca; }
.list-setting-empty__icon--amber   { background: rgba(217,119,6,.12);  color: #b45309; }

.list-setting-form .form-label { font-weight: 500; color: #334155; }
.list-setting-form .form-control,
.list-setting-form .form-select { border-color: #e2e8f0; }
.list-setting-form .form-control:focus,
.list-setting-form .form-select:focus {
    box-shadow: 0 0 0 0.2rem rgba(15,23,42,.08);
}

@media (prefers-reduced-motion: reduce) {
    .list-setting-table tbody tr.list-setting-row {
        opacity: 1; transform: none; animation: none;
    }
}
CSS;

$this->registerCss($css, [], 'list-setting-styles');
