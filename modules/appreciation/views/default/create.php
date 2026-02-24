<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use kartik\widgets\Select2;
use app\components\UserHelper;
use app\modules\appreciation\models\Appreciation;

$me = $me ?? UserHelper::GetEmployee();
$isModal = !empty($isModal);
$this->title = 'ส่งคำขอบคุณ';
if (!$isModal) {
    $this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
    $this->params['breadcrumbs'][] = ['label' => 'พลังแห่งคำขอบคุณ', 'url' => ['index']];
    $this->params['breadcrumbs'][] = $this->title;
}

$employeeByIdUrl = Url::to(['/depdrop/employee-by-id']);
$excludeEmpId = $me ? (int) $me->id : 0;

$select2PluginOptions = [
    'allowClear' => true,
    'minimumInputLength' => 0,
    'ajax' => [
        'url' => $employeeByIdUrl,
        'dataType' => 'json',
        'delay' => 200,
        'data' => new JsExpression("function(params) { return { q: params.term || '', exclude_emp_id: {$excludeEmpId} }; }"),
        'processResults' => new JsExpression("function(data) { return { results: data.results }; }"),
        'cache' => true,
    ],
    'escapeMarkup' => new JsExpression('function(markup) { return markup; }'),
    'templateResult' => new JsExpression('formatRepo'),
    'templateSelection' => new JsExpression('formatRepoSelection'),
];
if ($isModal) {
    $select2PluginOptions['dropdownParent'] = new JsExpression('$("#main-modal")');
}

$formatRepoJs = <<< 'JS'
function formatRepo(repo) {
    if (repo.loading) return repo.text || 'กำลังค้นหา...';
    if (!repo.id) return repo.text;
    return '<div class="d-flex align-items-center">' + (repo.avatar || repo.text) + '</div>';
}
function formatRepoSelection(repo) {
    return repo.text || repo.fullname || 'เลือกผู้รับคำชม';
}
JS;
$this->registerJs($formatRepoJs, View::POS_HEAD);
?>

<?php if ($isModal): ?>
<div class="p-2">
<?php endif; ?>

<div class="<?= $isModal ? '' : 'row justify-content-center py-3' ?>">
    <div class="<?= $isModal ? '' : 'col-12 col-lg-8 col-xl-7' ?>">
        <?php if (!$isModal): ?>
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-primary text-white py-4 px-4">
                <h5 class="mb-1 fw-bold"><i class="bi bi-heart me-1"></i> โพสต์คำชมให้เพื่อน</h5>
                <p class="mb-0 small opacity-75">ข้อความของคุณจะไปอยู่ในฟีด ให้เพื่อนๆ เห็นและกดชอบได้</p>
            </div>
            <div class="card-body p-4">
        <?php endif; ?>

                <?php $form = ActiveForm::begin([
                    'id' => 'appreciation-form',
                    'action' => Url::to(['create']),
                    'options' => ['data-modal-submit' => $isModal ? '1' : '0'],
                ]); ?>

                <div class="mb-4 avatar-form">
                    <?= $form->field($model, 'to_emp_id', [
                        'template' => "{label}\n{input}\n{hint}\n{error}",
                        'labelOptions' => ['class' => 'form-label fw-medium'],
                    ])->widget(Select2::class, [
                        'initValueText' => '',
                        'options' => [
                            'placeholder' => 'พิมพ์ชื่อหรือนามสกุลเพื่อค้นหา...',
                            'id' => 'appreciation-to_emp_id',
                        ],
                        'pluginOptions' => $select2PluginOptions,
                    ])->label('ส่งถึง') ?>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium d-block mb-2">ประเภทคำชม <span class="text-muted fw-normal small">(ไม่บังคับ)</span></label>
                    <div class="d-flex flex-wrap gap-2" role="group" aria-label="เลือกประเภทคำชม">
                        <?php
                        $badges = Appreciation::badgeLabels();
                        $emojis = Appreciation::badgeEmojis();
                        $currentBadge = $model->badge_type;
                        $inputName = Html::getInputName($model, 'badge_type');
                        ?>
                        <input type="radio" class="btn-check" name="<?= $inputName ?>" id="badge-none" value=""<?= $currentBadge === null || $currentBadge === '' ? ' checked' : '' ?> autocomplete="off">
                        <label class="btn btn-outline-secondary rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 appreciation-badge-option" for="badge-none">
                            <span class="text-muted small">ไม่ระบุ</span>
                        </label>
                        <?php foreach ($badges as $value => $label):
                            $id = 'badge-' . preg_replace('/[^a-z0-9]/', '_', $value);
                            $checked = ($currentBadge === (string)$value) ? ' checked' : '';
                        ?>
                            <input type="radio" class="btn-check" name="<?= $inputName ?>" id="<?= $id ?>" value="<?= Html::encode($value) ?>"<?= $checked ?> autocomplete="off">
                            <label class="btn btn-outline-primary rounded-3 px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm appreciation-badge-option" for="<?= $id ?>">
                                <span class="fs-5"><?= $emojis[$value] ?? '❤️' ?></span>
                                <span><?= Html::encode($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?= Html::error($model, 'badge_type', ['class' => 'invalid-feedback d-block']) ?>
                </div>

                <div class="mb-4">
                    <?= $form->field($model, 'message', [
                        'template' => "{label}\n{input}\n{hint}\n{error}",
                        'labelOptions' => ['class' => 'form-label fw-medium'],
                    ])->textarea([
                        'rows' => 4,
                        'placeholder' => 'เขียนข้อความขอบคุณหรือชื่นชมเพื่อนร่วมงาน...',
                        'class' => 'form-control rounded-2',
                    ])->label('ข้อความ') ?>
                </div>

                <div class="d-flex align-items-center gap-2 mb-4 p-3 rounded-2 bg-opacity-10 bg-warning">
                    <span class="badge bg-warning text-dark rounded-pill">+<?= (int) $model->points_given ?></span>
                    <span class="small text-muted">คะแนนจะถูกให้กับผู้รับคำชม</span>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <?= Html::submitButton('<i class="bi bi-send-heart me-1"></i> ส่งคำขอบคุณ', ['class' => 'btn btn-primary rounded-3 fw-medium px-4']) ?>
                    <?php if ($isModal): ?>
                        <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal" aria-label="Close">ยกเลิก</button>
                    <?php else: ?>
                        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
                    <?php endif; ?>
                </div>

                <?php ActiveForm::end(); ?>

        <?php if (!$isModal): ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($isModal): ?>
</div></div></div>
<?php
$createUrl = Url::to(['create']);
$js = <<<JS
(function() {
    var form = document.getElementById('appreciation-form');
    if (!form || form.getAttribute('data-modal-submit') !== '1') return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var \$form = $(form);
        \$form.find('button[type=submit]').prop('disabled', true);
        $.ajax({
            url: '{$createUrl}',
            type: 'POST',
            data: \$form.serialize(),
            dataType: 'json'
        }).done(function(res) {
            if (res.success && res.redirect_url) {
                $('#main-modal').modal('hide');
                window.location.href = res.redirect_url;
            } else if (res.success === false && res.redirect_url) {
                window.location.href = res.redirect_url;
            } else if (res.success === false && res.content) {
                $('#main-modal .modal-body').html(res.content);
                \$form.find('button[type=submit]').prop('disabled', false);
            }
        }).fail(function() {
            \$form.find('button[type=submit]').prop('disabled', false);
        });
    });
})();
JS;
$this->registerJs($js);
?>
<?php endif; ?>
