<?php

use app\components\AppHelper;
use app\components\SiteHelper;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use kartik\widgets\Typeahead;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;

/** @var yii\web\View $this */
/** @var app\models\Employees $model */
/** @var yii\widgets\ActiveForm $form */
?>
<!-- Row -->
<div class="row">
                                <div class="col-6">

                                    <?=$form->field($model, 'join_date')->widget(Datetimepicker::className(),[
                                            'options' => [
                                                'timepicker' => false,
                                                'datepicker' => true,
                                                'mask' => '99/99/9999',
                                                'lang' => 'th',
                                                'yearOffset' => 543,
                                                'format' => 'd/m/Y', 
                                            ],
                                            ]);
                                        ?>
                                </div>
                                <div class="col-6">
                                    <?=$form->field($model, 'department')->textInput();?>
                                </div>
                            </div>
                            <!-- End Row -->