<?php
$this->registerCss(<<<CSS
.ev-shell{--ev-primary:#2457a7;--ev-ink:#1d2939;--ev-muted:#667085;max-width:1080px;margin:0 auto}
.ev-head{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:1rem;margin:1rem 0 1.25rem}
.ev-head h1{font-size:1.4rem;margin:0 0 .25rem;color:var(--ev-ink)}
.ev-head p{margin:0;color:var(--ev-muted);font-size:.9rem}
.ev-empty{padding:3rem 1rem;text-align:center;color:var(--ev-muted);background:#fff;border:1px solid #e4e7ec;border-radius:.9rem}
.ev-empty i{font-size:2.4rem;color:#d0d5dd}
.ev-empty h2{margin:.75rem 0 .35rem;font-size:1.1rem;color:var(--ev-ink)}
.ev-empty p{margin:0;font-size:.9rem}
.ev-progress{display:flex;flex-wrap:wrap;align-items:center;gap:.9rem;padding:1rem;margin-bottom:1rem;background:#fff;border:1px solid #e4e7ec;border-radius:.9rem}
.ev-progress__bar{flex:1 1 240px;height:10px;background:#eef1f5;border-radius:1rem;overflow:hidden}
.ev-progress__bar span{display:block;height:100%;background:var(--ev-primary);border-radius:1rem}
.ev-progress__meta{color:var(--ev-muted);font-size:.88rem;white-space:nowrap}
.ev-progress__meta strong{color:var(--ev-ink);font-size:1.05rem}
.ev-group{margin-bottom:1rem;background:#fff;border:1px solid #e4e7ec;border-radius:.9rem;overflow:hidden}
.ev-group h2{display:flex;align-items:center;gap:.5rem;margin:0;padding:.75rem 1rem;font-size:.95rem;color:var(--ev-ink);background:#f8fafc;border-bottom:1px solid #e4e7ec}
.ev-group h2 span{margin-left:auto;color:var(--ev-muted);font-size:.82rem;font-weight:400}
.ev-list{list-style:none;margin:0;padding:0}
.ev-row{display:flex;flex-wrap:wrap;align-items:center;gap:.75rem;padding:.8rem 1rem;border-bottom:1px solid #f2f4f7}
.ev-row:last-child{border-bottom:0}
.ev-row--todo{border-left:3px solid #f0454b}
.ev-row--doing{border-left:3px solid #f79009}
.ev-row--ready{border-left:3px solid #12b76a}
.ev-row--done{border-left:3px solid #98a2b3;background:#fcfcfd}
.ev-row__who{flex:1 1 220px;min-width:0}
.ev-row__who strong{display:block;color:var(--ev-ink);font-size:.95rem}
.ev-row__who small{color:var(--ev-muted);font-size:.8rem}
.ev-row__state{flex:0 0 170px;display:flex;flex-direction:column;gap:.15rem}
.ev-row__state small{color:var(--ev-muted);font-size:.78rem}
.ev-row__score{flex:0 0 90px;text-align:right}
.ev-row__score strong{display:block;color:var(--ev-ink);font-size:1.05rem;line-height:1.1}
.ev-row__score small{color:var(--ev-muted);font-size:.72rem}
.ev-row__go{flex:0 0 auto;margin-left:auto}
.ev-chip{display:inline-block;padding:.1rem .55rem;border-radius:1rem;font-size:.78rem;font-weight:600;width:max-content}
.ev-chip--todo{background:#fef3f2;color:#b42318}
.ev-chip--doing{background:#fffaeb;color:#b54708}
.ev-chip--ready{background:#ecfdf3;color:#027a48}
.ev-chip--done{background:#f2f4f7;color:#475467}
.ev-hint{margin:.9rem 0 0;color:var(--ev-muted);font-size:.84rem}
.ev-alert{display:flex;align-items:flex-start;gap:.6rem;padding:.85rem 1rem;margin-bottom:1rem;background:#fffaeb;border:1px solid #fedf89;border-radius:.7rem;color:#b54708}
.ev-guide{display:flex;align-items:flex-start;gap:.6rem;padding:.8rem 1rem;margin-bottom:1rem;background:#eff6ff;border:1px solid #b2ddff;border-radius:.7rem;color:#175cd3;font-size:.88rem;line-height:1.6}
.ev-comp{margin-bottom:1rem;padding:0 0 1rem;background:#fff;border:1px solid #e4e7ec;border-radius:.9rem;overflow:hidden}
.ev-comp__head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.6rem;padding:.8rem 1rem;background:#f8fafc;border-bottom:1px solid #e4e7ec}
.ev-comp__head strong{color:var(--ev-ink);font-size:1rem}
.ev-comp__no{display:inline-block;margin-right:.5rem;padding:.1rem .5rem;background:var(--ev-primary);color:#fff;border-radius:1rem;font-size:.75rem;font-weight:700}
.ev-comp__expect{padding:.1rem .6rem;background:#eff6ff;color:var(--ev-primary);border-radius:1rem;font-size:.8rem;font-weight:600}
.ev-comp__def{margin:.75rem 1rem 0;color:var(--ev-muted);font-size:.85rem;line-height:1.6}
.ev-comp textarea{margin:.75rem 1rem 0;width:calc(100% - 2rem)}
.ev-level{margin:.75rem 1rem 0;border:1px solid #e4e7ec;border-radius:.7rem;overflow:hidden}
.ev-level__head{display:flex;align-items:center;gap:.6rem;width:100%;padding:.7rem .9rem;background:#fcfcfd;border:0;text-align:left;cursor:pointer}
.ev-level__head:hover{background:#f4f7fb}
.ev-level__head:focus-visible{outline:3px solid rgba(36,87,167,.25);outline-offset:-2px}
.ev-level__head>span:nth-child(2){flex:1 1 auto;min-width:0}
.ev-level__head strong{display:block;color:var(--ev-ink);font-size:.9rem}
.ev-level__head small{display:block;color:var(--ev-muted);font-size:.82rem;line-height:1.55;max-width:60ch}
.ev-level__caret{flex:0 0 auto;color:var(--ev-muted);transition:transform .15s ease}
.ev-level:not(.is-expanded) .ev-level__caret{transform:rotate(-90deg)}
.ev-level__count{flex:0 0 auto;padding:.1rem .55rem;background:#f2f4f7;color:#475467;border-radius:1rem;font-size:.78rem;font-weight:600;white-space:nowrap}
.ev-level.is-done .ev-level__count{background:#ecfdf3;color:#027a48}
.ev-items{list-style:none;margin:0;padding:0;display:none;border-top:1px solid #e4e7ec}
.ev-level.is-expanded .ev-items{display:block}
.ev-item{display:flex;flex-wrap:wrap;align-items:flex-start;gap:.6rem;padding:.55rem .9rem;border-bottom:1px solid #f2f4f7}
.ev-item:last-child{border-bottom:0}
.ev-item__no{flex:0 0 42px;color:var(--ev-muted);font-size:.82rem;padding-top:.35rem}
.ev-item__text{flex:1 1 320px;color:#344054;font-size:.86rem;line-height:1.55;padding-top:.3rem}
.ev-item__text em{display:block;margin-top:.2rem;color:#b54708;font-size:.76rem;font-style:normal}
.ev-item__control{flex:0 0 230px}
.ev-item--scale{background:#fffdf5}
.ev-comp__score{flex:0 0 auto;min-width:64px;padding:.1rem .6rem;background:#f2f4f7;color:#98a2b3;border-radius:1rem;font-size:.85rem;font-weight:700;text-align:center}
.ev-comp__score.is-set{background:#eff6ff;color:var(--ev-primary)}
.ev-total{margin-bottom:1rem;background:#fff;border:2px solid #e4e7ec;border-radius:.9rem;overflow:hidden}
.ev-total.is-complete{border-color:#12b76a}
.ev-total__head{display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:.5rem;padding:.8rem 1rem;background:#f8fafc;border-bottom:1px solid #e4e7ec}
.ev-total__head strong{color:var(--ev-ink);font-size:1rem}
.ev-total__head span{color:var(--ev-muted);font-size:.85rem}
.ev-total__list{list-style:none;margin:0;padding:0}
.ev-total__list li{display:flex;align-items:center;gap:.75rem;padding:.5rem 1rem;border-bottom:1px solid #f2f4f7;font-size:.88rem}
.ev-total__list li span{flex:1 1 auto;color:#344054}
.ev-total__list li em{flex:0 0 auto;color:var(--ev-muted);font-size:.8rem;font-style:normal}
.ev-total__list li strong{flex:0 0 72px;text-align:right;color:var(--ev-ink);font-variant-numeric:tabular-nums}
.ev-total__grand{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.85rem 1rem;background:#eff6ff}
.ev-total__grand span{color:#175cd3;font-size:.92rem;font-weight:600}
.ev-total__grand strong{color:var(--ev-primary);font-size:1.6rem;line-height:1;font-variant-numeric:tabular-nums}
.ev-total.is-complete .ev-total__grand{background:#ecfdf3}
.ev-total.is-complete .ev-total__grand span{color:#027a48}
.ev-total.is-complete .ev-total__grand strong{color:#027a48}
.ev-total__note{margin:0;padding:.6rem 1rem;color:var(--ev-muted);font-size:.78rem;line-height:1.6;border-top:1px solid #f2f4f7}
.ev-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.5rem;margin:1rem 0 2rem}
@media (max-width:720px){
  .ev-row__state,.ev-row__score{flex:1 1 auto;text-align:left}
  .ev-level__control{width:100%}
  .ev-level__control .form-select{min-width:0;flex:1}
  .ev-item__control{flex:1 1 100%}
}
CSS);
