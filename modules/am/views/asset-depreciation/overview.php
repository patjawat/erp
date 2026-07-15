<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\am\models\AccountingPeriod;

/** @var yii\web\View $this */
/** @var int $profileActive */
/** @var int $boundLevels */
/** @var int $assetTotal */
/** @var int $assetSnapped */
/** @var int $latestFy */
/** @var array $monthCounts */

$this->title = 'ภาพรวมค่าเสื่อมราคา';

$stage1Ready = $profileActive > 0 && $boundLevels > 0;
$stage2Ready = $latestFy > 0 && array_sum($monthCounts) > 0;

$monthTotal = array_sum($monthCounts);
$calc = $monthCounts[AccountingPeriod::STATUS_CALCULATED] ?? 0;
$posted = $monthCounts[AccountingPeriod::STATUS_POSTED] ?? 0;
$locked = $monthCounts[AccountingPeriod::STATUS_LOCKED] ?? 0;
$open = $monthCounts[AccountingPeriod::STATUS_OPEN] ?? 0;
$stage3Started = ($calc + $posted + $locked) > 0;

$feBe = $latestFy ?: null;

$statusPill = static function (bool $ready, string $readyText, string $todoText): string {
    $cls = $ready ? 'is-ready' : 'is-todo';
    $ico = $ready ? 'check' : 'circle-dashed';
    $txt = $ready ? $readyText : $todoText;
    return '<span class="dep-pill ' . $cls . '"><i data-lucide="' . $ico . '"></i>' . Html::encode($txt) . '</span>';
};

$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="trending-down"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3 dep-overview">
    <div class="dep-head mb-3">
        <p class="dep-sub mb-0">คิดค่าเสื่อมทำตามลำดับ 3 ขั้น ทำขั้นตั้งค่าครั้งเดียว เปิดงวดปีละครั้ง แล้วคำนวณทุกเดือน</p>
    </div>

    <ol class="dep-steps">
        <li class="dep-step">
            <div class="dep-num">1</div>
            <div class="dep-body">
                <div class="dep-body-head">
                    <h5>ตั้งค่าเกณฑ์ค่าเสื่อม</h5>
                    <?= $statusPill($stage1Ready, 'พร้อมใช้งาน', 'ยังไม่ครบ') ?>
                </div>
                <p class="dep-desc">สร้างเกณฑ์ (อัตรา/อายุ/มูลค่าซาก) แล้วผูกเกณฑ์เข้ากับประเภท หมวด หรือรายการทรัพย์สิน ทรัพย์สินจะรับเกณฑ์อัตโนมัติจากระดับที่เจาะจงที่สุด</p>
                <div class="dep-metric">
                    <span><b><?= number_format($profileActive) ?></b> เกณฑ์ที่ใช้งาน</span>
                    <span class="dep-dot">·</span>
                    <span>ผูกไว้ <b><?= number_format($boundLevels) ?></b> จุด</span>
                </div>
                <div class="dep-actions">
                    <?= Html::a('<i data-lucide="percent"></i> จัดการเกณฑ์', ['/am/depreciation-profile/index'], ['class' => 'btn btn-sm btn-primary']) ?>
                    <?= Html::a('<i data-lucide="link"></i> ผูกเกณฑ์เข้าลำดับชั้น', ['/am/depreciation-binding/index'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                </div>
            </div>
        </li>

        <li class="dep-step">
            <div class="dep-num">2</div>
            <div class="dep-body">
                <div class="dep-body-head">
                    <h5>เปิดงวดบัญชี</h5>
                    <?= $statusPill($stage2Ready, 'เปิดงวดแล้ว', 'ยังไม่เปิดงวด') ?>
                </div>
                <p class="dep-desc">สร้างงวดของปีงบประมาณ ระบบจะสร้างงวดรายเดือน ไตรมาส และปีงบให้ครบในครั้งเดียว</p>
                <div class="dep-metric">
                    <?php if ($feBe): ?>
                        <span>ปีงบล่าสุด <b><?= $feBe ?></b></span>
                        <span class="dep-dot">·</span>
                        <span>งวดรายเดือน <b><?= number_format($monthTotal) ?></b> งวด</span>
                    <?php else: ?>
                        <span class="dep-muted">ยังไม่มีงวดบัญชีในระบบ</span>
                    <?php endif; ?>
                </div>
                <div class="dep-actions">
                    <?= Html::a('<i data-lucide="calendar-range"></i> จัดการงวดบัญชี', ['/am/accounting-period/index'], ['class' => 'btn btn-sm ' . ($stage2Ready ? 'btn-outline-primary' : 'btn-primary')]) ?>
                </div>
            </div>
        </li>

        <li class="dep-step">
            <div class="dep-num">3</div>
            <div class="dep-body">
                <div class="dep-body-head">
                    <h5>คำนวณและบันทึกบัญชี</h5>
                    <?= $statusPill($stage3Started, 'กำลังเดินงวด', 'ยังไม่เริ่ม') ?>
                </div>
                <p class="dep-desc">แต่ละงวดเดือน: คำนวณ ตรวจผล แล้วบันทึกบัญชี เมื่อปิดปีจึงล็อกงวด งวดที่บันทึกแล้วแก้ด้วยรายการปรับปรุงเท่านั้น</p>
                <?php if ($feBe && $monthTotal): ?>
                    <div class="dep-progress" role="img" aria-label="สถานะงวดรายเดือน ปีงบ <?= $feBe ?>">
                        <?php foreach ([
                            ['n' => $locked, 'k' => 'locked', 'label' => 'ล็อก'],
                            ['n' => $posted, 'k' => 'posted', 'label' => 'บันทึกบัญชี'],
                            ['n' => $calc, 'k' => 'calc', 'label' => 'คำนวณแล้ว'],
                            ['n' => $open, 'k' => 'open', 'label' => 'เปิด'],
                        ] as $seg): ?>
                            <?php if ($seg['n'] > 0): ?>
                                <span class="seg seg-<?= $seg['k'] ?>" style="flex:<?= $seg['n'] ?>" title="<?= $seg['label'] ?> <?= $seg['n'] ?> งวด"></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="dep-legend">
                        <span><i class="lg lg-open"></i> เปิด <?= $open ?></span>
                        <span><i class="lg lg-calc"></i> คำนวณแล้ว <?= $calc ?></span>
                        <span><i class="lg lg-posted"></i> บันทึกบัญชี <?= $posted ?></span>
                        <span><i class="lg lg-locked"></i> ล็อก <?= $locked ?></span>
                    </div>
                <?php else: ?>
                    <div class="dep-metric"><span class="dep-muted">เปิดงวดบัญชีก่อน จึงจะคำนวณได้</span></div>
                <?php endif; ?>
                <div class="dep-actions">
                    <?= Html::a('<i data-lucide="calculator"></i> ไปหน้าคำนวณงวด', ['/am/accounting-period/index'] + ($feBe ? ['fiscal_year' => $feBe] : []), ['class' => 'btn btn-sm ' . ($stage2Ready ? 'btn-primary' : 'btn-outline-secondary disabled')]) ?>
                    <?= Html::a('<i data-lucide="flask-conical"></i> ทดลองคำนวณรายชิ้น', ['/am/asset-depreciation/preview-asset'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    <?= Html::a('<i data-lucide="file-bar-chart"></i> รายงาน', ['/am/depreciation-report/index'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                </div>
            </div>
        </li>
    </ol>

    <div class="dep-note">
        <i data-lucide="info"></i>
        <div>
            ครุภัณฑ์ที่รับเกณฑ์เข้าระบบแล้ว <b><?= number_format($assetSnapped) ?></b> จาก <b><?= number_format($assetTotal) ?></b> รายการ
            <?php if ($assetTotal > $assetSnapped): ?>
                <span class="dep-muted">— ที่เหลือจะรับเกณฑ์อัตโนมัติเมื่อบันทึกครุภัณฑ์ครั้งถัดไป (หากหมวดผูกเกณฑ์ไว้)</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$css = <<<CSS
.dep-overview {
    --ink-1:#1a202c; --ink-2:#4a5568; --ink-3:#718096;
    --line:rgba(15,23,42,.09); --surface:#fff; --surface-2:#f7f9fc;
    --ok:#0f6e56; --ok-bg:#e1f5ee; --todo:#854f0b; --todo-bg:#faeeda;
    --st-open:#a0aec0; --st-calc:#378add; --st-posted:#0f6e56; --st-locked:#2c2c2a;
}
.dep-overview .dep-sub { color:var(--ink-3); font-size:.925rem; max-width:70ch; }
.dep-steps { list-style:none; margin:0; padding:0; position:relative; }
.dep-step { position:relative; display:flex; gap:1rem; padding-bottom:1.25rem; }
.dep-step:not(:last-child)::before {
    content:""; position:absolute; left:17px; top:38px; bottom:0; width:2px; background:var(--line);
}
.dep-num {
    flex:0 0 auto; width:36px; height:36px; border-radius:50%;
    background:var(--surface-2); color:var(--ink-2); border:1px solid var(--line);
    display:flex; align-items:center; justify-content:center; font-weight:600; font-size:1rem; z-index:1;
}
.dep-body {
    flex:1 1 auto; background:var(--surface); border:1px solid var(--line);
    border-radius:10px; padding:.9rem 1.05rem; box-shadow:0 1px 2px rgba(15,23,42,.04);
}
.dep-body-head { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; }
.dep-body-head h5 { margin:0; font-size:1.05rem; color:var(--ink-1); font-weight:600; }
.dep-desc { color:var(--ink-2); font-size:.9rem; margin:.4rem 0 .6rem; max-width:74ch; line-height:1.55; }
.dep-metric { color:var(--ink-2); font-size:.9rem; margin-bottom:.7rem; display:flex; gap:.5rem; flex-wrap:wrap; align-items:baseline; }
.dep-metric b { color:var(--ink-1); font-weight:600; }
.dep-dot { color:var(--ink-3); }
.dep-muted { color:var(--ink-3); }
.dep-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
.dep-actions .btn i { width:1rem; height:1rem; vertical-align:-2px; margin-right:.25rem; }
.dep-pill {
    display:inline-flex; align-items:center; gap:.3rem; font-size:.78rem; font-weight:500;
    padding:.15rem .55rem; border-radius:999px; line-height:1.4;
}
.dep-pill i { width:.85rem; height:.85rem; }
.dep-pill.is-ready { background:var(--ok-bg); color:var(--ok); }
.dep-pill.is-todo { background:var(--todo-bg); color:var(--todo); }
.dep-progress { display:flex; height:10px; border-radius:6px; overflow:hidden; background:var(--surface-2); margin:.2rem 0 .55rem; }
.dep-progress .seg { display:block; }
.seg-open { background:var(--st-open); } .seg-calc { background:var(--st-calc); }
.seg-posted { background:var(--st-posted); } .seg-locked { background:var(--st-locked); }
.dep-legend { display:flex; gap:1rem; flex-wrap:wrap; font-size:.8rem; color:var(--ink-2); margin-bottom:.7rem; }
.dep-legend .lg { display:inline-block; width:.7rem; height:.7rem; border-radius:3px; vertical-align:-1px; margin-right:.3rem; }
.lg-open { background:var(--st-open); } .lg-calc { background:var(--st-calc); }
.lg-posted { background:var(--st-posted); } .lg-locked { background:var(--st-locked); }
.dep-note {
    display:flex; gap:.6rem; align-items:flex-start; margin-top:.25rem;
    background:var(--surface-2); border:1px solid var(--line); border-radius:8px;
    padding:.7rem .9rem; color:var(--ink-2); font-size:.875rem;
}
.dep-note i { width:1.05rem; height:1.05rem; flex:0 0 auto; margin-top:.15rem; color:var(--ink-3); }
CSS;
$this->registerCss($css);

$this->registerJs("if (window.lucide) { lucide.createIcons(); }");
?>
