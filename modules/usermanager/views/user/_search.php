<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var app\modules\usermanager\models\UserSearch $model */
$authManager = Yii::$app->authManager;
$roleList = ['' => 'ทุกบทบาท'];
if ($authManager) {
    foreach ($authManager->getRoles() as $r) {
        $roleList[$r->name] = $r->description ? $r->description . ' (' . $r->name . ')' : $r->name;
    }
}
?>
<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => ['class' => 'mb-4'],
    'fieldConfig' => [
        'options' => ['class' => 'mb-0'],
        'inputOptions' => ['class' => 'form-control'],
    ],
]); ?>
<div class="row g-2 align-items-end">
    <div class="col-12 col-md-5 col-lg-4">
        <div class="input-group rounded-pill border border-2 border-light shadow-sm overflow-hidden">
            <?= $form->field($model, 'q', [
                'template' => '{input}',
                'options' => ['class' => 'flex-grow-1'],
            ])->textInput([
                'placeholder' => 'ค้นหาชื่อเข้าใช้งาน ชื่อ-นามสกุล หรืออีเมล',
                'class' => 'form-control border-0 rounded-0',
            ])->label(false) ?>
            <button type="submit" class="btn btn-primary rounded-0 px-3">
                <i class="bi bi-search"></i>
            </button>
            <?= Html::a('<i class="bi bi-arrow-counterclockwise"></i>', ['index'], ['class' => 'btn btn-outline-secondary rounded-0 link-loading', 'title' => 'ล้างตัวกรอง']) ?>
        </div>
    </div>
    <div class="col-12 col-md-4 col-lg-3">
        <?= $form->field($model, 'role')->dropDownList($roleList, [
            'class' => 'form-select rounded-pill',
            'prompt' => 'ทุกบทบาท',
        ])->label('กรองตามบทบาท') ?>
    </div>
</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<< JS
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('usersearch-q');
    if (el) el.select();
});
JS;
$this->registerJs($js);
?>
