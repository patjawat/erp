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

$countAll    = AssetAudit::find()->count();
$countDraft  = AssetAudit::find()->where(['status' => AssetAudit::STATUS_DRAFT])->count();
$countActive = AssetAudit::find()->where(['status' => AssetAudit::STATUS_ACTIVE])->count();
$countClosed = AssetAudit::find()->where(['status' => AssetAudit::STATUS_CLOSED])->count();
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

    <!-- Header Bar -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <p class="text-muted small mb-0">
            <i class="bi bi-info-circle me-1"></i>
            ตามระเบียบข้อ 209 ต้องตรวจนับพัสดุอย่างน้อยปีละ 1 ครั้ง ก่อนสิ้นปีงบประมาณ
        </p>
        <?= Html::a('<i class="bi bi-plus-lg me-1"></i> สร้างใบตรวจนับ', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                        <i class="bi bi-archive fs-5 text-primary"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold text-primary lh-1"><?= $countAll ?></div>
                        <div class="text-muted small mt-1">ทั้งหมด</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                        <i class="bi bi-pencil-square fs-5 text-warning"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold text-warning lh-1"><?= $countDraft ?></div>
                        <div class="text-muted small mt-1">ฉบับร่าง</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                        <i class="bi bi-check2-circle fs-5 text-success"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold text-success lh-1"><?= $countActive ?></div>
                        <div class="text-muted small mt-1">ใช้งาน</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                        <i class="bi bi-lock fs-5 text-secondary"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold text-secondary lh-1"><?= $countClosed ?></div>
                        <div class="text-muted small mt-1">ปิดแล้ว</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Filter -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-0 pb-0 pt-3">
            <span class="fw-semibold text-muted small"><i class="bi bi-search me-1"></i> ค้นหาและกรอง</span>
        </div>
        <div class="card-body pt-2">
            <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'audit_no')->textInput(['placeholder' => 'เลขที่ตรวจนับ'])->label('เลขที่ตรวจนับ') ?>
                </div>
                <div class="col-6 col-md-2">
                    <?= $form->field($searchModel, 'fiscal_year')->textInput(['placeholder' => 'ปีงบประมาณ'])->label('ปีงบประมาณ') ?>
                </div>
                <div class="col-6 col-md-2">
                    <?= $form->field($searchModel, 'status')->dropDownList(AssetAudit::statusList(), ['prompt' => '-- ทุกสถานะ --'])->label('สถานะ') ?>
                </div>
                <div class="col-12 col-md-4">
                    <?= $form->field($searchModel, 'q')->textInput(['placeholder' => 'เลขที่ / ผู้ตรวจนับ / หมายเหตุ'])->label('คำค้น') ?>
                </div>
                <div class="col-12 col-md-1">
                    <?= Html::submitButton('<i class="bi bi-search"></i>', ['class' => 'btn btn-primary w-100', 'title' => 'ค้นหา']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => null,
                'summary' => '<div class="px-3 py-2 text-muted small border-bottom">แสดง {begin}-{end} จาก {totalCount} รายการ</div>',
                'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['class' => 'text-center', 'style' => 'width:50px'],
                        'contentOptions' => ['class' => 'text-center text-muted small'],
                    ],
                    [
                        'attribute' => 'audit_no',
                        'label' => 'เลขที่ตรวจนับ',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a(
                                '<i class="bi bi-file-earmark-text me-1 text-muted"></i>' . Html::encode($model->audit_no),
                                ['view', 'id' => $model->id],
                                ['class' => 'fw-semibold text-decoration-none']
                            );
                        },
                    ],
                    [
                        'attribute' => 'fiscal_year',
                        'label' => 'ปีงบประมาณ',
                        'contentOptions' => ['class' => 'text-center'],
                        'headerOptions' => ['class' => 'text-center'],
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
                        'headerOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'emp_id',
                        'label' => 'ผู้ตรวจนับ',
                        'value' => fn ($model) => $model->auditorLabel,
                    ],
                    [
                        'label' => 'รายการ',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $count = count($model->auditItems);
                            return '<span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">' . $count . '</span>';
                        },
                        'contentOptions' => ['class' => 'text-center'],
                        'headerOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'status',
                        'label' => 'สถานะ',
                        'format' => 'raw',
                        'value' => function ($model) {
                            [$cls, $icon] = match ($model->status) {
                                AssetAudit::STATUS_ACTIVE => ['bg-success', 'bi-check-circle-fill'],
                                AssetAudit::STATUS_CLOSED => ['bg-secondary', 'bi-lock-fill'],
                                default => ['bg-warning text-dark', 'bi-pencil-fill'],
                            };
                            return '<span class="badge ' . $cls . '"><i class="bi ' . $icon . ' me-1"></i>' . Html::encode($model->getStatusLabel()) . '</span>';
                        },
                        'contentOptions' => ['class' => 'text-center'],
                        'headerOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'จัดการ',
                        'template' => '{view} {update} {delete}',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center text-nowrap'],
                        'buttons' => [
                            'view' => fn ($url) => Html::a('<i class="bi bi-eye"></i>', $url, ['class' => 'btn btn-sm btn-outline-primary me-1', 'title' => 'ดูรายละเอียด']),
                            'update' => fn ($url) => Html::a('<i class="bi bi-pencil"></i>', $url, ['class' => 'btn btn-sm btn-outline-secondary me-1', 'title' => 'แก้ไข']),
                            'delete' => fn ($url, $model) => Html::a('<i class="bi bi-trash"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'title' => 'ลบ',
                                'data' => ['confirm' => 'ต้องการลบใบตรวจนับ ' . $model->audit_no . ' หรือไม่?', 'method' => 'post'],
                            ]),
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
