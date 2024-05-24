<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Order $model */
$this->title = 'ระบบพัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>


<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-body">
        <p class="card-text">ขอซื้อขอจ้าง</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-2 col-md-4 col-sm-12">
        <?= $this->render('step') ?>
    </div>
    <div class="col-lg-8 col-md-8 col-sm-12">


        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <p><i class="fa-solid fa-bag-shopping fs-3"></i> ใบขอซื้อ</p>
                    <p class="">
                        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-warning rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                        <?= Html::a('<i class="fa-regular fa-trash-can"></i> ยกเลิก', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-danger rounded-pill ',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this item?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </p>
                </div>
                <table class="table table-striped-columns">
                    <tbody>
                        <tr class="">
                            <td class="text-end" style="width:150px;">เลขที่ขอซื้อ</td>
                            <td><?= $model->code ?></td>
                            <td>ผู้ขอ</td>
                            <td> <?= $model->getUserReq()['avatar'] ?></td>
                        </tr>
                        <tr class="">
                            <td class="text-end">วันที่ขอซื้อ</td>
                            <td> <?php echo Yii::$app->thaiFormatter->asDateTime($model->created_at, 'medium') ?></td>
                            <td>เรื่อง</td>
                            <td>Item</td>
                        </tr>
                        <tr class="">
                            <td class="text-end">วันที่ต้องการ</td>

                            <td> <?php echo Yii::$app->thaiFormatter->asDate($model->data_json['due_date'], 'medium') ?></td>
                            <td>เหตุผล</td>
                            <td><?= $model->data_json['comment'] ?></td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>


        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">รายการ</th>
                                <th scope="col">หน่วย</th>
                                <th scope="col">ราคาต่อหน่วย</th>
                                <th scope="col">จำนวนเงิน</th>
                                <th class="d-flex justify-content-center gap-2">
                                    <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มรายการ', ['/sm/product/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มวัสดุใหม่'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="">
                                <td>กระดาษ A4</td>
                                <td>รีม</td>
                                <td>240</td>
                                <td>1</td>
                                <td class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-sm btn-warning rounded-pill"><i
                                            class="fa-regular fa-pen-to-square"></i></button>
                                    <button class="btn btn-sm btn-danger rounded-pill"><i
                                            class="fa-regular fa-trash-can"></i></button>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>



    </div>
</div>