<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use yii\widgets\MaskedInput;
use app\components\AppHelper;
use kartik\widgets\Typeahead;
use app\components\SiteHelper;
use app\modules\hr\models\Employees;
use iamsaint\datetimepicker\Datetimepicker;
use app\modules\filemanager\components\FileManagerHelper;
$domain = Url::base(true);

$this->title = 'ตั้งค่าองค์กร';

?>


<!-- <h1 class="text-center"><i class="bi bi-building-fill-check fs-1"></i> ข้อมูลองค์กร</h1> -->


<?php $form = ActiveForm::begin(['id' => 'form-company']); ?>
<div class="row d-flex justify-content-center">
    <div class="col-7">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <span class="text-danger fw-semibold"><?php echo $domain.'/line/home'?></span>
                    <p>หน้าหลัก</p>
                </div>
                <?= $form->field($model, 'data_json[line_liff_home]')->textInput()->label(false) ?>
            </div>
            <div class="card-footer">

            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <span class="text-danger fw-semibold"><?php echo $domain.'/line/profile'?></span>
                    <p>โปรไฟล์</p>
                </div>
                <?= $form->field($model, 'data_json[line_liff_profile]')->textInput()->label(false) ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <span class="text-danger fw-semibold"><?php echo $domain.'/line/auth/login'?></span>
                    <p>เข้าสู่ระบบ</p>
                </div>
                <?= $form->field($model, 'data_json[line_liff_login]')->textInput()->label(false) ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <span class="text-danger fw-semibold"><?php echo $domain.'/line/auth/register'?></span>
                    <p>ลงทะเบียน</p>
                </div>
                <?= $form->field($model, 'data_json[line_liff_register]')->textInput()->label(false) ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
            <div class="d-flex justify-content-between">
                    <span class="text-danger fw-semibold"><?php echo $domain.'/line/profile/line-connet'?></span>
                    <p>เชื่อม Line-Connect</p>
                </div>
                <?= $form->field($model, 'data_json[line_liff_user_connect]')->textInput()->label(false) ?>
            </div>
        </div>


    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-body">
                <?= $form->field($model, 'data_json[line_qrcode]')->textInput()->label('Line QR-code URL') ?>
                <?php if(isset($model->data_json['line_qrcode'])):?>
                <img src="<?php echo $model->data_json['line_qrcode']?>" alt="">
                <?php endif;?>

            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <?= $form->field($model, 'data_json[line_channel_token]')->textArea(['rows' => 5])->label('Channel access token') ?>
                <a href="https://developers.line.biz/console" target="_blank" class="btn btn-primary">Line
                    Developers</a>
                <a href="https://docs.google.com/document/d/1UQM2Z9feJCIbOg3MfznXX9T420XZ2lYZuZf9ALdsHpY/edit?usp=sharing"
                    target="_blank" class="btn btn-warning"><i class="fa-solid fa-book"></i> คู่มือ</a>
                <?php //  $form->field($model, 'data_json[line_liff_about]')->textInput()->label('ERP About') ?>
            </div>

        </div>
    </div>
</div>

<div class="form-group d-flex justify-content-center">
    <?= AppHelper::BtnSave() ?>
</div>

<?php ActiveForm::end(); ?>