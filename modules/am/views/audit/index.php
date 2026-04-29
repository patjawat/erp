<?php

use app\components\AppHelper;
use app\modules\am\models\AssetAudit;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetAuditSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ตรวจนับพัสดุประจำปี';
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

<div class="audit-index">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <div class="text-muted small">ตามระเบียบข้อ 209 ตรวจนับพัสดุอย่างน้อยปีละ 1 ครั้ง ก่อนสิ้นปีงบประมาณ</div>
        </div>
        <?= Html::a('สร้างใบตรวจนับ', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'audit_no')->textInput(['placeholder' => 'เลขที่ตรวจนับ'])->label('เลขที่ตรวจนับ') ?>
                </div>
                <div class="col-12 col-md-2">
                    <?= $form->field($searchModel, 'fiscal_year')->textInput(['placeholder' => 'ปีงบประมาณ'])->label('ปีงบประมาณ') ?>
                </div>
                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'status')->dropDownList(AssetAudit::statusList(), ['prompt' => '-- สถานะทั้งหมด --'])->label('สถานะ') ?>
                </div>
                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'q')->textInput(['placeholder' => 'ค้นหาจากเลขที่/ผู้ตรวจนับ/หมายเหตุ'])->label('คำค้น') ?>
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
                        'attribute' => 'audit_no',
                        'label' => 'เลขที่ตรวจนับ',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a(Html::encode($model->audit_no), ['view', 'id' => $model->id], ['class' => 'fw-semibold']);
                        },
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
                        'attribute' => 'audit_date',
                        'label' => 'วันที่ตรวจนับ',
                        'value' => fn ($model) => $model->audit_date ? AppHelper::convertToThai($model->audit_date) : '-',
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'emp_id',
                        'label' => 'ผู้ตรวจนับ',
                        'value' => fn ($model) => $model->auditorLabel,
                    ],
                    [
                        'label' => 'รายการ',
                        'value' => fn ($model) => count($model->auditItems),
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'status',
                        'label' => 'สถานะ',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $class = match ($model->status) {
                                AssetAudit::STATUS_ACTIVE => 'bg-success',
                                AssetAudit::STATUS_CLOSED => 'bg-secondary',
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
