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
$this->title = 'ทะเบียนประวัติ';
$this->params['breadcrumbs'][] = $this->title;
// $user0 = Employees::find()->where(['user_id' => 0])->andWhere(['!=','id',1])->count('id');
// $user1 = Employees::find()->where(['>','user_id',0])->andWhere(['!=','id',1])->count('id');

?>
<?php Pjax::begin(['id' => 'title-container', 'timeout' => 500000]); ?>
<style>
    #w1-cols-list{
        padding:10px;
    }
</style>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
จำนวนทั้งหมด <span id="showTotalCount"> <?= $dataProvider->getTotalCount() ?>

</span>
รายการ <?= $notStatus > 0 ? Html::a('| ' . AppHelper::MsgWarning('ไม่ระบุตำแหน่ง') . ' ' . $notStatus . ' คน', ['/hr/employees/', 'not-status' => true]) : '' ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('menu') ?>
<?php $this->endBlock(); ?>


<?php Pjax::end(); ?>

<?php Pjax::begin(['id' => 'hr-container', 'enablePushState' => true, 'timeout' => 50000]); ?>

<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center align-items-center">
        <div class="d-flex justify-content-start gap-2">
            <?= app\components\AppHelper::Btn([
                'url' => ['create'],
                'modal' => true,
                'size' => 'lg',
            ]) ?>
        </div>
        <div>


        </div>
        <div class="d-flex gap-2">

            <?= $this->render('_search', ['model' => $searchModel]); ?>

            <?= Html::a('<i class="bi bi-list-ul"></i>', ['/setting/set-view', 'view' => 'list'], ['class' => 'btn btn-outline-primary setview']) ?>
            <?= Html::a('<i class="bi bi-grid"></i>', ['/setting/set-view', 'view' => 'grid'], ['class' => 'btn btn-outline-primary setview']) ?>
<button id="download-button" class="btn btn-primary shadow"><i class="fa-solid fa-file-export"></i> ส่งออกข้อมูลบุคลากร</button>


            

        </div>

    </div>
</div>

<?php if (SiteHelper::getDisplay() == 'list'): ?>
<?= $this->render('display/list', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]); ?>

<?php else: ?>
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
            // Your code goes here ...
            console.log('success',\$('#totalCount').text());
            \$('#showTotalCount').text(\$('#totalCount').text())
            // \$.pjax.reload({ container:'#title-container', history:false,replace: false});         
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
                    beforLoadModal();
                },
                success: function(data) {
                    \$("#main-modal").modal("toggle");
                    var monthName = \$('#stockeventsearch-receive_month').find(':selected').text();
                    var filename = 'ข้อมูลบุคลากร'+'.xlsx';
                    const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    link.click();
                    $("#main-modal").modal("hide");
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