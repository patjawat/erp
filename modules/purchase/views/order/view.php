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
?>
<?php Pjax::begin(['id' => 'purchase-container']); ?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/sm/views/default/menu') ?>
<?php $this->endBlock(); ?>
<div class="card">
    <div class="card-body">
        <h5>ขอซื้อขอจ้าง</h5>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-2 col-md-4 col-sm-12">
        <?= $this->render('timeline', ['model' => $model]) ?>
    </div>
    <div class="col-lg-8 col-md-8 col-sm-12">
        <div class="d-flex justify-content-between align-items-center">

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="process-tab" data-bs-toggle="tab" data-bs-target="#process"
                        type="button" role="tab" aria-controls="process" aria-selected="true"><i
                            class="fas fa-file-alt fa-fw"></i> กระบวนการการขอซื้อขอจ้าง</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="board-tab" data-bs-toggle="tab" data-bs-target="#board"
                        type="button" role="tab" aria-controls="board" aria-selected="true"><i
                            class="fas fa-file-alt fa-fw"></i> กรรมการตรวจรับ</button>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="additional-tab" data-bs-toggle="tab" data-bs-target="#additional"
                        type="button" role="tab" aria-controls="additional" aria-selected="false"><i
                            class="far fa-list-alt fa-fw"></i> ใบเสนอราคา/เพิ่มเติมอื่นๆ...</button>
                </li>
            </ul>
            <?= Html::a('<i class="fa-solid fa-print"></i> พิมพ์เอกสาร', ['/purchase/order/document', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> พิมพ์เอกสารประกอบการจัดซื้อ'], ['class' => 'btn btn-light open-modal', 'data' => ['size' => 'modal-md']]) ?>
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="process" role="tabpanel" aria-labelledby="process-tab">
                <table class="table table-striped-columns">
                    <tbody>
                        <?= $this->render('step1', ['model' => $model]) ?>
                        <?= $model->pq_number ? $this->render('step2', ['model' => $model]) : '' ?>
                        <?= $model->po_number ? $this->render('step3', ['model' => $model]) : '' ?>
                    </tbody>
                </table>
                <?= $this->render('list_items', ['model' => $model]) ?>
            </div>
            <div class="tab-pane fade" id="board" role="tabpanel" aria-labelledby="board-tab">
                <?= $this->render('list_board', ['model' => $model]) ?>
            </div>
            <div class="tab-pane fade" id="additional" role="tabpanel" aria-labelledby="additional-tab">
                <?= $this->render('_view_order_files', ['model' => $model]) ?>
            </div>
        </div>


    </div>
    <!-- End col-8 -->
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