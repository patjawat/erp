<?php
$this->registerCss(<<<CSS
.tg-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.8rem;margin-bottom:1rem}
.tg-card{position:relative;padding:1rem 1.1rem;background:#fff;border:1px solid #e4e7ec;border-left:5px solid var(--tg-accent,#2457a7);border-radius:.7rem}
.tg-card__label{display:flex;align-items:center;gap:.4rem;color:var(--tg-accent,#2457a7);font-size:.82rem;font-weight:700}
.tg-card__value{display:block;margin:.35rem 0 .1rem;font-size:2rem;line-height:1;font-weight:700;color:#1d2939}
.tg-card__hint{color:#667085;font-size:.8rem}
.tg-layout{display:grid;grid-template-columns:minmax(0,1.9fr) minmax(280px,.75fr);gap:1rem;align-items:start}
.tg-panel{background:#fff;border:1px solid #e4e7ec;border-radius:.8rem;padding:1.1rem}
.tg-panel h2{font-size:1.05rem;margin:0 0 1rem;color:#1d2939;font-weight:700}
.tg-matrix{display:grid;grid-template-columns:52px repeat(3,minmax(0,1fr));gap:.5rem}
.tg-matrix__corner{background:transparent}
.tg-colhead{display:grid;place-items:center;padding:.7rem .4rem;border-radius:.5rem;background:linear-gradient(135deg,#0f766e,#12a594);color:#fff;font-size:.84rem;font-weight:700;line-height:1.4;text-align:center}
.tg-rowhead{display:grid;place-items:center;border-radius:.5rem;background:#2f6fed;color:#fff;font-size:.82rem;font-weight:700}
.tg-rowhead span{writing-mode:vertical-rl;transform:rotate(180deg);text-align:center;line-height:1.2}
.tg-cell{position:relative;display:flex;flex-direction:column;min-height:150px;padding:.6rem .65rem 1.7rem;border:1px solid var(--tg-border);border-radius:.5rem;background:var(--tg-bg)}
.tg-cell--risk{--tg-bg:#fee4e2;--tg-border:#fca5a5;--tg-ink:#b42318}
.tg-cell--watch{--tg-bg:#fef7c3;--tg-border:#fde272;--tg-ink:#a15c07}
.tg-cell--solid{--tg-bg:#dcfce7;--tg-border:#86efac;--tg-ink:#15803d}
.tg-cell--star{--tg-bg:#bbf7d0;--tg-border:#4ade80;--tg-ink:#14532d}
.tg-cell__head{display:flex;align-items:flex-start;justify-content:space-between;gap:.4rem;margin-bottom:.45rem}
.tg-cell__title{color:var(--tg-ink);font-size:.82rem;font-weight:700;line-height:1.25}
.tg-cell__title small{display:block;margin-top:.1rem;color:#475467;font-size:.72rem;font-weight:500}
.tg-cell__add{flex:0 0 auto;display:grid;place-items:center;width:22px;height:22px;border-radius:50%;border:1px solid var(--tg-border);background:rgba(255,255,255,.75);color:var(--tg-ink);font-size:.85rem;line-height:1;text-decoration:none}
.tg-cell__add:hover{background:#fff;color:var(--tg-ink)}
.tg-people{display:flex;flex-direction:column;gap:.25rem;max-height:180px;overflow-y:auto;scrollbar-width:thin}
.tg-person{display:block;padding:.28rem .5rem;border:1px solid rgba(255,255,255,.9);border-radius:.35rem;background:rgba(255,255,255,.72);color:#1d2939;font-size:.79rem;text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tg-person:hover{background:#fff;color:#0b4a9c;border-color:var(--tg-border)}
.tg-cell__count{position:absolute;right:.6rem;bottom:.5rem;color:var(--tg-ink);font-size:.76rem;font-weight:700}
.tg-cell__empty{margin:auto 0;color:#98a2b3;font-size:.78rem;text-align:center}
.tg-legend{display:flex;flex-wrap:wrap;gap:1rem;margin-top:1rem;padding-top:.9rem;border-top:1px solid #eef1f5}
.tg-legend span{display:inline-flex;align-items:center;gap:.4rem;color:#475467;font-size:.8rem}
.tg-legend i{width:12px;height:12px;border-radius:50%;display:inline-block}
.tg-side{display:flex;flex-direction:column;gap:1rem}
.tg-toolbar{display:flex;flex-wrap:wrap;align-items:flex-end;gap:.6rem;margin-bottom:1rem;padding:.85rem 1rem;background:#f7f9fc;border:1px solid #e4e7ec;border-radius:.7rem}
.tg-toolbar label{display:block;margin-bottom:.15rem;color:#667085;font-size:.76rem;font-weight:600}
.tg-toolbar .form-select{min-width:170px}
.tg-unplaced{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;margin-bottom:1rem;padding:.7rem .95rem;border:1px dashed #fbbf24;border-radius:.6rem;background:#fffbeb;color:#92400e;font-size:.85rem}
@media(max-width:1199.98px){.tg-layout{grid-template-columns:1fr}.tg-cards{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:767.98px){.tg-matrix{grid-template-columns:40px repeat(3,minmax(0,1fr));gap:.35rem}.tg-colhead{font-size:.68rem;padding:.5rem .2rem}.tg-cell{min-height:130px;padding:.45rem .45rem 1.6rem}.tg-cards{grid-template-columns:1fr}}
CSS);
