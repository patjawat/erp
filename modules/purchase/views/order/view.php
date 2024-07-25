<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\SiteHelper;
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
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h6><i class="fa-solid fa-circle-info text-primary"></i> ใบขอซื้อ/ขอจ้าง : <?php
                            try {
                                echo $model->data_json['order_type_name'];
                            } catch (\Throwable $th) {
                            }
                        ?></h6>
                <?=Html::a('<i class="bi bi-trash fw-bold"></i> ยกเลิกรายการ',['/purchase/po-order/update','id' => $model->id,'title' => '<i class="bi bi-pencil-square"></i> แก้ไขคำสั่งซื้อ'],['class' => 'btn btn-danger rounded-pill shadow text-center open-modal shadow me-5','data' => ['size' => 'modal-md']])?>
            </div>
        </div>


        <div class="card">
            <div class="card-body">

                <div class="row">
                    <div class="col-9">

                        <div class="border border-secondary border-opacity-25 p-3 rounded">
                            <div class="d-flex justify-between">


                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs" role="pillist" style="visibility: visible;">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="pill" href="#home1" role="pill"><i
                                                class="fa-solid fa-circle-info"></i> รายละเอียดการขอซื้อ</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="pill" href="#pq_detail" role="pill"><i
                                                class="fa-solid fa-user-tag"></i> ทะเบียนคุม</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="pill" href="#po_detail" role="pill"><i
                                                class="fa-solid fa-users"></i> คำสั่งซื้อ</a>
                                    </li>
                                </ul>

                            </div>

                            <!-- Tab panes -->
                            <div class="tab-content p-0">
                                <div id="home1" class="tab-pane active">
                                    <?= $this->render('detail', ['model' => $model]) ?>
                                </div>
                                <div id="pq_detail" class="tab-pane fade">
                                    <?= $this->render('pq_detail', ['model' => $model]) ?>

                                </div>
                                <div id="po_detail" class="container tab-pane fade">
                                    <?= $this->render('po_detail', ['model' => $model]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <?=$this->render('order_status')?>
                        <hr>
                        <div class="d-flex justify-content-center mt-3">
                            <?=Html::a('<i class="bi bi-printer-fill"></i> พิมพ์เอกสาร',['/purchase/order/document','id' => $model->id,'title' => '<i class="bi bi-printer-fill"></i> พิมพ์เอกสาร'],['class' => 'btn btn-primary rouned-pull shadow text-center rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
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
    <div class="col-4">
        <!-- ผู้อำนวยการอนุมัติ -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0"><span class="badge bg-primary rounded-pill text-white">3</span> ผู้อำนวยการอนุมัติ</h6>
                <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#Director"
                    aria-expanded="true" aria-controls="collapseCard">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>

            <div class="card-body collapse" id="Director">
                <!-- Start Flex Contriler -->
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-truncate">
                        <?= SiteHelper::viewDirector()['avatar'] ?>
                    </div>
                </div>
                <!-- End Flex Contriler -->
            </div>

            <div class="card-footer d-flex justify-content-between">
                <h6>การอนุมัติ</h6>
                <div>
                    <?php if($model->data_json['pr_director_confirm'] == 'Y'):?>
                    <?=Html::a('<i class="bi bi-check2-circle"></i> อนุมัติ',['/purchase/pr-order/director-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-success rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                    <?php elseif($model->data_json['pr_director_confirm'] == 'N'):?>
                    <?=Html::a('<i class="fa-solid fa-user-slash"></i> ไม่อนุมัติ',['/purchase/pr-order/director-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-danger rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                    <?php else:?>
                    <?=Html::a('<i class="fa-regular fa-clock"></i> รออนุมัติ',['/purchase/pr-order/director-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-warning rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                    <?php endif?>
                </div>
            </div>

        </div>
        <!-- จบส่วนผู้อำนวยการอนุมัติ -->


        <!-- ผู้ตรวจสอบ -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2">

                <h6 class="mb-0"><span class="badge bg-primary rounded-pill text-white">2</span> ผู้ตรวจสอบ</h6>
                <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#me"
                    aria-expanded="true" aria-controls="collapseCard">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <div class="card-body collapse" id="me">
                <!-- Start Flex Contriler -->
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-truncate">
                        <h6>ผู้ตรวจสอบ</h6>
                        <?= $model->getMe()['avatar'] ?>
                    </div>
                </div>
                <!-- End Flex Contriler -->
            </div>
            <div class="card-footer d-flex justify-content-between">

                <h6>จนท.พัสดุตรวจสอบ</h6>
                <div>
                    <?php if($model->data_json['pr_officer_checker'] == 'Y'):?>
                    <?=Html::a('<i class="bi bi-check2-circle"></i> ผ่าน',['/purchase/pr-order/checker-confirm','id' => $model->id],
                                ['class' => 'btn btn-sm btn-success rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                    <?php elseif($model->data_json['pr_officer_checker'] == 'N'):?>
                    <?=Html::a('<i class="fa-solid fa-user-slash"></i> ไม่ผ่าน',['/purchase/pr-order/checker-confirm','id' => $model->id],
                                ['class' => 'btn btn-sm btn-danger rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                    <?php else:?>
                    <?=Html::a('<i class="fa-regular fa-clock"></i> ตรวจสอบ',['/purchase/pr-order/checker-confirm','id' => $model->id],
                                ['class' => 'btn btn-warning rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                    <?php endif?>
                </div>
            </div>
        </div>
        <!-- จบส่วนผู้ตรวจสอบ -->


        <!-- ผู้เห็นชอบ -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0"><span class="badge bg-primary rounded-pill text-white">1</span> ผู้เห็นชอบ</h6>
                <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#leader"
                    aria-expanded="true" aria-controls="collapseCard">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <div class="card-body collapse" id="leader">
                <!-- Start Flex Contriler -->
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-truncate">
                        <?= $model->viewLeaderUser()['avatar'] ?>
                    </div>
                </div>
                <!-- End Flex Contriler -->
            </div>
            <div class="card-footer d-flex justify-content-between">

                <h6>อนุมัติ/เห็นชอบ</h6>
                <div>
                    <?php if($model->pr_number != ''):?>
                    <?php if($model->data_json['pr_leader_confirm'] == 'Y'):?>
                    <?=Html::a('<i class="bi bi-check2-circle"></i> เห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-success rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                    <?php elseif($model->data_json['pr_leader_confirm'] == 'N'):?>
                    <?=Html::a('<i class="fa-solid fa-user-slash"></i> ไม่เห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-danger rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                    <?php else:?>
                    <?=Html::a('<i class="fa-regular fa-clock"></i> รอเห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-warning rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                    <?php endif?>
                    <?php endif?>
                </div>
            </div>
        </div>
        <!-- จบส่วนผู้เห็นชอบ -->

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-person-circle"></i> กรรมการกำหนดรายละเอียด</h6>
                    <?= Html::a('<i class="bi bi-plus-circle-fill"></i>', [
                            '/purchase/order-item/committee','title' => 'กรรมการกำหนดรายละเอียด'
                        ], ['class' => 'open-modal','data' => ['size' => 'modal-lg']]) ?>
                </div>
                <?=$model->StackComitteeDetail()?>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <h6>กรรมการ</h6>
                <?=Html::a('เลือกกลุ่มกรรมการ',['/'],['class' => 'btn btn-sm btn-primary rounded-pill shadow'])?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-person-circle"></i> กรรมการตรวจรับ</h6>
                    <?= Html::a('<i class="bi bi-plus-circle-fill"></i>', [
                            '/purchase/order-item/committee','title' => 'กรรมการตรวจรับ'
                        ], ['class' => 'open-modal','data' => ['size' => 'modal-lg']]) ?>
                </div>
                <?=$model->StackComittee()?>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <h6>กรรมการ</h6>
                <?=Html::a('เลือกกลุ่มกรรมการ',['/'],['class' => 'btn btn-sm btn-primary rounded-pill shadow'])?>
            </div>
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