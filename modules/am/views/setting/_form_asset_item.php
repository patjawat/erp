<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use app\models\Categorise;
$listAssetType = ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type'])->all(),'code','title');
/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetType $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="asset-type-form">
                    <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'ระบุชื่อครุภัณฑ์'])->label("ชื่อรายการ") ?>
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true,'placeholder'=>'ระบุรหัส'])->label("รหัส FSN") ?>
                    <?= $form->field($model, 'data_json[service_life]')->textInput(['placeholder' => "ระบุจำนวน ปี"])->label("อายุการใช้งาน (ปี)") ?>        
                    <?= $form->field($model, 'data_json[depreciation]')->textInput(['placeholder' => "ตัวอยย่าง 00.00"])->label("อัตราค่าเสื่อม") ?>
                    <?=$form->field($model, 'category_id')->widget(Select2::classname(), [
                                    'data' => $listAssetType,
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                        'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => []
                                ])->label('ประเภท');
                        ?>

</div>
