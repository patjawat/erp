<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\hr\models\IdpPlan;
use app\components\widgets\DataSummaryWidget;

$this->title = 'IDP Management';
echo $this->render('_styles');
echo $this->render('@app/modules/hr/views/workforce/_styles');
$this->beginBlock('page-title'); ?>
<div><h4 class="fw-semibold mb-1"><?= Html::encode($this->title) ?></h4><div class="text-muted small">กำหนดรอบและติดตามแผนพัฒนารายบุคคล</div></div>
<?php $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/hr/menu', ['active' => 'idp']); $this->endBlock();
$models = $dataProvider->getModels();
?>
<style>
.idp-page{padding:1rem 1rem 2rem}
.idp-library{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:10px;box-shadow:0 1px 2px rgba(15,23,42,.04);overflow:hidden}
.idp-library__filter{padding:1rem 1.1rem;border-bottom:1px solid rgba(15,23,42,.08);background:#f7f9fc}
.idp-library__filter label{display:block;font-size:.8rem;font-weight:600;color:#4a5568;margin-bottom:.25rem}
.idp-library__filter .form-control,.idp-library__filter .form-select{min-height:42px;border-color:rgba(15,23,42,.14);border-radius:8px}
.idp-library__footer{padding:.75rem 1rem;border-top:1px solid rgba(15,23,42,.08);background:#f7f9fc}
.idp-mobile-item{display:block;padding:.85rem;border-bottom:1px solid rgba(15,23,42,.08);text-decoration:none;color:inherit}
.idp-mobile-item:last-child{border-bottom:0}
[data-bs-theme="dark"] .idp-library{background:var(--bs-body-bg)}
[data-bs-theme="dark"] .idp-library__filter,[data-bs-theme="dark"] .idp-library__footer{background:var(--bs-tertiary-bg)}
@media(max-width:991.98px){.idp-page{padding:.75rem}.idp-library__desktop{display:none}}
@media(min-width:992px){.idp-library__mobile{display:none}}
</style>
<div class="idp-shell idp-page" id="idp-management">
    <?= $this->render('@app/modules/hr/views/workforce/_menu', ['active' => 'idp']) ?>

    <?= $this->render('@app/modules/hr/views/_kpi_cards', [
        'title' => 'IDP',
        'subtitle' => 'ภาพรวมแผนพัฒนารายบุคคล',
        'cards' => [
            ['label' => 'บุคลากร', 'value' => array_sum($counts), 'icon' => 'bi-people-fill', 'color' => 'primary'],
            ['label' => 'รอหัวหน้าเห็นชอบ', 'value' => (int)($counts['submitted'] ?? 0), 'icon' => 'bi-person-check', 'color' => 'info'],
            ['label' => 'รอ HR เปิดบันทึก', 'value' => (int)($counts['approved'] ?? 0), 'icon' => 'bi-folder2-open', 'color' => 'warning'],
            ['label' => 'รอปิดรอบ', 'value' => (int)($counts['assessment'] ?? 0), 'icon' => 'bi-flag', 'color' => 'success'],
        ],
    ]) ?>

    <div class="idp-library">
        <div class="idp-library__filter">
            <form method="get">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label for="idp-cycle">รอบ IDP</label>
                        <select id="idp-cycle" name="cycle_id" class="form-select">
                            <?php foreach ($cycles as $item): ?><option value="<?= $item->id ?>" <?= $cycle && $cycle->id == $item->id ? 'selected' : '' ?>><?= Html::encode($item->title) ?></option><?php endforeach ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="idp-q">ค้นหาบุคลากร</label>
                        <input id="idp-q" name="q" value="<?= Html::encode($q) ?>" class="form-control" placeholder="ชื่อ นามสกุล หรือรหัส">
                    </div>
                    <div class="col-md-2">
                        <label for="idp-status">สถานะ</label>
                        <select id="idp-status" name="status" class="form-select">
                            <option value="">ทุกสถานะ</option>
                            <?php foreach (IdpPlan::statusOptions() as $key => $label): ?><option value="<?= $key ?>" <?= $status === $key ? 'selected' : '' ?>><?= Html::encode($label) ?></option><?php endforeach ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>กรองข้อมูล</button>
                        <?= Html::a('<i class="bi bi-calendar-plus me-1"></i>สร้างรอบ', ['cycle'], ['class' => 'btn btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?>
                        <?php if ($cycle): ?><?= Html::a('แก้ไขรอบ', ['cycle', 'id' => $cycle->id], ['class' => 'btn btn-outline-secondary open-modal', 'data-size' => 'modal-lg']) ?><?php endif ?>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($models): ?>
        <div class="idp-library__desktop">
            <table class="table table-hover align-middle idp-table">
                <thead><tr><th>บุคลากร</th><th>รอบ IDP</th><th>เป้าหมาย</th><th>ความก้าวหน้า</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead>
                <tbody>
                <?php foreach ($models as $plan): ?>
                    <tr>
                        <td><div class="fw-semibold"><?= Html::encode($plan->employee?->fullname ?? '-') ?></div><div class="text-muted small"><?= Html::encode($plan->employee?->departmentName() ?? '-') ?></div></td>
                        <td><?= Html::encode($plan->cycle?->title ?? '-') ?></td>
                        <td><?= count($plan->goals) ?> เป้าหมาย</td>
                        <td style="min-width:130px"><div class="d-flex align-items-center gap-2"><div class="idp-progress flex-grow-1"><span style="width:<?= min(100, (float)$plan->progress_percent) ?>%"></span></div><span class="small"><?= (int)$plan->progress_percent ?>%</span></div></td>
                        <td><span class="idp-status idp-status--<?= Html::encode($plan->status) ?>"><?= Html::encode($plan->statusLabel) ?></span></td>
                        <td class="text-end"><?= Html::a('เปิดแผน', ['employee', 'emp_id' => $plan->emp_id], ['class' => 'btn btn-sm btn-outline-primary', 'data-pjax' => '0']) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <div class="idp-library__mobile">
            <?php foreach ($models as $plan): ?>
                <a class="idp-mobile-item" href="<?= Url::to(['employee', 'emp_id' => $plan->emp_id]) ?>" data-pjax="0">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <strong><?= Html::encode($plan->employee?->fullname ?? '-') ?></strong>
                        <span class="idp-status idp-status--<?= Html::encode($plan->status) ?>"><?= Html::encode($plan->statusLabel) ?></span>
                    </div>
                    <div class="text-muted small mt-1"><?= Html::encode($plan->employee?->departmentName() ?? '-') ?> · <?= count($plan->goals) ?> เป้าหมาย · ก้าวหน้า <?= (int)$plan->progress_percent ?>%</div>
                </a>
            <?php endforeach ?>
        </div>

        <div class="idp-library__footer"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div>
        <?php else: ?>
        <div class="idp-empty"><h2>ยังไม่มีแผนในรอบนี้</h2><p>เมื่อพนักงานเริ่มจัดทำ IDP รายการจะปรากฏที่นี่โดยอัตโนมัติ</p></div>
        <?php endif ?>
    </div>
</div>
