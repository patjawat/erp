<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetCondition $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="asset-condition-form">
    <?php $form = ActiveForm::begin([
        'id' => 'asset-condition-form',
    ]); ?>

    <div class="row g-3">
        <div class="col-12">
            <?= $form->field($model, 'id')->textInput([
                'maxlength' => true,
                'readonly' => !$model->isNewRecord,
            ]) ?>
        </div>

        <div class="col-12">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'sort_order')->input('number') ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'is_active')->dropDownList([
                1 => 'Active',
                0 => 'Inactive',
            ]) ?>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
        <?= Html::submitButton('บันทึก', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
$('body').off('beforeSubmit.assetCondition', '#asset-condition-form').on('beforeSubmit.assetCondition', '#asset-condition-form', function (e) {
    e.preventDefault();
    var form = $(this);

    $.ajax({
        url: form.attr('action') || window.location.href,
        type: form.attr('method') || 'post',
        data: form.serialize(),
        dataType: 'json',
        success: function (response) {
            if (response && response.status === 'success') {
                if (typeof closeModal === 'function') {
                    closeModal();
                } else if ($('#main-modal').length) {
                    $('#main-modal').modal('hide');
                }
                if (response.container && $(response.container).length && $.fn.pjax) {
                    $.pjax.reload({container: response.container, timeout: false});
                } else {
                    window.location.reload();
                }
                return;
            }

            if (response && response.content) {
                $('#main-modal-label').html(response.title || '');
                $('.modal-body').html(response.content);
                if (typeof response.footer !== 'undefined') {
                    $('.modal-footer').html(response.footer);
                }
            }
        },
        error: function () {
            alert('ไม่สามารถบันทึกข้อมูลได้');
        }
    });

    return false;
});
JS;
$this->registerJs($js);
?>
