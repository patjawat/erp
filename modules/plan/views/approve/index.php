<?php

use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Employees;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanOrder[] $models */
/** @var int $thaiYear */
/** @var string $status */
/** @var array $counts */
/** @var array $years */

$this->title = 'อนุมัติแผน';
$this->params['breadcrumbs'][] = $this->title;

// map รายการ + หมวด
$itemIds = array_filter(ArrayHelper::getColumn($models, 'plan_item_id'));
$itemMap = [];
if ($itemIds) {
    foreach ((new Query())
        ->select(['code' => 'i.code', 'item' => 'i.title', 'cat' => 'c.title'])
        ->from(['i' => 'categorise'])
        ->leftJoin(['c' => 'categorise'], "c.code = i.category_id AND c.name = 'plan_category'")
        ->where(['i.name' => 'plan_item', 'i.code' => array_values($itemIds)])
        ->all() as $r) {
        $itemMap[$r['code']] = $r;
    }
}

// map ผู้ขอ (emp_id -> ชื่อ)
$empIds = array_filter(ArrayHelper::getColumn($models, 'emp_id'));
$empMap = $empIds
    ? ArrayHelper::map(Employees::find()->where(['id' => array_values($empIds)])->all(), 'id', 'fullname')
    : [];

$statusMeta = [
    'submit'  => ['label' => 'รออนุมัติ', 'class' => 'bg-warning text-dark'],
    'approve' => ['label' => 'อนุมัติ', 'class' => 'bg-success'],
    'reject'  => ['label' => 'ไม่อนุมัติ', 'class' => 'bg-danger'],
];

$tabs = [
    'submit'  => 'รออนุมัติ',
    'approve' => 'อนุมัติแล้ว',
    'reject'  => 'ไม่อนุมัติ',
    'all'     => 'ทั้งหมด',
];

$groupLabels = ['department' => 'หน่วยงาน', 'parcel' => 'พัสดุ', 'personnel' => 'บุคลากร', 'expenses' => 'ค่าใช้สอย'];
$group = $group ?? 'all';
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body mb-0"><i class="fa-solid fa-clipboard-check me-2"></i><?= $this->title ?></h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'approve']) ?>
<?php $this->endBlock(); ?>

<?php if ($flash = Yii::$app->session->getFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= Html::encode($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <ul class="nav nav-pills">
        <?php foreach ($tabs as $key => $label): ?>
            <li class="nav-item">
                <a class="nav-link <?= $status === $key ? 'active' : '' ?>" href="<?= Url::to(['index', 'thai_year' => $thaiYear, 'status' => $key, 'group' => $group]) ?>">
                    <?= $label ?>
                    <?php if (isset($counts[$key]) && $counts[$key] > 0): ?>
                        <span class="badge rounded-pill bg-light text-dark ms-1"><?= $counts[$key] ?></span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <form method="get" class="d-flex align-items-center gap-2">
        <input type="hidden" name="status" value="<?= Html::encode($status) ?>">
        <label class="mb-0 fw-medium">ปีงบประมาณ</label>
        <select name="thai_year" class="form-select w-auto" onchange="this.form.submit()">
            <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= (int) $y === $thaiYear ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
        <label class="mb-0 fw-medium ms-2">ประเภท</label>
        <select name="group" class="form-select w-auto" onchange="this.form.submit()">
            <option value="all" <?= $group === 'all' ? 'selected' : '' ?>>ทุกประเภท</option>
            <?php foreach ($groupLabels as $g => $lb): ?>
                <option value="<?= $g ?>" <?= $group === $g ? 'selected' : '' ?>><?= $lb ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ประเภท</th>
                    <th>หน่วยงาน</th>
                    <th>รายการ</th>
                    <th>ผู้ขอ</th>
                    <th class="text-end">ยอดรวม (บาท)</th>
                    <th class="text-center">สถานะ / ดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($models)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">ไม่มีรายการในสถานะนี้</td></tr>
                <?php endif; ?>
                <?php foreach ($models as $m): ?>
                    <?php
                    $it = $itemMap[$m->plan_item_id] ?? null;
                    $st = $statusMeta[$m->status] ?? ['label' => $m->status, 'class' => 'bg-secondary'];
                    $dj = is_array($m->data_json) ? $m->data_json : (json_decode((string) $m->data_json, true) ?: []);
                    ?>
                    <tr>
                        <td><span class="badge bg-primary bg-opacity-25 text-dark"><?= $groupLabels[$m->plan_group_id] ?? Html::encode($m->plan_group_id) ?></span></td>
                        <td><?= Html::encode($m->departmentName()) ?></td>
                        <td>
                            <div class="fw-semibold"><?= Html::encode($it['item'] ?? $m->plan_item_id ?? '-') ?></div>
                            <?php if (!empty($it['cat'])): ?><small class="text-muted"><?= Html::encode($it['cat']) ?></small><?php endif; ?>
                            <?php if (!empty($m->description)): ?><div class="small text-muted"><?= Html::encode($m->description) ?></div><?php endif; ?>
                        </td>
                        <td><?= Html::encode($empMap[$m->emp_id] ?? '-') ?></td>
                        <td class="text-end"><?= number_format((float) $m->order_price, 2) ?></td>
                        <td class="text-center">
                            <?php if ($m->status === 'submit'): ?>
                                <div class="d-flex gap-1 justify-content-center">
                                    <?= Html::beginForm(['approve', 'id' => $m->id], 'post') ?>
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fa-solid fa-check me-1"></i>อนุมัติ</button>
                                    <?= Html::endForm() ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-reject"
                                        data-id="<?= $m->id ?>"
                                        data-label="<?= Html::encode(($it['item'] ?? '') . ' — ' . $m->departmentName()) ?>">
                                        <i class="fa-solid fa-xmark me-1"></i>ไม่อนุมัติ
                                    </button>
                                </div>
                            <?php else: ?>
                                <span class="badge <?= $st['class'] ?>"><?= $st['label'] ?></span>
                                <?php if ($m->status === 'reject' && !empty($dj['reject_reason'])): ?>
                                    <div class="small text-danger mt-1"><i class="fa-solid fa-comment-dots me-1"></i><?= Html::encode($dj['reject_reason']) ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal ไม่อนุมัติ (ใส่เหตุผล) -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <?= Html::beginForm(['reject'], 'post', ['id' => 'reject-form']) ?>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ไม่อนุมัติแผน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2 text-muted small" id="reject-label"></p>
                <label class="form-label">เหตุผล (แจ้งให้หน่วยงานทราบ)</label>
                <textarea name="reason" class="form-control" rows="3" placeholder="ระบุเหตุผลที่ไม่อนุมัติ"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-danger">ยืนยันไม่อนุมัติ</button>
            </div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>

<?php
$rejectUrlBase = Url::to(['reject']);
$js = <<<JS
(function(){
    var modalEl = document.getElementById('rejectModal');
    var modal = new bootstrap.Modal(modalEl);
    var form = document.getElementById('reject-form');
    document.querySelectorAll('.btn-reject').forEach(function(btn){
        btn.addEventListener('click', function(){
            form.action = '$rejectUrlBase?id=' + this.dataset.id;
            document.getElementById('reject-label').textContent = this.dataset.label || '';
            form.querySelector('textarea[name=reason]').value = '';
            modal.show();
        });
    });
})();
JS;
$this->registerJs($js);
?>
