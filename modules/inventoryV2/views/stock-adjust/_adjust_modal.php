<?php
use yii\helpers\Html;

/**
 * ฟอร์มปรับยอดแบบ modal — numbered stepper 4 ขั้น (ตาม PRODUCT.md design system)
 * ผูกกับพัสดุ+คลังที่ส่งมาแล้ว (ไม่ต้องค้นหา)
 * @var \app\modules\inventoryV2\models\StockItem $item
 * @var \app\modules\inventoryV2\models\Warehouse $warehouse
 * @var float $balance
 * @var float $value
 * @var float $avgCost
 * @var string $saveUrl
 */
$unitName = method_exists($item, 'getUnitName') ? (string) $item->getUnitName() : '';
$pricePrefill = $avgCost > 0 ? number_format($avgCost, 4, '.', '') : '';
?>
<div id="sam-root" class="stock-adjust-modal"
     data-balance="<?= htmlspecialchars((string) $balance) ?>"
     data-value="<?= htmlspecialchars((string) round($value, 2)) ?>"
     data-avg="<?= htmlspecialchars((string) round($avgCost, 6)) ?>"
     data-save-url="<?= Html::encode($saveUrl) ?>">

    <input type="hidden" id="sam-warehouse-id" value="<?= (int) $warehouse->id ?>">
    <input type="hidden" id="sam-item-code" value="<?= Html::encode($item->item_code) ?>">
    <input type="hidden" id="sam-mode" value="recount">

    <div class="sam-context">
        <div class="sam-context__head">
            <span class="sam-context__icon"><i class="bi bi-box-seam"></i></span>
            <div class="sam-context__id">
                <div class="sam-context__name"><?= Html::encode($item->item_name) ?></div>
                <div class="sam-context__meta"><?= Html::encode($item->item_code) ?> · <?= Html::encode($warehouse->warehouse_name) ?></div>
            </div>
        </div>
        <div class="sam-metrics">
            <div class="sam-metric">
                <span class="sam-metric__label">คงเหลือ</span>
                <span class="sam-metric__value" id="sam-cur-qty"><?= number_format($balance, 2) ?></span>
                <span class="sam-metric__unit"><?= Html::encode($unitName ?: 'หน่วย') ?></span>
            </div>
            <div class="sam-metric">
                <span class="sam-metric__label">มูลค่า</span>
                <span class="sam-metric__value" id="sam-cur-val"><?= number_format($value, 2) ?></span>
                <span class="sam-metric__unit">บาท</span>
            </div>
            <div class="sam-metric">
                <span class="sam-metric__label">ต้นทุนเฉลี่ย</span>
                <span class="sam-metric__value" id="sam-avg"><?= $avgCost > 0 ? number_format($avgCost, 2) : '—' ?></span>
                <span class="sam-metric__unit">บาท/หน่วย</span>
            </div>
        </div>
    </div>

    <div class="sam-steps">

        <section class="sam-step is-done" data-step="1">
            <div class="sam-step__rail">
                <span class="sam-step__num"><span class="sam-step__n">1</span><i class="bi bi-check-lg sam-step__c"></i></span>
            </div>
            <div class="sam-step__body">
                <h6 class="sam-step__title">เลือกรูปแบบการปรับ</h6>
                <p class="sam-step__desc" id="sam-hint">เพิ่ม = ตีมูลค่าตามต้นทุน · ลด = คิดตามต้นทุนเฉลี่ย → มูลค่าขยับตามจริง</p>
                <div class="seg-control" role="radiogroup" aria-label="รูปแบบการปรับ">
                    <input type="radio" class="sam-vh" name="sam-mode-radio" id="sam-mode-recount" value="recount" checked>
                    <label class="seg-control__item" for="sam-mode-recount">
                        <i class="bi bi-calculator seg-control__icon"></i>
                        <span class="seg-control__title">ตรวจนับ · คิดมูลค่า</span>
                    </label>
                    <input type="radio" class="sam-vh" name="sam-mode-radio" id="sam-mode-qty" value="qty_only">
                    <label class="seg-control__item" for="sam-mode-qty">
                        <i class="bi bi-123 seg-control__icon"></i>
                        <span class="seg-control__title">จำนวนอย่างเดียว</span>
                    </label>
                </div>
            </div>
        </section>

        <section class="sam-step" data-step="2">
            <div class="sam-step__rail">
                <span class="sam-step__num"><span class="sam-step__n">2</span><i class="bi bi-check-lg sam-step__c"></i></span>
            </div>
            <div class="sam-step__body">
                <h6 class="sam-step__title">ระบุจำนวนที่ถูกต้อง</h6>
                <p class="sam-step__desc">กรอกยอดตรวจนับจริง ระบบจะคำนวณจำนวนที่ปรับให้ หรือกรอกจำนวนที่ปรับเองก็ได้</p>
                <div class="sam-field-row">
                    <div class="sam-field">
                        <label class="sam-label" for="sam-target">ยอดตรวจนับจริง</label>
                        <input type="number" id="sam-target" class="form-control-input" step="any" inputmode="decimal" placeholder="เช่น 720">
                    </div>
                    <div class="sam-field">
                        <label class="sam-label" for="sam-adjust">จำนวนที่ปรับ</label>
                        <input type="number" id="sam-adjust" class="form-control-input" step="any" inputmode="decimal" placeholder="+ เพิ่ม / − ลด">
                    </div>
                </div>
            </div>
        </section>

        <section class="sam-step" data-step="3">
            <div class="sam-step__rail">
                <span class="sam-step__num"><span class="sam-step__n">3</span><i class="bi bi-check-lg sam-step__c"></i></span>
            </div>
            <div class="sam-step__body">
                <h6 class="sam-step__title">กำหนดมูลค่า</h6>
                <p class="sam-step__desc" id="sam-price-hint">ใส่มูลค่ารวมที่ต้องการ → คำนวณต้นทุน/หน่วยให้เอง ไม่ต้องเดา</p>
                <div class="sam-field-row" id="sam-price-wrap">
                    <div class="sam-field">
                        <label class="sam-label" for="sam-target-value">มูลค่าเป้าหมายรวม (บาท)</label>
                        <input type="number" id="sam-target-value" class="form-control-input" step="any" min="0" inputmode="decimal" placeholder="เช่น 9748.75">
                    </div>
                    <div class="sam-field">
                        <label class="sam-label" for="sam-price">ต้นทุน/หน่วย</label>
                        <input type="number" id="sam-price" class="form-control-input" step="any" min="0" inputmode="decimal" value="<?= $pricePrefill ?>" placeholder="เฉลี่ย">
                    </div>
                </div>
                <div class="sam-neutral-note" id="sam-neutral-note">โหมดนี้ปรับเฉพาะจำนวน มูลค่ารวมคงเดิม</div>
            </div>
        </section>

        <section class="sam-step" data-step="4">
            <div class="sam-step__rail">
                <span class="sam-step__num"><span class="sam-step__n">4</span><i class="bi bi-check-lg sam-step__c"></i></span>
            </div>
            <div class="sam-step__body">
                <h6 class="sam-step__title">ตรวจสอบและบันทึก</h6>

                <div class="sam-preview" id="sam-preview" hidden>
                    <div class="sam-preview__col">
                        <span class="sam-preview__label">ก่อนปรับ</span>
                        <span class="sam-preview__qty" id="sam-pv-before-qty">—</span>
                        <span class="sam-preview__val" id="sam-pv-before-val">—</span>
                    </div>
                    <i class="bi bi-arrow-right sam-preview__arrow"></i>
                    <div class="sam-preview__col sam-preview__col--delta">
                        <span class="sam-preview__label">ผลต่าง</span>
                        <span class="sam-preview__qty" id="sam-pv-delta-qty">—</span>
                        <span class="sam-preview__val" id="sam-pv-delta-val">—</span>
                    </div>
                    <i class="bi bi-arrow-right sam-preview__arrow"></i>
                    <div class="sam-preview__col sam-preview__col--after">
                        <span class="sam-preview__label">หลังปรับ</span>
                        <span class="sam-preview__qty" id="sam-pv-after-qty">—</span>
                        <span class="sam-preview__val" id="sam-pv-after-val">—</span>
                    </div>
                </div>
                <div class="sam-preview-empty" id="sam-preview-empty">กรอกจำนวนที่ขั้นตอน 2 เพื่อดูผลลัพธ์ก่อนบันทึก</div>

                <div class="sam-field mt-3">
                    <label class="sam-label" for="sam-note">หมายเหตุ</label>
                    <div class="sam-note-group dropup">
                        <input type="text" id="sam-note" class="form-control-input" placeholder="เลือกเหตุผล หรือพิมพ์เอง">
                        <button class="btn-note-drop dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false" aria-label="เลือกเหตุผล">
                            <i class="bi bi-chevron-expand"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php foreach ([
                                'ตรวจนับประจำงวด',
                                'ปรับให้ตรงบัญชี',
                                'ของชำรุด/เสียหาย',
                                'ของหมดอายุ',
                                'ของสูญหาย',
                                'เจอของเพิ่ม',
                                'แก้ยอดคีย์ผิด',
                            ] as $preset): ?>
                                <li><a class="dropdown-item sam-note-opt" href="#" data-note="<?= Html::encode($preset) ?>"><?= Html::encode($preset) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <button type="button" class="btn-save" id="sam-save" disabled>
                    <i class="bi bi-check-lg"></i>
                    <span class="btn-save__label">บันทึกการปรับยอด</span>
                </button>
            </div>
        </section>

    </div>
</div>

<style>
.stock-adjust-modal {
    --ink-1:#1a202c; --ink-2:#4a5568; --ink-3:#718096; --ink-4:#a0aec0;
    --surface:#fff; --surface-2:#f7f9fc; --surface-3:#eef2f7; --surface-hover:#f1f5f9;
    --line:rgba(15,23,42,.08); --line-strong:rgba(15,23,42,.14);
    --primary:#0d6efd; --primary-ink:#0a58ca; --primary-soft:rgba(13,110,253,.08); --primary-line:rgba(13,110,253,.22);
    --success:#15803d; --success-soft:rgba(21,128,61,.10);
    --warning:#b45309; --danger:#b91c1c; --danger-soft:rgba(185,28,28,.10);
    --radius:12px; --radius-sm:8px; --radius-xs:6px;
    --shadow-1:0 1px 2px rgba(15,23,42,.04),0 1px 1px rgba(15,23,42,.03);
    --shadow-2:0 6px 18px rgba(15,23,42,.10),0 2px 6px rgba(15,23,42,.06);
    --ease:cubic-bezier(0.16,1,0.3,1); --t-fast:120ms var(--ease); --t-mid:180ms var(--ease); --t-slow:240ms var(--ease);
    color:var(--ink-1);
    font-variant-numeric:tabular-nums;
}
.stock-adjust-modal .sam-vh { position:absolute; width:1px; height:1px; opacity:0; pointer-events:none; }

.stock-adjust-modal .sam-context {
    padding:.85rem 1rem; border:1px solid var(--line); border-radius:var(--radius);
    background:var(--surface-2); box-shadow:var(--shadow-1); margin-bottom:1.1rem;
}
.stock-adjust-modal .sam-context__head { display:flex; align-items:center; gap:.7rem; margin-bottom:.8rem; }
.stock-adjust-modal .sam-context__icon {
    display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px;
    border-radius:10px; background:var(--primary-soft); color:var(--primary); font-size:1.15rem; flex:0 0 auto;
}
.stock-adjust-modal .sam-context__id { min-width:0; }
.stock-adjust-modal .sam-context__name { font-size:.98rem; font-weight:700; line-height:1.25; color:var(--ink-1); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.stock-adjust-modal .sam-context__meta { font-size:.76rem; color:var(--ink-3); margin-top:.1rem; }
.stock-adjust-modal .sam-metrics { display:grid; grid-template-columns:repeat(3,1fr); gap:.5rem; }
.stock-adjust-modal .sam-metric {
    display:flex; flex-direction:column; align-items:center; gap:.05rem; padding:.55rem .3rem;
    background:var(--surface); border:1px solid var(--line); border-radius:var(--radius-sm);
}
.stock-adjust-modal .sam-metric__label { font-size:.72rem; color:var(--ink-3); }
.stock-adjust-modal .sam-metric__value { font-size:1.02rem; font-weight:700; color:var(--ink-1); line-height:1.2; }
.stock-adjust-modal .sam-metric__unit { font-size:.68rem; color:var(--ink-4); }

.stock-adjust-modal .sam-steps { display:flex; flex-direction:column; }
.stock-adjust-modal .sam-step { display:grid; grid-template-columns:36px minmax(0,1fr); gap:.85rem; padding-bottom:1.35rem; position:relative; }
.stock-adjust-modal .sam-step:last-child { padding-bottom:0; }
.stock-adjust-modal .sam-step__rail { display:flex; flex-direction:column; align-items:center; }
.stock-adjust-modal .sam-step__rail::after {
    content:""; flex:1 0 auto; width:2px; margin:.4rem 0 -.35rem; background:var(--line-strong);
    border-radius:2px; transition:background var(--t-mid);
}
.stock-adjust-modal .sam-step:last-child .sam-step__rail::after { display:none; }
.stock-adjust-modal .sam-step__num {
    position:relative; display:inline-flex; align-items:center; justify-content:center;
    width:36px; height:36px; border-radius:50%; flex:0 0 auto;
    background:var(--surface-3); color:var(--ink-3); font-size:.9rem; font-weight:800;
    transition:background var(--t-mid), color var(--t-mid), box-shadow var(--t-mid);
}
.stock-adjust-modal .sam-step__c { display:none; font-size:1.05rem; }
.stock-adjust-modal .sam-step__title { margin:.32rem 0 .12rem; font-size:.95rem; font-weight:700; color:var(--ink-2); line-height:1.25; transition:color var(--t-mid); }
.stock-adjust-modal .sam-step__desc { margin:0 0 .7rem; font-size:.78rem; color:var(--ink-3); line-height:1.45; }

.stock-adjust-modal .sam-step.is-active .sam-step__num { background:var(--primary); color:#fff; box-shadow:0 0 0 4px var(--primary-soft); }
.stock-adjust-modal .sam-step.is-active .sam-step__title { color:var(--ink-1); }
.stock-adjust-modal .sam-step.is-done .sam-step__num { background:var(--success); color:#fff; }
.stock-adjust-modal .sam-step.is-done .sam-step__title { color:var(--ink-1); }
.stock-adjust-modal .sam-step.is-done .sam-step__n { display:none; }
.stock-adjust-modal .sam-step.is-done .sam-step__c { display:inline-flex; animation:sam-pop var(--t-slow); }
.stock-adjust-modal .sam-step.is-done .sam-step__rail::after { background:var(--success); }
@keyframes sam-pop { 0%{ transform:scale(.4); opacity:0; } 60%{ transform:scale(1.12); } 100%{ transform:scale(1); opacity:1; } }

.stock-adjust-modal .seg-control { display:grid; grid-template-columns:1fr 1fr; gap:.35rem; padding:.3rem; background:var(--surface-3); border-radius:var(--radius-sm); }
.stock-adjust-modal .seg-control__item {
    display:flex; align-items:center; justify-content:center; gap:.45rem; margin:0; cursor:pointer;
    padding:.55rem .4rem; border-radius:var(--radius-xs); border:1px solid transparent; background:transparent;
    color:var(--ink-2); font-weight:600; transition:all var(--t-fast);
}
.stock-adjust-modal .seg-control__icon { font-size:1rem; }
.stock-adjust-modal .seg-control__title { font-size:.84rem; }
.stock-adjust-modal .seg-control__item:hover { background:var(--surface-hover); color:var(--ink-1); }
.stock-adjust-modal .sam-vh:checked + .seg-control__item { background:var(--surface); border-color:var(--primary-line); color:var(--primary-ink); box-shadow:0 0 0 3px var(--primary-soft); }
.stock-adjust-modal .sam-vh:focus-visible + .seg-control__item { border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-soft); }

.stock-adjust-modal .sam-field-row { display:grid; grid-template-columns:1fr 1fr; gap:.6rem; }
.stock-adjust-modal .sam-field { display:flex; flex-direction:column; gap:.35rem; }
.stock-adjust-modal .sam-label { font-size:.8rem; font-weight:600; color:var(--ink-2); }
.stock-adjust-modal .form-control-input {
    min-height:42px; width:100%; padding:.5rem .7rem; border:1px solid var(--line-strong);
    border-radius:var(--radius-sm); background:var(--surface); color:var(--ink-1); font-size:.9rem;
    transition:border-color var(--t-fast), box-shadow var(--t-fast);
}
.stock-adjust-modal .form-control-input::placeholder { color:var(--ink-4); }
.stock-adjust-modal .form-control-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-soft); }
.stock-adjust-modal .sam-neutral-note {
    display:none; margin-top:.1rem; padding:.5rem .7rem; font-size:.78rem; color:var(--ink-2);
    background:var(--surface-2); border:1px solid var(--line); border-radius:var(--radius-sm);
}
.stock-adjust-modal.is-qty-only .sam-neutral-note { display:block; }
.stock-adjust-modal.is-qty-only #sam-price-wrap { display:none; }

.stock-adjust-modal .sam-note-group { position:relative; display:flex; flex-wrap:nowrap; }
.stock-adjust-modal .sam-note-group > .form-control-input { flex:1 1 auto; width:1%; min-width:0; border-radius:var(--radius-sm) 0 0 var(--radius-sm); }
.stock-adjust-modal .btn-note-drop {
    flex:0 0 auto; display:inline-flex; align-items:center; gap:.25rem; padding:0 .85rem;
    border:1px solid var(--line-strong); border-left:none; border-radius:0 var(--radius-sm) var(--radius-sm) 0;
    background:var(--surface-2); color:var(--ink-2); font-size:.95rem; cursor:pointer;
    transition:background var(--t-fast), color var(--t-fast);
}
.stock-adjust-modal .btn-note-drop:hover { background:var(--surface-hover); color:var(--ink-1); }
.stock-adjust-modal .btn-note-drop:focus-visible { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-soft); position:relative; z-index:2; }
.stock-adjust-modal .btn-note-drop::after { display:none; }
.stock-adjust-modal .sam-note-group .dropdown-menu {
    z-index:1090; min-width:100%; padding:.35rem; margin:0 0 .4rem;
    border:1px solid var(--line); border-radius:var(--radius-sm); background:var(--surface); box-shadow:var(--shadow-2);
}
.stock-adjust-modal .dropdown-item.sam-note-opt {
    padding:.5rem .6rem; border-radius:var(--radius-xs); font-size:.85rem; color:var(--ink-2); white-space:nowrap;
}
.stock-adjust-modal .dropdown-item.sam-note-opt:hover,
.stock-adjust-modal .dropdown-item.sam-note-opt:focus { background:var(--primary-soft); color:var(--primary-ink); }

.stock-adjust-modal .sam-preview {
    display:flex; align-items:stretch; gap:.4rem; padding:.85rem; border:1px solid var(--line);
    border-radius:var(--radius); background:var(--surface-2); animation:sam-reveal var(--t-slow);
}
@keyframes sam-reveal { from{ opacity:0; transform:translateY(6px); } to{ opacity:1; transform:translateY(0); } }
.stock-adjust-modal .sam-preview__col { flex:1 1 0; display:flex; flex-direction:column; align-items:center; gap:.12rem; min-width:0; text-align:center; }
.stock-adjust-modal .sam-preview__label { font-size:.7rem; color:var(--ink-3); }
.stock-adjust-modal .sam-preview__qty { font-size:1.02rem; font-weight:700; color:var(--ink-1); line-height:1.2; }
.stock-adjust-modal .sam-preview__val { font-size:.76rem; color:var(--ink-3); }
.stock-adjust-modal .sam-preview__arrow { display:flex; align-items:center; color:var(--ink-4); font-size:.85rem; }
.stock-adjust-modal .sam-preview__col--delta .sam-preview__qty.is-up,
.stock-adjust-modal .sam-preview__col--delta .sam-preview__val.is-up { color:var(--success); }
.stock-adjust-modal .sam-preview__col--delta .sam-preview__qty.is-down,
.stock-adjust-modal .sam-preview__col--delta .sam-preview__val.is-down { color:var(--danger); }
.stock-adjust-modal .sam-preview__col--after .sam-preview__qty,
.stock-adjust-modal .sam-preview__col--after .sam-preview__val { color:var(--primary-ink); }
.stock-adjust-modal .sam-preview-empty { padding:.85rem; font-size:.8rem; color:var(--ink-3); text-align:center; background:var(--surface-2); border:1px dashed var(--line-strong); border-radius:var(--radius); }

.stock-adjust-modal .btn-save {
    display:flex; align-items:center; justify-content:center; gap:.5rem; width:100%; min-height:46px; margin-top:1rem;
    border:none; border-radius:var(--radius-sm); background:var(--primary); color:#fff; font-size:.95rem; font-weight:600;
    cursor:pointer; transition:background var(--t-fast), transform var(--t-fast), opacity var(--t-fast);
}
.stock-adjust-modal .btn-save:hover:not(:disabled) { background:var(--primary-ink); }
.stock-adjust-modal .btn-save:active:not(:disabled) { transform:translateY(1px); }
.stock-adjust-modal .btn-save:disabled { opacity:.5; cursor:not-allowed; }
.stock-adjust-modal .btn-save.is-saving { background:var(--primary-ink); cursor:progress; }
.stock-adjust-modal .btn-save.is-done { background:var(--success); }

@media (max-width:520px) {
    .stock-adjust-modal .sam-field-row { grid-template-columns:1fr; }
    .stock-adjust-modal .sam-preview { flex-wrap:wrap; }
    .stock-adjust-modal .sam-preview__arrow { display:none; }
    .stock-adjust-modal .sam-preview__col { flex-basis:30%; }
}
@media (prefers-reduced-motion:reduce) {
    .stock-adjust-modal * { transition:none !important; animation:none !important; }
}
</style>

<script>
(function () {
    var root = document.getElementById('sam-root');
    if (!root) return;

    var curQty = parseFloat(root.getAttribute('data-balance')) || 0;
    var curVal = parseFloat(root.getAttribute('data-value')) || 0;
    var avg = parseFloat(root.getAttribute('data-avg')) || 0;
    var saveUrl = root.getAttribute('data-save-url');

    function $id(id) { return document.getElementById(id); }
    function fmtQty(v) { v = parseFloat(v); return isNaN(v) ? '—' : v.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function fmtMoney(v) { v = parseFloat(v); return isNaN(v) ? '—' : v.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ฿'; }
    function getMode() { return $id('sam-mode').value; }

    function effPrice(qty) {
        if (getMode() === 'qty_only') return 0;
        if (qty > 0) {
            var p = parseFloat($id('sam-price').value);
            return (!isNaN(p) && p >= 0) ? p : avg;
        }
        return avg;
    }

    function recomputeFromTargetValue() {
        if (getMode() === 'qty_only') return;
        var tv = parseFloat($id('sam-target-value').value);
        var qty = parseFloat($id('sam-adjust').value);
        if (isNaN(tv) || isNaN(qty) || qty === 0) return;
        var price = (tv - curVal) / qty;
        if (price < 0) price = 0;
        $id('sam-price').value = price.toFixed(6);
    }

    function readTargetValue() {
        var tv = parseFloat($id('sam-target-value').value);
        return isNaN(tv) ? null : tv;
    }

    function isValueOnly(qty) {
        var tv = readTargetValue();
        return getMode() !== 'qty_only' && qty === 0 && tv !== null && Math.abs(tv - curVal) > 0.000001;
    }

    function valueDeltaFor(qty) {
        var tv = readTargetValue();
        if (getMode() !== 'qty_only' && tv !== null) return tv - curVal;
        return (getMode() === 'qty_only') ? 0 : qty * effPrice(qty);
    }

    function updatePreview() {
        var qtyRaw = parseFloat($id('sam-adjust').value);
        var qty = isNaN(qtyRaw) ? 0 : qtyRaw;
        var valueOnly = isValueOnly(qty);
        var valid = qty !== 0 || valueOnly;
        $id('sam-preview').hidden = !valid;
        $id('sam-preview-empty').style.display = valid ? 'none' : '';
        if (!valid) { updateSteps(); $id('sam-save').disabled = true; return; }

        var vDelta = valueDeltaFor(qty);
        $id('sam-pv-before-qty').textContent = fmtQty(curQty);
        $id('sam-pv-before-val').textContent = fmtMoney(curVal);
        var dq = $id('sam-pv-delta-qty'), dv = $id('sam-pv-delta-val');
        dq.textContent = valueOnly ? 'คงเดิม' : ((qty > 0 ? '+' : '') + fmtQty(qty));
        dq.className = 'sam-preview__qty ' + (valueOnly ? '' : (qty > 0 ? 'is-up' : 'is-down'));
        dv.textContent = vDelta === 0 ? 'มูลค่าคงเดิม' : ((vDelta > 0 ? '+' : '') + fmtMoney(vDelta));
        dv.className = 'sam-preview__val ' + (vDelta > 0 ? 'is-up' : (vDelta < 0 ? 'is-down' : ''));
        $id('sam-pv-after-qty').textContent = fmtQty(curQty + qty);
        $id('sam-pv-after-val').textContent = fmtMoney(curVal + vDelta);

        $id('sam-save').disabled = false;
        updateSteps();
    }

    // ไล่สถานะ done/active ของ stepper ตามความคืบหน้าจริง
    function updateSteps() {
        var qtyRaw = parseFloat($id('sam-adjust').value);
        var qty = isNaN(qtyRaw) ? 0 : qtyRaw;
        var valueOnly = isValueOnly(qty);
        var done1 = true;
        var done2 = qty !== 0 || valueOnly;
        var done3 = done2 && (getMode() === 'qty_only' || valueOnly || effPrice(qty) > 0);
        var doneMap = { 1: done1, 2: done2, 3: done3 };
        var active = !done2 ? 2 : (!done3 ? 3 : 4);
        Array.prototype.forEach.call(root.querySelectorAll('.sam-step'), function (el) {
            var n = parseInt(el.getAttribute('data-step'), 10);
            el.classList.toggle('is-done', !!doneMap[n]);
            el.classList.toggle('is-active', n === active && !doneMap[n]);
        });
    }

    function syncMode() {
        var mode = getMode();
        root.classList.toggle('is-qty-only', mode === 'qty_only');
        var qty = parseFloat($id('sam-adjust').value);
        var isDecrease = !isNaN(qty) && qty < 0;
        var showPrice = (mode !== 'qty_only') && (isNaN(qty) || qty >= 0);
        $id('sam-price-wrap').style.display = showPrice ? '' : 'none';

        var hint = $id('sam-price-hint');
        if (mode === 'qty_only') {
            hint.style.display = 'none';
        } else if (isDecrease) {
            hint.style.display = '';
            hint.textContent = 'ลดยอด — คิดต้นทุนตามค่าเฉลี่ย ' + (avg > 0 ? fmtMoney(avg) : 'ปัจจุบัน') + '/หน่วย ให้อัตโนมัติ';
        } else {
            hint.style.display = '';
            hint.textContent = 'ใส่มูลค่ารวมที่ต้องการ → คำนวณต้นทุน/หน่วยให้เอง ไม่ต้องเดา';
        }

        $id('sam-hint').textContent = (mode === 'qty_only')
            ? 'ปรับเฉพาะจำนวน — มูลค่ารวมคงเดิม (ใช้เมื่อแก้ตัวเลขล้วน)'
            : 'เพิ่ม = ตีมูลค่าตามต้นทุน · ลด = คิดตามต้นทุนเฉลี่ย → มูลค่าขยับตามจริง';
        updatePreview();
    }

    Array.prototype.forEach.call(document.getElementsByName('sam-mode-radio'), function (r) {
        r.addEventListener('change', function () { $id('sam-mode').value = this.value; recomputeFromTargetValue(); syncMode(); });
    });
    $id('sam-target').addEventListener('input', function () {
        var t = parseFloat(this.value);
        if (!isNaN(t)) $id('sam-adjust').value = parseFloat((t - curQty).toFixed(6));
        recomputeFromTargetValue();
        syncMode();
    });
    $id('sam-adjust').addEventListener('input', function () { recomputeFromTargetValue(); syncMode(); });
    $id('sam-target-value').addEventListener('input', function () { recomputeFromTargetValue(); updatePreview(); });
    $id('sam-price').addEventListener('input', function () { $id('sam-target-value').value = ''; updatePreview(); });

    Array.prototype.forEach.call(document.querySelectorAll('.sam-note-opt'), function (opt) {
        opt.addEventListener('click', function (e) { e.preventDefault(); $id('sam-note').value = this.getAttribute('data-note'); });
    });

    $id('sam-save').addEventListener('click', function () {
        var qtyRaw = parseFloat($id('sam-adjust').value);
        var qty = isNaN(qtyRaw) ? 0 : qtyRaw;
        if (qty === 0 && !isValueOnly(qty)) { alert('กรุณาระบุจำนวนที่ปรับ หรือมูลค่าเป้าหมายที่ต่างจากเดิม'); return; }
        var btn = this;
        btn.disabled = true; btn.classList.add('is-saving');
        btn.querySelector('.btn-save__label').textContent = 'กำลังบันทึก...';
        $.ajax({
            url: saveUrl, type: 'POST', dataType: 'json',
            data: {
                warehouse_id: $id('sam-warehouse-id').value,
                item_code: $id('sam-item-code').value,
                adjustment_qty: qty,
                current_qty: curQty,
                target_qty: $id('sam-target').value,
                mode: getMode(),
                unit_price: $id('sam-price').value,
                current_value: curVal,
                target_value: $id('sam-target-value').value,
                source: 'balance-modal',
                note: $id('sam-note').value
            }
        }).done(function (res) {
            if (res && res.success) {
                btn.classList.remove('is-saving'); btn.classList.add('is-done');
                btn.querySelector('.btn-save__label').textContent = 'สำเร็จ';
                if (typeof success === 'function') { success('ปรับยอดสำเร็จ — ' + (res.order_no || '')); }
                var modalEl = document.getElementById('main-modal');
                if (modalEl && window.bootstrap && bootstrap.Modal) {
                    var inst = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
                    if (inst) inst.hide();
                }
                setTimeout(function () { location.reload(); }, 400);
            } else {
                alert((res && res.message) ? res.message : 'เกิดข้อผิดพลาด');
                btn.disabled = false; btn.classList.remove('is-saving');
                btn.querySelector('.btn-save__label').textContent = 'บันทึกการปรับยอด';
            }
        }).fail(function () {
            alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
            btn.disabled = false; btn.classList.remove('is-saving');
            btn.querySelector('.btn-save__label').textContent = 'บันทึกการปรับยอด';
        });
    });

    syncMode();
})();
</script>
