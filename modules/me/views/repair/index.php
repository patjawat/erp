<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\bootstrap5\LinkPager;
use app\modules\sm\models\Order;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ทะเบียนประวัติแจ้งซ่อม';
$this->params['breadcrumbs'][] = ['label' => 'แจ้งซ่อม', 'url' => ['/me/repair']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-screwdriver-wrench"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu',['active' => 'repair']) ?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'purchase-container','timeout' => 5000]); ?>

<div class="card">
    <div class="card-body">
         <div class="d-flex justify-content-between">
        <?=Html::a('<i class="fa-solid fa-circle-plus"></i> แจ้งซ่อมใหม่', ['/helpdesk/default/repair-select', 'title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-md']])?>
        <?=$this->render('@app/modules/helpdesk/views/repair/_search', ['model' => $searchModel])?>
    </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
    <h6><i class="bi bi-ui-checks"></i> ทะเบียนงานซ่อม <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
</div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">รายการ</th>
                    <th scope="col">ผู้แจ้งซ่อม</th>
                    <th style="width:300px">ผู้ร่วมงานซ่อม </th>
                    <th class="text-center" style="width:150px">สถานะ</th>
                    <th class="text-center" style="width:150px">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $key => $model): ?>
                <tr class="align-middle">
                    <td><?php echo ($key+1)?></td>
                    <td>
                            <div class="d-flex">
                            <?php echo $model->RepairType()['image']?>
                            <div class="avatar-detail">
                                
                                <p class="text-primary fw-semibold fs-13 mb-0">
                                    <span class="badge text-bg-primary fs-13"><i class="fa-solid fa-circle-exclamation"></i>
                                    <?php echo $model->RepairType()['title']?>
                                </span>
                                <?= $model->viewUrgency() ?>
                                <?php echo $model->viewCreateDateTime()?>
                            </p>
                            <p style="width:600px" class="text-truncate fw-semibold fs-6 mb-0"><?php echo $model->title?></p>
                        </div>
                    </div>
                    </td>
                    <td> <?= $model->showAvatarCreate(); ?></td>
                    <td><?= $model->StackTeam() ?></td>
                    <td class="text-center"> <?= $model->viewStatus() ?></td>
                    <td class="text-center"> <?=Html::a('<i class="fa-solid fa-eye fs-1"></i>',['/me/repair/view','id' => $model->id,'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม'],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?></td>
                </tr>
    
                <?php endforeach; ?>
            </tbody>
        </table>
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
    </div>
</div>




<?php Pjax::end(); ?>
<?php
$js = <<< JS

jQuery(document).on("pjax:start", function () {
    // startProgres()
    
});
jQuery(document).on("pjax:end", function () {
    startProgres()
});

startProgres()

function startProgres(){

    var delay = 300;
    $(".progress-bar").each(function(i){
        $(this).delay( delay*i ).animate( { width: $(this).attr('aria-valuenow') + '%' }, delay );

        $(this).prop('Counter',0).animate({
        Counter: $(this).text()
    }, {
        duration: delay,
        step: function (now) {
            // $(this).text(Math.ceil(now)+'%');
        }
    });
});
}
JS;
$this->registerJS($js,View::POS_END)
?>