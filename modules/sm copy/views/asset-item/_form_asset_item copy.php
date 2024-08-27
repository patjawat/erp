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
        <?php if($model->isNewRecord):?>
            <div class="col-8">
                <div class="mb-3 highlight-addon field-mock">
                    <label class="form-label has-star" for="mock-code">รหัสหมวดหมู่</label>
                    <input type="text" id="mock-category_id" class="form-control" name="mock"
                        value="<?=$model->category_id?>" disabled>

                </div>
            </div>
            <?php endif;?>
            <div class="<?=$model->isNewRecord ? 'col-4' : 'col-12'?>">
                <?=$form->field($model, 'code')->textInput()->label("รหัสครุภัณฑ์")?>
            </div>
            <div class="col-8">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'ระบุชื่อครุภัณฑ์...'])->label("ชื่อรายการ") ?>
            </div>
            <div class="col-4">
                <?php
                echo $form->field($model, 'data_json[unit]')->widget(Select2::classname(), [
                    'data' => ['1' => 'ชิ้น','2' => 'กล่อง'],
                    'options' => ['placeholder' => 'ระบุ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])->label("หน่วยนับ")
                ?>

            </div>
            
            <div class="col-12">
                <?= $form->field($model, 'data_json[amount]')->textInput(['placeholder' => "จำนวนคงเหลือ",'type' => 'number'])->label("จำนวนคงเหลือ") ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'data_json[amount_max]')->textInput(['placeholder' => "เตือนเมื่อเกินจำนวน",'type' => 'number'])->label("Max") ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'data_json[amount_min]')->textInput(['placeholder' => "เตือนเมื่อค่าน้อยกว่า",'type' => 'number'])->label("Min") ?>
            </div>
            <?php if($model->isNewRecord):?>
            <div class="col-12">
                <div>
                    <?= $form->field($model, 'fsn_auto')->checkbox(['custom' => true])->label('สร้างรหัสอัตโนมัติ') ?>
                </div>
            </div>

            <?php endif;?>

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