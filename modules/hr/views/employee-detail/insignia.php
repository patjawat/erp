<?php
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\web\View;

?>



    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'data_json[name]')->textInput(['placeholder' => 'เช่น ประถมาภรณ์มงกุฎไทย'])->label('ชั้นตราเครื่องราชอิสริยาภรณ์') ?>
        </div>
    </div>

    <hr> <h5 class="mb-3 text-primary"><i class="glyphicon glyphicon-book"></i> ข้อมูลราชกิจจานุเบกษา</h5>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'data_json[thai_year]')->textInput(['placeholder' => '2567'])->label('ปี พ.ศ.') ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'data_json[gazette_book]')->textInput()->label('เล่มที่') ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'data_json[gazette_section]')->textInput()->label('ตอนที่') ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'data_json[gazette_page]')->textInput()->label('หน้าที่') ?>
        </div>
    </div>

    <hr>
    <h5 class="mb-3 text-primary"><i class="glyphicon glyphicon-calendar"></i> วันที่และสถานะ</h5>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'data_json[gazette_date]')->textInput()->label('วันที่ประกาศในราชกิจจาฯ') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'data_json[receive_date]')->textInput()->label('วันที่ได้รับจริง') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'data_json[return_status]')->dropDownList(
                [
                    'ไม่ต้องคืน' => 'ไม่ต้องคืน',
                    'ยังไม่คืน' => 'ยังไม่คืน (อยู่ระหว่างครอบครอง)',
                    'คืนแล้ว' => 'คืนแล้ว',
                    'ชดใช้เงินแทน' => 'ชดใช้เงินแทน',
                ],
                ['prompt' => '--- เลือกสถานะ ---']
            )->label('สถานะการส่งคืน') ?>
        </div>
    </div>

<?php
$js = <<< JS
 thaiDatepicker('#employeedetail-data_json-gazette_date,#employeedetail-data_json-receive_date')
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJs($js);
?>