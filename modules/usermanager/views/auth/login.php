<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\authclient\widgets\AuthChoice;

$this->title = 'ระบบบริหารยานพาหนะ';
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
    #w0 > div{
        margin-bottom:10px;
    }
    /* h1{
        background: -webkit-linear-gradient(#b584dc, #163adf);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    } */

    .btn-primary{
        background-image: linear-gradient( 135deg, #8ba9f8 10%, #0749f3 100%);
        border: none;
}

.btn-danger{
    background-image: linear-gradient( 135deg, #f06572 10%, #ed1227 100%);
    border: none;
}

html, body {
    font-family: 'Prompt', sans-serif;
    font-size: 0.95rem;
    background: #fafafa;
    overflow-x: hidden;
    overflow-y: auto;
    letter-spacing: 0.05px;
    font-weight: 200;

}
    
</style>
<div class="row justify-content-center align-items-center">

    <div class="col-lg-4 col-md-6 col-sm-12">
       <div class="text-center">
           <?=Html::img('@web/images/car_logo.png',['width' =>300]);?>
        </div>
        <p>กรุณายืนยันตัวตนเพื่อเข้าสู่ระบบ:</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

        <?= $form->field($model, 'username',[
    'inputTemplate' => '<div class="input-group">
    <div class="input-group-prepend">
    <span class="input-group-text"><i class="fa-solid fa-user-lock"></i>&nbsp;</span>
    </div>
    {input}
    </div>',
])->textInput(['autofocus' => true,'placeholder' => 'ระบุชื่อเข้าใช้งาน'])->label(false) ?>

        <?= $form->field($model, 'password',[
    'inputTemplate' => '<div class="input-group">
    <div class="input-group-prepend">
    <span class="input-group-text"><i class="fa-solid fa-lock"></i>&nbsp;</span>
    </div>
    {input}
    </div>',
])->passwordInput(['placeholder' => 'ระบุรหัสผ่าน'])->label('รหัสผ่าน')->label(false); ?>

        <?= $form->field($model, 'rememberMe')->checkbox() ?>

        <div style="color:#999;margin:1em 0">
            If you forgot your password you can <?= Html::a('reset it', ['site/request-password-reset']) ?>.
        </div>

        <div class="form-group mb-3">
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            <?=Html::a('สมัครสมาชิก',['/auth/signup'],['class' => 'btn btn-success']);?>
        </div>

        <?php ActiveForm::end(); ?>

                <?php $authAuthChoice = yii\authclient\widgets\AuthChoice::begin(['baseAuthUrl' => ['auth/auth']]); ?>

                <?php foreach ($authAuthChoice->getClients() as $client): ?>
                <?php
                    switch ($client->getName()){
                        case 'facebook':
                            $class = 'primary';
                            $icon = '<i class="fa-brands fa-facebook"></i>';
                            break;
                        case 'google':
                            $class = 'danger';
                            $icon = '<i class="fa-brands fa-google"></i>';
                            break;
                        case 'live':
                            $class = 'warning';
                            break;

                    }?>
                <div class="d-grid gap-2">
                    <?=$authAuthChoice->clientLink($client, $icon.' Login with '.ucfirst($client->getName()), ['class' => 'btn btn-'.$class])?>

                </div>
                    <?php endforeach; ?>
                <?php yii\authclient\widgets\AuthChoice::end(); ?>
       

    </div>

</div>
<?php // Html::img('@web/images/bk.jpg',['width' => 800])?>
