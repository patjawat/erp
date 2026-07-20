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
$this->registerCssFile('@web/css/appreciation-media.css');
$this->registerCss(<<<'CSS'
.appreciation-composer { color: #334155; }
.appreciation-composer--form {
    display: block;
    min-width: 0;
    padding: 0;
    border: 0;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
}
.appreciation-modal-body,
.appreciation-modal-body > div,
.appreciation-modal-body form,
.appreciation-composer--form > * {
    min-width: 0;
    max-width: 100%;
}
.appreciation-composer__intro {
    padding: .25rem 0 1rem;
    border-bottom: 1px solid #eef2f6;
}
.appreciation-composer__mark {
    width: 44px;
    height: 44px;
    color: #fff;
    background: linear-gradient(145deg, #fb7185, #e83e5b);
    box-shadow: 0 7px 16px rgba(232, 62, 91, .22);
}
.appreciation-composer .select2-container--krajee-bs5 .select2-selection {
    min-height: 48px;
    padding-top: .35rem;
    border-color: #e2e8f0;
    border-radius: 14px;
    background: #f8fafc;
}
.appreciation-composer__message {
    min-height: 150px;
    padding: 1rem 1.1rem;
    border: 0;
    border-radius: 16px;
    background: #f8fafc;
    box-shadow: none !important;
    font-size: 1rem;
    line-height: 1.7;
    resize: vertical;
}
.appreciation-composer__message:focus { background: #fff; outline: 2px solid #dbeafe; }
.appreciation-upload {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .65rem;
    min-height: 76px;
    padding: 1rem;
    color: #475569;
    border: 1.5px dashed #cbd5e1;
    border-radius: 16px;
    background: #fbfdff;
    cursor: pointer;
    transition: border-color 180ms ease, background-color 180ms ease;
}
.appreciation-upload:hover { border-color: #60a5fa; background: #eff6ff; }
.appreciation-upload__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    color: #2563eb;
    border-radius: 50%;
    background: #dbeafe;
    font-size: 1.1rem;
}
.appreciation-badge-options {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .5rem;
}
.appreciation-badge-option {
    width: 100%;
    min-width: 0;
    justify-content: flex-start;
    border-radius: 8px !important;
    white-space: normal;
    text-align: left;
}
.appreciation-badge-option.btn-check:checked + label,
.btn-check:checked + .appreciation-badge-option { box-shadow: 0 4px 12px rgba(37, 99, 235, .16); }
.appreciation-composer__footer {
    position: sticky;
    bottom: -8px;
    z-index: 2;
    padding: .85rem 0 .25rem;
    background: rgba(255,255,255,.96);
}
@media (max-width: 575.98px) {
    .appreciation-modal-body { padding: 0 !important; }
    .appreciation-composer__intro { align-items: flex-start !important; }
    .appreciation-composer__message { min-height: 128px; }
    .appreciation-badge-options { grid-template-columns: 1fr; }
    .appreciation-composer__footer {
        bottom: 0;
        display: grid !important;
        grid-template-columns: 1fr;
        padding-bottom: max(.5rem, env(safe-area-inset-bottom));
    }
    .appreciation-composer__footer .btn { width: 100%; }
}
.appreciation-success-pop {
    position: fixed;
    inset: 0;
    z-index: 2000;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    text-align: center;
    color: #fff;
    animation: appreciation-pop-life 2.4s ease both;
}
.appreciation-success-image {
    display: block;
    width: min(360px, 76vw);
    height: auto;
    margin: 0 auto -1rem;
    filter: drop-shadow(0 22px 28px rgba(80, 20, 35, .3));
    animation: appreciation-heart-pop 700ms cubic-bezier(.18,.89,.32,1.28) both;
}
.appreciation-success-pop__message {
    font-size: 1.35rem;
    font-weight: 700;
    text-shadow: 0 2px 8px rgba(15, 23, 42, .7);
}
@keyframes appreciation-heart-pop {
    0% { opacity: 0; transform: scale(.35) translateY(30px); }
    65% { opacity: 1; transform: scale(1.08) translateY(0); }
    100% { opacity: 1; transform: scale(1); }
}
@keyframes appreciation-pop-life {
    0%, 72% { opacity: 1; }
    100% { opacity: 0; transform: translateY(-18px); }
}
@media (prefers-reduced-motion: reduce) {
    .appreciation-success-pop,
    .appreciation-success-image { animation: none; }
}
CSS);
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
    return repo.text || repo.fullname || 'เลือกผู้รับคำขอบคุณ';
}
JS;
$this->registerJs($formatRepoJs, View::POS_HEAD);
?>

<?php if ($isModal): ?>
<div class="p-2 appreciation-modal-body">
<?php endif; ?>

<div class="<?= $isModal ? '' : 'row justify-content-center py-3' ?>">
    <div class="<?= $isModal ? '' : 'col-12 col-lg-8 col-xl-7' ?>">
        <?php if (!$isModal): ?>
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-primary text-white py-4 px-4">
                <h5 class="mb-1 fw-bold"><i class="bi bi-heart me-1"></i> โพสต์คำขอบคุณให้เพื่อน</h5>
                <p class="mb-0 small opacity-75">ข้อความของคุณจะไปอยู่ในฟีด ให้เพื่อนๆ เห็นและกดชอบได้</p>
            </div>
            <div class="card-body p-4">
        <?php endif; ?>

                <?php $form = ActiveForm::begin([
                    'id' => 'appreciation-form',
                    'action' => Url::to(['create']),
                    'options' => ['data-modal-submit' => $isModal ? '1' : '0', 'enctype' => 'multipart/form-data'],
                ]); ?>

                <div class="appreciation-composer appreciation-composer--form">
                <div class="appreciation-composer__intro d-flex align-items-center gap-3 mb-3">
                    <span class="appreciation-composer__mark rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0"><i class="bi bi-heart-fill"></i></span>
                    <div><div class="fw-bold">เล่าเรื่องดี ๆ ที่อยากขอบคุณ</div><div class="small text-muted">ข้อความนี้จะส่งถึงเพื่อนของคุณโดยตรง</div></div>
                </div>

                <div class="mb-3 avatar-form">
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
                    ])->label('<i class="bi bi-person-plus me-1 text-primary"></i> แท็กเพื่อนที่อยากขอบคุณ') ?>
                </div>

                <div class="mb-3">
                    <?= $form->field($model, 'message', [
                        'template' => "{input}\n{hint}\n{error}",
                    ])->textarea([
                        'rows' => 5,
                        'placeholder' => 'วันนี้เพื่อนคนนี้ช่วยอะไรคุณไว้... เล่าให้เขาฟังหน่อย 😊',
                        'class' => 'form-control appreciation-composer__message',
                        'aria-label' => 'เขียนข้อความขอบคุณ',
                    ])->label(false) ?>
                </div>

                <div class="mb-4">
                    <?= $form->field($model, 'imageFile', ['template' => "{input}\n{error}"])->fileInput([
                        'accept' => 'image/png,image/jpeg,image/webp',
                        'class' => 'visually-hidden',
                        'id' => 'appreciation-image-file',
                    ])->label(false) ?>
                    <label class="appreciation-upload" for="appreciation-image-file">
                        <span class="appreciation-upload__icon"><i class="bi bi-image"></i></span>
                        <span><strong class="d-block">เพิ่มรูปให้เรื่องนี้</strong><small class="text-muted">แตะเพื่อเลือกรูปภาพ · ไม่เกิน 5 MB</small></span>
                    </label>
                    <div id="appreciation-image-preview" class="d-none mt-3 appreciation-frame appreciation-frame--classic">
                        <img src="" alt="ตัวอย่างภาพคำขอบคุณ" class="img-fluid appreciation-preview-image">
                        <p class="appreciation-preview-caption mb-0">ภาพของคุณพร้อมแล้ว</p>
                    </div>
                </div>

                <div class="mb-4 pt-1">
                    <label class="form-label fw-semibold d-block mb-1">เพื่อนคนนี้น่ารักตรงไหน?</label>
                    <p class="small text-muted mb-2">เลือกคำที่ตรงกับเรื่องราว เพื่อเชื่อมโยงกับค่านิยมองค์กร</p>
                    <div class="appreciation-badge-options" role="group" aria-label="เลือกประเภทคำขอบคุณ">
                        <?php
                        $badges = Appreciation::badgeLabels();
                        $emojis = Appreciation::badgeEmojis();
                        $currentBadge = $model->badge_type;
                        $inputName = Html::getInputName($model, 'badge_type');
                        ?>
                        <input type="radio" class="btn-check" name="<?= $inputName ?>" id="badge-none" value=""<?= $currentBadge === null || $currentBadge === '' ? ' checked' : '' ?> autocomplete="off">
                        <label class="btn btn-light border px-3 py-2 d-inline-flex align-items-center gap-2 appreciation-badge-option" for="badge-none">
                            <span class="text-muted small">ขอบคุณทั่วไป</span>
                        </label>
                        <?php foreach ($badges as $value => $label):
                            $id = 'badge-' . preg_replace('/[^a-z0-9]/', '_', $value);
                            $checked = ($currentBadge === (string)$value) ? ' checked' : '';
                        ?>
                            <input type="radio" class="btn-check" name="<?= $inputName ?>" id="<?= $id ?>" value="<?= Html::encode($value) ?>"<?= $checked ?> autocomplete="off">
                            <label class="btn btn-outline-primary px-3 py-2 d-inline-flex align-items-center gap-2 appreciation-badge-option" for="<?= $id ?>">
                                <span class="fs-5"><?= $emojis[$value] ?? '❤️' ?></span>
                                <span><?= Html::encode($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?= Html::error($model, 'badge_type', ['class' => 'invalid-feedback d-block']) ?>
                </div>

                <div class="mb-4 d-none" id="appreciation-frame-options">
                    <label class="form-label fw-medium d-block mb-2"><i class="bi bi-stars me-1 text-warning"></i> แต่งกรอบรูป</label>
                    <div class="d-flex flex-wrap gap-2" role="radiogroup" aria-label="เลือกรูปแบบกรอบภาพ">
                        <?php foreach (Appreciation::frameLabels() as $frameValue => $frameLabel): $frameId='frame-'.$frameValue; ?>
                            <input type="radio" class="btn-check appreciation-frame-input" name="<?= Html::getInputName($model, 'frame_style') ?>" id="<?= $frameId ?>" value="<?= Html::encode($frameValue) ?>"<?= $model->frame_style===$frameValue?' checked':'' ?>>
                            <label class="btn btn-outline-secondary" for="<?= $frameId ?>"><?= Html::encode($frameLabel) ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mb-3 px-1">
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">+<?= (int) $model->points_given ?></span>
                    <span class="small text-muted">เพื่อนจะได้รับคะแนนกำลังใจจากโพสต์นี้</span>
                </div>

                <div class="appreciation-composer__footer d-flex flex-wrap gap-2">
                    <?= Html::submitButton('<i class="bi bi-heart-fill me-2"></i> ส่งคำขอบคุณให้เพื่อน', ['class' => 'btn btn-primary rounded-pill fw-semibold px-4 py-2']) ?>
                    <?php if ($isModal): ?>
                        <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal" aria-label="Close">ยกเลิก</button>
                    <?php else: ?>
                        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
                    <?php endif; ?>
                </div>
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
$successHeartUrl = Url::to('@web/img/appreciation/success-heart-v2.png');
$js = <<<JS
(function() {
    var form = document.getElementById('appreciation-form');
    if (!form || form.getAttribute('data-modal-submit') !== '1') return;
    var successHeartPreload = new Image();
    successHeartPreload.src = '{$successHeartUrl}';
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var \$form = $(form);
        \$form.find('button[type=submit]').prop('disabled', true);
        $.ajax({
            url: '{$createUrl}',
            type: 'POST',
            data: new FormData(form),
            dataType: 'json',
            processData: false,
            contentType: false
        }).done(function(res) {
            if (res.success) {
                $('.appreciation-success-pop').remove();
                var pop = document.createElement('div');
                pop.className = 'appreciation-success-pop';
                pop.style.cssText = 'position:fixed;inset:0;z-index:10000;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;text-align:center;';
                pop.innerHTML = '<img class="appreciation-success-image" src="{$successHeartUrl}" alt="">' +
                    '<div class="appreciation-success-pop__message">เพื่อนของคุณได้รับข้อความแล้ว</div>';
                document.body.appendChild(pop);
                var heart = pop.querySelector('.appreciation-success-image');
                var message = pop.querySelector('.appreciation-success-pop__message');
                heart.style.cssText = 'display:block;width:min(360px,76vw);height:auto;margin:0 auto -1rem;filter:drop-shadow(0 22px 28px rgba(80,20,35,.3));';
                message.style.cssText = 'color:#fff;font-size:1.35rem;font-weight:700;text-shadow:0 2px 8px rgba(15,23,42,.85);';
                if (heart.animate && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    heart.animate([
                        {opacity:0, transform:'scale(.35) translateY(30px)'},
                        {opacity:1, transform:'scale(1.08) translateY(0)', offset:.7},
                        {opacity:1, transform:'scale(1) translateY(0)'}
                    ], {duration:700, easing:'cubic-bezier(.18,.89,.32,1.28)', fill:'both'});
                    pop.animate([
                        {opacity:1, transform:'translateY(0)', offset:0},
                        {opacity:1, transform:'translateY(0)', offset:.72},
                        {opacity:0, transform:'translateY(-18px)', offset:1}
                    ], {duration:2400, easing:'ease', fill:'both'});
                }
                $('#main-modal').modal('hide');
                window.setTimeout(function() { if (pop.parentNode) pop.remove(); }, 2500);
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

<?php
$previewJs = <<<'JS'
(function () {
    var input = document.getElementById('appreciation-image-file');
    var preview = document.getElementById('appreciation-image-preview');
    var frameOptions = document.getElementById('appreciation-frame-options');
    if (!input || !preview) return;
    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        if (!file) {
            preview.classList.add('d-none');
            if (frameOptions) frameOptions.classList.add('d-none');
            return;
        }
        if (frameOptions) frameOptions.classList.remove('d-none');
        var reader = new FileReader();
        reader.onload = function (event) {
            preview.querySelector('img').src = event.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    });
    document.querySelectorAll('.appreciation-frame-input').forEach(function (radio) {
        radio.addEventListener('change', function () {
            preview.className = 'mt-3 appreciation-frame appreciation-frame--' + radio.value;
            if (!input.files || !input.files[0]) preview.classList.add('d-none');
        });
    });
})();
JS;
$this->registerJs($previewJs);
?>
