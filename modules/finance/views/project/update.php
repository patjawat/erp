<?php

use app\modules\finance\models\FinanceCashProject;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var FinanceCashProject $model */

$this->title = 'แก้ไขโครงการ';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'โครงการเงินบำรุง', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'แก้ไข';

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-diagram-3 fs-4"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'budget']);
$this->endBlock();
?>

<?= $this->render('@app/modules/finance/views/budget/_menu', ['active' => 'project']) ?>

<div class="card shadow-sm">
    <div class="card-body">
        <?= Html::beginForm('', 'post') ?>
        <div class="row g-3">
            <div class="col-md-2"><label class="form-label small mb-1">รหัส</label><?= Html::activeTextInput($model, 'code', ['class' => 'form-control']) ?></div>
            <div class="col-md-6"><label class="form-label small mb-1">ชื่อโครงการ</label><?= Html::activeTextInput($model, 'name', ['class' => 'form-control']) ?></div>
            <div class="col-md-2"><label class="form-label small mb-1">ปีงบ</label><?= Html::activeTextInput($model, 'fiscal_year', ['class' => 'form-control', 'type' => 'number']) ?></div>
            <div class="col-md-2"><label class="form-label small mb-1">แหล่งเงิน</label><?= Html::activeDropDownList($model, 'fund_source', FinanceCashProject::fundSourceOptions(), ['class' => 'form-select', 'prompt' => '—']) ?></div>
            <div class="col-md-3"><label class="form-label small mb-1">วงเงินโครงการ</label><?= Html::activeTextInput($model, 'budget_amount', ['class' => 'form-control text-end', 'inputmode' => 'decimal']) ?></div>
            <div class="col-md-3"><label class="form-label small mb-1">ใช้งาน</label><?= Html::activeDropDownList($model, 'is_active', [1 => 'ใช้งาน', 0 => 'ปิด'], ['class' => 'form-select']) ?></div>
            <div class="col-12"><label class="form-label small mb-1">หมายเหตุ</label><?= Html::activeTextInput($model, 'note', ['class' => 'form-control']) ?></div>
        </div>
        <?php if ($model->hasErrors()): ?><div class="alert alert-danger mt-3 mb-0 small"><?= implode('<br>', $model->getErrorSummary(true)) ?></div><?php endif; ?>
        <div class="d-flex gap-2 mt-3">
            <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึก', ['class' => 'btn btn-success']) ?>
            <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-outline-secondary">ยกเลิก</a>
            <?= Html::beginForm(['delete', 'id' => $model->id], 'post', ['class' => 'ms-auto', 'onsubmit' => "return confirm('ลบโครงการนี้? (รายการที่ผูกจะถูกปลดออก)')"]) ?>
            <?= Html::submitButton('<i class="bi bi-trash me-1"></i>ลบ', ['class' => 'btn btn-outline-danger']) ?>
            <?= Html::endForm() ?>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>
