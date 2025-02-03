<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use kartik\widgets\ActiveForm;
use app\modules\dms\models\DocumentsDetail;

$me = UserHelper::GetEmployee();
$documents = DocumentsDetail::find()->where(['name' => 'comment', 'to_id' => $me->id])->all();
$list = ArrayHelper::map($documents, 'id', function ($model) {
    return $model->document->topic;
});
/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCar $model */
/** @var yii\widgets\ActiveForm $form */
?>
<div class="row">
    <div class="col-7">
        <?php $form = ActiveForm::begin([
            'id' => 'booking-form'
        ]); ?>
        <div class="row">
            <div class="col-6">
                <div class="row">
                    <div class="col-8">
                        <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'เลือกวันที่ต้องการเดินทาง'])->label('ต้องการใช้รถตั้งแต่วันที่') ?>
                        <?= $form->field($model, 'date_end')->textInput(['placeholder' => 'เลือกวันที่เดินทางกลับ'])->label('ถึงวันที่') ?></div>
                    <div class="col-4">
                        <?= $form->field($model, 'time_start')->widget('yii\widgets\MaskedInput', ['mask' => '99:99']) ?>
                        <?= $form->field($model, 'time_end')->widget('yii\widgets\MaskedInput', ['mask' => '99:99']) ?>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'location')->widget(Select2::classname(), [
                        'data' => $model->ListOrg(),
                        'options' => ['placeholder' => 'เลือกหน่วยงาน'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            // 'width' => '370px',
                        ],
                        'pluginEvents' => [
                            'select2:select' => 'function(result) { 
                                            }',
                            'select2:unselecting' => 'function() {

                                            }',
                        ]
                    ]) ?>
                <?= $form->field($model, 'urgent')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <?= $form->field($model, 'reason')->textArea(['rows' => 5])->label('เหตุผล') ?>
        <?php
                    echo $form->field($model, 'document_id')->widget(Select2::classname(), [
                        'data' => $list,
                        'options' => ['placeholder' => 'เลือกหนังสืออ้างอิง ...'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'dropdownParent' => '#main-modal',
                        ],
                    ])->label('หนังสืออ้างอิง');
                    ?>
        <div class="row">
            <div class="col-6">
                <?= $form->field($model, 'driver_id')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'leader_id')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <?= $form->field($model, 'car_type')->textInput(['maxlength' => true])->label(false) ?>
        
        <?= $form->field($model, 'license_plate')->textInput(['maxlength' => true]) ?>
        <?php
        //  $form->field($model, 'license_plate')->widget(Select2::classname(), [
        //                 'data' => $model->ListCarItems(),
        //                 'options' => ['placeholder' => 'เลือกหน่วยงาน'],
        //                 'pluginOptions' => [
        //                     'allowClear' => true,
        //                     // 'width' => '370px',
        //                 ],
        //                 'pluginEvents' => [
        //                     'select2:select' => 'function(result) { 
        //                                     }',
        //                     'select2:unselecting' => 'function() {

        //                                     }',
        //                 ]
        //             ])
                     ?>
                    
        <div class="form-group mt-3 d-flex justify-content-center gap-3">
            <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
            <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
                    class="fa-regular fa-circle-xmark"></i> ปิด</button>
        </div>
        <?php ActiveForm::end(); ?>

        
    </div>
    <div class="col-5">
        <div id="viewCars">ทะเบียนยานพาหนะ</div>
    </div>
</div>


<?php
$listCarsUrl = Url::to(['/me/booking-car/list-cars']);
$js = <<<JS


      thaiDatepicker('#booking-date_start,#booking-date_end')

      \$('#booking-date_start').on('change', function() {
            var dateStart = \$('#booking-date_start').val();
            var dateEnd = \$('#booking-date_end').val();
            listCars(dateStart,dateEnd)
        });

        

      \$('#booking-form').on('beforeSubmit', function (e) {
        var form = \$(this);

        Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกขอใช้ยานพาหนะ!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
        }).then((result) => {
        if (result.isConfirmed) {
            beforLoadModal()
            \$.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: async function (response) {
                    form.yiiActiveForm('updateMessages', response, true);
                    if(response.status == 'success') {
                        closeModal()
                        // success()
                        await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                    }
                }
            });
        }
        });
        return false;
    });

    function listCars()
    {
        var dateStart = $('#booking-date_start').val();
        var dateEnd = $('#booking-date_end').val();
        var carType = $('#booking-car_type').val();
        \$.ajax({
            type: "get",
            url: "$listCarsUrl",
            data: {
                date_start:dateStart,
                date_end:dateEnd,
                car_type:carType
            },
            dataType: "json",
            success: function (res) {
                $('#viewCars').html(res.content)
                
            }
        });
    }

    $("body").on("click", ".license_plate", function (e) {
        e.preventDefault();
        let licensePlate = $(this).data("license_plate"); // ดึงค่าจาก data-license_plate
        $('#booking-license_plate').val(licensePlate)
        
    });

    
    JS;
$this->registerJS($js, View::POS_END);

?>