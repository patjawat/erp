<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Authentication';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="main-wrapper">

<div class="account-content">
    <div class="container">

        <!-- Account Logo -->
        <div class="account-logo">
            <!-- <a href="admin-dashboard.html"><img src="assets/img/logo2.png" alt="Dreamguy's Technologies"></a> -->
        </div>
        <!-- /Account Logo -->

        <div class="account-box">
            <div class="account-wrapper">
                <h3 class="account-title"><?= Html::encode($this->title) ?></h3>
                <p class="account-subtitle">เข้าใช้งานระบบ</p>

                <!-- Account Form -->
                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                <?= $form->field($model, 'username')->textInput(['autofocus' => true,'value' => 'admin']) ?>
                <?= $form->field($model, 'password')->passwordInput(['value' => '112233']) ?>

                <?= $form->field($model, 'rememberMe')->checkbox() ?>
                <div class="form-group text-center">
                    <button class="btn btn-primary account-btn rounded-pill" type="submit">Login</button>
                </div>

                <?php ActiveForm::end(); ?>
                <!-- /Account Form -->
                <div class="form-group text-center">
                    <?php $authAuthChoice = yii\authclient\widgets\AuthChoice::begin(['baseAuthUrl' => ['/auth/auth'],'popupMode' => true,
]); ?>
                    <?php foreach ($authAuthChoice->getClients() as $client): ?>
                    <?php // $authAuthChoice->clientLink($client, '<i class="bi bi-google"></i> เข้าสู่ระบบด้วย '.ucfirst($client->getName()), ['class' => 'btn btn-success rounded-pill shadow-lg']) ?>
                    <?php endforeach; ?>
                </div>

                <div class="account-footer">
                    <p>
                        อยากมีบัญชีหรือยัง  
                        <?=Html::a('ลงทะเบียนเลย!',['/site/sign-up'])?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<style>
    .account-page .main-wrapper .account-content .account-box {
    background-color: #ffffff;
    /* border: 1px solid #ededed; */
    box-shadow: 3px 7px 20px 0px rgb(0 0 0 / 13%);
    margin: 0 auto;
    overflow: hidden;
    width: 480px;
    border-radius: 20px;
}

    svg#clouds[_ngcontent-ng-c105734841] {
    position: fixed;
    bottom: -100px;

    z-index: -10;
    width: 2600px;
}
</style>
<svg _ngcontent-ng-c105734841="" id="clouds" xmlns="http://www.w3.org/2000/svg" width="2611.084" height="485.677" viewBox="0 0 2611.084 485.677"><title _ngcontent-ng-c105734841="">Gray Clouds Background</title><path _ngcontent-ng-c105734841="" id="Path_39" data-name="Path 39" d="M2379.709,863.793c10-93-77-171-168-149-52-114-225-105-264,15-75,3-140,59-152,133-30,2.83-66.725,9.829-93.5,26.25-26.771-16.421-63.5-23.42-93.5-26.25-12-74-77-130-152-133-39-120-212-129-264-15-54.084-13.075-106.753,9.173-138.488,48.9-31.734-39.726-84.4-61.974-138.487-48.9-52-114-225-105-264,15a162.027,162.027,0,0,0-103.147,43.044c-30.633-45.365-87.1-72.091-145.206-58.044-52-114-225-105-264,15-75,3-140,59-152,133-53,5-127,23-130,83-2,42,35,72,70,86,49,20,106,18,157,5a165.625,165.625,0,0,0,120,0c47,94,178,113,251,33,61.112,8.015,113.854-5.72,150.492-29.764a165.62,165.62,0,0,0,110.861-3.236c47,94,178,113,251,33,31.385,4.116,60.563,2.495,86.487-3.311,25.924,5.806,55.1,7.427,86.488,3.311,73,80,204,61,251-33a165.625,165.625,0,0,0,120,0c51,13,108,15,157-5a147.188,147.188,0,0,0,33.5-18.694,147.217,147.217,0,0,0,33.5,18.694c49,20,106,18,157,5a165.625,165.625,0,0,0,120,0c47,94,178,113,251,33C2446.709,1093.793,2554.709,922.793,2379.709,863.793Z" transform="translate(142.69 -634.312)" fill="#ffff"></path></svg>
