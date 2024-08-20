<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */
/** @var yii\widgets\ActiveForm $form */

?>



    <?php
     echo $form->field($model, 'vendor_id')->widget(Select2::classname(), [
                                        'data' => $model->listVendor(),
                                        'options' => ['placeholder' => 'เลือกผู้จำหน่วย/บริจาค'],
                                        'pluginEvents' => [
                                            "select2:unselect" => "function() { 
                                                $('#asset-fsn').val('')
                                            }",
                                            "select2:select" => "function() {
                                                // console.log($(this).val());
                                                $.ajax({
                                                    type: 'get',
                                                    url: '".Url::to(['/depdrop/categorise-by-code'])."',
                                                    data: {
                                                        code: $(this).val(),
                                                        name:'asset_name'
                                                    },
                                                    dataType: 'json',
                                                    success: function (res) {
                                                        $('#asset-fsn').val(res.code)
                                                    }
                                                });
                                        }",],
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                            'dropdownParent' => '#main-modal',
                                        ],
                                    ])->label();
                                    
                                    ?>
<?= $form->field($model, 'data_json[do_number]')->textInput()->label('เลขที่ส่งสินค้า');?>
