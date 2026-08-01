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
/** @var string $status */
/** @var string $q */
/** @var int $deptFilter */
/** @var string $type */

$this->title = 'แผนของหน่วยงาน';
$this->params['breadcrumbs'][] = $this->title;

$orgNames = ArrayHelper::map($orgs, 'id', 'name');

// map รหัส item -> ชื่อรายการ + หมวด สำหรับแสดงผล
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
    'draft'  => ['label' => 'ร่าง', 'class' => 'bg-secondary'],
    'submit' => ['label' => 'รออนุมัติ', 'class' => 'bg-warning text-dark'],
    'approve' => ['label' => 'อนุมัติ', 'class' => 'bg-success'],
    'reject' => ['label' => 'ไม่อนุมัติ', 'class' => 'bg-danger'],
];
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body mb-0"><i class="fa-solid fa-folder-tree me-2"></i><?= $this->title ?></h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/me/menu', ['active' => 'plan']) ?>
<?php $this->endBlock(); ?>

<?php if ($flash = Yii::$app->session->getFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= Html::encode($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
// การ์ด 3 ประเภทคำขอ
$typeCards = [
    ['key' => 'parcel',    'label' => 'คำขอพัสดุ',    'icon' => 'fa-box-open',            'class' => 'primary'],
    ['key' => 'personnel', 'label' => 'คำขอบุคลากร',   'icon' => 'fa-user-group',          'class' => 'info'],
    ['key' => 'expenses',  'label' => 'คำขอค่าใช้สอย', 'icon' => 'fa-file-invoice-dollar', 'class' => 'success'],
];
?>
<div class="row g-3 mb-3">
    <?php foreach ($typeCards as $tc): ?>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100 <?= $type === $tc['key'] ? 'border border-2 border-' . $tc['class'] : '' ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2"><?= $tc['label'] ?></h6>
                            <div class="small text-muted">จำนวน</div>
                            <div class="h2 fw-bold mb-0"><?= number_format($byType[$tc['key']]['count']) ?></div>
                        </div>
                        <div class="rounded-3 bg-<?= $tc['class'] ?> bg-opacity-10 d-flex align-items-center justify-content-center" style="width:56px;height:56px">
                            <i class="fa-solid <?= $tc['icon'] ?> fs-4 text-<?= $tc['class'] ?>"></i>
                        </div>
                    </div>
                    <a href="<?= Url::to(['index', 'thai_year' => $thaiYear, 'type' => $tc['key']]) ?>" class="btn btn-link p-0 mt-2 text-decoration-none">
                        รายละเอียดเพิ่มเติม <i class="fa-solid fa-chevron-right small ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="get" class="row g-2 align-items-end">
            <input type="hidden" name="type" value="<?= Html::encode($type) ?>">
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">ปีงบประมาณ</label>
                <select name="thai_year" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ($years as $y): ?>
                        <option value="<?= $y ?>" <?= (int) $y === $thaiYear ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">สถานะ</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>ทุกสถานะ</option>
                    <?php foreach ($statusMeta as $k => $s): ?>
                        <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $s['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (count($orgNames) > 1): ?>
                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1">หน่วยงาน</label>
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="0">ทุกหน่วยงาน</option>
                        <?php foreach ($orgNames as $id => $name): ?>
                            <option value="<?= $id ?>" <?= (int) $deptFilter === (int) $id ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div class="col-12 col-md">
                <label class="form-label small mb-1">ค้นหา (รายการ / วัตถุประสงค์)</label>
                <input type="text" name="q" value="<?= Html::encode($q) ?>" class="form-control form-control-sm" placeholder="พิมพ์คำค้น...">
            </div>
            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>ค้นหา</button>
                <?= Html::a('ล้าง', ['index', 'thai_year' => $thaiYear], ['class' => 'btn btn-sm btn-light']) ?>
            </div>
        </form>
    </div>
</div>

<?php
// ตารางสรุปตามหมวด (คำขอทั้งหมด / อนุมัติแล้ว)
$bucketOrder = ['parcel' => 1, 'personnel' => 2, 'expenses' => 3, 'other' => 4];
$bucketLabel = ['parcel' => 'พัสดุ', 'personnel' => 'บุคลากร', 'expenses' => 'ค่าใช้สอย', 'other' => 'อื่นๆ'];
uasort($byCat, function ($a, $b) use ($bucketOrder) {
    return [$bucketOrder[$a['bucket']] ?? 9, $a['title']] <=> [$bucketOrder[$b['bucket']] ?? 9, $b['title']];
});
?>
<?php if (!empty($byCat)): ?>
<div class="card mb-3">
    <div class="card-header bg-light fw-semibold"><i class="fa-solid fa-table-list me-1"></i> สรุปคำขอตามหมวด</div>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light text-center">
                <tr>
                    <th rowspan="2" class="align-middle text-start">หมวด</th>
                    <th colspan="2">คำขอทั้งหมด</th>
                    <th colspan="2">อนุมัติแล้ว</th>
                </tr>
                <tr>
                    <th class="text-end">รายการ</th><th class="text-end">วงเงิน (บาท)</th>
                    <th class="text-end">รายการ</th><th class="text-end">วงเงิน (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($byCat as $cat): ?>
                    <tr>
                        <td>
                            <span class="badge bg-secondary bg-opacity-25 text-dark me-1"><?= $bucketLabel[$cat['bucket']] ?? '-' ?></span>
                            <?= Html::encode($cat['title']) ?>
                        </td>
                        <td class="text-end"><?= number_format($cat['total_cnt']) ?></td>
                        <td class="text-end"><?= number_format($cat['total_amt'], 2) ?></td>
                        <td class="text-end"><?= number_format($cat['appr_cnt']) ?></td>
                        <td class="text-end text-success"><?= number_format($cat['appr_amt'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php $phase = PlanHelper::phase($thaiYear); ?>
<div class="d-flex justify-content-between align-items-center mb-2">
    <span class="small text-muted">
        รอบทำแผนปี <?= $thaiYear ?> :
        <span class="badge <?= PlanHelper::phaseClass($phase) ?>"><?= PlanHelper::phaseLabel($phase) ?></span>
        <?php if ($phase === PlanHelper::PHASE_LOCK): ?><span class="text-warning">— เพิ่มใหม่ได้ แต่แก้ของเดิมไม่ได้</span><?php endif; ?>
    </span>
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

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>รายการ</th>
                    <th>หน่วยงาน</th>
                    <th class="text-end">ยอดรวม (บาท)</th>
                    <th class="text-center">สถานะ</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($models)): ?>
                    <?php $filtered = $status !== 'all' || $q !== '' || $deptFilter; ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">
                        <?= $filtered
                            ? 'ไม่พบรายการตามเงื่อนไขที่ค้นหา'
                            : 'ยังไม่มีแผนในปีงบประมาณ ' . $thaiYear . ' — กด "จัดทำแผนใหม่" เพื่อเริ่ม' ?>
                    </td></tr>
                <?php endif; ?>
                <?php foreach ($models as $m): ?>
                    <?php
                    $it = $itemMap[$m->plan_item_id] ?? null;
                    $st = $statusMeta[$m->status] ?? ['label' => $m->status, 'class' => 'bg-secondary'];
                    $editable = in_array($m->status, ['draft', 'reject'], true);
                    $dj = is_array($m->data_json) ? $m->data_json : (json_decode((string) $m->data_json, true) ?: []);
                    ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= Html::encode($it['item'] ?? $m->plan_item_id ?? '-') ?></div>
                            <?php if (!empty($it['cat'])): ?><small class="text-muted"><?= Html::encode($it['cat']) ?></small><?php endif; ?>
                            <?php if (!empty($m->description)): ?><div class="small text-muted"><?= Html::encode($m->description) ?></div><?php endif; ?>
                        </td>
                        <td><?= Html::encode($orgNames[$m->department_id] ?? '-') ?></td>
                        <td class="text-end"><?= number_format((float) $m->order_price, 2) ?></td>
                        <td class="text-center">
                            <span class="badge <?= $st['class'] ?>"><?= $st['label'] ?></span>
                            <?php if ($m->status === 'reject' && !empty($dj['reject_reason'])): ?>
                                <div class="small text-danger mt-1"><i class="fa-solid fa-comment-dots me-1"></i><?= Html::encode($dj['reject_reason']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php
                            $rowCanEdit = PlanHelper::canEdit($m->thai_year);
                            $rowCanAdd  = PlanHelper::canAdd($m->thai_year);
                            ?>
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
