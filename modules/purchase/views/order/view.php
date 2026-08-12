<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Order $model */
$this->title = 'แสดงรายละเอียด';
$this->params['breadcrumbs'][] = ['label' => 'ขอซื้อขอจ้าง', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
       <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt-text-icon lucide-receipt-text"><path d="M13 16H8"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M4 3a1 1 0 0 1 1-1 1.3 1.3 0 0 1 .7.2l.933.6a1.3 1.3 0 0 0 1.4 0l.934-.6a1.3 1.3 0 0 1 1.4 0l.933.6a1.3 1.3 0 0 0 1.4 0l.933-.6a1.3 1.3 0 0 1 1.4 0l.934.6a1.3 1.3 0 0 0 1.4 0l.933-.6A1.3 1.3 0 0 1 19 2a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1 1.3 1.3 0 0 1-.7-.2l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.934.6a1.3 1.3 0 0 1-1.4 0l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-1.4 0l-.934-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-.7.2 1 1 0 0 1-1-1z"/></svg>
        <?= $this->title ?>ขอซื้อขอจ้าง
    </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<a href="<?= Url::to(['/purchase/order']) ?>"
    class="btn btn-primary">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-move-left-icon lucide-move-left">
        <path d="M6 8L2 12L6 16" />
        <path d="M2 12H22" />
    </svg>
    ย้อนกลับ
</a>
<?php $this->endBlock(); ?>



<?php if ($model->status == 7): ?>
    <div class="row d-flex justify-content-center">
        <div class="col-4">
            <div class="card text-bg-secondary mb-3" style="max-width: 18rem;">
                <div class="card-body">
                    <div class="text-white h5"><i class="fa-solid fa-triangle-exclamation text-danger"></i> รายการถูกยกเลิก</div>
                    <p class="card-text"><?php echo $model->data_json['cancel_order_note'] ?? '-' ?></p>
                </div>
            </div>
        </div>
    </div>



<?php else: ?>


    <?php Pjax::begin(['id' => 'purchase-container', 'timeout' => 88888888]); ?>

    <?php
    try {
        $orderTypeName =  $model->data_json['order_type_name'];
    } catch (\Throwable $th) {
        $orderTypeName = '';
    }
    ?>
    <div class="row">
        <div class="col-8">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <h6><i class="fa-solid fa-circle-info text-primary"></i> ใบขอซื้อ/ขอจ้าง : <?= $orderTypeName ?></h6>

                    <?= Html::a('<i class="bi bi-trash fw-bold"></i> ยกเลิกรายการ', ['/purchase/order/cancel-order', 'id' => $model->id], ['class' => 'btn btn-danger shadow open-modal', 'data' => ['size' => 'modal-md']]) ?>

                </div>
            </div>

            <div class="card">
                <div class="card-body">

                    <div class="row">
                        <div class="col-9">

                            <div class="border border-secondary border-opacity-25 p-3 rounded">
                                <div class="d-flex justify-content-between">
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs" role="pillist" style="visibility: visible;">
                                        <li class="nav-item">
                                            <a class="nav-link <?= $model->status <= 1 ? 'active' : null; ?>" data-bs-toggle="pill" href="#home1" role="pill"><span
                                                    class="badge bg-primary rounded-pill text-white">1</span> ขอซื้อ</a>
                                        </li>
                                        <?php if ($model->status >= 2): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?= $model->status == 2 ? 'active' : null; ?>" data-bs-toggle="pill" href="#pq_detail" role="pill"><span
                                                        class="badge bg-primary rounded-pill text-white">2</span> ทะเบียนคุม</a>
                                            </li>
                                        <?php endif ?>
                                        <?php if ($model->status >= 3): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?= $model->status == 3 ? 'active' : null; ?>" data-bs-toggle="pill" href="#po_detail" role="pill"><span
                                                        class="badge bg-primary rounded-pill text-white">3</span> คำสั่งซื้อ</a>
                                            </li>
                                        <?php endif ?>

                                        <?php if ($model->status >= 4): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?= $model->status == 4 ? 'active' : null; ?>" data-bs-toggle="pill" href="#gr_detail" role="pill"><span
                                                        class="badge bg-primary rounded-pill text-white">4</span> ตรวจรับ</a>
                                            </li>
                                        <?php endif ?>

                                        <?php if ($model->status >= 5 && $model->category_id != 'M25'): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?= $model->status == 6 ? 'active' : null; ?>" data-bs-toggle="pill" href="#warehouse_detail" role="pill"><span
                                                        class="badge bg-primary rounded-pill text-white">5</span> <?= $model->group_id == 3 ? 'ทะเบียนทรัพสินย์'  : 'คลัง' ?></a>
                                            </li>
                                        <?php endif ?>
                                        <?php if ($model->status >= 7): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?= $model->status == 7 ? 'active' : null; ?>" data-bs-toggle="pill" href="#accounting_detail" role="pill"><span
                                                        class="badge bg-primary rounded-pill text-white">7</span> ส่งบัญชี</a>
                                            </li>
                                        <?php endif ?>
                                    </ul>
                                    <div class="dropdown float-end">
                                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">

                                        </div>
                                    </div>

                                </div>

                                <!-- Tab panes -->
                                <div class="tab-content p-0">
                                    <div id="home1" class="tab-pane <?= $model->status <= 1 ? 'active' : null; ?>">
                                        <?= $this->render('detail', ['model' => $model]) ?>
                                    </div>
                                    <div id="pq_detail" class="tab-pane <?= ($model->status == 2) ? 'active' : null; ?>">
                                        <?= $this->render('pq_detail', ['model' => $model]) ?>

                                    </div>
                                    <div id="po_detail" class="container tab-pane <?= $model->status == 3 ? 'active' : null; ?>">
                                        <?= $this->render('po_detail', ['model' => $model]) ?>
                                    </div>

                                    <div id="gr_detail" class="container tab-pane <?= $model->status == 4 ? 'active' : null; ?>">
                                        <?= $this->render('gr_detail', ['model' => $model]) ?>
                                    </div>
                                    <?php if ($model->category_id != 'M25'): ?>
                                        <div id="warehouse_detail" class="container tab-pane <?= $model->status == 5 ? 'active' : null; ?>">
                                            <!-- คลัง -->
                                            <?= $this->render('warehouse_detail', ['model' => $model]) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div id="accounting_detail" class="container tab-pane <?= $model->status == 6 ? 'active' : null; ?>">
                                        <?= $this->render('accounting_detail', ['model' => $model]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <?= $this->render('order_status', ['model' => $model]) ?>
                            <hr>
                            <div class="d-flex justify-content-center mt-3">
                                <?= Html::a('<i class="bi bi-printer-fill"></i> พิมพ์เอกสาร', ['/purchase/order/document', 'id' => $model->id, 'title' => '<i class="bi bi-printer-fill"></i> พิมพ์เอกสาร'], ['class' => 'btn btn-primary rouned-pull shadow text-center open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-5">
                        <h6><i class="fa-solid fa-circle-info text-primary"></i> รายการขอซื้อ/ขอจ้าง</h6>

                    </div>
                    <?php echo $this->render('@app/modules/purchase/views/order/order_items', ['model' => $model]) ?>
                    <?php
                    // print_r($model->calculateVAT());
                    ?>

                    <div class="row d-flex justify-content-end">
                        <div class="col-4">
                            <div class="d-grid gap-2">

                                <?php if ($model->status == '' && count($model->ListOrderItems()) > 0): ?>
                                    <?= Html::a('<i class="fa-solid fa-circle-exclamation"></i> ส่งคำขอซื้อ', [
                                        '/purchase/pr-order/pr-confirm',
                                        'id' => $model->id,
                                        'status' => 1,
                                    ], ['class' => 'btn btn-primary rounded shadow confirm-order', 'data' => ['title' => 'ยืนยัน?', 'text' => 'ส่งคำขอซื้อเพื่อรอการพิจารณา']]) ?>
                                <?php endif; ?>

                                <?php if ($model->status == 1 && $model->data_json['pr_leader_confirm'] == 'Y' && $model->data_json['pr_officer_checker'] == 'Y' && $model->data_json['pr_director_confirm'] == 'Y'): ?>
                                    <?= Html::a('<i class="fa-solid fa-circle-exclamation"></i> ลงทะเบียนคุม', [
                                        '/purchase/pq-order/update',
                                        'id' => $model->id,

                                    ], ['class' => 'btn btn-primary rounded shadow open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-4">
            <!-- ผู้ตรวจสอบและอนุมัต -->
            <?php // $this->render('checker',['model' => $model])
            ?>
            <div class="card">
                <div class="card-body">
                    <?= $this->render('@app/modules/approve/views/approve/level_approve_v2', ['model' => $model, 'name' => 'purchase']) ?>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h6><i class="bi bi-person-circle"></i> กรรมการกำหนดรายละเอียด</h6>

                    </div>
                    <?= $model->StackComitteeDetail() ?>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <?= Html::a('รายการ', [
                        '/purchase/order-item/committee-detail',
                        'category_id' => $model->id,
                        'title' => '<i class="bi bi-person-circle"></i> กรรมการกำหนดรายละเอียด'
                    ], ['class' => 'open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                    <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> เพิ่มกรรมการ', ['/purchase/order-item/create', 'id' => $model->id, 'name' => 'committee_detail', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการกำหนดรายละเอียด'], ['class' => 'btn btn-sm btn-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h6><i class="bi bi-person-circle"></i> กรรมการตรวจรับ</h6>
                    </div>
                    <?= $model->StackComittee() ?>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <?= Html::a('รายการ', [
                        '/purchase/order-item/committee',
                        'category_id' => $model->id,
                        'title' => '<i class="bi bi-person-circle"></i> กรรมการตรวจรับ'
                    ], ['class' => 'open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                    <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> เพิ่มกรรมการ', ['/purchase/order-item/create', 'id' => $model->id, 'name' => 'committee', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>
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


<?php endif ?>