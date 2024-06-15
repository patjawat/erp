<?php

use app\models\Categorise;
use app\modules\sm\models\Order;
use unclead\multipleinput\MultipleInput;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Order $model */
$this->title = 'ระบบพัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$listItems = Order::find()->where(['category_id' => $model->id])->all();
?>


<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php //  $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'purchase-container']); ?>

<?= $this->render('../default/menu2') ?>
<div class="row justify-content-center">
    <div class="col-lg-2 col-md-4 col-sm-12">
        <?= $this->render('step', ['model' => $model]) ?>
    </div>
    <div class="col-lg-8 col-md-8 col-sm-12">

        <div class="d-flex justify-content-between">
            <div>
                <h5> <span class="badge rounded-pill bg-primary text-white">1</span> ขั้นตอนการขอซื้อขอจ้าง</h5>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="dropdown float-end">
                    <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-ellipsis"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" style="">
                        <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                        <?= Html::a('<i class="fa-regular fa-file-word me-1"></i> พิมพ์', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                        <?= Html::a('<i class="bx bx-trash me-1 text-danger me-1"></i> ลบ', ['/sm/asset-type/delete', 'id' => $model->id], [
                            'class' => 'dropdown-item  delete-item',
                        ]) ?>
                    </div>
                </div>
                <table class="table table-striped-columns">
                    <tbody>
                        <tr class="">
                            <td class="text-end" style="width:150px;">เลขที่ขอซื้อ</td>
                            <td class="fw-semibold"><?= $model->code ?></td>
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
                            <td class="text-end">เหตุผล</td>
                            <td><?= isset($model->data_json['comment']) ? $model->data_json['comment'] : '' ?></td>
                            <td class="text-end">วันที่ต้องการ</td>
                            <td> <?= isset($model->data_json['due_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['due_date'], 'medium') : '' ?>
                            </td>
                        </tr>
                        <td class="text-end">ผู้เห็นชอบ</td>
                        <td colspan="5"><?= $model->viewLeaderUser()['avatar'] ?></td>
                        </tr>
                        <tr>
                            <td class="text-end">ความเห็น</td>
                            <td colspan="5">
                                <?= isset($model->data_json['pr_confirm_2']) ? '<span class="badge rounded-pill bg-success-subtle"><i class="fa-regular fa-thumbs-up"></i> ' . $model->data_json['pr_confirm_2'] . '</span>' : '' ?>
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
                            <?php foreach ($listItems as $item): ?>
                            <tr class="">
                                <td class="align-middle">
                                    <?php
                                    try {
                                        echo Html::img($item->product->ShowImg(), ['class' => '  ', 'style' => 'max-width:50px;;height:280px;max-height: 50px;']);
                                    } catch (\Throwable $th) {
                                        // throw $th;
                                    }
                                    ?>
                                </td>
                                <td class="align-middle"><?= $item->product->title ?></td>
                                <td class="align-middle text-center"><?= $item->product->data_json['unit'] ?></td>
                                <td class="align-middle text-end fw-semibold">
                                    <?php
                                    try {
                                        echo number_format($item->price, 2);
                                    } catch (\Throwable $th) {
                                        // throw $th;
                                    }
                                    ?>
                                </td>
                                <td class="align-middle text-center">
                                    <?= $item->amount ?>
                                </td>
                                <td class="align-middle text-end">
                                    <div class="d-flex justify-content-end fw-semibold">
                                        <?php
                                        try {
                                            echo number_format(($item->amount * $item->price), 2);
                                        } catch (\Throwable $th) {
                                            // throw $th;
                                        }
                                        ?>
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
                                <?php if ($model->status == '' && count($listItems) > 0): ?>
                                <?= Html::a('<i class="fa-solid fa-circle-exclamation"></i> ส่งคำขอซื้อ', [
                                    '/purchase/pr-order/pr-confirm',
                                    'id' => $model->id,
                                    'status' => 2,
                                ], ['class' => 'btn btn-primary rounded shadow pr-confirm']) ?>
                                <?php endif; ?>

                                <?php if ($model->approve == 'Y'): ?>
                                <?php foreach ($model->ListPrStatus() as $status): ?>
                                <?php if ($status->code == 5): ?>
                                <?= Html::a($status->title, ['/purchase/pq-order/create', 'category_id' => $model->code, 'title' => '<i class="fa-regular fa-circle-check"></i> ลงทะเบียนคุม'], ['class' => 'btn btn-primary rounded shadow open-modal shadow', 'data' => ['size' => 'modal-xl']]) ?>
                                <?php else: ?>
                                <?= $model->status == $status->code ? Html::a('<span class="badge rounded-pill bg-light text-dark">' . $status->code . '</span> ' . $status->title, ['/purchase/pr-order/confirm-status', 'id' => $model->id, 'status' => ($status->code + 1), 'title' => '<i class="fa-solid fa-circle-exclamation"></i> ' . $status->title], ['class' => 'btn btn-primary rounded shadow open-modal shadow', 'data' => ['size' => 'modal-md']]) : '' ?>
                                <?php endif; ?>

                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?= $this->render('step2', ['model' => $model]) ?>
        <?= $this->render('step3', ['model' => $model]) ?>

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