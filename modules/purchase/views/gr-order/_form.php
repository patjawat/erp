<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\datecontrol\DateControl;
use iamsaint\datetimepicker\Datetimepicker;
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
        <div class="card border border-primary">
            <div class="d-flex p-3">
                <img class="avatar" src="/img/placeholder-img.jpg" alt="">
                <div class="avatar-detail">
                    <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top">
                        <?= isset($model->data_json['vendor_name']) ? $model->data_json['vendor_name'] : '' ?>
                    </h6>
                    <p class="text-primary mb-0 fs-13">
                        <?=isset($model->data_json['vendor_address']) ? $model->data_json['vendor_address'] : '-'?></p>
                </div>
            </div>
            <div class="card-body pb-1">
                <table class="table table-sm table-striped-columns">
                    <tbody>
                        <tr class="">
                            <td style="width: 150px;">กำหนดวันส่งมอบ</td>
                            <td><?php // $model->data_json['delivery_date']?></td>
                        </tr>
                        <tr class="">
                            <td style="width: 108px;">ใบสั่งซื้อเลขที่</td>
                            <td><?=$model->po_number?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted d-flex justify-content-between">
                <p>ผู้ขาย</p>

            </div>
        </div>

    </div>

    <div class="col-xl-7 col-lg-6 col-md-12 col-sm-12">
        <div class="card border border-primary">
            <div class="card-body">
                <div class="d-flex justify-conent-between">
                    <?= $model->getMe()['avatar'] ?>
                    <div class="d-flex ms-auto p-2">
                        เลขที่ : <span class="ms-2 fw-semibold"><?=$model->gr_number?></span>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-6">
                    <?=$form->field($model, 'data_json[gr_date]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('วันที่ตรวจรับ');
                ?>

                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'data_json[gr_number]')->textInput()->label('เลขที่ส่งสินค้า') ?>
                    </div>
                </div>

            </div>
            <div class="card-footer text-muted d-flex justify-content-between">
                <p>ผลการตรวจสอบ</p>
                <?=$form->field($model, 'data_json[order_item_checker]')->radioList(
                                    ['Y' => 'ครบถ้วน', 'N' => 'ไม่ครบถ้วน'],
                                    ['custom' => true, 'inline' => true]
                                )->label(false);
                            ?>

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


        var thaiYear = function (ct) {
        var leap=3;  
        var dayWeek=["พฤ.", "ศ.", "ส.", "อา.","จ.", "อ.", "พ."];  
        if(ct){  
            var yearL=new Date(ct).getFullYear()-543;  
            leap=(((yearL % 4 == 0) && (yearL % 100 != 0)) || (yearL % 400 == 0))?2:3;  
            if(leap==2){  
                dayWeek=["ศ.", "ส.", "อา.", "จ.","อ.", "พ.", "พฤ."];  
            }  
        }              
        this.setOptions({  
            i18n:{ th:{dayOfWeek:dayWeek}},dayOfWeekStart:leap,  
        })                
    };    
     
   
    $("#order-data_json-gr_date").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    }); 

    JS;
$this->registerJS($js,View::POS_END)
?>