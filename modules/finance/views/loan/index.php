<?php

use app\modules\finance\models\FinanceLoan;
use app\modules\finance\models\FinanceLoanExpenseType;
use app\modules\finance\models\FinanceLoanSearch;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var FinanceLoanSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $summary */

$this->title = 'ทะเบียนเงินยืม';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2"><i class="bi bi-person-vcard fs-4" aria-hidden="true"></i><h4 class="mb-0"><?= Html::encode($this->title) ?></h4></div>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ควบคุมคำขอ การรับเงิน วันครบกำหนด และการส่งใช้<?php $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/finance/menu', ['active' => 'loan']); $this->endBlock();

$isFiltered = (bool) array_filter([$searchModel->q, $searchModel->status, $searchModel->fiscal_year, $searchModel->expense_type_id, $searchModel->due_state]);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex flex-wrap gap-2">
        <?= Html::a('<i class="bi bi-plus-circle me-1"></i> เพิ่มใบยืม', ['create'], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="bi bi-hourglass-split me-1"></i> ลูกหนี้ค้าง', ['outstanding'], ['class' => 'btn btn-outline-danger']) ?>
        <?= Html::a('<i class="bi bi-table me-1"></i> ทะเบียนคุม', ['register'], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::a('<i class="bi bi-printer me-1"></i> พิมพ์เอกสาร', ['/finance/loan-document/index'], ['class' => 'btn btn-outline-primary']) ?>
        <?= Html::a('<i class="bi bi-file-earmark-arrow-up me-1"></i> นำเข้าจากไฟล์', ['import'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
</div>

<section class="card border mb-3" aria-label="สรุปทะเบียนเงินยืม">
    <div class="card-body py-3">
        <div class="row g-3 align-items-center">
            <div class="col-6 col-lg">
                <div class="text-body-secondary small"><?= $isFiltered ? 'ตามที่กรอง' : 'ทั้งหมด' ?></div>
                <div class="fs-5 fw-semibold"><?= number_format($summary['count']) ?> <small class="fs-6 fw-normal">ใบ</small></div>
            </div>
            <div class="col-6 col-lg border-start">
                <div class="text-body-secondary small">ยอดเงินยืม</div>
                <div class="fs-5 fw-semibold font-monospace"><?= number_format($summary['approved'], 2) ?></div>
            </div>
            <div class="col-6 col-lg border-start">
                <div class="text-body-secondary small">ยอดคงเหลือ</div>
                <div class="fs-5 fw-semibold font-monospace"><?= number_format($summary['outstanding'], 2) ?></div>
            </div>
            <div class="col-6 col-lg border-start">
                <div class="text-body-secondary small">เกินกำหนด</div>
                <div class="fs-5 fw-semibold <?= $summary['overdue'] ? 'text-danger-emphasis' : '' ?>"><?= number_format($summary['overdue']) ?> <small class="fs-6 fw-normal">ใบ</small></div>
            </div>
        </div>
    </div>
</section>

<section class="card border" aria-labelledby="loan-list-heading">
    <div class="card-header bg-body">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0" id="loan-list-heading">รายการเงินยืม</h5>
            <span class="text-body-secondary small"><?= number_format($dataProvider->getTotalCount()) ?> ใบ</span>
        </div>
        <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index'], 'options' => ['class' => 'row g-2 align-items-end']]); ?>
        <div class="col-12 col-lg-4"><?= $form->field($searchModel, 'q')->textInput(['placeholder' => 'เลขที่สัญญา ผู้ยืม วัตถุประสงค์ หรือเลขที่บันทึก']) ?></div>
        <div class="col-6 col-lg-2"><?= $form->field($searchModel, 'fiscal_year')->dropDownList(FinanceLoanSearch::fiscalYearOptions(), ['prompt' => 'ทุกปี']) ?></div>
        <div class="col-6 col-lg-2"><?= $form->field($searchModel, 'status')->dropDownList(FinanceLoan::statusOptions(), ['prompt' => 'ทุกสถานะ']) ?></div>
        <div class="col-6 col-lg-2"><?= $form->field($searchModel, 'due_state')->dropDownList(FinanceLoanSearch::dueStateOptions(), ['prompt' => 'ทุกกำหนดเวลา']) ?></div>
        <div class="col-6 col-lg-2"><?= $form->field($searchModel, 'expense_type_id')->dropDownList(FinanceLoanExpenseType::options(), ['prompt' => 'ทุกประเภท']) ?></div>
        <div class="col-12 col-lg-2 pb-3 d-grid"><?= Html::submitButton('<i class="bi bi-search me-1"></i> ค้นหา', ['class' => 'btn btn-outline-primary']) ?></div>
        <?php if ($isFiltered): ?>
        <div class="col-12 col-lg-2 pb-3 d-grid"><?= Html::a('ล้างตัวกรอง', ['index'], ['class' => 'btn btn-link']) ?></div>
        <?php endif; ?>
        <?php ActiveForm::end(); ?>
    </div>

    <div class="table-responsive d-none d-lg-block">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n<div class=\"card-footer bg-body d-flex justify-content-between align-items-center flex-wrap gap-2\">{summary}{pager}</div>",
            'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
            'columns' => [
                [
                    'attribute' => 'contract_no',
                    'format' => 'raw',
                    'value' => static fn(FinanceLoan $m) => Html::a(Html::encode($m->contract_no), ['view', 'id' => $m->id], ['class' => 'fw-semibold text-nowrap'])
                        . '<div class="small text-body-secondary text-nowrap">' . Html::encode($m->expenseType->name ?? '') . '</div>',
                ],
                ['attribute' => 'borrower_name', 'contentOptions' => ['class' => 'text-nowrap']],
                ['attribute' => 'purpose', 'label' => 'วัตถุประสงค์'],
                ['attribute' => 'due_at', 'label' => 'ครบกำหนด', 'format' => ['date', 'php:d/m/Y'], 'contentOptions' => ['class' => 'text-nowrap']],
                ['attribute' => 'approved_amount', 'label' => 'ยอดยืม', 'format' => ['decimal', 2], 'headerOptions' => ['class' => 'text-end'], 'contentOptions' => ['class' => 'text-end text-nowrap font-monospace']],
                ['attribute' => 'outstanding_amount', 'label' => 'คงเหลือ', 'format' => ['decimal', 2], 'headerOptions' => ['class' => 'text-end'], 'contentOptions' => ['class' => 'text-end text-nowrap fw-semibold font-monospace']],
                ['attribute' => 'status', 'format' => 'raw', 'value' => static fn(FinanceLoan $m) => Html::tag('span', Html::encode($m->statusLabel()), ['class' => 'badge bg-secondary-subtle text-secondary-emphasis'])],
                ['label' => 'กำหนดส่งใช้', 'format' => 'raw', 'value' => static fn(FinanceLoan $m) => Html::tag('span', Html::encode($m->dueLabel()), ['class' => 'badge ' . $m->dueBadgeClass()])],
                ['label' => '', 'format' => 'raw', 'value' => static fn(FinanceLoan $m) => Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['update', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-secondary text-nowrap'])],
            ],
            'emptyText' => 'ยังไม่มีใบยืมในทะเบียน เริ่มด้วยปุ่ม “เพิ่มใบยืม”',
            'emptyTextOptions' => ['class' => 'text-center text-body-secondary py-5'],
        ]) ?>
    </div>

    <ul class="list-group list-group-flush d-lg-none" role="list">
        <?php foreach ($dataProvider->getModels() as $model): ?>
        <li class="list-group-item py-3">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <div>
                    <?= Html::a(Html::encode($model->contract_no), ['view', 'id' => $model->id], ['class' => 'fw-semibold']) ?>
                    <div class="text-body-secondary small"><?= Html::encode($model->borrower_name) ?></div>
                </div>
                <span class="badge <?= $model->dueBadgeClass() ?>"><?= Html::encode($model->dueLabel()) ?></span>
            </div>
            <p class="small mb-2"><?= Html::encode($model->purpose) ?></p>
            <div class="d-flex justify-content-between align-items-end gap-2">
                <div class="small">
                    <span class="badge bg-secondary-subtle text-secondary-emphasis mb-1"><?= Html::encode($model->statusLabel()) ?></span>
                    <div class="text-body-secondary">ครบกำหนด <?= $model->due_at ? Yii::$app->formatter->asDate($model->due_at, 'php:d/m/Y') : 'ไม่ระบุ' ?></div>
                </div>
                <div class="text-end">
                    <div class="text-body-secondary small">คงเหลือ</div>
                    <div class="fw-semibold font-monospace"><?= number_format($model->outstanding_amount, 2) ?></div>
                </div>
            </div>
        </li>
        <?php endforeach; ?>
        <?php if (!$dataProvider->getModels()): ?>
        <li class="list-group-item text-center text-body-secondary py-5">ยังไม่มีใบยืมในทะเบียน</li>
        <?php endif; ?>
    </ul>
    <div class="card-footer bg-body d-lg-none"><?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?></div>
</section>
