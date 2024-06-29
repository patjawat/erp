<?php

use app\modules\purchase\models\Order;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Order $model */
/** @var yii\widgets\ActiveForm $form */
$listPqNumber = ArrayHelper::map(Order::find()->where(['name' => 'order'])->all(), 'id', 'pq_number');
?>

<?php Pjax::begin(['id' => 'purchase-container']); ?>
<?php //  $this->render('../default/menu2') ?>

<div class="card">
    <div class="card-body">
        <h6><i class="fa-solid fa-cart-shopping"></i> ใบสั่งซื้อสินค้า (PO)</h6>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-2 col-md-4 col-sm-12">
        <?= $this->render('../order/timeline', ['model' => $model]) ?>
    </div>

    <div class="col-lg-9 col-md-9 col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="mt-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <?php if ($model->status >= 5): ?>
                                <a class="<?= $model->status == 5 ? 'nav-link  active' : 'nav-link ' ?>"
                                    data-bs-toggle="tab" href="#importStore"><span
                                        class="badge rounded-pill bg-body text-primary">5</span> รับเข้าคลัง</a>
                            </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <?php if ($model->status >= 4): ?>
                                <a class="<?= $model->status == 4 ? 'nav-link  active' : 'nav-link ' ?>"
                                    data-bs-toggle="tab" href="#checker"><span
                                        class="badge rounded-pill bg-body text-primary">4</span> ตรวจรับวัสดุ</a>
                            </li>
                            <?php endif; ?>

                            <li class="nav-item">
                                <?php if ($model->status >= 3): ?>
                                <a class="<?= $model->status == 3 ? 'nav-link  active' : 'nav-link ' ?>"
                                    data-bs-toggle="tab" href="#basic-data"><span
                                        class="badge rounded-pill bg-body text-primary">3</span> ใบสั่งซื้อ</a>
                            </li>
                            <?php endif; ?>


                            <?php if ($model->status >= 2): ?>
                            <li class="<?= $model->status == 2 ? 'nav-item  active' : 'nav-item ' ?>">
                                <a class="nav-link" data-bs-toggle="tab" href="#pq-detail"><span
                                        class="badge rounded-pill bg-primary text-white">2</span> ทะเบียนคุม</a>
                            </li>
                            <?php endif; ?>
                            <?php if ($model->status == '' || $model->status >= 1): ?>
                            <li class="<?= $model->status == '' ? 'nav-item active' : 'nav-item ' ?>">
                                <a class="nav-link" data-bs-toggle="tab" href="#po-detail"><span
                                        class="badge rounded-pill bg-primary text-white">1</span> ขอซื้อ/ขอจ้าง</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                        <?= Html::a('<i class="fa-solid fa-print"></i> พิมพ์เอกสาร', ['/purchase/order/document', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> พิมพ์เอกสารประกอบการจัดซื้อ'], ['class' => 'btn btn-light mb-4 open-modal', 'data' => ['size' => 'modal-md']]) ?>
                    </div>
                    <div class="tab-content mt-3">
                        <div class="tab-pane <?= $model->status == 5 ? 'active' : '' ?>" id="importStore">
                            <div class="d-flex justify-content-between align-items-center">
                                <h1>รับเข้าคลัง</h1>
                                <?= Html::a('<i class="fa-solid fa-circle-exclamation"></i> รับเข้าคลัง', [
                                    '/purchase/order/confirm-status',
                                    'id' => $model->id,
                                    'status' => 6,
                                ], ['class' => 'btn btn-primary rounded shadow pr-confirm']) ?>
                            </div>
                            <?= $this->render('../order/_view_order_files', ['model' => $model]) ?>
                        </div>


                        <div class="tab-pane <?= $model->status == 4 ? 'active' : '' ?>" id="checker">
                            <div class="d-flex justify-content-between align-items-center">
                                <h1>ตรวจรับ</h1>
                                <?= Html::a('<i class="fa-solid fa-circle-exclamation"></i> ส่งคำขอซื้อ', [
                                    '/purchase/order/confirm-status',
                                    'id' => $model->id,
                                    'status' => 5,
                                ], ['class' => 'btn btn-primary rounded shadow pr-confirm']) ?>
                            </div>
                            <?= $this->render('../order/_view_order_files', ['model' => $model]) ?>
                        </div>

                        <div class="tab-pane <?= $model->status == 3 ? 'active' : '' ?>" id="basic-data">
                            <?= $this->render(
                                '../po-order/_form',
                                ['model' => $model]
                            ) ?>
                        </div>
                        <div class="tab-pane <?= $model->status == '' ? 'active' : '' ?>" id="po-detail">
                            <table class="table table-striped-columns">
                                <tbody>
                                    <?= $this->render(
                                        '../order/step1',
                                        ['model' => $model]
                                    ) ?>
                                </tbody>
                            </table>
                            <?= $this->render('../order/list_board_detail', ['model' => $model]) ?>
                            <?= $this->render('../order/list_board', ['model' => $model]) ?>
                            <?= $this->render('list_items', ['model' => $model]) ?>
                        </div>
                        <div class="tab-pane <?= $model->status == 2 ? 'active' : '' ?>" id="pq-detail">
                            <table class="table table-striped-columns">
                                <tbody>
                                    <?= $this->render(
                                        '../order/step2',
                                        ['model' => $model]
                                    ) ?>
                                </tbody>
                            </table>
                            <?= $this->render('../order/_view_order_files', ['model' => $model]) ?>
                        </div>



                        <!-- End Tabs1 -->

                    </div>

                    <!-- End tabs Content -->
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



    \$('#order-id').on("select2:unselect", function (e) { 
        console.log("select2:unselect", e);
        window.location.href ='/purchase/po-order/create'
    });
    // function getId(id){
    //     window.location.href = Url::to(['/purchase/po-order/create'])
    // }
    JS;
$this->registerJS($js)
?>

<?php Pjax::end() ?>