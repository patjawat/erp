<?php
$this->registerCss(<<<CSS
.cp-toolbar{display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;padding:.85rem 1rem;background:#fff;border:1px solid #e4e7ec;border-radius:.8rem}
.cp-toolbar label{display:block;margin-bottom:.2rem;color:#667085;font-size:.82rem}
.cp-toolbar .form-select{min-width:150px}
.cp-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.8rem;margin:1rem 0}
.cp-card{padding:1rem;background:#fff;border:1px solid #e4e7ec;border-left:4px solid var(--cp-accent,#2457a7);border-radius:.8rem}
.cp-card__label{display:flex;align-items:center;gap:.4rem;color:#667085;font-size:.86rem}
.cp-card__label i{color:var(--cp-accent,#2457a7)}
.cp-card__value{display:block;margin:.3rem 0;font-size:1.65rem;line-height:1.1;color:#1d2939;font-weight:700}
.cp-card__hint{color:#667085;font-size:.78rem}
.cp-panel{background:#fff;border:1px solid #e4e7ec;border-radius:.8rem;overflow:hidden}
.cp-table thead th{background:#f8fafc;color:#475467;font-size:.84rem;font-weight:600;border-bottom:1px solid #e4e7ec}
.cp-table td{border-bottom:1px solid #f2f4f7;vertical-align:top;padding-top:.85rem;padding-bottom:.85rem}
.cp-table tbody tr:last-child td{border-bottom:0}
.cp-order{color:#2457a7;font-weight:700;white-space:nowrap}
.cp-name{color:#1d2939;font-weight:600}
.cp-def{margin-top:.2rem;color:#667085;font-size:.84rem;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.cp-note{margin-top:.35rem;color:#b54708;font-size:.8rem}
.cp-count{display:inline-block;padding:.15rem .55rem;background:#f2f4f7;border-radius:1rem;color:#475467;font-size:.82rem;white-space:nowrap}
.cp-count--empty{background:#fef3f2;color:#b42318}
.cp-badge{display:inline-block;padding:.15rem .6rem;border-radius:1rem;font-size:.8rem;font-weight:600}
.cp-badge--active{background:#ecfdf3;color:#027a48}
.cp-badge--draft{background:#fffaeb;color:#b54708}
.cp-badge--retired{background:#f2f4f7;color:#667085}
.cp-alert{display:flex;align-items:flex-start;gap:.6rem;padding:.85rem 1rem;background:#fffaeb;border:1px solid #fedf89;border-radius:.7rem;color:#b54708}
.cp-alert .alert-link{color:#93370d;font-weight:600}
.cp-levels{display:flex;flex-wrap:wrap;gap:.25rem;margin-top:.35rem}
.cp-chip{padding:.05rem .45rem;background:#eff6ff;color:#2457a7;border-radius:.35rem;font-size:.74rem;white-space:nowrap}
.cp-chip--empty{background:#f2f4f7;color:#98a2b3}
.cp-meta{margin-top:.25rem;color:#98a2b3;font-size:.78rem}
.cp-peek{margin-left:.35rem;color:#98a2b3;text-decoration:none}
.cp-peek:hover{color:#2457a7}
.cp-bulk{padding:1rem;background:#f8fafc;border:1px solid #d8dee8;border-radius:.8rem}
.cp-bulk__head strong{display:block;color:#1d2939;font-size:.95rem}
.cp-bulk__head span{color:#667085;font-size:.84rem;line-height:1.55}
.cp-bulk__controls{display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;margin-top:.85rem}
.cp-bulk__controls label{display:block;margin-bottom:.2rem;color:#475467;font-size:.82rem;font-weight:600}
.cp-bulk__controls .form-select{min-width:190px}
.cp-score{color:#1d2939;font-size:1.05rem;font-weight:700;line-height:1.2;font-variant-numeric:tabular-nums}
.cp-overview__head{display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:.5rem;padding:.85rem 1rem;border-bottom:1px solid #e4e7ec}
.cp-overview__head h2{margin:0;font-size:1rem;color:#1d2939}
.cp-overview__head span{color:#667085;font-size:.84rem}
.cp-bar{height:8px;background:#eef1f5;border-radius:1rem;overflow:hidden}
.cp-bar span{display:block;height:100%;background:#2457a7;border-radius:1rem}
.cp-badge--ready{background:#ecfdf3;color:#027a48}
.cp-badge--todo{background:#fef3f2;color:#b42318}
.cp-round{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.9rem}
.cp-round__tabs{display:flex;flex-wrap:wrap;gap:.5rem}
.cp-round__tab{display:flex;flex-direction:column;gap:.1rem;min-width:190px;padding:.55rem .85rem;background:#fff;border:1px solid #d8dee8;border-radius:.7rem;color:#475467;text-decoration:none}
.cp-round__tab strong{color:#1d2939;font-size:.95rem}
.cp-round__tab small{color:#667085;font-size:.76rem}
.cp-round__tab:hover{border-color:#2457a7;color:#2457a7}
.cp-round__tab.is-active{border-color:#2457a7;background:#eff6ff;box-shadow:0 0 0 1px #2457a7}
.cp-round__tab--add{align-items:center;justify-content:center;min-width:130px;border-style:dashed;color:#2457a7}
.cp-round__chip{align-self:flex-start;margin-top:.2rem;padding:.05rem .5rem;border-radius:1rem;font-size:.72rem;font-weight:600}
.cp-round__chip--draft{background:#fffaeb;color:#b54708}
.cp-round__chip--open{background:#ecfdf3;color:#027a48}
.cp-round__chip--closed{background:#f2f4f7;color:#667085}
.cp-round__actions{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem}
.cp-bulkbar{display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;margin:0 0 .75rem;padding:.75rem 1rem;background:#f8fafc;border:1px dashed #d0d5dd;border-radius:.7rem;opacity:.6}
.cp-bulkbar.is-active{background:#eff6ff;border-style:solid;border-color:#2457a7;opacity:1}
.cp-bulkbar label{display:block;margin-bottom:.2rem;color:#475467;font-size:.8rem;font-weight:600}
.cp-bulkbar__count{align-self:center;color:#475467;font-size:.86rem;white-space:nowrap}
.cp-bulkbar__count strong{color:#2457a7;font-size:1.1rem}
.cp-bulkbar__grow{flex:1 1 240px;min-width:220px}
.cp-bulkbar__actions{display:flex;gap:.5rem;flex-wrap:wrap}
.cp-assign-table td{vertical-align:top}
.cp-assign-table .cp-name{font-size:.9rem}
.cp-origin{display:inline-block;padding:.1rem .5rem;border-radius:1rem;font-size:.76rem;font-weight:600}
.cp-origin--bulk{background:#eff6ff;color:#2457a7}
.cp-origin--manual{background:#fffaeb;color:#b54708}
.cp-origin--off{background:#f2f4f7;color:#98a2b3}
.cp-row--off{background:#fcfcfd}
.cp-row--off .cp-name,.cp-row--off .cp-def,.cp-row--off .cp-order{opacity:.55}
.cp-table--expect .cp-def{-webkit-line-clamp:1}
.cp-empty{padding:2.5rem 1rem;text-align:center;color:#667085}
.cp-empty i{font-size:2rem;color:#d0d5dd}
.cp-empty p{margin:.6rem 0 0}
.cp-hint{margin:.9rem 0 0;color:#667085;font-size:.84rem}
.cp-level{margin-bottom:1rem;border:1px solid #e4e7ec;border-radius:.7rem;overflow:hidden}
.cp-level__head{display:flex;align-items:flex-start;gap:.6rem;padding:.7rem .9rem;background:#f8fafc;border-bottom:1px solid #e4e7ec}
.cp-level__no{flex:0 0 auto;padding:.1rem .6rem;background:#2457a7;color:#fff;border-radius:1rem;font-size:.8rem;font-weight:700;white-space:nowrap}
.cp-level__desc{color:#344054;font-size:.88rem;line-height:1.55}
.cp-level table{width:100%;margin:0}
.cp-level td{padding:.55rem .9rem;border-bottom:1px solid #f2f4f7;font-size:.88rem;line-height:1.55;vertical-align:top}
.cp-level tr:last-child td{border-bottom:0}
.cp-ind__no{width:56px;color:#667085;white-space:nowrap}
.cp-ind__scale{width:230px}
.cp-scale{display:inline-block;padding:.1rem .5rem;background:#eef2ff;color:#4338ca;border-radius:.4rem;font-size:.76rem}
.cp-scale--default{background:#f2f4f7;color:#667085}
.cp-scale__opts{margin-top:.25rem;color:#98a2b3;font-size:.72rem;line-height:1.45}
@media (max-width:1100px){.cp-cards{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width:640px){.cp-cards{grid-template-columns:1fr}.cp-toolbar .ms-auto{width:100%}}
CSS);
