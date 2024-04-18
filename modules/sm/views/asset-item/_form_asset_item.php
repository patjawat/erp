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
use app\models\Categorise;
?>
<?php
echo "<pre>";
// print_r($$itemType);
echo "</pre>";
?>
<div class="row">
    <div class="col-5">
        <input type="file" id="my_file" style="display: none;" />

        <a href="#" class="select-photo">
            <?php if($model->showImg() != false):?>
            <?= Html::img($model->showImg(),['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;']) ?>
            <?php else:?>
            <?=Html::img('@web/img/placeholder-img.jpg',['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;'])?>
            <?php endif;?>
        </a>
    </div>
    <div class="col-7">
        <div class="row">
            <div class="col-12">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'ระบุชื่อครุภัณฑ์...'])->label("ชื่อรายการ") ?>
            </div>
            <div class="col-8">
                <?=$form->field($model, 'code')->textInput()->label("รหัสครุภัณฑ์")?>
            </div>
            <div class="col-4">
                <?php 
                    $units = Categorise::findAll(['name' => 'unit']);

                    // สร้าง array เพื่อใช้ใน Select2
                    $unitData = [];
                    foreach ($units as $unit) {
                        $unitData[$unit->id] = $unit->title;
                    }

                ?>
                <?php
                echo $form->field($model, 'data_json[unit]')->widget(Select2::classname(), [
                    'data' => $unitData,
                    'options' => ['placeholder' => 'ระบุ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])->label("หน่วยนับ")
                ?>

            </div>
            <div class="col-12">
                                <?php
                echo $form->field($model, 'data_json[asset_type]')->widget(Select2::classname(), [
                    'data' => ['3' => 'ครุภัณฑ์','4' => 'วัสดุ'],
                    'options' => ['placeholder' => 'ระบุ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])->label("ประเภท")
                ?>
            </div>
        </div>
    </div>
</div>


<?php
$js = <<< JS

if($("#assetitem-fsn_auto").val()){
    $( "#assetitem-fsn_auto" ).prop( "checked", localStorage.getItem('fsn_auto') == 1 ? true : false );   
    $('#assetitem-code').prop('disabled',localStorage.getItem('fsn_auto') == 1 ? true : false );
    
    if(localStorage.getItem('fsn_auto') == 1)
    {
        $('#assetitem-code').val('อัตโนมัติ')
    }
}

$("#assetitem-fsn_auto").change(function() {
    //ตั้งค่า Run FSN Auto
    if(this.checked) {
        localStorage.setItem('fsn_auto',1);
        $('#assetitem-code').prop('disabled',this.checked);
        $('#assetitem-code').val('อัตโนมัติ')
    }else{
        localStorage.setItem('fsn_auto',0);
        var category_id = $('#assetitem-category_id').val();
        $('#assetitem-code').prop('disabled',this.checked);

        $('#assetitem-code').val('')
    }
});


JS;
$this->registerJs($js);
?>