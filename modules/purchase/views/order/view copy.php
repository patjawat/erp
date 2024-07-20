<?php
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Order $model */
$this->title = 'ขอซื้อขอจ้าง';
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>


<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/sm/views/default/menu') ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'purchase-container']); ?>
<div class="row">
    <div class="col-8">



        <div class="row justify-content-center">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h6><i class="fa-solid fa-circle-info text-primary"></i> ใบขอซื้อ/ขอจ้าง</h6>
                            <div class="dropdown float-end">
                                <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" style="">
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                    <?= Html::a('<i class="fa-regular fa-file-word me-1"></i> พิมพ์', ['/ms-word/purchase_3', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>

                                </div>
                            </div>
                        </div>
                        <div class="border border-secondary border-opacity-25 p-3 rounded">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="pillist" style="visibility: visible;">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="pill" href="#home1" role="pill"><i
                                            class="fa-solid fa-circle-info"></i> รายละเอียดการขอซื้อ</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="pill" href="#list_committee_detail"
                                        role="pill"><i class="fa-solid fa-user-tag"></i> กรรมการกำหนดรายละเอียด</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="pill" href="#list_committee" role="pill"><i
                                            class="fa-solid fa-users"></i> กรรมการตรวจรับ</a>
                                </li>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content p-0">
                                <div id="home1" class="tab-pane active">
                                    <?= $this->render('detail', ['model' => $model]) ?>
                                </div>
                                <div id="list_committee_detail" class="tab-pane fade">
                                    <?= $this->render('@app/modules/purchase/views/order-item/list_committee_detail', ['model' => $model]) ?>

                                </div>
                                <div id="list_committee" class="container tab-pane fade">
                                    <?= $this->render('@app/modules/purchase/views/order-item/list_committee', ['model' => $model]) ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-5">
                            <h6><i class="fa-solid fa-circle-info text-primary"></i> รายการขอซื้อ/ขอจ้าง</h6>

                        </div>
                        <?= $this->render('@app/modules/purchase/views/order/order_items', ['model' => $model]) ?>

                        <div class="row d-flex justify-content-end">
                            <div class="col-4">
                                <div class="d-grid gap-2">

                                    <?php if ($model->status == '' && count($model->ListOrderItems()) > 0): ?>
                                    <?= Html::a('<i class="fa-solid fa-circle-exclamation"></i> ส่งคำขอซื้อ', [
                            '/purchase/pr-order/pr-confirm',
                            'id' => $model->id,
                            'status' => 1,
                        ], ['class' => 'btn btn-primary rounded shadow confirm-order','data' => ['title' => 'ยืนยัน?','text' => 'ส่งคำขอซื้อเพื่อรอการพิจารณา']]) ?>
                                    <?php endif; ?>

                                    <?php if($model->status == 1 && $model->data_json['pr_leader_confirm'] == 'Y' && $model->data_json['pr_officer_checker'] == 'Y' && $model->data_json['pr_director_confirm'] == 'Y'):?>
                                    <?= Html::a('<i class="fa-solid fa-circle-exclamation"></i> ลงทะเบียนคุม', [
                            '/purchase/pq-order/update',
                            'id' => $model->id,

                        ], ['class' => 'btn btn-primary rounded shadow open-modal','data' => ['size' => 'modal-lg']]) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>



    </div>
    <div class="col-4">

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6>อนุมัติ/เห็นชอบ</h6>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <a class="dropdown-item open-modal"
                                href="/purchase/order/update?id=6&amp;title=%3Ci+class%3D%22fa-regular+fa-pen-to-square%22%3E%3C%2Fi%3E+%E0%B9%81%E0%B8%81%E0%B9%89%E0%B9%84%E0%B8%82"
                                data-size="modal-md"><i class="fa-regular fa-pen-to-square me-1"></i> ดำเนินการ</a>
                        </div>
                    </div>
                </div>

<!-- Start` -->
                <div class="d-flex flex-column gap-3">

               
                <div>
                    <?= $model->viewLeaderUser()['avatar'] ?>

                    <?php if($model->pr_number != ''):?>
                    <?php if($model->data_json['pr_leader_confirm'] == 'Y'):?>
                    <?=Html::a('<i class="bi bi-check2-circle"></i> เห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-success open-modal','data' => ['size' => 'modal-md']])?>
                    <?php elseif($model->data_json['pr_leader_confirm'] == 'N'):?>
                    <?=Html::a('<i class="fa-solid fa-user-slash"></i> ไม่เห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-danger open-modal','data' => ['size' => 'modal-md']])?>
                    <?php else:?>
                    <?=Html::a('<i class="fa-regular fa-clock"></i> รอเห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-warning open-modal','data' => ['size' => 'modal-md']])?>
                    <?php endif?>
                    <?php endif?>
                </div>
                
                <?php if($model->data_json['pr_leader_confirm'] == 'Y'):?>
                    <div>
                        <?= $model->getMe()['avatar'] ?>
                        <?php if($model->data_json['pr_officer_checker'] == 'Y'):?>
                <?=Html::a('<i class="bi bi-check2-circle"></i> ผ่าน',['/purchase/pr-order/checker-confirm','id' => $model->id],
                                ['class' => 'btn btn-sm btn-success open-modal','data' => ['size' => 'modal-md']])?>
                <?php elseif($model->data_json['pr_officer_checker'] == 'N'):?>
                <?=Html::a('<i class="fa-solid fa-user-slash"></i> ไม่ผ่าน',['/purchase/pr-order/checker-confirm','id' => $model->id],
                                ['class' => 'btn btn-sm  btn-danger open-modal','data' => ['size' => 'modal-md']])?>
                <?php else:?>
                <?=Html::a('<i class="fa-regular fa-clock"></i> ตรวจสอบ',['/purchase/pr-order/checker-confirm','id' => $model->id],
                                ['class' => 'btn btn-sm btn-warning open-modal','data' => ['size' => 'modal-md']])?>
                <?php endif?>

                    </div>
                    <?php endif?>


                    </div>
                    <!-- End  -->
            </div>
            <div class="card-footer"></div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6>กรรมการกำหนดรายละเอียด</h6>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> ดำเนินการ', [
                            '/purchase/order-item/committee-detail','title' => 'กรรมการกำหนดรายละเอียด'
                        ], ['class' => 'dropdown-item rounded shadow open-modal','data' => ['size' => 'modal-lg']]) ?>
                        </div>
                    </div>
                </div>
                <?=$model->StackComitteeDetail()?>
            </div>
            <div class="card-footer"></div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6>กรรมการตรวจรับ</h6>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> กำเนินการ', [
                            '/purchase/order-item/committee','title' => 'กรรมการตรวจรับ'
                        ], ['class' => 'dropdown-item rounded shadow open-modal','data' => ['size' => 'modal-lg']]) ?>
                        </div>
                    </div>
                </div>
                <?=$model->StackComittee()?>
            </div>
            <div class="card-footer"></div>
        </div>


    </div>
</div>

<?php
$js = <<< JS


    \$("body").on("click", ".confirm-order", async function (e) {
      e.preventDefault();
      var url = \$(this).attr("href");
      var title = \$(this).data('title');
      var text = \$(this).data('text');
      var size = $(this).data("size");
      console.log($(this).data('title'));
      await Swal.fire({
        title: title,
        text: text,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "ใช่, ยืนยัน!",
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