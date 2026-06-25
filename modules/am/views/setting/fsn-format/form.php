<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\bootstrap5\ActiveForm;
use app\modules\am\services\AssetNumberGenerator;
use app\components\AppHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AmAssetNumberFormat $model */

$currentYear = (int) AppHelper::YearBudget();
$sampleCategory = AssetNumberGenerator::SAMPLE_CATEGORY;

$tokenChips = [
    ['label' => 'รหัสหมวด',     'token' => '{category}',    'sample' => $sampleCategory],
    ['label' => 'ปี พ.ศ. 2 หลัก', 'token' => '{year:2}',      'sample' => substr((string) $currentYear, -2)],
    ['label' => 'ปี พ.ศ. 4 หลัก', 'token' => '{year:4}',      'sample' => (string) $currentYear],
    ['label' => 'ปี ค.ศ. 2 หลัก', 'token' => '{year:ad:2}',   'sample' => substr((string) ($currentYear - 543), -2)],
    ['label' => 'ปี ค.ศ. 4 หลัก', 'token' => '{year:ad:4}',   'sample' => (string) ($currentYear - 543)],
    ['label' => 'ลำดับ ไม่ pad', 'token' => '{seq:0}',       'sample' => '1'],
    ['label' => 'ลำดับ 2 หลัก',  'token' => '{seq:2}',       'sample' => '01'],
    ['label' => 'ลำดับ 3 หลัก',  'token' => '{seq:3}',       'sample' => '001'],
    ['label' => 'ลำดับ 4 หลัก',  'token' => '{seq:4}',       'sample' => '0001'],
];

$presets = [
    ['name' => 'ปี.ลำดับ (เดิม)',   'pattern' => '{category}/{year:2}.{seq:2}'],
    ['name' => 'ลำดับ/ปี (ไม่ pad)', 'pattern' => '{category}-{seq:0}/{year:2}'],
    ['name' => 'ปี4-ลำดับ3',         'pattern' => '{category}-{year:4}-{seq:3}'],
    ['name' => 'ปี ค.ศ. 4 หลัก',     'pattern' => '{category}/{year:ad:4}-{seq:3}'],
];
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-fsn-format',
    'options' => [
        'data-confirm-title' => 'ยืนยันบันทึกรูปแบบ',
        'data-confirm-text' => 'ตรวจสอบตัวอย่างทางขวาก่อนบันทึก รูปแบบนี้จะใช้สร้างหมายเลขครุภัณฑ์ใหม่',
    ],
]); ?>

<div class="modal-body">
    <div class="row g-3">
        <div class="col-lg-7">
            <?= $form->field($model, 'name')->textInput([
                'maxlength' => true,
                'class' => 'form-control',
                'placeholder' => 'เช่น ลำดับ/ปี ไม่ pad',
                'autofocus' => true,
            ])->label('ชื่อรูปแบบ') ?>

            <?= $form->field($model, 'pattern')->textInput([
                'maxlength' => true,
                'class' => 'form-control font-monospace',
                'placeholder' => '{category}-{seq:0}/{year:2}',
                'id' => 'fsn-pattern-input',
                'autocomplete' => 'off',
            ])->label('รูปแบบ (Pattern)')->hint('พิมพ์เอง หรือคลิก preset / token ด้านขวา') ?>

            <div class="mb-3">
                <label class="form-label small fw-medium text-body-secondary mb-2">รูปแบบสำเร็จรูป</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($presets as $p): ?>
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2 js-preset"
                                data-pattern="<?= Html::encode($p['pattern']) ?>">
                            <span><?= Html::encode($p['name']) ?></span>
                            <code class="small text-body-secondary"><?= Html::encode($p['pattern']) ?></code>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-check mt-3 pt-2 border-top">
                <input type="hidden" name="set_active" value="0">
                <input class="form-check-input" type="checkbox" name="set_active" value="1" id="set_active" <?= $model->is_active ? 'checked' : '' ?>>
                <label class="form-check-label" for="set_active">
                    <span class="fw-medium">ใช้รูปแบบนี้ทันที</span>
                    <small class="d-block text-body-secondary">ตั้งเป็นรูปแบบที่ใช้สร้างหมายเลขครุภัณฑ์</small>
                </label>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="rounded-2 bg-body-tertiary p-3 mb-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-eye text-primary"></i>
                    <h6 class="m-0 fw-semibold">ตัวอย่างจริง</h6>
                </div>
                <p class="small text-body-secondary mb-3">
                    หมวด <code><?= Html::encode($sampleCategory) ?></code> ปี <?= $currentYear ?>
                </p>
                <div class="vstack gap-2">
                    <div class="d-flex align-items-center justify-content-between gap-2 px-2 py-1 rounded-2 bg-body">
                        <span class="small text-body-secondary">ลำดับที่ 1</span>
                        <code class="text-body" id="fsn-preview-1">-</code>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 px-2 py-1 rounded-2 bg-body">
                        <span class="small text-body-secondary">ลำดับที่ 15</span>
                        <code class="text-body" id="fsn-preview-15">-</code>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 px-2 py-1 rounded-2 bg-body">
                        <span class="small text-body-secondary">ลำดับที่ 120</span>
                        <code class="text-body" id="fsn-preview-120">-</code>
                    </div>
                </div>
                <div class="alert alert-warning small mt-3 mb-0 d-none" id="fsn-warn">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    <span id="fsn-warn-text"></span>
                </div>
            </div>

            <div>
                <label class="form-label small fw-medium text-body-secondary mb-2">
                    Token ที่ใช้ได้ <span class="text-body-tertiary">(คลิกเพื่อแทรก)</span>
                </label>
                <div class="d-flex flex-wrap gap-1" id="fsn-token-chips">
                    <?php foreach ($tokenChips as $chip): ?>
                        <button type="button"
                                class="btn btn-sm btn-outline-primary py-1 px-2 js-token"
                                data-token="<?= Html::encode($chip['token']) ?>"
                                title="<?= Html::encode($chip['token']) ?>">
                            <span class="small fw-medium"><?= Html::encode($chip['label']) ?></span>
                            <code class="small ms-1 text-body-secondary"><?= Html::encode($chip['sample']) ?></code>
                        </button>
                    <?php endforeach; ?>
                </div>
                <label class="form-label small fw-medium text-body-secondary mt-3 mb-1">ตัวคั่น</label>
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach (['/', '-', '.', '_'] as $sep): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary js-sep px-2" data-sep="<?= Html::encode($sep) ?>">
                            <code><?= Html::encode($sep) ?></code>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer border-top">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
    <?= Html::submitButton('<i class="fa-solid fa-check me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
(function () {
    const input = document.getElementById('fsn-pattern-input');
    if (!input) return;

    const warn = document.getElementById('fsn-warn');
    const warnText = document.getElementById('fsn-warn-text');
    const previews = {
        1: document.getElementById('fsn-preview-1'),
        15: document.getElementById('fsn-preview-15'),
        120: document.getElementById('fsn-preview-120'),
    };

    const ctx = { category: %CATEGORY%, yearBe: %YEAR% };

    function expandToken(name, opts, ctx, seq) {
        switch (name) {
            case 'category':
                return String(ctx.category || '');
            case 'year': {
                let calendar = 'be', digits = 2;
                for (const o of opts) {
                    if (o === 'be' || o === 'ad') calendar = o;
                    else if (/^\\d+\$/.test(o)) digits = parseInt(o, 10);
                }
                let y = ctx.yearBe;
                if (calendar === 'ad') y -= 543;
                const s = String(y);
                return digits === 2 ? s.slice(-2) : s;
            }
            case 'seq': {
                let pad = 2;
                if (opts[0] && /^\\d+\$/.test(opts[0])) pad = parseInt(opts[0], 10);
                const s = String(seq);
                return pad <= 0 ? s : s.padStart(pad, '0');
            }
            default:
                return '{' + name + (opts.length ? ':' + opts.join(':') : '') + '}';
        }
    }

    function renderPattern(pattern, seq) {
        return pattern.replace(/\\{(\\w+)((?::\\w+)*)\\}/g, function (_m, name, optsStr) {
            const opts = optsStr ? optsStr.split(':').filter(function (s) { return s !== ''; }) : [];
            return expandToken(name, opts, ctx, seq);
        });
    }

    function validate(pattern) {
        if (!pattern.trim()) return 'pattern ต้องไม่ว่าง';
        if (!pattern.includes('{category}')) return 'ขาด token {category} อาจทำให้หมายเลขซ้ำกัน';
        if (!/\\{seq(:\\d+)?\\}/.test(pattern)) return 'ขาด token {seq} อาจทำให้หมายเลขซ้ำในปีเดียวกัน';
        return null;
    }

    function refresh() {
        const pattern = input.value || '';
        Object.keys(previews).forEach(function (seq) {
            const out = renderPattern(pattern, parseInt(seq, 10));
            if (previews[seq]) previews[seq].textContent = out || '-';
        });
        const msg = validate(pattern);
        if (msg) {
            warn.classList.remove('d-none');
            warnText.textContent = msg;
        } else {
            warn.classList.add('d-none');
        }
    }

    function insertAtCursor(text) {
        const start = input.selectionStart || 0;
        const end = input.selectionEnd || 0;
        input.value = input.value.substring(0, start) + text + input.value.substring(end);
        const pos = start + text.length;
        input.focus();
        input.setSelectionRange(pos, pos);
        refresh();
    }

    document.querySelectorAll('.js-token').forEach(function (btn) {
        btn.addEventListener('click', function () { insertAtCursor(btn.dataset.token); });
    });
    document.querySelectorAll('.js-sep').forEach(function (btn) {
        btn.addEventListener('click', function () { insertAtCursor(btn.dataset.sep); });
    });
    document.querySelectorAll('.js-preset').forEach(function (btn) {
        btn.addEventListener('click', function () {
            input.value = btn.dataset.pattern;
            input.focus();
            refresh();
        });
    });
    input.addEventListener('input', refresh);
    refresh();
})();

handleFormSubmit('#form-fsn-format');
JS;

$js = strtr($js, [
    '%CATEGORY%' => Json::encode($sampleCategory),
    '%YEAR%' => (int) $currentYear,
]);
$this->registerJs($js);
