<?php

use app\modules\finance\models\FinanceCashProject;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var FinanceCashProject $model */
/** @var FinanceCashProject[] $projects */

$this->title = 'โครงการเงินบำรุง / เงินนอกงบประมาณ';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
$canOperate = Yii::$app->user->can('financeOperate');
$money = fn ($v) => number_format((float) $v, 2);

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-diagram-3 fs-4"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('sub-title');
echo 'จำแนกเงินนอกงบประมาณตามโครงการ (บริจาค/อุดหนุน/เงินบำรุงเฉพาะโครงการ)';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'project']);
$this->endBlock();
?>

<?php if ($canOperate): ?>
<div class="card shadow-sm mb-3">
    <div class="card-header bg-body"><h6 class="mb-0"><i class="bi bi-plus-circle me-1"></i>สร้างโครงการ</h6></div>
    <div class="card-body">
        <?= Html::beginForm(['index'], 'post') ?>
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-2"><label class="form-label small mb-1">รหัส</label><?= Html::activeTextInput($model, 'code', ['class' => 'form-control form-control-sm']) ?></div>
            <div class="col-12 col-md-4"><label class="form-label small mb-1">ชื่อโครงการ</label><?= Html::activeTextInput($model, 'name', ['class' => 'form-control form-control-sm']) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">ปีงบ</label><?= Html::activeTextInput($model, 'fiscal_year', ['class' => 'form-control form-control-sm', 'type' => 'number']) ?></div>
            <div class="col-6 col-md-2"><label class="form-label small mb-1">แหล่งเงิน</label><?= Html::activeDropDownList($model, 'fund_source', FinanceCashProject::fundSourceOptions(), ['class' => 'form-select form-select-sm', 'prompt' => '—']) ?></div>
            <div class="col-6 col-md-2 d-grid"><button class="btn btn-success btn-sm"><i class="bi bi-save me-1"></i>สร้าง</button></div>
        </div>
        <?php if ($model->hasErrors()): ?><div class="text-danger small mt-2"><?= implode(' ', $model->getFirstErrors()) ?></div><?php endif; ?>
        <?= Html::endForm() ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-3">
    <?php if (!$projects): ?>
        <div class="col-12"><div class="card shadow-sm"><div class="card-body text-center text-body-secondary py-5"><i class="bi bi-diagram-3 fs-1 d-block mb-2"></i>ยังไม่มีโครงการ</div></div></div>
    <?php endif; ?>
    <?php foreach ($projects as $p): ?>
        <?php $inc = $p->incomeTotal(); $exp = $p->expenseTotal(); ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm <?= $p->is_active ? '' : 'opacity-75' ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <h6 class="mb-0 text-truncate"><?= $p->code ? '<span class="text-body-secondary">' . Html::encode($p->code) . '</span> ' : '' ?><?= Html::encode($p->name) ?></h6>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis text-nowrap ms-2"><?= $p->fiscal_year ?: '-' ?></span>
                    </div>
                    <div class="text-body-secondary small mb-2"><?= Html::encode($p->fundSourceLabel()) ?> · <?= $p->txnCount() ?> รายการ</div>
                    <div class="d-flex justify-content-between py-1 border-top"><span class="text-body-secondary small">รับ</span><strong class="text-success-emphasis"><?= $money($inc) ?></strong></div>
                    <div class="d-flex justify-content-between py-1"><span class="text-body-secondary small">จ่าย</span><strong class="text-danger"><?= $money($exp) ?></strong></div>
                    <div class="d-flex justify-content-between py-1"><span class="text-body-secondary small">คงเหลือ</span><strong><?= $money($inc - $exp) ?></strong></div>
                </div>
                <div class="card-footer bg-body d-flex gap-2">
                    <a href="<?= Url::to(['view', 'id' => $p->id]) ?>" class="btn btn-sm btn-outline-primary flex-fill"><i class="bi bi-link-45deg me-1"></i>ผูกรายการ</a>
                    <a href="<?= Url::to(['/finance/register/view', 'key' => 'fund_by_project', 'fiscal_year' => $p->fiscal_year, 'project_id' => $p->id]) ?>" class="btn btn-sm btn-outline-secondary" title="ทะเบียนคุม"><i class="bi bi-journal-check"></i></a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
