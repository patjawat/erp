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
<?php Pjax::begin(['id' => 'title-container','timeout' => 50000 ]); ?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check fs-1"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
ทั้งหมด <span id="showTotalCount"><?=$dataProvider->getTotalCount()?></span> รายการ
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'asset'])?>
<?php $this->endBlock(); ?>


<?php Pjax::end(); ?>
<?php // Pjax::begin(['id' => 'am-container','timeout' => 50000 ]); ?>


<?php if($group):?>
<?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> ลงทะเบียน'.$title,
                    'url' =>['create','group' => $group,'title' => $title],
                    'model' =>true,
                    'size' => 'lg',
            ])?>
<?php else:?>

<?php endif;?>

<div class="card">
    <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <?php //  Html::a('<i class="fa-solid fa-circle-plus"></i> ลงทะเบียนคุภัณฑ์', ['select-type','group' => $group,'title' => $title], ['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']]) ?>
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/am/asset/create'], ['class' => 'btn btn-primary rounded-pill']) ?>
                <?= $this->render('_search', ['model' => $searchModel]); ?>
            </div>
    </div>
</div>

        <?php if(SiteHelper::getDisplay() == 'list'):?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียนทรัพย์สิน <span class="badge rounded-pill text-bg-primary"> <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ</h6>
        </div>
        <?=$this->render('show/list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);?>
    </div>
</div>
        <?php else:?>
        <?=$this->render('show/grid', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);?>

        <?php endif?>

        <?php if(count(($dataProvider->getModels())) == 0):?>
        <div class="row d-flex justify-content-center">
            <div class="col-6">
                <div
                    class="f-flex justify-content-center align-items-center mt-5 bg-primary bg-opacity-10  p-3 rounded-2">
                    <h4 class="text-center"> <i class="fa-solid fa-circle-exclamation text-primary"></i>
                        ไม่มีทรัพย์สินที่ได้รับผิดชอบ</h4>
                    <p class="text-center">หากต้องการสืบค้นสามารถใช้ตัวกรองเพื่อค้นหาข้อมูลได้</p>
                </div>
            </div>
        </div>
        <?php endif;?>



<div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
    <?= yii\bootstrap5\LinkPager::widget([
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
<span id="totalCount" class="d-none"><?=$dataProvider->getTotalCount();?></span>

<?php // Pjax::end(); ?>

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