<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\sm\models\Order;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ทะเบียนประวัติแจ้งซ่อม';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-screwdriver-wrench"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'purchase-container','timeout' => 5000]); ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> แจ้งซ่อม <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
            <div class="d-flex justify-content-between gap-3">
            <?=$this->render('_search', ['model' => $searchModel])?>
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/helpdesk/default/repair-select', 'title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม'], ['class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-md']]) ?>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">รายการ</th>
                    <th style="width:300px">ผู้ร่วมงาน </th>
                    <th class="text-center" style="width:200px">ความเร่งด่วน</th>
                    <th class="text-center" style="width:150px">สถานะ</th>
                    <th class="text-center" style="width:150px">ดำเนินการ</th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>

                    <tr class="">
                        <td class="fw-light">
                            <?php
                            $title = $model->data_json['title'].' | '.$model->viewCreateDate();
                            echo $model->getUserReq($title)['avatar'] ?>
                            </td>
                            <td><?= $model->avatarStack() ?></td>
                            <td class="text-center"><?= $model->viewUrgency() ?></td>
                    <td class="text-center"> <?= $model->viewStatus() ?></td>
                    <td class="text-center"> <?=Html::a('<i class="fa-solid fa-eye"></i>',['/helpdesk/repair/timeline','id' => $model->id,'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?></td>

                    </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>

         

        </div>

        <div class="d-flex justify-content-center">
    <?= yii\bootstrap5\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'firstPageLabel' => 'หน้าแรก',
        'lastPageLabel' => 'หน้าสุดท้าย',
        'options' => [
            'class' => 'pagination pagination-sm',
        ],
    ]); ?>
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