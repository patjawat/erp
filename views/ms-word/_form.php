<?php

use app\modules\hr\models\Employees;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\form\ActiveForm;

use yii\helpers\Html;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$employee = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

?>
<style>
.col-form-label {
    text-align: end;
}
</style>


<?php $form = ActiveForm::begin([
    'id' => 'form-order',
    'fieldConfig' => ['labelSpan' => 3, 'options' => ['class' => 'form-group mb-1 mr-2 me-2']]
]); ?>

  <?=$form->field($model, 'set_date')->widget(Datetimepicker::className(),[
    'options' => [
        'timepicker' => false,
        'datepicker' => true,
        'mask' => '99/99/9999',
        'lang' => 'th',
        'yearOffset' => 543,
        'format' => 'd/m/Y', 
    ],
    ])->label('ลงวันที่');
?>


<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ยืนยัน', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>


<?php ActiveForm::end(); ?>



<?php
$js = <<< JS

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
     
   
    $("#order-set_date").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });   


    // $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
    // $(".modal-dialog").addClass("modal-sm");
    $("#main-modal").removeClass('modal-md modal-xl').addClass("modal-md")


        \$('#form-order').on('beforeSubmit', function (e) {
                var form = \$(this);
                
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        form.yiiActiveForm('updateMessages', response, true);
                        if(response.status == 'success') {
                            await $("#main-modal").modal("show");
                            await $("#main-modal-label").html(response.title);
                            await $(".modal-body").html(response.content);
                            await $("#main-modal").removeClass('modal-md').addClass("modal-xl")
                            try {
                                // loadRepairHostory()
                            } catch (error) {
                                
                            }
                            // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                            // await  \$.pjax.reload({ container:'#order', history:false,replace: false,timeout: false});                               
                        }
                    }
                });
                return false;
            });


    JS;
$this->registerJS($js,View::POS_END)
?>