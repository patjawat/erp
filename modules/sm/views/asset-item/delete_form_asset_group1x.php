<?php
use app\components\AppHelper;
use yii\helpers\Html;
// use yii\bootstrap5\ActiveForm;
use kartik\form\ActiveForm;
use yii\widgets\MaskedInput;
use kartik\select2\Select2;
/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */
/** @var yii\widgets\ActiveForm $form */
$title = Yii::$app->request->get('title');


?>
<div class="row">
    <div class="col-4">
        <input type="file" id="my_file" style="display: none;" />
        <a href="#" class="select-photo">
            <?php if($model->showImg() != false):?>
            <?= Html::img($model->showImg(),['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;']) ?>
            <?php else:?>
            <?=Html::img('@web/img/placeholder-img.jpg',['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;'])?>
            <?php endif;?>
        </a>
    </div>
    <div class="col-8">
        <div class="row">
            <div class="col-8">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'ระบุชื่อรายการ'])->label("ชื่อรายการ") ?>
            </div>
            <div class="col-4">
                <?=$form->field($model, 'code')->textInput()->label("รหัสวัสดุ")?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'data_json[service_life]')->textInput(['placeholder' => "ระบุจำนวน ปี"])->label("อายุการใช้งาน (ปี)") ?>

            </div>
            <div class="col-6">
                <?= $form->field($model, 'data_json[depreciation]')->textInput(['placeholder' => "ตัวอยย่าง 00.00"])->label("อัตราค่าเสื่อม") ?>
            </div>

            <div class="col-4">
                <div>
                    <?= $form->field($model, 'fsn_auto')->checkbox(['custom' => true])->label('สร้างรหัสอัตโนมัติ') ?>
                </div>
            </div>
        </div>

    </div>
</div>


<?php
$js = <<< JS

$( "#assetitem-fsn_auto" ).prop( "checked", localStorage.getItem('fsn_auto') == 1 ? true : false );
$('#assetitem-code').prop('disabled',localStorage.getItem('fsn_auto') == 1 ? true : false );
if(localStorage.getItem('fsn_auto') == 1)
{
    $('#assetitem-code').val('สร้างรหัสอัตโนมัติ')
}
$("#assetitem-fsn_auto").change(function() {
    //ตั้งค่า Run FSN Auto
    if(this.checked) {
        localStorage.setItem('fsn_auto',1);
        $('#assetitem-code').prop('disabled',this.checked);
        $('#assetitem-code').val('สร้างรหัสอัตโนมัติ')
    }else{
        localStorage.setItem('fsn_auto',0);
        $('#assetitem-code').prop('disabled',this.checked);
        $('#assetitem-code').val('')
    }
});


JS;
$this->registerJs($js);
?>