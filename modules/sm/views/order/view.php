<?php

use app\modules\sm\models\Order;
use unclead\multipleinput\MultipleInput;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

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
<?php Pjax::begin(['id' => 'sm-container']); ?>

<div class="card">
    <div class="card-body">
        <p class="card-text">ขอซื้อขอจ้าง</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-2 col-md-4 col-sm-12">
        <?= $this->render('step', ['model' => $model]) ?>
    </div>
    <div class="col-lg-8 col-md-8 col-sm-12">

        <div class="d-flex justify-content-between">
            <div>
                <h5> <span class="badge rounded-pill bg-primary text-white">1</span> ขั้นตอนการขอซื้อขอจ้าง</h5>
            </div>
            <p class="">
                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-warning rounded-pill open-modal shadow', 'data' => ['size' => 'modal-lg']]) ?>
                <?= Html::a('<i class="fa-regular fa-trash-can"></i> ยกเลิก', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-sm btn-danger rounded-pill shadow',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this item?',
                        'method' => 'post',
                    ],
                ]) ?>
            </p>
        </div>
        <div class="card">
            <div class="card-body">
                <!-- <div class="d-flex justify-content-between">
                    <p><i class="fa-solid fa-bag-shopping fs-3"></i> ใบขอซื้อ</p>
                </div> -->
                <table class="table table-striped-columns">
                    <tbody>
                        <tr class="">
                            <td class="text-end" style="width:150px;">เลขที่ขอซื้อ</td>
                            <td class="fw-semibold"><?= $model->pr_number ?></td>
                            <td class="text-end">ผู้ขอ</td>
                            <td> <?= $model->getUserReq()['avatar'] ?></td>
                        </tr>
                        <tr class="">
                            <td>เพื่อจัดซื้อ/ซ่อมแซม</td>
                            <td>
                                <?php
                                    try {
                                        echo $model->data_json['product_type_name'];
                                    } catch (\Throwable $th) {
                                    }
                                ?></td>
                            <td class="text-end">วันที่ขอซื้อ</td>
                            <td> <?php echo Yii::$app->thaiFormatter->asDateTime($model->created_at, 'medium') ?></td>

                        </tr>
                        <tr class="">
                            <td>เหตุผล</td>
                            <td><?= $model->data_json['comment'] ?></td>
                            <td class="text-end">วันที่ต้องการ</td>
                            <td> <?php echo Yii::$app->thaiFormatter->asDate($model->data_json['due_date'], 'medium') ?>
                            </td>

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
                                <th>รูป</th>
                                <th style="width:500px">รายการ</th>
                                <th class="text-center" style="width:80px">หน่วย</th>
                                <th class="text-end">ราคาต่อหน่วย</th>
                                <th class="text-center" style="width:80px">จำนวน</th>
                                <th class="text-end">จำนวนเงิน</th>
                                <th style="width:180px">
                                    <div class="d-flex justify-content-center">
                                        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มรายการ', ['/sm/order/product-list', 'order_id' => $model->id, 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มวัสดุใหม่'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-xl']]) ?>

                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (Order::find()->where(['category_id' => $model->id])->all() as $item): ?>
                            <tr class="">
                                <td class="align-middle">
                                    <?php
                                    try {
                                        // code...
                                        echo Html::img($item->product->ShowImg(), ['class' => '  ', 'style' => 'max-width:50px;;height:280px;max-height: 50px;']);
                                    } catch (\Throwable $th) {
                                        // throw $th;
                                    }
                                    ?>
                                </td>
                                <td class="align-middle"><?= $item->product->title ?></td>
                                <td class="align-middle text-center"><?= $item->product->data_json['unit'] ?></td>
                                <td class="align-middle text-end fw-semibold"><?= number_format($item->price, 2) ?></td>
                                <td class="align-middle text-center">
                                    <?= $item->amount ?>
                                </td>
                                <td class="align-middle text-end">
                                    <div class="d-flex justify-content-end fw-semibold">
                                        <?= number_format(($item->amount * $item->price), 2) ?>
                                    </div>
                                </td>
                                
                                <td class="align-middle gap-2">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/sm/order/update-item', 'id' => $item->id], ['class' => 'btn btn-sm btn-warning rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                        <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['/sm/order/delete-item', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger rounded-pill delete-item']) ?>
                                    </div>

                                </td>
                            </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>

                    <div class="row">
                        <div class="col-6"></div>
                        <div class="col-6">
                            <div class="table-responsive">
                                <table class="table">
                                    <tbody>
                                        <tr class="">
                                            <td>ทั้งหมด</td>
                                            <td><span
                                                    class="fw-semibold"><?= number_format($model->SumPo(), 2) ?></span>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                            <div class="d-grid gap-2">
                                <?php if ($model->status == ''): ?>
                                <?= Html::a('<i class="fa-solid fa-circle-exclamation"></i> ส่งคำขอซื้อ', ['/sm/order/pr-confirm', 'id' => $model->id], ['class' => 'btn btn-primary rounded shadow pr-confirm']) ?>
                                <?php endif; ?>
                                <?php if ($model->status == 1): ?>
                                    <?= Html::a('<span class="badge rounded-pill bg-light text-dark">2</span> หัวหน้าเห็นชอบ', ['/sm/order/confirm-status', 'id' => $model->id, 'title' => '<i class="fa-solid fa-circle-exclamation"></i> ตรวจสอบคำขอซื้อ/ขอจ้าง'], ['class' => 'btn btn-primary rounded shadow open-modal shadow', 'data' => ['size' => 'modal-md']]) ?>
                                    <?php endif; ?>      

                                    <?php if ($model->status == 2): ?>
                                    <?= Html::a('<span class="badge rounded-pill bg-light text-dark">2</span> ตรวจสอบคำขอซื้อ', ['/sm/order/confirm-status', 'id' => $model->id, 'title' => '<i class="fa-solid fa-circle-exclamation"></i> ตรวจสอบคำขอซื้อ/ขอจ้าง'], ['class' => 'btn btn-primary rounded shadow open-modal shadow', 'data' => ['size' => 'modal-md']]) ?>
                                    <?php endif; ?>  
                                    <?php if ($model->status == 3): ?>
                                    <?= Html::a('<span class="badge rounded-pill bg-light text-dark">2</span>  ผู้อำนวยการอนุมัติ  ', ['/sm/order/confirm-status', 'id' => $model->id, 'title' => '<i class="fa-solid fa-circle-exclamation"></i> ผู้อำนวยการอนุมัติ'], ['class' => 'btn btn-primary rounded shadow open-modal shadow', 'data' => ['size' => 'modal-md']]) ?>
                                    <?php endif; ?>  

                                    <?php if ($model->status == 4): ?>
                                    <?= Html::a('<span class="badge rounded-pill bg-light text-dark">2</span>  ลงทะเบียนคุม  ', ['/sm/order/confirm-status', 'id' => $model->id, 'title' => '<i class="fa-solid fa-circle-exclamation"></i> ลงทะเบียนคุม'], ['class' => 'btn btn-primary rounded shadow open-modal shadow', 'data' => ['size' => 'modal-md']]) ?>
                                    <?php endif; ?>  
                                     
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
$js = <<< JS


    \$("body").on("click", ".pr-confirm", async function (e) {
      e.preventDefault();
      var url = \$(this).attr("href");
      await Swal.fire({
        title: "ยืนยัน?",
        text: "ส่งคำขอซื้อเพื่อให้หัวหน้าพิจารณาเห็นชอบ!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "ใช่, ลบเลย!",
        cancelButtonText: "ยกเลิก",
      }).then(async (result) => {
        console.log("result", result.value);
        if (result.value == true) {
           await \$.ajax({
            type: "post",
            url: url,
            dataType: "json",
            success:  function (response) {
              if (response.status == "success") {
                 \$.pjax.reload({
                  container: response.container,
                  history: false,
                  url: response.url,
                });
                success("ดำเนินการลบสำเร็จ รอหัวหน้าเห็นชอบ!.");
                // location.reload();
                if (response.close) {
                   \$("#main-modal").modal("hide");
                }
              }
            },
          });
        }
      });
    });

    JS;
$this->registerJS($js);
?>

<?php Pjax::end(); ?>