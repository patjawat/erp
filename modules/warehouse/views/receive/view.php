<?php

use app\modules\warehouse\models\StockOrder;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Order $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'warehouse']); ?>
<div class="card">
    <div class="card-body d-flex justify-content-between align-items-center">
        <h5><i class="fa-solid fa-shop"></i> รับสินค้าเข้าคลัง</h5>

        <div>
            <?php Html::a('เลือกรายการ', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?php Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('ยกเลิก', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>
        
        </div>
    </div>
</div>



<div class="row">

    <div class="col-7">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="fa-solid fa-file-lines"></i> ข้อมูลตรวจรับ</h6>
                    <div class="dropdown float-end">
            <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right" style="">
                <?= Html::a('<i class="fa-regular fa-eye me-1 text-primary"></i> แสดง', ['/warehouse/receive/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                <?= Html::a('<i class="bx bx-trash me-1 text-danger"></i> ลบ', ['/sm/asset-type/delete', 'id' => $model->id], [
                    'class' => 'dropdown-item  delete-item',
                ]) ?>
            </div>
        </div>
                </div>
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'label' => 'เลขที่ขอซื้อ',
                            'value' => function ($model) {
                                // return $model->pr_number;
                            }
                        ],
                        [
                            'label' => 'เลขที่สั่งซื้อ',
                            'value' => function ($model) {
                                // return $model->po_number;
                            }
                        ],
                        [
                            'label' => 'สานะ',
                            'value' => function ($model) {
                                // return $model->viewStatus();
                            }
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col-5">
        <?= $this->render('list_committee', ['model' => $model]) ?>
    </div>
    <div class="col-12">
    <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                        <h6><i class="fa-solid fa-file-circle-plus"></i> รายการตรวจรับ</h6>
                        <div>
                            <!-- <button class="btn btn-sm btn-primary rounded-pill"><i class="fa-solid fa-plus"></i>
                                เลือกรายการ</button> -->
                                <?= Html::a('<i class="fa-solid fa-plus"></i> เลือกรายการ', ['/warehouse/receive/list-item-form-po', 'po_number' => $model->category_id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                        </div>
                        
                    </div>
                <div class="table-responsive">
                    <table class="table table-primary">
                        <thead>
                            <tr>
                                <th scope="col">ชื่อรายการ</th>
                                <th scope="col">ประเภท</th>
                                <th scope="col">หน่วย</th>
                                <th scope="col">จำนวนรับ</th>
                                <th scope="col">ล็อต</th>
                                <th scope="col">วันที่ผลิต</th>
                                <th scope="col">วันที่หมดอายุ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (StockOrder::find()->where(['name' => 'receive_item'])->all() as $item): ?>
                            <tr class="">
                                <td scope="row">
                                    <?php
                                    try {
                                        echo $item->data_json['product_name'];
                                    } catch (\Throwable $th) {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>R1C2</td>
                                <td>R1C3</td>
                                <td>R1C3</td>
                                <td>R1C3</td>
                                <td>R1C3</td>
                                <td>R1C3</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>


<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึกรับเข้าคลัง', ['class' => 'btn btn-primary', 'id' => 'toStock']) ?>
</div>
<?php
use yii\helpers\Url;

$url = Url::to(['/warehouse/receive/save-to-stock']);
$js = <<< JS
    \$('#toStock').click(function (e) { 
        e.preventDefault();
        
        \$.ajax({
            type: "post",
            url: "$url",
            dataType: "json",
            success: function (response) {
                
            }
        });
    });
    JS;

$this->registerJS($js);
?>
<?php Pjax::end(); ?>