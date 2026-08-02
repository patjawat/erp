<?php

use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\modules\plan\components\PlanHelper;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Employees $me */
/** @var app\modules\plan\models\PlanOrder[] $models */
/** @var int $thaiYear */
/** @var array $years */
/** @var app\modules\hr\models\Organization[] $orgs */
/** @var array $byType */
/** @var array $byCat */
/** @var array $byStatus */
/** @var int $grandCnt */
/** @var float $grandAmt */
/** @var string $status */
/** @var string $q */
/** @var int $deptFilter */
/** @var string $type */

$this->title = 'แผนของหน่วยงาน';
$this->params['breadcrumbs'][] = $this->title;

$orgNames  = ArrayHelper::map($orgs, 'id', 'name');
$multiDept = count($orgNames) > 1;

// map รหัส item -> ชื่อรายการ + หมวด
$itemIds = array_filter(ArrayHelper::getColumn($models, 'plan_item_id'));
$itemMap = [];
if ($itemIds) {
    $rows = (new Query())
        ->select(['code' => 'i.code', 'item' => 'i.title', 'cat' => 'c.title'])
        ->from(['i' => 'categorise'])
        ->leftJoin(['c' => 'categorise'], "c.code = i.category_id AND c.name = 'plan_category'")
        ->where(['i.name' => 'plan_item', 'i.code' => array_values($itemIds)])
        ->all();
    foreach ($rows as $r) {
        $itemMap[$r['code']] = $r;
    }
}

$statusMeta = [
    'draft'   => ['label' => 'ร่าง',       'color' => 'secondary'],
    'submit'  => ['label' => 'รออนุมัติ',  'color' => 'warning'],
    'approve' => ['label' => 'อนุมัติ',     'color' => 'success'],
    'reject'  => ['label' => 'ไม่อนุมัติ',  'color' => 'danger'],
    'renew'   => ['label' => 'ปรับแผน',    'color' => 'info'],
];

// สร้างลิงก์โดยคงตัวกรองปัจจุบัน แล้ว override เฉพาะที่ระบุ
$link = function (array $ov) use ($thaiYear, $status, $type, $q, $deptFilter) {
    return Url::to(['index',
        'thai_year'     => $ov['thai_year']     ?? $thaiYear,
        'status'        => $ov['status']        ?? $status,
        'type'          => $ov['type']          ?? $type,
        'q'             => $ov['q']             ?? $q,
        'department_id' => $ov['department_id'] ?? $deptFilter,
    ]);
};

$phase = PlanHelper::phase($thaiYear);
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body mb-0"><i class="fa-solid fa-folder-tree me-2"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/me/menu', ['active' => 'plan']) ?>
<?php $this->endBlock(); ?>

<style>
.me-chip, .me-type { transition: background-color .15s ease-out, border-color .15s ease-out; }
.me-chip:hover, .me-type:hover { background-color: var(--bs-secondary-bg); }
@media (prefers-reduced-motion: reduce) { .me-chip, .me-type { transition: none; } }
</style>

<?php if ($flash = Yii::$app->session->getFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= Html::encode($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- แถบดำเนินการ: รอบทำแผน + จัดทำแผนใหม่ -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="text-body-secondary small">
        รอบทำแผนปี <?= $thaiYear ?>
        <span class="badge <?= PlanHelper::phaseClass($phase) ?> ms-1"><?= PlanHelper::phaseLabel($phase) ?></span>
        <?php if ($phase === PlanHelper::PHASE_LOCK): ?>
            <span class="text-warning-emphasis ms-1">— เพิ่มใหม่ได้ แต่แก้ของเดิมไม่ได้</span>
        <?php endif; ?>
    </div>
    <?php if (PlanHelper::canAdd($thaiYear)): ?>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-plus me-1"></i> จัดทำแผนใหม่
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><?= Html::a('<i class="fa-solid fa-box-open me-2"></i> แผนพัสดุ (ครุภัณฑ์/วัสดุ)', ['create-parcel'], ['class' => 'dropdown-item']) ?></li>
                <li><?= Html::a('<i class="fa-solid fa-user-group me-2"></i> แผนบุคลากร', ['create'], ['class' => 'dropdown-item']) ?></li>
                <li><?= Html::a('<i class="fa-solid fa-file-invoice-dollar me-2"></i> แผนค่าใช้สอย', ['create'], ['class' => 'dropdown-item']) ?></li>
            </ul>
        </div>
    <?php endif; ?>
</div>

<!-- ภาพรวม: งบที่วางแผน + กรองตามสถานะ -->
<?php
$statusChips = [
    'all'     => ['label' => 'ทั้งหมด',    'c' => $grandCnt,               'color' => 'primary'],
    'draft'   => ['label' => 'ร่าง',       'c' => $byStatus['draft']['c'],  'color' => 'secondary'],
    'submit'  => ['label' => 'รออนุมัติ',  'c' => $byStatus['submit']['c'], 'color' => 'warning'],
    'approve' => ['label' => 'อนุมัติ',     'c' => $byStatus['approve']['c'],'color' => 'success'],
    'reject'  => ['label' => 'ไม่อนุมัติ',  'c' => $byStatus['reject']['c'], 'color' => 'danger'],
];
?>
<div class="card border mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-lg-auto">
                <div class="text-body-secondary small">งบประมาณที่วางแผน ปี <?= $thaiYear ?></div>
                <div class="h4 fw-semibold text-body mb-0"><?= number_format($grandAmt, 2) ?> <small class="fw-normal text-body-secondary">บาท</small></div>
            </div>
            <div class="col-12 col-lg">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <?php foreach ($statusChips as $key => $sc): $active = $status === $key; ?>
                        <a href="<?= $link(['status' => $key]) ?>" class="me-chip text-decoration-none d-inline-flex align-items-center gap-2 px-3 py-2 rounded border <?= $active ? 'border-primary border-2 bg-body-tertiary' : '' ?>">
                            <span class="badge bg-<?= $sc['color'] ?>-subtle text-<?= $sc['color'] ?>-emphasis"><?= $sc['label'] ?></span>
                            <span class="fw-semibold text-body"><?= number_format($sc['c']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- แยกตามประเภทคำขอ -->
<?php
$typeCards = [
    'parcel'    => ['label' => 'พัสดุ',     'icon' => 'fa-box-open'],
    'personnel' => ['label' => 'บุคลากร',   'icon' => 'fa-user-group'],
    'expenses'  => ['label' => 'ค่าใช้สอย', 'icon' => 'fa-file-invoice-dollar'],
];
$tcKeys = array_keys($typeCards);
?>
<div class="card border mb-3">
    <div class="card-body">
        <div class="row g-0 text-center">
            <?php foreach ($typeCards as $key => $tc): $active = $type === $key; ?>
                <div class="col-4 <?= $key !== end($tcKeys) ? 'border-end' : '' ?>">
                    <a href="<?= $link(['type' => $active ? 'all' : $key]) ?>" class="me-type d-block text-decoration-none rounded p-2 <?= $active ? 'border border-primary border-2 bg-body-tertiary' : '' ?>">
                        <div class="text-body-secondary small"><i class="fa-solid <?= $tc['icon'] ?> me-1"></i><?= $tc['label'] ?></div>
                        <div class="h5 fw-semibold text-body mb-0 mt-1"><?= number_format($byType[$key]['count']) ?> <small class="fw-normal text-body-secondary">รายการ</small></div>
                        <div class="small text-body-secondary"><?= number_format($byType[$key]['amount'], 2) ?> บาท</div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- รายการแผน -->
<div class="card border">
    <div class="card-header bg-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="fw-semibold">
            รายการแผน
            <?php if ($status !== 'all' || $type !== 'all' || $q !== '' || $deptFilter): ?>
                <span class="text-body-secondary fw-normal">(กรองอยู่)</span>
            <?php endif; ?>
        </span>
        <form method="get" class="d-flex flex-wrap align-items-center gap-2">
            <input type="hidden" name="status" value="<?= Html::encode($status) ?>">
            <input type="hidden" name="type" value="<?= Html::encode($type) ?>">
            <select name="thai_year" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y ?>" <?= (int) $y === $thaiYear ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($multiDept): ?>
                <select name="department_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    <option value="0">ทุกหน่วยงาน</option>
                    <?php foreach ($orgNames as $id => $name): ?>
                        <option value="<?= $id ?>" <?= (int) $deptFilter === (int) $id ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <div class="input-group input-group-sm w-auto">
                <input type="text" name="q" value="<?= Html::encode($q) ?>" class="form-control form-control-sm" placeholder="ค้นหารายการ / วัตถุประสงค์">
                <button type="submit" class="btn btn-outline-secondary"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
            <?php if ($status !== 'all' || $type !== 'all' || $q !== '' || $deptFilter): ?>
                <?= Html::a('ล้างตัวกรอง', ['index', 'thai_year' => $thaiYear], ['class' => 'btn btn-sm btn-link text-decoration-none']) ?>
            <?php endif; ?>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-body-tertiary">
                <tr>
                    <th>รายการ</th>
                    <?php if ($multiDept): ?><th>หน่วยงาน</th><?php endif; ?>
                    <th class="text-end">ยอดรวม (บาท)</th>
                    <th class="text-center">สถานะ</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($models)): ?>
                    <?php $filtered = $status !== 'all' || $type !== 'all' || $q !== '' || $deptFilter; ?>
                    <tr><td colspan="<?= $multiDept ? 5 : 4 ?>" class="text-center text-body-secondary py-5">
                        <?php if ($filtered): ?>
                            <i class="fa-solid fa-filter-circle-xmark fs-3 d-block mb-2 opacity-50"></i>
                            ไม่พบรายการตามเงื่อนไข
                            <div class="mt-2"><?= Html::a('ล้างตัวกรอง', ['index', 'thai_year' => $thaiYear], ['class' => 'btn btn-sm btn-outline-secondary']) ?></div>
                        <?php else: ?>
                            <i class="fa-solid fa-clipboard-list fs-3 d-block mb-2 opacity-50"></i>
                            ยังไม่มีแผนในปีงบประมาณ <?= $thaiYear ?>
                            <?php if (PlanHelper::canAdd($thaiYear)): ?>
                                <div class="mt-1">กดปุ่ม <span class="fw-semibold">จัดทำแผนใหม่</span> ด้านบนเพื่อเริ่ม</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td></tr>
                <?php endif; ?>
                <?php foreach ($models as $m): ?>
                    <?php
                    $it = $itemMap[$m->plan_item_id] ?? null;
                    $st = $statusMeta[$m->status] ?? ['label' => $m->status, 'color' => 'secondary'];
                    $editable = in_array($m->status, ['draft', 'reject'], true);
                    $dj = is_array($m->data_json) ? $m->data_json : (json_decode((string) $m->data_json, true) ?: []);
                    $rowCanEdit = PlanHelper::canEdit($m->thai_year);
                    $rowCanAdd  = PlanHelper::canAdd($m->thai_year);
                    ?>
                    <tr>
                        <td>
                            <div class="fw-semibold text-body"><?= Html::encode($it['item'] ?? $m->plan_item_id ?? '-') ?></div>
                            <?php if (!empty($it['cat'])): ?><small class="text-body-secondary"><?= Html::encode($it['cat']) ?></small><?php endif; ?>
                            <?php if (!empty($m->description)): ?><div class="small text-body-secondary"><?= Html::encode($m->description) ?></div><?php endif; ?>
                        </td>
                        <?php if ($multiDept): ?>
                            <td class="text-body-secondary"><?= Html::encode($orgNames[$m->department_id] ?? '-') ?></td>
                        <?php endif; ?>
                        <td class="text-end fw-semibold"><?= number_format((float) $m->order_price, 2) ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $st['color'] ?>-subtle text-<?= $st['color'] ?>-emphasis"><?= $st['label'] ?></span>
                            <?php if ($m->status === 'reject' && !empty($dj['reject_reason'])): ?>
                                <div class="small text-danger-emphasis mt-1"><i class="fa-solid fa-comment-dots me-1"></i><?= Html::encode($dj['reject_reason']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <?php if ($editable && $rowCanEdit): ?>
                                    <?= Html::a('<i class="fa-solid fa-pen"></i>', ['update', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'แก้ไข']) ?>
                                <?php endif; ?>
                                <?php if ($editable && $rowCanAdd): ?>
                                    <?= Html::a('<i class="fa-solid fa-paper-plane"></i> ส่งขออนุมัติ', ['submit', 'id' => $m->id], [
                                        'class' => 'btn btn-sm btn-outline-primary',
                                        'data' => ['method' => 'post', 'confirm' => 'ส่งแผนนี้ขออนุมัติ?'],
                                    ]) ?>
                                <?php endif; ?>
                                <?php if ($m->status === 'draft' && $rowCanEdit): ?>
                                    <?= Html::a('<i class="fa-solid fa-trash"></i>', ['delete', 'id' => $m->id], [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'title' => 'ลบ',
                                        'data' => ['method' => 'post', 'confirm' => 'ลบแผนนี้?'],
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- สรุปตามหมวด (พับเก็บ) -->
<?php if (!empty($byCat)):
    $bucketOrder = ['parcel' => 1, 'personnel' => 2, 'expenses' => 3, 'other' => 4];
    $bucketLabel = ['parcel' => 'พัสดุ', 'personnel' => 'บุคลากร', 'expenses' => 'ค่าใช้สอย', 'other' => 'อื่นๆ'];
    uasort($byCat, function ($a, $b) use ($bucketOrder) {
        return [$bucketOrder[$a['bucket']] ?? 9, $a['title']] <=> [$bucketOrder[$b['bucket']] ?? 9, $b['title']];
    });
?>
<div class="mt-3">
    <button class="btn btn-sm btn-link text-decoration-none px-0" type="button" data-bs-toggle="collapse" data-bs-target="#catSummary">
        <i class="fa-solid fa-table-list me-1"></i> สรุปตามหมวด <i class="fa-solid fa-chevron-down small ms-1"></i>
    </button>
    <div class="collapse" id="catSummary">
        <div class="card border">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th>หมวด</th>
                            <th class="text-end">รายการ</th>
                            <th class="text-end">วงเงินรวม (บาท)</th>
                            <th class="text-end">อนุมัติแล้ว (บาท)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($byCat as $cat): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis me-1"><?= $bucketLabel[$cat['bucket']] ?? '-' ?></span>
                                    <?= Html::encode($cat['title']) ?>
                                </td>
                                <td class="text-end"><?= number_format($cat['total_cnt']) ?></td>
                                <td class="text-end"><?= number_format($cat['total_amt'], 2) ?></td>
                                <td class="text-end text-success-emphasis"><?= number_format($cat['appr_amt'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
