<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

use kartik\select2\Select2;
/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetType $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="asset-type-form">
<div class="row">
        <div class="col-12">
                    <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'ระบุชื่อครุภัณฑ์'])->label("ชื่อรายการ") ?>
             
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true,'placeholder'=>'ระบุรหัส'])->label("รหัส") ?>
                
                    <?= $form->field($model, 'data_json[service_life]')->textInput(['placeholder' => "ระบุจำนวน ปี"])->label("อายุการใช้งาน (ปี)") ?>

                    <?= $form->field($model, 'data_json[depreciation]')->textInput(['placeholder' => "ตัวอยย่าง 00.00"])->label("อัตราค่าเสื่อม") ?>

                <?php
                echo $form->field($model, 'category_id')->widget(Select2::classname(), [
                    'data' => [
                        2 => "สิ่งปลูกสร้าง",
                        3 => "ครุภัณฑ์",
                        4 => "วัสดุ"
                    ],
                    'options' => ['placeholder' => 'ระบุ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label("ประเภท")
                ?>
       
        </div>
    </div>

</div>
