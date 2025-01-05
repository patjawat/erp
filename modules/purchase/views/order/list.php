<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\sm\models\Order;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ระบบขอซื้อ';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'purchase-container','timeout' => 5000]); ?>

<div class="card">
    <div class="card-body">
        <h6>ทะเบียนคุม</h6>
        <div class="table-responsive" style="height:800px">
        <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="fw-semibold" style="width:350px">ผู้ขอซื้อ</th>
                        <th class="fw-semibold">มูลค่า</th>
                        <th class="fw-semibold">ผู้ขาย</th>
                        <th class="fw-semibold">ความคืบหน้า</th>
                        <th class="fw-semibold text-center" style="width:176px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr class="">
                        <td class="fw-light"> <?= $model->getUserReq()['avatar'] ?></td>
                        <td class="fw-light align-middle">
   <div class="fw-semibold"><?= number_format($model->calculateVAT()['priceAfterVAT'],2) ?></div>
                        </td>
                        <td class="fw-light align-middle"><?= $model->data_json['vendor_name'] ?></td>
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






<?php
$js = <<< JS
var delay = 500;
$(".progress-bar").each(function(i){
    $(this).delay( delay*i ).animate( { width: $(this).attr('aria-valuenow') + '%' }, delay );

    $(this).prop('Counter',0).animate({
        Counter: $(this).text()
    }, {
        duration: delay,
        step: function (now) {
            $(this).text(Math.ceil(now)+'%');
        }
    });
});
JS;
$this->registerJS($js)
?>
<?php Pjax::end(); ?>