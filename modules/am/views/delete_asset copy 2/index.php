<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\am\models\Asset;



/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');
$this->title = 'ทะเบียนครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'ทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package text-primary">
            <path d="m7.5 4.27 9 5.15"></path>
            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
            <path d="m3.3 7 8.7 5 8.7-5"></path>
            <path d="M12 22V12"></path>
        </svg> ระบบบริหารทรัพย์สิน</h4>
</div>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'title-container', 'timeout' => 50000]); ?>



<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('../default/menu', ['active' => 'asset']) ?>
<?php $this->endBlock(); ?>


<?php Pjax::end(); ?>
<?php // Pjax::begin(['id' => 'am-container','timeout' => 50000 ]); 
?>

<?= $this->render('@app/modules/am/views/default/car_summary_price') ?>
<?= $this->render('@app/modules/am/views/default/_list', [
    'tabs' => $tabs,
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
]) ?>

<?php if ($group): ?>
    <?= app\components\AppHelper::Btn([
        'title' => '<i class="fa-solid fa-circle-plus"></i> ลงทะเบียน' . $title,
        'url' => ['create', 'group' => $group, 'title' => $title],
        'model' => true,
        'size' => 'lg',
    ]) ?>
<?php else: ?>

<?php endif; ?>

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
                    <i class="bi bi-ui-checks"></i> ทะเบียนทรัพย์สิน
                    <span class="badge text-bg-light">
                        <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
                </h6>
                <div class="d-flex justify-content-between gap-3">
                    <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/am/asset/create'], ['class' => 'btn btn-light']) ?>
                    <div class="dropdown">
                        <button class="btn btn-success shadow dropdown-toggle" type="button"
                            id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-file-excel"></i> Excel
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><?= Html::a('<i class="fa-solid fa-file-csv me-2"></i>นำเข้าด้วย CSV', ['/am/import', 'title' => 'นำเข้าไฟล์ CSV'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                            <li><?= Html::a('<i class="fa-solid fa-file me-2"></i> ตัวอย่างไฟล์นำเข้า', 'https://docs.google.com/spreadsheets/d/1YjAwT8Qklc6gEx30T_fXa_XkfncrCRe3pt9FwC6QYok/edit?usp=sharing', ['class' => 'dropdown-item', 'target' => '_blank']) ?></li>
                            <li>
                                <?= Html::a(
                                    '<i class="fa-solid fa-file-excel me-2"></i> ส่งออก Excel',
                                    '#',
                                    ['class' => 'dropdown-item delete-all-item', 'data-order-id' => 1]
                                ) ?>

                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <?= $this->render('show/list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]); ?>
        </div>
    </div>
<?php else: ?>

    <div class="d-flex justify-content-between mb-3">
        <h6>
            <i class="bi bi-ui-checks"></i> <?= $this->title ?>
            <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?> </span> รายการ
        </h6>
        <div class="d-flex justify-content-between gap-3">
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/am/asset/create'], ['class' => 'btn btn-light']) ?>
            <div class="dropdown">
                <button class="btn btn-success shadow dropdown-toggle" type="button"
                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                    <li><?= Html::a('<i class="fa-solid fa-file-csv me-2"></i>นำเข้าด้วย CSV', ['/am/import', 'title' => 'นำเข้าไฟล์ CSV'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                    <li><?= Html::a('<i class="fa-solid fa-file me-2"></i> ตัวอย่างไฟล์นำเข้า', 'https://docs.google.com/spreadsheets/d/1YjAwT8Qklc6gEx30T_fXa_XkfncrCRe3pt9FwC6QYok/edit?usp=sharing', ['class' => 'dropdown-item', 'target' => '_blank']) ?></li>
                    <li>
                        <?= Html::a(
                            '<i class="fa-solid fa-file-excel me-2"></i> ส่งออก Excel',
                            '#',
                            ['class' => 'dropdown-item delete-all-item', 'data-order-id' => 1]
                        ) ?>

                    </li>
                </ul>
            </div>
        </div>
    </div>

    <?= $this->render('show/grid', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]); ?>

<?php endif ?>





</div>
<span id="totalCount" class="d-none"><?= $dataProvider->getTotalCount(); ?></span>

<?php // Pjax::end(); 
?>

<?php
$js = <<< JS

$('#am-container').on('pjax:success', function() {
    // Your code goes here ...
    console.log('success',$('#totalCount').text());
    $('#showTotalCount').text($('#totalCount').text())
    $.pjax.reload({ container:'#title-container', history:false,replace: false});         
});


$('.delete-asset').click(function (e) { 
    e.preventDefault();
    let url = $(this).attr('href');

    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "ข้อมูลนี้จะถูกลบและไม่สามารถกู้คืนได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: url,
                dataType: "json",
                success: function (res) {
                    if (res.status == 'success') {
                        Swal.fire({
                            title: 'ลบข้อมูลสำเร็จ!',
                            text: 'รายการถูกลบเรียบร้อยแล้ว',
                            icon: 'success',
                            timer: 1000, // ตั้งค่าให้ Swal ปิดอัตโนมัติหลัง 1 วินาที
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '/am/asset'; // Redirect หลังจาก timer หมด
                        });
                    } else {
                        Swal.fire(
                            'เกิดข้อผิดพลาด!',
                            res.message || 'ไม่สามารถลบข้อมูลได้',
                            'error'
                        );
                    }
                },
                error: function () {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                        'error'
                    );
                }
            });
        }
    });
});


JS;
$this->registerJS($js);

?>