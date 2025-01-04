<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>
<div>


    <div class="d-flex gap-2">


        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label('คำค้นหา') ?>
        <?php
        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ปีงบประมาณ'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '120px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                        $(this).submit()
                        }',
                        'select2:unselecting' => "function() {
                            $(this).submit()
                           $('#leavesearch-date_start').val('__/__/____');
                           $('#leavesearch-date_end').val('__/__/____');
                       }",
            ]
        ])->label('ปีงบประมาณ');
        ?>
       <div class="d-flex justify-content-between gap-2" style="width:250px">
    <?=
    $form->field($model, 'date_start')->widget(Datetimepicker::className(), [
        'options' => [
            'timepicker' => false,
            'datepicker' => true,
            'mask' => '99/99/9999',
            'lang' => 'th',
            'yearOffset' => 543,
            'format' => 'd/m/Y',
        ],
    ])->label('ตั้งแต่วันที่');
    ?>
        <?=
        $form->field($model, 'date_end')->widget(Datetimepicker::className(), [
            'options' => [
                'timepicker' => false,
                'datepicker' => true,
                'mask' => '99/99/9999',
                'lang' => 'th',
                'yearOffset' => 543,
                'format' => 'd/m/Y',
            ],
        ])->label('ถึงวันที่');
        ?>
        
      
            </div>
        <div class="d-flex flex-row mb-3 mt-4">
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
        </div>
       
        <div class="mb-3 mt-4">
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false"><i class="fa-solid fa-filter"></i> เพิ่มเติม
                </button>
                <div class="dropdown-menu p-4 relative" style="width:500px">

                    <div class="d-flex flex-row gap-4">

                        <?php
                        echo $form->field($model, 'status')->checkboxList($model->listStatus(), ['custom' => true, 'inline' => false]);
                        ?>

                        <?php echo $form->field($model, 'leave_type_id')->checkboxList($model->listLeaveType(),['custom' => true, 'inline' => false]); ?>
                    </div>

                    <?php echo $form->field($model, 'emp_id')->hiddenInput()->label(false); ?>
                </div>
            </div>

        </div>
        <div class="d-flex flex-row mb-3 mt-4">
            <?php // echo Html::a('<i class="bi bi-person-fill-gear"></i> สิทธิวันลา',['/me/leave/permission','title' => '<i class="bi bi-person-fill-gear"></i> สิทธิวันลา'],['class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-lg']])?>
            <?php echo Html::a('<i class="bi bi-person-fill-gear"></i> วันหยุดของฉัน',['/me/holidays','title' => '<i class="bi bi-person-fill-gear"></i> วันหยุดของฉัน'],['id' => 'calendar-me','class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-xl']])?>
            
        </div>

    </div>
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

        \$("#leavesearch-date_start").datetimepicker({
            timepicker:false,
            format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000
            lang:'th',  // แสดงภาษาไทย
            onChangeMonth:thaiYear,
            onShow:thaiYear,
            yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
            closeOnDateSelect:true,
        });

        \$("#leavesearch-date_end").datetimepicker({
            timepicker:false,
            format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000
            lang:'th',  // แสดงภาษาไทย
            onChangeMonth:thaiYear,
            onShow:thaiYear,
            yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
            closeOnDateSelect:true,
        });

    JS;
$this->registerJS($js, View::POS_END);

?>