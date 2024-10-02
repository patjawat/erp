<?php

use app\modules\sm\models\Order;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ระบบขอซื้อ';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/sm/views/default/menu') ?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'purchase-container','timeout' => 5000]); ?>
<div class="card">
    <div class="card-body d-flex justify-content-between align-items-center align-middle">
        <div class="d-flex gap-3 justify-content-start">
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างคำขอซื้อ/ขอจ้าง ', ['/purchase/pr-order/create', 'name' => 'order', 'title' => '<i class="bi bi-plus-circle"></i> สร้างคำขอซื้อ-ขอจ้างใหม่'], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-md']]) ?>
            <?php //  Html::a('<i class="fa-solid fa-circle-plus me-1"></i> ขอซื้อ/ขอจ้าง', ['/purchase/pr-order/create', 'name' => 'pr', 'title' => '<i class="bi bi-plus-circle"></i> เพิ่มใบขอซื้อ-ขอจ้าง'], ['class' => 'btn btn-light open-modal','data' =>['size' => 'modal-md']]) ?>
        </div>
        <div class="d-flex align-items-center  align-middle gap-2">
            <?=$this->render('_search', ['model' => $searchModel])?>
           
            <?php //  Html::a('<i class="fa-solid fa-gear"></i>', ['#', 'title' => 'การตั้งค่าบุคลากร'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <h6><i class="bi bi-ui-checks"></i> ทะเบียนขอซื้อขอจ้าง <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="fw-semibold" style="width:110px">เลขที่</th>
                        <th class="fw-semibold" style="width:300px">ผู้ขอ</th>
                        <th class="fw-semibold" style="width:180px">มูลค่า/ประเภท</th>
                        <th class="fw-semibold" >เลขที่สั่งซื้อ/ผู้ขาย</th>
                        <th class="fw-semibold" style="width: 200px;">กรรมการตรวจรับ</th>
                        <th class="fw-semibold" style="width: 200px;">ผู้เห็นชอบ</th>
                        <th class="fw-semibold"  style="width: 200px;">ความคืบหน้า</th>
                        <th class="fw-semibold text-cener" style="width:100px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr class="">
                        <td><span class="fw-semibold "><?=$model->pr_number?></span></td>
                        <td class="fw-light"> <?= $model->getUserReq()['avatar'] ?></td>
                        <td class="fw-light align-middle">
                        <div class="d-felx flex-column">
                            <div class="fw-semibold ">
                                <?= number_format($model->calculateVAT()['priceAfterVAT'],2)?>
                            </div>
                            <div class="text-primary mb-0 fs-15"><?=isset($model->data_json['order_type_name']) ? $model->data_json['order_type_name'] : ''?></div>
                                
                        </div>
                        </td>
                        <td class="fw-light align-middle">
                            <div class=" d-flex flex-column">

                                <span class="fw-semibold "><?=$model->po_number?></span>
                                <?= isset($model->data_json['vendor_name']) ? $model->data_json['vendor_name'] : '' ?>
                            </div>
                        </td>
                        <td class="fw-light align-middle"><?= $model->StackComittee() ?></td>
                        <td class="fw-light align-middle"><?=$model->showChecker()['leader']?></td>
                        <td class="fw-light align-bottom">
                        <?php if($model->deleted_at == null):?>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted mb-0 fs-13">
                                    <?=$model->viewStatus()['status_name']?><span class="text-primary">
                                        <?=$model->viewStatus()['progress']?>%</span>
                                </span>
                                <span class="text-muted mb-0 fs-13"><?=$model->viewUpdated()?>ที่แล้ว</span>
                            </div>

                            <?php else:?>

                                <div class="d-flex justify-content-between">
                                <span class="text-muted mb-0 fs-13">
                                <i class="fa-regular fa-circle-stop text-danger"></i> ยกเลิกรายการ<span class="text-primary">
                                        <?=$model->viewStatus()['progress']?>%</span>
                                </span>
                                <span class="text-muted mb-0 fs-13"><?=$model->viewUpdated()?>ที่แล้ว</span>
                            </div>

                    <?php endif;?>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-<?=$model->viewStatus()['color']?>" role="progressbar"
                                    aria-label="Progress" aria-valuenow="<?=$model->viewStatus()['progress']?>"
                                    aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </td>
                        <td class="fw-light">
                            <div class="btn-group">
                                <?= Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>', ['/purchase/order/view', 'id' => $model->id], ['class' => 'btn btn-light w-100']) ?>
                                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <?php if ($model->status == 3): ?>
                                    <li><?= Html::a('<i class="bi bi-bag-plus-fill me-1"></i> ลงทะเบีนยคุม', ['/purchase/po-order/create', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> ลงทะเบีนยคุม'], ['class' => 'dropdown-item open-modal-x', 'data' => ['size' => 'modal-md']]) ?>
                                        <?php endif;?>
                                    <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์เอกสาร', ['/purchase/order/document','id' => $model->id,'title' => '<i class="bi bi-printer-fill"></i> พิมพ์เอกสาร'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                                </ul>
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