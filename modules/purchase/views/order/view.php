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
                <h6><i class="fa-solid fa-circle-info text-primary"></i> ใบขอซื้อ/ขอจ้าง : <?=$orderTypeName?></h6>
                <?=Html::a('<i class="bi bi-trash fw-bold"></i> ยกเลิกรายการ',['/purchase/po-order/update','id' => $model->id,'title' => '<i class="bi bi-pencil-square"></i> แก้ไขคำสั่งซื้อ'],['class' => 'btn btn-danger rounded-pill shadow text-center open-modal shadow me-5','data' => ['size' => 'modal-md']])?>
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
                                        <a class="nav-link <?=$model->status == '' ? 'active' : null;?>" data-bs-toggle="pill" href="#home1" role="pill"><span
                                                class="badge bg-primary rounded-pill text-white">1</span>
                                            รายละเอียดการขอซื้อ</a>
                                    </li>
                                    <?php if($model->data_json['pr_director_confirm'] == 'Y'):?>
                                    <li class="nav-item">
                                        <a class="nav-link <?=$model->status == 1 ? 'active' : null;?>" data-bs-toggle="pill" href="#pq_detail" role="pill"><span
                                                class="badge bg-primary rounded-pill text-white">2</span> ทะเบียนคุม</a>
                                    </li>
                                    <?php endif?>
                                    <?php if($model->status >= 2):?>
                                        <li class="nav-item">
                                            <a class="nav-link <?=$model->status == 2 ? 'active' : null;?>" data-bs-toggle="pill" href="#po_detail" role="pill"><span
                                            class="badge bg-primary rounded-pill text-white">3</span> คำสั่งซื้อ</a>
                                        </li>
                                        <?php endif?>

                                        <?php if($model->status >= 3):?>
                                        <li class="nav-item">
                                            <a class="nav-link <?=$model->status == 4 ? 'active' : null;?>" data-bs-toggle="pill" href="#gr_detail" role="pill"><span
                                            class="badge bg-primary rounded-pill text-white">3</span> ตรวจรับ</a>
                                        </li>
                                        <?php endif?>
                                </ul>
                                <div class="dropdown float-end">
                                    <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right" style="">
                                       
                                    </div>
                                </div>

                            </div>

                            <!-- Tab panes -->
                            <div class="tab-content p-0">
                                <div id="home1" class="tab-pane <?=$model->status == '' ? 'active' : null;?>">
                                    <?= $this->render('detail', ['model' => $model]) ?>
                                </div>
                                <div id="pq_detail" class="tab-pane <?=$model->status == 1 ? 'active' : null;?>">
                                    <?= $this->render('pq_detail', ['model' => $model]) ?>

                                </div>
                                <div id="po_detail" class="container tab-pane <?=$model->status == 2 ? 'active' : null;?>">
                                    <?= $this->render('po_detail', ['model' => $model]) ?>
                                </div>

                                <div id="gr_detail" class="container tab-pane <?=$model->status == 4 ? 'active' : null;?>">
                                    <?= $this->render('gr_detail', ['model' => $model]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <?=$this->render('order_status',['model' => $model])?>
                        <hr>
                        <div class="d-flex justify-content-center mt-3">
                            <?=Html::a('<i class="bi bi-printer-fill"></i> พิมพ์เอกสาร',['/purchase/order/document','id' => $model->id,'title' => '<i class="bi bi-printer-fill"></i> พิมพ์เอกสาร'],['class' => 'btn btn-primary rouned-pull shadow text-center rounded-pill open-modal','data' => ['size' => 'modal-lg']])?>
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
        <!-- ผู้ตรวจสอบและอนุมัต -->
       <?=$this->render('checker',['model' => $model])?>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-person-circle"></i> กรรมการกำหนดรายละเอียด</h6>

                </div>
                <?=$model->StackComitteeDetail()?>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <h6>กรรมการ</h6>
                <?= Html::a('ดำเนินการ', [
                            '/purchase/order-item/committee-detail','title' => '<i class="bi bi-person-circle"></i> กรรมการกำหนดรายละเอียด'
                        ], ['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']]) ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-person-circle"></i> กรรมการตรวจรับ</h6>
                </div>
                <?=$model->StackComittee()?>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <h6>กรรมการ</h6>
                <?= Html::a('ดำเนินการ', [
                            '/purchase/order-item/committee','title' => '<i class="bi bi-person-circle"></i> กรรมการตรวจรับ'
                        ], ['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']]) ?>
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