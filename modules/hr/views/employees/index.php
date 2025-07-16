<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use yii\bootstrap5\LinkPager;
use app\components\SiteHelper;
use app\components\EmployeeHelper;
use app\modules\hr\models\Employees;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'บุคลากร';
$this->params['breadcrumbs'][] = $this->title;
// ออกแบบ
// https://www.canva.com/ai/code/thread/4c1031df-3a56-4eff-8b71-df1a519ca530
?>

<?php Pjax::begin(['id' => 'hr-container', 'enablePushState' => true, 'timeout' => 50000]); ?>

<style>
#w1-cols-list {
    padding: 10px;
}
</style>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
จำนวนทั้งหมด <span id="showTotalCount"> <?= $dataProvider->getTotalCount() ?>
</span>
รายการ
<?= $notStatus > 0 ? Html::a('| ' . AppHelper::MsgWarning('ไม่ระบุตำแหน่ง') . ' ' . $notStatus . ' คน', ['/hr/employees/', 'not-status' => true]) : '' ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/hr/views/employees/menu',['active' => 'employees'])?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-header bg-primary-gradient text-white d-flex justify-content-between">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
  <div>
                    <?= Html::a('<i class="bi bi-list-ul"></i>', ['/setting/set-view', 'view' => 'list'], ['class' => 'btn btn-outline-light setview']) ?>
                    <?= Html::a('<i class="bi bi-grid"></i>', ['/setting/set-view', 'view' => 'grid'], ['class' => 'btn btn-outline-light setview']) ?>
                </div>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>


<?php if (SiteHelper::getDisplay() == 'list'): ?>

<div class="card">

    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทะเบียนบุคลากร
                <span class="badge text-bg-light">
                    <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
            </h6>
            <div class="d-flex justify-content-between gap-3">
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่ ', ['/hr/employees/create'], ['class' => 'btn btn-light shadow open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                <button id="download-button" class="btn btn-success shadow"><i
                        class="fa-solid fa-file-export me-1"></i>Excel</button>
              
            </div>
        </div>
    </div>
    <div class="card-body">
        <?= $this->render('display/list', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]); ?>
    </div>
</div>


<?php else: ?>

<div class="d-flex justify-content-between mb-3">
    <h6>
        <i class="bi bi-ui-checks"></i> ทะเบียนบุคลากร
        <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ
    </h6>
    <div>
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่ ', ['/hr/employees/create'], ['class' => 'btn btn-primary shadow open-modal', 'data' => ['size' => 'modal-xl']]) ?>
        <button id="download-button" class="btn btn-success shadow"><i
                class="fa-solid fa-file-export me-1"></i>Excel</button>

    </div>
</div>

<?= $this->render('display/grid', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]); ?>

<?php endif ?>


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
<span id="totalCount" class="d-none"><?= $dataProvider->getTotalCount(); ?></span>

<?php
$url = Url::to(['/hr/employees/export-excel']);
$js = <<< JS

        $('#hr-container').on('pjax:success', function() {
             $('body').find('#total-count').text(\$('#totalCount').text());
        });

        \$("body").on("click", "#download-button", function (e) {
            var monthName = \$('#stockeventsearch-receive_month').find(':selected').text();
            var year = \$('#stockeventsearch-thai_year').find(':selected').text();
            var form = $('#employees-filter')
            \$.ajax({
                url: '$url', // Adjust to match your controller and action URL
                method: 'GET',
                data: form.serialize(),
                xhrFields: {
                    responseType: 'blob' // Important for handling binary data
                },
                beforeSend: function(){
                    // beforLoadModal();
                },
                success: function(data) {
                  const modal = bootstrap.Modal.getInstance(document.getElementById('main-modal'));
                    var monthName = \$('#stockeventsearch-receive_month').find(':selected').text();
                    var filename = 'ข้อมูลบุคลากร'+'.xlsx';
                    const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    link.click();
                },
                error: function() {
                    alert('File could not be downloaded.');
                }
            });
        });

        

    JS;
    $this->registerJS($js,View::POS_END)

?>
<?php Pjax::end(); ?>