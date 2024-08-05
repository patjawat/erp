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
    <div class="card-body d-flex align-middle flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex gap-3 justify-content-start">
            <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> ขอซื้อ/ขอจ้าง', ['/purchase/pr-order/create', 'name' => 'pr', 'title' => '<i class="bi bi-plus-circle"></i> เพิ่มใบขอซื้อ-ขอจ้าง'], ['class' => 'btn btn-light open-modal','data' =>['size' => 'modal-md']]) ?>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?=$this->render('_search', ['model' => $searchModel])?>
            <?= Html::a('<i class="bi bi-list-ul"></i>', ['#', 'view' => 'list'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="bi bi-grid"></i>', ['#', 'view' => 'grid'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-gear"></i>', ['#', 'title' => 'การตั้งค่าบุคลากร'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h6>ทะเบียนคุม</h6>
        <div class="table-responsive" style="height:800px">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th class="fw-semibold" style="width:280px">ผู้ขอซื้อ</th>
                        <th class="fw-semibold">ประเภท/มูลค่า</th>
                        <th class="fw-semibold">ผู้ขาย</th>
                        <th class="fw-semibold" style="width: 200px;">กรรมการตรวจรับ</th>
                        <th class="fw-semibold" style="width: 200px;">ผู้เห็นชอบ</th>
                        <th class="fw-semibold"  style="width: 300px;">ความคืบหน้า</th>
                        <th class="fw-semibold text-center" style="width:176px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr class="">
                        <td class="fw-light"> <?= $model->getUserReq()['avatar'] ?></td>
                        <td class="fw-light align-middle">
                        <div class="d-felx flex-column">
                                <div class="text-primary mb-0 fs-15"><?=isset($model->data_json['order_type_name']) ? $model->data_json['order_type_name'] : ''?></div>
                                <div class="fw-semibold ">
                                <i class="fa-solid fa-tag"></i> <?= number_format($model->calculateVAT()['priceAfterVAT'],2)?>
                                </div>
                                
                        </div>
                        </td>
                        <td class="fw-light align-middle"><?= isset($model->data_json['vendor_name']) ? $model->data_json['vendor_name'] : '' ?></td>
                        <td class="fw-light align-middle"><?= $model->StackComittee() ?></td>
                        <td class="fw-light align-middle"><?=$model->showChecker()['leader']?></td>
                        <td class="fw-light align-bottom">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted mb-0 fs-13">
                                    <?=$model->viewStatus()['status_name']?><span class="text-primary">
                                        <?=$model->viewStatus()['progress']?>%</span>
                                </span>
                                <span class="text-muted mb-0 fs-13"><?=$model->viewUpdated()?>ที่แล้ว</span>

                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-<?=$model->viewStatus()['color']?>" role="progressbar"
                                    aria-label="Progress" aria-valuenow="<?=$model->viewStatus()['progress']?>"
                                    aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </td>
                
                        <td class="fw-light">
                            <div class="btn-group">
                                <?= Html::a('<i class="bi bi-clock"></i> ดำเนินการ', ['/purchase/order/view', 'id' => $model->id], ['class' => 'btn btn-light w-100']) ?>
                                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <?php if ($model->status == 3): ?>
                                    <li><?= Html::a('<i class="bi bi-bag-plus-fill me-1"></i> ลงทะเบีนยคุม', ['/purchase/po-order/create', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> ลงทะเบีนยคุม'], ['class' => 'dropdown-item open-modal-x', 'data' => ['size' => 'modal-md']]) ?>
                                        <?php endif;?>
                                    <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์เอกสาร', ['/sm/order/document', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> พิมพ์เอกสารประกอบการจัดซื้อ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                    <li><?= Html::a('<i class="bi bi-bag-plus-fill me-1"></i> สร้างใบสั่งซื้อ', ['/purchase/po-order/create', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> พิมพ์เอกสารประกอบการจัดซื้อ'], ['class' => 'dropdown-item open-modal-x', 'data' => ['size' => 'modal-md']]) ?>
                                    <li> <?=Html::a('<i class="fa-solid fa-circle-plus text-white"></i> ตรวจรับ',['/purchase/gr-order/update','id' => $model->id,'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> สร้างใบตรวจรับ'],['class' => 'open-modal dropdown-item','data' => ['size' => 'modal-xl']])?></li>
                                    </li>
                                </ul>
                            </div>

                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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