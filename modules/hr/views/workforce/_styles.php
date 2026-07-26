<?php
$this->registerCss(<<<CSS
.workforce-shell{--wf-primary:#2457a7;--wf-ink:#1d2939;--wf-muted:#667085;max-width:1440px;margin:0 auto}
.workforce-nav{--wf-primary:#2457a7;display:flex;width:max-content;max-width:100%;gap:.35rem;overflow-x:auto;margin-bottom:1rem;padding:.35rem;background:#f3f6fa;border:1px solid #d8dee8;border-radius:.8rem;scrollbar-width:thin}
.workforce-nav__item{--wf-item:#475467;--wf-item-soft:#f2f4f7;display:inline-flex;flex:0 0 124px;align-items:center;justify-content:center;gap:.45rem;min-width:124px;min-height:42px;padding:.5rem .7rem;border:1px solid var(--wf-item);border-radius:.55rem;background:#fff;color:var(--wf-item);text-decoration:none;font-weight:600;white-space:nowrap}
.workforce-nav__item svg{width:17px;height:17px}
.workforce-nav__item--overview{--wf-item:#475467;--wf-item-soft:#f2f4f7}
.workforce-nav__item--jd{--wf-item:#4338ca;--wf-item-soft:#eef2ff}
.workforce-nav__item--idp{--wf-item:#2457a7;--wf-item-soft:#eff6ff}
.workforce-nav__item--trm{--wf-item:#0f766e;--wf-item-soft:#f0fdfa}
.workforce-nav__item--appraisal{--wf-item:#9a6700;--wf-item-soft:#fffbeb}
.workforce-nav__item--health{--wf-item:#be123c;--wf-item-soft:#fff1f2}
.workforce-nav__item--exit{--wf-item:#7e22ce;--wf-item-soft:#faf5ff}
.workforce-nav__item:hover{color:var(--wf-item);border-color:var(--wf-item);background:var(--wf-item-soft)}
.workforce-nav__item:focus-visible{outline:3px solid rgba(36,87,167,.25);outline-offset:1px}
.workforce-nav__item.is-active{color:var(--wf-item);border-color:var(--wf-item);background:var(--wf-item-soft);font-weight:700;box-shadow:0 0 0 1px var(--wf-item)}
.workforce-nav__item.is-active:hover{color:var(--wf-item);background:var(--wf-item-soft)}
.workforce-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin:1.25rem 0}
.workforce-head h1{font-size:1.45rem;margin:0 0 .25rem;color:var(--wf-ink)}
.workforce-head p{margin:0;color:var(--wf-muted)}
.workforce-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.8rem}
.workforce-metric{padding:1rem;background:#fff;border:1px solid #e4e7ec;border-radius:.8rem}
.workforce-metric__label{color:var(--wf-muted);font-size:.86rem}
.workforce-metric__value{display:block;margin:.3rem 0;font-size:1.65rem;line-height:1.1;color:var(--wf-ink);font-weight:700}
.workforce-metric__hint{color:var(--wf-muted);font-size:.8rem}
.workforce-grid{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(260px,.8fr);gap:1rem;margin-top:1rem}
.workforce-panel{background:#fff;border:1px solid #e4e7ec;border-radius:.8rem;padding:1rem}
.workforce-panel h2{font-size:1rem;margin:0 0 .75rem;color:var(--wf-ink)}
.workforce-link{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.8rem 0;border-bottom:1px solid #eef1f5;color:var(--wf-ink);text-decoration:none}
.workforce-link:last-child{border-bottom:0}
.workforce-link:hover{color:var(--wf-primary)}
.workforce-link small{display:block;color:var(--wf-muted);margin-top:.15rem}
.workforce-empty{padding:3rem 1rem;text-align:center;background:#fff;border:1px solid #e4e7ec;border-radius:.8rem}
.workforce-empty__icon{display:inline-grid;place-items:center;width:52px;height:52px;margin-bottom:.8rem;border-radius:50%;background:#eef4ff;color:var(--wf-primary)}
.workforce-empty__icon svg{width:24px;height:24px}
.workforce-empty h2{font-size:1.15rem;color:var(--wf-ink)}
.workforce-empty p{max-width:580px;margin:.4rem auto 1rem;color:var(--wf-muted)}
.jd-registry{overflow:hidden;background:#fff;border:1px solid #e4e7ec;border-radius:.8rem}.jd-registry__head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.1rem;border-bottom:1px solid #e4e7ec}.jd-registry__head h2{font-size:1rem;margin:0 0 .2rem;color:var(--wf-ink)}.jd-registry__head p{margin:0;color:var(--wf-muted);font-size:.84rem}.jd-registry__search{display:flex;align-items:center;gap:.5rem;padding:.8rem 1.1rem;background:#f7f9fc;border-bottom:1px solid #e4e7ec}.jd-registry__search-control{position:relative;flex:1;max-width:480px}.jd-registry__search-control svg{position:absolute;left:.75rem;top:50%;width:17px;height:17px;color:#667085;transform:translateY(-50%);pointer-events:none}.jd-registry__search-control .form-control{min-height:42px;padding-left:2.35rem;border-color:#d0d5dd;border-radius:.5rem}.jd-registry__search-control .form-control:focus{border-color:#0d6efd;box-shadow:0 0 0 3px rgba(13,110,253,.08)}.jd-registry__table{width:100%;border-collapse:collapse;font-size:.86rem}.jd-registry__table th{padding:.65rem .85rem;background:#f7f9fc;border-bottom:1px solid #e4e7ec;color:#4a5568;font-size:.78rem;font-weight:600;text-align:left}.jd-registry__table td{padding:.7rem .85rem;border-bottom:1px solid rgba(15,23,42,.08);color:#1a202c;vertical-align:middle}.jd-registry__table tbody tr:hover{background:#f1f5f9}.jd-registry__table strong,.jd-registry__table small{display:block}.jd-registry__table small{margin-top:.15rem;color:#718096}.jd-status,.jd-sign-state{display:inline-flex;align-items:center;width:max-content;border-radius:999px;padding:.24rem .55rem;font-size:.75rem;font-weight:600;white-space:nowrap}.jd-status.is-success,.jd-sign-state.is-done{background:rgba(21,128,61,.1);color:#15803d}.jd-status.is-warning,.jd-sign-state.is-waiting{background:rgba(180,83,9,.1);color:#92400e}.jd-status.is-danger{background:rgba(185,28,28,.1);color:#b91c1c}.jd-status.is-neutral,.jd-sign-state.is-muted{background:#eef2f7;color:#4a5568}.jd-registry__mobile{list-style:none;margin:0;padding:0}.jd-registry__mobile li{padding:1rem;border-bottom:1px solid #e4e7ec}.jd-registry__mobile strong,.jd-registry__mobile small{display:block}.jd-registry__mobile small{margin-top:.15rem;color:#718096}.jd-registry__mobile dl{margin:.8rem 0}.jd-registry__mobile dl div{display:grid;grid-template-columns:90px 1fr;gap:.5rem;padding:.25rem 0}.jd-registry__mobile dt{color:#667085;font-size:.78rem;font-weight:600}.jd-registry__mobile dd{margin:0;color:#1d2939;font-size:.84rem}.jd-registry__footer{padding:.8rem 1rem;background:#fff}.jd-registry__empty{display:flex;flex-direction:column;align-items:center;padding:3rem 1rem;text-align:center}.jd-registry__empty span{margin-top:.3rem;color:#667085}
@media(max-width:991.98px){.workforce-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.workforce-grid{grid-template-columns:1fr}}
@media(max-width:767.98px){.workforce-nav__item{flex-basis:112px;min-width:112px}}
@media(max-width:575.98px){.workforce-nav{margin-left:-.25rem;margin-right:-.25rem}.workforce-nav__item{padding:.48rem .7rem}.workforce-metrics{grid-template-columns:1fr 1fr;gap:.55rem}.workforce-metric{padding:.8rem}.workforce-metric__value{font-size:1.35rem}.jd-registry__head{align-items:flex-start;flex-direction:column}.jd-registry__search{align-items:stretch;flex-wrap:wrap}.jd-registry__search-control{flex-basis:100%;max-width:none}.jd-registry__search .btn{min-height:42px;flex:1}}
CSS);
