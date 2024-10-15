<?php
use app\modules\sm\models\SupVendor;
use kartik\grid\GridView;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\SupVendorSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ผู้แทนจำหน่าย';
$this->params['breadcrumbs'][] = ['label' => 'บริหารพัสดุ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'sm-container']); ?>

<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex justify-content-start">
            <?= app\components\AppHelper::Btn([
                'url' => ['create'],
                'modal' => true,
                'size' => 'lg',
            ]) ?>

        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-list-ul"></i>', ['#', 'view' => 'list'], ['class' => 'btn btn-outline-primary',
                        'title' => 'แสกงผลแบบรายการ',
                        'data' => [
                            'bs-placement' => 'top',
                            'bs-toggle' => 'tooltip',
                        ]]) ?>
            <?= Html::a('<i class="bi bi-grid"></i>', ['#', 'view' => 'grid'], ['class' => 'btn btn-outline-primary',
                    'title' => 'แสดงผลแบบกลุ่ม',
                    'data' => [
                        'bs-placement' => 'top',
                        'bs-toggle' => 'tooltip',
                    ]]) ?>
            <?= Html::a('<i class="fa-solid fa-file-import me-1"></i>', ['/sm/vendor/import-csv'], [
                'class' => 'btn btn-outline-primary',
                'title' => 'นำเข้าข้อมูลจากไฟล์ .csv',
                'data' => [
                    'bs-placement' => 'top',
                    'bs-toggle' => 'tooltip',
                ],
            ]) ?>
        </div>

    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => '{items}',
            'columns' => [
                [
                    'class' => 'kartik\grid\SerialColumn',
                    'contentOptions' => ['class' => 'kartik-sheet-style'],
                    'width' => '36px',
                    'pageSummary' => 'Total',
                    'pageSummaryOptions' => ['colspan' => 6],
                    'header' => '',
                    'headerOptions' => ['class' => 'kartik-sheet-style'],
                ],
                [
                    'attribute' => 'title',
                    'header' => 'รายการ',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return html::a($model->title, ['view', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-lg']]);;
                    },
                ],
                [
                    'attribute' => 'address',
                    'header' => 'ที่อยู่',
                    'width' => '400px',
                    'value' => function ($model) {
                        return $model->address;
                    },
                ],
                [
                    'attribute' => 'code',
                    'header' => 'เลขประจำตัวผู้เสียภาษี',
                    'width' => '200px',
                    'vAlign' => 'middle',
                    'value' => function ($model) {
                        return $model->code;
                    },
                ],
                [
                    'attribute' => 'contact_name',
                    'header' => 'ชื่อผู้ติดต่อ',
                    'width' => '300px',
                    'value' => function ($model) {
                        return $model->contact_name;
                    },
                ],
                [
                    'attribute' => 'phone',
                    'header' => 'โทรศัพท์',
                    'width' => '250px',
                    'value' => function ($model) {
                        return $model->phone;
                    },
                ],
                [
                    'attribute' => 'email',
                    'header' => 'อีเมล',
                    'width' => '200px',
                    'value' => function ($model) {
                        return $model->email;
                    },
                ],
            ],
        ]); ?>

    </div>
</div>

<div class="d-flex justify-content-center">

    <div class="text-muted">
        <?= LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'firstPageLabel' => 'หน้าแรก',
            'lastPageLabel' => 'หน้าสุดท้าย',
            'options' => [
                'listOptions' => 'pagination pagination-sm',
                'class' => 'pagination-sm',
            ],
        ]); ?>
    </div>
</div>

<?php Pjax::end(); ?>