<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
$this->title = $title;
?>
<!-- แสดงรายการคำขอซื้อ -->
<div class="card" style="height: 435px;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <p><i class="fa-solid fa-user-check text-black-50"></i> <?=$this->title?></p>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <?= Html::a('<i class="fa-solid fa-circle-info text-primary me-2"></i> เพิ่มเติม', ['/sm/order'], ['class' => 'dropdown-item']) ?>
                        </div>
                    </div>
                </div>
                <table class="table  m-b-0 transcations mt-2">
                    <tbody>
                        <?php foreach($dataProvider->getModels() as $model):?>
                        <tr>

                            <td class="fw-light "> <?= 
                            Html::a($model->getUserReq()['avatar'],['/purchase/order/view','id' => $model->id]) ?></td>

                            <td class="text-end">
                                <div class="d-inline-block">
                                    <h6 class="mb-2 fs-15 fw-semibold"><?= number_format($model->calculateVAT()['priceAfterVAT'],2) ?> บาท</h6>
                                    <p class="mb-0 fs-11 text-muted"><?=$model->viewCreated()?>ที่แล้ว</p>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>

            </div>
        </div>
<?php

$prOrderUrl = Url::to(['/purchase/pr-order/list']);
$js = <<< JS

function loadPrOrder(){
    $.ajax({
        type: "get",
        url: "$prOrderUrl",
        data: "data",
        dataType: "dataType",
        success: function (response) {
            
        }
    });
}
JS;
$this->registerJS($js,View::POS_END);
?>