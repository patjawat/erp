<?php

use yii\helpers\Html;
use app\modules\am\models\AccountingPeriod;

/** @var yii\web\View $this */
/** @var int $fyBE */
/** @var AccountingPeriod[] $periods */
/** @var int[] $years */

$this->title = 'งวดบัญชีค่าเสื่อม';

$statusBadge = static function ($s) {
    $map = [
        AccountingPeriod::STATUS_OPEN => 'secondary',
        AccountingPeriod::STATUS_CALCULATED => 'info',
        AccountingPeriod::STATUS_POSTED => 'primary',
        AccountingPeriod::STATUS_LOCKED => 'dark',
    ];
    $labels = AccountingPeriod::statusOptions();
    return '<span class="badge bg-' . ($map[$s] ?? 'secondary') . '">' . ($labels[$s] ?? $s) . '</span>';
};

$typeGroups = [
    AccountingPeriod::TYPE_MONTH => 'รายเดือน',
    AccountingPeriod::TYPE_QUARTER => 'รายไตรมาส',
    AccountingPeriod::TYPE_FISCAL_YEAR => 'ปีงบประมาณ',
];
$grouped = [];
foreach ($periods as $p) {
    $grouped[$p->period_type][] = $p;
}

$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="calendar-range"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-end align-items-center mt-3 mb-3 flex-wrap gap-2">
        <div class="d-flex gap-2">
            <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex gap-2']) ?>
                <select name="fiscal_year" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($years as $y): ?>
                        <option value="<?= $y ?>" <?= $y == $fyBE ? 'selected' : '' ?>>ปีงบ <?= $y ?></option>
                    <?php endforeach; ?>
                    <?php if (!in_array($fyBE, array_map('intval', $years), true)): ?>
                        <option value="<?= $fyBE ?>" selected>ปีงบ <?= $fyBE ?> (ยังไม่สร้าง)</option>
                    <?php endif; ?>
                </select>
            <?= Html::endForm() ?>

            <?= Html::beginForm(['generate'], 'post', ['class' => 'd-flex gap-2']) ?>
                <input type="number" name="fiscal_year" class="form-control" style="width:120px" placeholder="พ.ศ." value="<?= $fyBE ?>">
                <?= Html::submitButton('<i data-lucide="plus"></i> สร้างงวดปีงบ', ['class' => 'btn btn-primary']) ?>
            <?= Html::endForm() ?>
        </div>
    </div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $cls): ?>
        <?php if (Yii::$app->session->hasFlash($flash)): ?>
            <div class="alert alert-<?= $cls ?>"><?= Yii::$app->session->getFlash($flash) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="ap-flow">
        <?php
        $flow = [
            ['t' => 'เปิด', 'd' => 'ยังไม่คำนวณ', 'k' => 'open'],
            ['t' => 'คำนวณแล้ว', 'd' => 'ตรวจผลได้ ยังแก้ไขได้', 'k' => 'calc'],
            ['t' => 'บันทึกบัญชีแล้ว', 'd' => 'ลงบัญชี แก้ด้วยปรับปรุงเท่านั้น', 'k' => 'posted'],
            ['t' => 'ล็อก', 'd' => 'ปิดถาวร แก้ไม่ได้', 'k' => 'locked'],
        ];
        foreach ($flow as $i => $s): ?>
            <?php if ($i > 0): ?><span class="ap-arrow" aria-hidden="true"><i data-lucide="chevron-right"></i></span><?php endif; ?>
            <span class="ap-node ap-<?= $s['k'] ?>">
                <span class="ap-dot"></span>
                <span class="ap-txt"><b><?= $s['t'] ?></b><small><?= $s['d'] ?></small></span>
            </span>
        <?php endforeach; ?>
        <span class="ap-note"><i data-lucide="info"></i> คิดค่าเสื่อมที่งวด<b>รายเดือน</b> · ไตรมาส/ปีงบรวมยอดจากรายเดือนอัตโนมัติ</span>
    </div>

    <?php if (empty($periods)): ?>
        <div class="alert alert-warning">ยังไม่มีงวดสำหรับปีงบ <?= $fyBE ?> — กด "สร้างงวดปีงบ" เพื่อสร้าง 12 เดือน + 4 ไตรมาส + 1 ปีงบ</div>
    <?php endif; ?>

    <?php foreach ($typeGroups as $type => $groupLabel): ?>
        <?php if (empty($grouped[$type])) continue; ?>
        <div class="card mb-3">
            <div class="card-header fw-semibold"><?= $groupLabel ?></div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>งวด</th><th>ช่วงวันที่</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grouped[$type] as $p): ?>
                            <tr>
                                <td><?= Html::encode($p->name) ?></td>
                                <td class="text-muted small"><?= $p->start_date ?> – <?= $p->end_date ?></td>
                                <td><?= $statusBadge($p->status) ?></td>
                                <td class="text-end">
                                    <?php if ($p->period_type === AccountingPeriod::TYPE_MONTH && !$p->isClosed()): ?>
                                        <?= Html::a('<i data-lucide="calculator"></i> คำนวณ/ตรวจ', ['/am/asset-depreciation/run', 'period_id' => $p->id], ['class' => 'btn btn-sm btn-outline-info']) ?>
                                    <?php endif; ?>
                                    <?php if ($p->status === AccountingPeriod::STATUS_CALCULATED || ($p->period_type !== AccountingPeriod::TYPE_MONTH && $p->status === AccountingPeriod::STATUS_OPEN)): ?>
                                        <?= Html::a('<i data-lucide="check"></i> บันทึกบัญชี', ['post', 'id' => $p->id], [
                                            'class' => 'btn btn-sm btn-primary',
                                            'data' => ['method' => 'post', 'confirm' => "บันทึกบัญชีงวด {$p->name}? หลังบันทึกจะแก้ไขไม่ได้ (ต้องใช้ปรับปรุง/กลับรายการ)"],
                                        ]) ?>
                                    <?php endif; ?>
                                    <?php if ($p->status === AccountingPeriod::STATUS_POSTED): ?>
                                        <?= Html::a('<i data-lucide="lock"></i> ล็อก', ['lock', 'id' => $p->id], [
                                            'class' => 'btn btn-sm btn-outline-dark',
                                            'data' => ['method' => 'post', 'confirm' => "ล็อกงวด {$p->name}? ล็อกแล้วแก้ไม่ได้อีก"],
                                        ]) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
$css = <<<CSS
.ap-flow {
    --line:rgba(15,23,42,.09); --ink-1:#1a202c; --ink-2:#4a5568; --ink-3:#718096; --surface-2:#f7f9fc;
    display:flex; align-items:stretch; flex-wrap:wrap; gap:.4rem;
    background:var(--surface-2); border:1px solid var(--line); border-radius:10px;
    padding:.7rem .9rem; margin-bottom:1rem;
}
.ap-node { display:flex; align-items:center; gap:.5rem; padding:.15rem .35rem; }
.ap-node .ap-dot { width:.7rem; height:.7rem; border-radius:50%; flex:0 0 auto; }
.ap-node .ap-txt { display:flex; flex-direction:column; line-height:1.25; }
.ap-node .ap-txt b { color:var(--ink-1); font-size:.86rem; font-weight:600; }
.ap-node .ap-txt small { color:var(--ink-3); font-size:.74rem; }
.ap-open .ap-dot { background:#a0aec0; }
.ap-calc .ap-dot { background:#378add; }
.ap-posted .ap-dot { background:#0f6e56; }
.ap-locked .ap-dot { background:#2c2c2a; }
.ap-arrow { display:flex; align-items:center; color:var(--ink-3); }
.ap-arrow i { width:1rem; height:1rem; }
.ap-note {
    display:flex; align-items:center; gap:.35rem; margin-left:auto;
    color:var(--ink-2); font-size:.8rem; padding-left:.5rem;
}
.ap-note b { color:var(--ink-1); font-weight:600; }
.ap-note i { width:1rem; height:1rem; color:var(--ink-3); }
@media (max-width:768px){ .ap-note { margin-left:0; width:100%; } }
CSS;
$this->registerCss($css);
$this->registerJs("if (window.lucide) { lucide.createIcons(); }");
?>
