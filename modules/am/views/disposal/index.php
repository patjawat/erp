<?php

use app\components\AppHelper;
use app\modules\am\models\AssetDisposal;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDisposalSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ใบขอจำหน่ายครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
  <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
<i data-lucide="file-check" class="me-2"></i>
    <?= $this->title ?>
  </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'work']) ?>
<?php $this->endBlock(); ?>

<div class="asset-disposal-index">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-semibold text-body mb-1"><?= Html::encode($this->title) ?></h4>
            <div class="text-muted small">สร้างใบขอจำหน่าย เลขที่เช่น <code>จน.002/2568</code></div>
        </div>
        <?= Html::a('สร้างใบขอจำหน่าย', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'disposal_no')->textInput(['placeholder' => 'เลขที่ใบขอจำหน่าย'])->label('เลขที่') ?>
                </div>
                <div class="col-12 col-md-2">
                    <?= $form->field($searchModel, 'fiscal_year')->textInput(['placeholder' => 'ปีงบประมาณ'])->label('ปีงบประมาณ') ?>
                </div>
                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'status')->dropDownList(AssetDisposal::statusList(), ['prompt' => '-- สถานะทั้งหมด --'])->label('สถานะ') ?>
                </div>
                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'q')->textInput(['placeholder' => 'ค้นหาจากเลขที่/ผู้รับผิดชอบ/วิธีจำหน่าย'])->label('คำค้น') ?>
                </div>
                <div class="col-12 col-md-1">
                    <?= Html::submitButton('ค้นหา', ['class' => 'btn btn-outline-primary w-100']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => null,
                'summary' => 'แสดง {begin}-{end} จาก {totalCount} รายการ',
                'tableOptions' => ['class' => 'table table-bordered table-hover align-middle mb-0'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'disposal_no',
                        'label' => 'เลขที่',
                        'format' => 'raw',
                        'value' => fn ($model) => Html::a(Html::encode($model->disposal_no), ['view', 'id' => $model->id], ['class' => 'fw-semibold']),
                    ],
                    [
                        'attribute' => 'fiscal_year',
                        'label' => 'ปีงบประมาณ',
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'department',
                        'label' => 'หน่วยงาน',
                        'value' => fn ($model) => $model->departmentRef->name ?? '-',
                    ],
                    [
                        'attribute' => 'disposal_date',
                        'label' => 'วันที่',
                        'value' => fn ($model) => $model->disposal_date ? AppHelper::convertToThai($model->disposal_date) : '-',
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'disposal_method',
                        'label' => 'วิธีจำหน่าย',
                        'value' => fn ($model) => $model->disposal_method ?: '-',
                    ],
                    [
                        'attribute' => 'responsible_emp_id',
                        'label' => 'ผู้รับผิดชอบ',
                        'value' => fn ($model) => $model->responsibleLabel,
                    ],
                    [
                        'label' => 'รายการ',
                        'value' => fn ($model) => count($model->disposalItems),
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'status',
                        'label' => 'สถานะ',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $class = match ($model->status) {
                                AssetDisposal::STATUS_APPROVED => 'bg-info text-dark',
                                AssetDisposal::STATUS_DONE => 'bg-success',
                                default => 'bg-warning text-dark',
                            };
                            return '<span class="badge ' . $class . '">' . Html::encode($model->getStatusLabel()) . '</span>';
                        },
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {update} {delete}',
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
