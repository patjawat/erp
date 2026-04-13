<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

$data = $model->data_json ?? [];

$form = ActiveForm::begin([
'id' => 'telegram-setting-form',
'type' => ActiveForm::TYPE_VERTICAL
]);
?>

<div class="container-fluid py-3">

<div class="card">
    <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">

        <h4 class="mb-0">
            <i class="bi bi-telegram text-primary"></i>
            Telegram Settings
        </h4>

        <span class="badge bg-success px-3 py-2">
            <i class="bi bi-check-circle"></i>
            Bot Connected
        </span>

    </div>
    </div>
</div>




    <div class="row g-3">

        <!-- BOT CONFIG -->
        <div class="col-lg-6">

            <div class="card shadow-sm">

                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-robot"></i>
                    Bot Configuration
                </div>

                <div class="card-body">

                    <?= $form->field($model,'data_json[bot_token]')
->textInput([
'value'=>$data['bot_token'] ?? '',
'placeholder'=>'Bot Token'
]) ?>

                    <?= $form->field($model,'data_json[bot_username]')
->textInput([
'value'=>$data['bot_username'] ?? '',
'placeholder'=>'@yourbot'
]) ?>

                    <?= $form->field($model,'data_json[webhook]')
->textInput([
'value'=>$data['webhook'] ?? '',
'placeholder'=>'https://domain.com/telegram/webhook'
]) ?>

                    <hr>

                    <div class="d-flex gap-2 justify-content-end">

                        <button type="button" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-plug"></i>
                            Test Bot
                        </button>

                        <button type="button" class="btn btn-outline-dark btn-sm">
                            <i class="bi bi-link-45deg"></i>
                            Set Webhook
                        </button>

                    </div>

                </div>
            </div>

        </div>


        <!-- MINI APP -->
        <div class="col-lg-6">

            <div class="card shadow-sm">

                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-phone"></i>
                    Mini App
                </div>

                <div class="card-body">

                    <?= $form->field($model,'data_json[mini_app]')
->textInput([
'value'=>$data['mini_app'] ?? '',
'placeholder'=>'https://domain.com/mobile'
]) ?>

                    <?= $form->field($model,'data_json[enable_mini_app]')
->checkbox([
'value'=>1,
'checked'=>($data['enable_mini_app'] ?? 0)
])->label('Enable Mini App') ?>

                </div>

            </div>

        </div>




        <!-- NOTIFICATION -->
        <div class="col-lg-6">

            <div class="card shadow-sm">

                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-bell"></i>
                    Notification Settings
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-6">

                            <?= $form->field($model,'data_json[notify_approve]')
->checkbox([
'value'=>1,
'checked'=>($data['notify_approve'] ?? 0)
])->label('Approve Request') ?>

                        </div>

                        <div class="col-6">

                            <?= $form->field($model,'data_json[notify_asset]')
->checkbox([
'value'=>1,
'checked'=>($data['notify_asset'] ?? 0)
])->label('Asset') ?>

                        </div>

                        <div class="col-6">

                            <?= $form->field($model,'data_json[notify_leave]')
->checkbox([
'value'=>1,
'checked'=>($data['notify_leave'] ?? 0)
])->label('Leave') ?>

                        </div>

                        <div class="col-6">

                            <?= $form->field($model,'data_json[notify_repair]')
->checkbox([
'value'=>1,
'checked'=>($data['notify_repair'] ?? 0)
])->label('Repair') ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- USER BINDING -->
        <div class="col-lg-6">

            <div class="card shadow-sm">

                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-people"></i>
                    Telegram User Binding
                </div>

                <div class="card-body p-0">

                    <table class="table table-hover mb-0">

                        <thead class="table-light">

                            <tr>
                                <th>User</th>
                                <th>Telegram ID</th>
                                <th>Status</th>
                            </tr>

                        </thead>

                        <tbody>

                            <tr>
                                <td>สมชาย</td>
                                <td>8177437409</td>
                                <td>
                                    <span class="badge bg-success">Connected</span>
                                </td>
                            </tr>

                            <tr>
                                <td>สมหญิง</td>
                                <td>-</td>
                                <td>
                                    <span class="badge bg-secondary">Not Connected</span>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


    </div>


    <div class="text-end mt-4">

        <?= Html::submitButton(
'<i class="bi bi-save"></i> Save Settings',
[
'class'=>'btn btn-primary px-4',
'id'=>'btn-save-telegram'
]
) ?>

    </div>

</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS

$('#telegram-setting-form').on('beforeSubmit', function(){

Swal.fire({
title:'บันทึกการตั้งค่า?',
icon:'question',
showCancelButton:true,
confirmButtonText:'บันทึก'
}).then((result)=>{

if(result.isConfirmed){

Swal.fire({
title:'กำลังบันทึก...',
allowOutsideClick:false,
didOpen:()=>{
Swal.showLoading();
}
});

$('#telegram-setting-form')[0].submit();

}

});

return false;

});

JS;
$this->registerJs($js, View::POS_END);
?>