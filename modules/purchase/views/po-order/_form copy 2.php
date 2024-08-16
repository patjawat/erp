<?php

use app\modules\purchase\models\Order;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\components\SiteHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\datecontrol\DateControl;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Order $model */
/** @var yii\widgets\ActiveForm $form */
$listPqNumber = ArrayHelper::map(Order::find()->where(['name' => 'order'])->all(), 'id', 'pq_number');
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/sm/views/default/menu') ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'purchase']); ?>

<div class="row">
<div class="col-8">

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5><i class="fa-solid fa-circle-info text-primary"></i> ใบสั่งซื้อสินค้า</h5>

            <div class="dropdown float-end">
                <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fa-solid fa-ellipsis"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                    <?= Html::a('<i class="fa-regular fa-file-word me-1"></i> พิมพ์', ['/ms-word/purchase_3', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                    <?= Html::a('<i class="bx bx-trash text-danger me-1"></i> ลบ', ['/sm/asset-type/delete', 'id' => $model->id], [
                            'class' => 'dropdown-item  delete-item',
                            ]) ?>
                </div>
            </div>
        </div>
        <br>
        <?php $form = ActiveForm::begin([
                    'action' => ['/purchase/po-order/update', 'id' => $model->id],
                    // 'type' => ActiveForm::TYPE_HORIZONTAL,
                    'fieldConfig' => ['labelSpan' => 4, 'options' => ['class' => 'form-group mb-1 mr-2 me-2']]
                ]); ?>


        <div class="row">
            <div class="col-6">

                <table class="table table-striped-columns mt-4">
                    <tbody>
                        <tr>
                            <td class="text-end">ผู้ขอ</td>
                            <td colspan="3"> <?= $model->getUserReq()['avatar'] ?></td>

                        </tr>
                        <tr class="">
                            <td class="text-end" style="width:150px;">เลขที่ขอซื้อ</td>
                            <td class="fw-semibold"><?= $model->pr_number ?></td>
                            <td class="text-end">ประเภท</td>
                            <td> <?= $model->data_json['product_type_name']?></td>
                        </tr>
                        <tr class="">

                            <td class="text-end">ผู้ขาย</td>
                            <td colspan="3"><?= $model->data_json['vendor_name']?></td>

                        </tr>

                    </tbody>
                </table>
            </div>
            <div class="col-6">
                <div class="row">

                    <div class="col-6">

                        <?= $form->field($model, 'data_json[po_date]')
                                ->widget(DateControl::classname(), [
                                    'type' => DateControl::FORMAT_DATE,
                                    'language' => 'th',
                                    'widgetOptions' => [
                                        'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                                        'pluginOptions' => [
                                            'autoclose' => true
                                        ]
                                    ]
                                ])->label('ลงวันที่') ?>
                        <?= $form->field($model, 'data_json[warranty]')
                                                    ->widget(DateControl::classname(), [
                                    'type' => DateControl::FORMAT_DATE,
                                    'language' => 'th',
                                    'widgetOptions' => [
                                        'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                                        'pluginOptions' => [
                                            'autoclose' => true
                                        ]
                                    ]
                                ])->label('การรับประกัน') ?>
                        <?= $form->field($model, 'data_json[order_receipt_date]')->widget(DateControl::classname(), [
                                    'type' => DateControl::FORMAT_DATE,
                                    'language' => 'th',
                                    'widgetOptions' => [
                                        'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                                        'pluginOptions' => [
                                            'autoclose' => true
                                        ]
                                    ]
                                ])->label('วันที่รับใบสั่ง') ?>

                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'data_json[delivery_date]')->widget(DateControl::classname(), [
                                    'type' => DateControl::FORMAT_DATE,
                                    'language' => 'th',
                                    'widgetOptions' => [
                                        'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                                        'pluginOptions' => [
                                            'autoclose' => true
                                        ]
                                    ]
                                ])->label('กำหนดวันส่งมอบ') ?>
                        <?= $form->field($model, 'data_json[credit_days]')->textInput()->label('ครดิต (วัน)') ?>
                        <?= $form->field($model, 'data_json[signing_date]')->widget(DateControl::classname(), [
                                    'type' => DateControl::FORMAT_DATE,
                                    'language' => 'th',
                                    'widgetOptions' => [
                                        'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                                        'pluginOptions' => [
                                            'autoclose' => true
                                        ]
                                    ]
                                ])->label('วันที่ลงนาม') ?>

                    </div>
                </div>

            </div>
        </div>
        <div class="mt-3"></div>
        <?= $this->render('@app/modules/purchase/views/order/order_items', ['model' => $model]) ?>
        <div class="row d-flex justify-content-end">
            <div class="col-md-4 gap-3">
                <div class="d-grid gap-2">
                    <?= Html::submitButton('บันทึก', ['class' => 'btn btn-primary shadow']) ?>
                </div>
            </div>
        </div>
        <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>




        <?php ActiveForm::end(); ?>


    </div>
</div>


    
</div>
<div class="col-4">

<?=$this->render('@app/modules/purchase/views/order/order_status')?>
        <!-- ผู้อำนวยการอนุมัติ -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0">ผู้อำนวยการอนุมัติ</h6>
                <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#Director"
                    aria-expanded="true" aria-controls="collapseCard">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>

            <div class="card-body collapse <?=$model->data_json['pr_director_confirm'] == 'Y' ? '' : 'show'?>"
                id="Director">
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
                                ['class' => 'btn btn-sm btn-success rounded-pill  open-modal','data' => ['size' => 'modal-md']])?>
                    <?php elseif($model->data_json['pr_director_confirm'] == 'N'):?>
                    <?=Html::a('<i class="fa-solid fa-user-slash"></i> ไม่อนุมัติ',['/purchase/pr-order/director-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-danger rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                    <?php else:?>
                    <?=Html::a('<i class="fa-regular fa-clock"></i> รออนุมัติ',['/purchase/pr-order/director-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-warning rounded-pill  open-modal','data' => ['size' => 'modal-md']])?>
                    <?php endif?>
                </div>
            </div>

        </div>
        <!-- จบส่วนผู้อำนวยการอนุมัติ -->


        <!-- ผู้ตรวจสอบ -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0">ผู้ตรวจสอบ</h6>
                <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#me"
                    aria-expanded="true" aria-controls="collapseCard">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <div class="card-body collapse <?=$model->data_json['pr_officer_checker'] == 'Y' ? '' : 'show'?>" id="me">
                <!-- Start Flex Contriler -->
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-truncate">
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
                                ['class' => 'btn btn-sm btn-success rounded-pill  open-modal','data' => ['size' => 'modal-md']])?>
                    <?php elseif($model->data_json['pr_officer_checker'] == 'N'):?>
                    <?=Html::a('<i class="fa-solid fa-user-slash"></i> ไม่ผ่าน',['/purchase/pr-order/checker-confirm','id' => $model->id],
                                ['class' => 'btn btn-sm btn-danger rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                    <?php else:?>
                    <?=Html::a('<i class="fa-regular fa-clock"></i> ตรวจสอบ',['/purchase/pr-order/checker-confirm','id' => $model->id],
                                ['class' => 'btn btn-sm btn-warning rounded-pill  open-modal','data' => ['size' => 'modal-md']])?>
                    <?php endif?>
                </div>
            </div>
        </div>
        <!-- จบส่วนผู้ตรวจสอบ -->


        <!-- ผู้เห็นชอบ -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0">ผู้เห็นชอบ</h6>
                <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#leader"
                    aria-expanded="true" aria-controls="collapseCard">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <div class="card-body collapse <?=$model->data_json['pr_leader_confirm'] == 'Y' ? '' : 'show'?>"
                id="leader">
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
                                ['class' => 'btn btn-sm btn-success  rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
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
                    <h6>กรรมการกำหนดรายละเอียด</h6>
                    <?= Html::a('<i class="bi bi-plus-circle-fill"></i>', [
                            '/purchase/order-item/committee-detail','title' => 'กรรมการตรวจรับ'
                        ], ['class' => 'open-modal','data' => ['size' => 'modal-lg']]) ?>
                </div>
                <?=$model->StackComitteeDetail()?>
            </div>
            <div class="card-footer"></div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6>กรรมการตรวจรับ</h6>
                    <?= Html::a('<i class="bi bi-plus-circle-fill"></i>', [
                            '/purchase/order-item/committee','title' => 'กรรมการตรวจรับ'
                        ], ['class' => 'open-modal','data' => ['size' => 'modal-lg']]) ?>

                </div>
                <?=$model->StackComittee()?>
            </div>
            <div class="card-footer"></div>
        </div>
</div>
</div>

<?php

$js = <<< JS

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

<?php  Pjax::end() ?>