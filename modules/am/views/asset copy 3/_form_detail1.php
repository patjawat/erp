<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\components\AppHelper;
use kartik\select2\Select2;
use iamsaint\datetimepicker\Datetimepicker;
use app\components\CategoriseHelper;
use app\modules\hr\models\Organization;
/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */
/** @var yii\widgets\ActiveForm $form */

?>

<?= $form->field($model, 'asset_item')->hiddenInput(['maxlength' => true])->label(false)?>
<?= $form->field($model, 'asset_status')->hiddenInput(['maxlength' => true])->label(false)?>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-lg-6 col-sm-12">
                <div class="row">
                    <div class="col-6">
                        <?= $form->field($model, 'data_json[tonnage_number]')->textInput()->label('เลขระวาง') ?>
                        <?= $form->field($model, 'data_json[land_number]')->textInput()->label('หมายเลขที่ดิน') ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'code')->textInput()->label('เลขโฉนดที่ดิน') ?>
                        <?= $form->field($model, 'data_json[land_explore]')->textInput()->label('หน้าสำรวจ') ?>
                    </div>
                </div>
                <?= $form->field($model, 'data_json[land_owner]')->textInput()->label('ผู้ถือครอง') ?>
                <?= $form->field($model, 'data_json[office]')->textInput()->label('สำนักงานที่ดิน') ?>
                <?= $form->field($model, 'data_json[land_add]')->textArea()->label('ที่ตั้งบ้านเลขที่') ?>
            </div>
            <!-- End Col-6 -->
            <div class="col-lg-6 col-sm-12">
                <div class="row">
                    <div class="col-4">
                        <?= $form->field($model, 'data_json[land_size]')->textInput()->label('เนื้อที่ไร่') ?>
                        </div>
                        
                        <div class="col-4">
                        <?= $form->field($model, 'data_json[land_size_ngan]')->textInput()->label('เนื้อที่งาน') ?>
                    </div>
                    <div class="col-4">
                        <?= $form->field($model, 'data_json[land_size_tarangwa]')->textInput()->label('เนื้อที่ตารางวา') ?>
                    </div>
                </div>
                <?= $form->field($model, 'data_json[holder_date]')->textInput()->label('วันถือครอง') ?>

                <?= $form->field($model, 'data_json[latitude]')->textInput()->label('พิกัดละติจูด') ?>
                <?= $form->field($model, 'data_json[longitude]')->textInput()->label('พิกัดลองจิจูด') ?>

                <?= $form->field($model, 'data_json[phone]')->textInput()->label('เบอร์ติดต่อ') ?>

            </div>
            <div class="col-sm-12">
                <?=$model->Upload($model->ref,'asset_pic')?>
            </div>
        </div>
    </div>
</div>