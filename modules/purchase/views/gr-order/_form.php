<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\datecontrol\DateControl;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Order $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
    'id' => 'form-gr',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/purchase/gr-order/validator']
]); ?>
<div class="row">
    

    <div class="col-xl-5 col-lg-6 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex">
                    <div class="rounded-pill bg-primary-subtle p-2 me-2">
                        <i class="fa-solid fa-file-circle-question text-primary fs-5"></i>
                    </div>
                    <div class="avatar-detail">
                        <h6 class="mb-1 fs-15"><span class="" href="/hr/employees/view?id=1">ข้อมูลผู้ขาย
                                : <?=$model->data_json['vendor_name']?></span></h6>
                        <p class="text-muted mb-0 fs-13"><?=$model->data_json['vendor_address']?></p>
                    </div>
                </div>

                <table class="table table-sm table-striped-columns">

                    <tbody>

                        <tr class="">
                            <td style="width: 150px;">กำหนดวันส่งมอบ</td>
                            <td><?=$model->data_json['delivery_date']?></td>
                        </tr>
                        <tr class="">
                            <td style="width: 108px;">ใบสั่งซื้อเลขที่</td>
                            <td><?=$model->po_number?></td>
                        </tr>
                        <tr class="">
                            <td>ทะเบียนคุม</td>
                            <td><?=$model->pq_number?></td>
                        </tr>
                    </tbody>
                </table>

            </div>

            <div class="card-footer text-muted">ข้อมูลการจัดซื้อจัดจ้าง</div>
        </div>
    </div>

    <div class="col-xl-7 col-lg-6 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <?= $model->getMe()['avatar'] ?>
                    </div>
                    <div class="col-6">

                        <?= $form->field($model, 'data_json[do_date]')->widget(DateControl::classname(), [
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'th',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'ระบุวันที่ตรวจรับ ...'],
                            'pluginOptions' => [
                                'autoclose' => true
                                ]
                                ]
                                ])->label('วันที่ตรวจรับ') ?>
     

                    </div>
                </div>

                <div class="row">
                   
                    <div class="col-6">
                        <?=$form->field($model, 'data_json[order_item_checker]')->radioList(
                                    ['Y' => 'ครบถ้วน', 'N' => 'ไม่ครบถ้วน'],
                                    ['custom' => true, 'inline' => true]
                                )->label('ผลการตรวจสอบ ');
                            ?>

                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'data_json[do_number]')->textInput()->label('เลขที่ส่งสินค้า') ?>
                    </div>
                </div>

            </div>
            <div class="card-footer text-muted d-flex justify-content-between">
            <p>ผู้ตรวจรับ</p>    
           
            </div>
        </div>
    </div>

</div>

<?= $this->render('@app/modules/purchase/views/order/order_items', ['model' => $model]) ?>

<div class="row d-flex justify-content-end">
            <div class="col-md-4 gap-3">
                <div class="d-grid gap-2">
                <?= Html::submitButton('บันทึกตรวจรับ', ['class' => 'btn btn-primary shadow']) ?>
                </div>
            </div>
        </div>

<?php ActiveForm::end(); ?>
<?php

$js = <<< JS

    $('#form-gr').on('beforeSubmit',  function (e) {
        e.preventDefault();
        var url = $(this).attr("href");
        
        var checker = $('input[name="Order[data_json][order_item_checker]"]:checked').val();
        console.log(checker);
        if(checker == 'Y'){
            var text = 'ตรวจสอบวัสดุครบถ้วน นำวัสดุเข้าคลัง!';
        }else{
            var text = 'วัสดุครบถ้วนระบบจะยังคงสถานะ พัสดุตรวจสอบ!';

        }

         Swal.fire({
            title: "ยืนยัน!",
            text: checker == 'Y' ? 'ตรวจสอบวัสดุครบถ้วน นำวัสดุเข้าคลัง!' : 'รายการไม่ครบถ้วนระบบจะยังคงสถานะ ตรวจรับวัสดุ!',
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "ใช่,ตกลง!",
            cancelButtonText: "ยกเลิก",
        }).then(async (result) => {
            
            if (result.value == true) {
                var form = $(this);
                $.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (res) {
                        console.log(res)
                        form.yiiActiveForm('updateMessages', res, true);
                        if (form.find('.invalid-feedback').length) {
                            // validation failed
                        } else {
                            // validation succeeded
                        }
                        if(res.status == 'success') {
                            // alert(data.status)ห
                            console.log(res.container);
                            $('#main-modal').modal('toggle');
                            success()
                            $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});                                                        
                        }
                    }
                });
            }
        });
        return false;
        });

    JS;
$this->registerJS($js,View::POS_END)
?>