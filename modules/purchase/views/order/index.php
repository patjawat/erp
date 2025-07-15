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
$this->title = 'ทะเบียนประวัติ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบขอซื้อ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = $this->title;
$betwenTitle = '';
if($searchModel->date_between == 'pr_create_date'){
    $betwenTitle = 'วันที่ขอซื้อ';

}elseif($searchModel->date_between == 'po_date'){
    $betwenTitle = 'วันที่สั่งซื้อ';

}elseif($searchModel->date_between == 'gr_date'){
    $betwenTitle = 'วันที่ตรวจรับ';

}else{
    $betwenTitle = '';
}


?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-list-ul me-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/sm/views/default/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/sm/views/default/menu',['active' => 'order'])?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'purchase-container','enablePushState' => true,'timeout' => 88888888]); ?>


<div class="card">
        <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
      <?=$this->render('_search', ['model' => $searchModel])?>
    </div>
</div>

<div class="card">

    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทะเบียนขอซื้อขอจ้าง
                <span class="badge text-bg-light">
                    <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> ระบบการ
            </h6>
            <div class="d-flex justify-content-between">
                 <?= Html::a('<i class="fa-solid fa-circle-plus text-primary"></i> สร้างใหม่ ', ['/purchase/pr-order/create', 'name' => 'order', 'title' => '<i class="bi bi-plus-circle"></i> สร้างคำขอซื้อ-ขอจ้างใหม่'], ['class' => 'btn btn-light shadow open-modal', 'data' => ['size' => 'modal-md']]) ?>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <div class="d-flex justify-content-between">
                <div>
                    มูลค่า <span class="fw-semibold badge rounded-pill text-bg-light fs-6"><?=$searchModel->SummaryTotal()?></span>บาท
                </div>
           
        </div>
        <div>

        </div>


        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                    <th class="fw-semibold" style="width:110px">เลขทะเบียนคุม</th>
                    <th class="fw-semibold" style="width:300px">ผู้ขอ/วันเวลา</th>
                    <th class="fw-semibold" style="width:180px">ประเภท</th>
                    <th class="fw-semibold">เลขที่สั่งซื้อ/ผู้ขาย</th>
                    <th class="fw-semibold" style="width: 180px;">การตรวจสอบ</th>
                    <th class="fw-semibold text-end" style="width:150px">มูลค่า/ประเภทเงิน</th>
                    <th class="fw-semibold" style="width: 230px;">ความคืบหน้า</th>
                    <th class="fw-semibold text-cener" style="width:100px">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $key => $item):?>
                <tr>
                    <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                    <td><span class="fw-semibold "><?=$item->pq_number?></span></td>
                    <td class="fw-light"> <?= $item->getUserReq()['avatar'] ?></td>
                    <td><?=isset($item->data_json['order_type_name']) ? $item->data_json['order_type_name'] : ''?>
                    </td>

                    <td class="fw-light align-middle">
                        <div class=" d-flex flex-column">
                            <span class="fw-semibold "><?=$item->po_number?></span>
                            <?= isset($item->data_json['vendor_name']) ? $item->data_json['vendor_name'] : '' ?>
                        </div>
                    </td>
                    <td class="fw-light align-middle">
                        <?php // $item->showChecker()['leader']?>
                        <?php echo $item->StackApprove()?>
                    </td>
                    <td class="fw-light align-middle text-end">
                        <div class="d-felx flex-column">
                            <div class="fw-semibold ">
                                <?= number_format($item->calculateVAT()['priceAfterVAT'],2)?>
                            </div>
                            <div class="text-primary mb-0 fs-15">
                                <?=isset($item->data_json['pq_budget_type_name']) ? $item->data_json['pq_budget_type_name'] : ''?>
                            </div>
                        </div>
                    </td>
                    <td class="fw-light align-middle">
                        <?php if($item->deleted_at == null):?>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted mb-0 fs-13">
                                <span
                                    class="badge rounded-pill text-bg-<?=$item->viewStatus()['color']?> mb-2 fs-13"><?=$item->viewStatus()['status_name']?></span>
                                <span class="text-primary">
                                    <?=$item->viewStatus()['progress']?>%</span>
                            </span>
                            <span class="text-muted mb-0 fs-13"><?=$item->viewUpdated()?>ที่แล้ว</span>
                        </div>

                        <?php else:?>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted mb-0 fs-13">
                                <i class="fa-regular fa-circle-stop text-danger"></i> ยกเลิกรายการ<span
                                    class="text-primary">
                                    <?=$item->viewStatus()['progress']?>%</span>
                            </span>
                            <span class="text-muted mb-0 fs-13"><?=$item->viewUpdated()?>ที่แล้ว</span>
                        </div>

                        <?php endif;?>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-<?=$item->viewStatus()['color']?>" role="progressbar"
                                aria-label="Progress" aria-valuenow="<?=$item->viewStatus()['progress']?>"
                                aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </td>
                    <td class="fw-light">
                        <div class="btn-group">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>', ['/purchase/order/view', 'id' => $item->id], ['class' => 'btn btn-light w-100']) ?>
                            <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                <i class="bi bi-caret-down-fill"></i>
                            </button>

                            <?php if($item->status !== 7):?>
                            <ul class="dropdown-menu">
                                <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> คำขอซื้อ', ['/purchase/pr-order/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-print"></i> คำขอซื้อ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>


                                    <?php if ($item->status >= 2): ?>
                                <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> ทะเบีนยคุม', ['/purchase/pr-order/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-print"></i> ทะเบีนยคุม'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                    <?php endif;?>

                                    <?php if ($item->status >= 3): ?>
                                <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> คำสั่งซื้อ', ['/purchase/po-order/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-print"></i> คำสั่งซื้อ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                                    <?php endif;?>

                                    <?php if ($item->status >= 4): ?>
                                <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> ใบตรวจรับ', ['/purchase/gr-order/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-print"></i> ใบตรวจรับ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                                    <?php endif;?>

                                <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์เอกสาร', ['/purchase/order/document','id' => $item->id,'title' => '<i class="bi bi-printer-fill"></i> พิมพ์เอกสาร'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                </li>
                            </ul>
                            <?php endif;?>
                        </div>

                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

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