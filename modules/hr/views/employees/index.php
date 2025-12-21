<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
use yii\bootstrap5\LinkPager;
use app\components\SiteHelper;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ทะเบียนบุคลากร';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
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
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <path d="M16 3.128a4 4 0 0 1 0 7.744"></path>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
            <circle cx="9" cy="7" r="4"></circle>
        </svg>
        <?=$this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?=$this->render('@app/modules/hr/menu',['active' => 'employees'])
?>
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
<?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'employees']) ?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-header bg-primary-gradient text-white d-flex justify-content-between">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
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
                <div>
                    <?= Html::a('<i class="bi bi-list-ul"></i>', ['/setting/set-view', 'view' => 'list'], ['class' => 'btn btn-outline-light setview']) ?>
                    <?= Html::a('<i class="bi bi-grid"></i>', ['/setting/set-view', 'view' => 'grid'], ['class' => 'btn btn-outline-light setview']) ?>
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
            <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?> </span> รายการ
        </h6>
        <div>
            <?= Html::a('<i class="bi bi-list-ul"></i>', ['/setting/set-view', 'view' => 'list'], ['class' => 'btn btn-outline-light setview']) ?>
            <?= Html::a('<i class="bi bi-grid"></i>', ['/setting/set-view', 'view' => 'grid'], ['class' => 'btn btn-outline-light setview']) ?>
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

        $("body").on("click", "#download-button", function (e) {
            var btn = $('#dropdownMenuButton1');
            var originalHtml = btn.html(); // เก็บเนื้อหาปุ่มไว้คืนตอนหลัง

            var form = $('#employees-filter');
            $.ajax({
                url: '$url', // ปรับเป็น URL ของคุณ
                method: 'GET',
                data: form.serialize(),
                xhrFields: {
                    responseType: 'blob' // สำคัญสำหรับ binary data
                },
                beforeSend: function(){
                    // เปลี่ยนปุ่มเป็นสถานะโหลด
                    btn.html('<i class="fa fa-spinner fa-spin me-1"></i> กำลังดาวน์โหลด...');
                },
                success: function(data) {
                    const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'ข้อมูลบุคลากร.xlsx';
                    link.click();
                },
                error: function() {
                    alert('ไม่สามารถดาวน์โหลดไฟล์ได้');
                },
                complete: function() {
                    // คืนปุ่มกลับ
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
JS;
$this->registerJS($js, View::POS_END)

?>
<?php Pjax::end(); ?>