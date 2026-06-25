<?php

use yii\helpers\Html;
use yii\web\View;

/**
 * Reusable risk-level segmented chips (3 levels: ต่ำ/กลาง/สูง).
 *
 * Required params:
 * @var string $name  input name attribute, e.g. "Asset[risk_level]"
 * @var string $id    input id, e.g. "asset-risk_level"
 * @var string|null $value current value ('L' | 'M' | 'H' | null)
 *
 * Optional params:
 * @var string $label   default 'ระดับความเสี่ยง'
 * @var string $hint    helper text below chips
 * @var bool   $showClear  default true
 * @var array  $options    override default 3-level options (advanced)
 */

/** @var yii\web\View $this */

$name    = isset($name) ? (string) $name : '';
$id      = isset($id) ? (string) $id : '';
$value   = isset($value) && $value !== null ? (string) $value : '';
$label   = isset($label) ? (string) $label : 'ระดับความเสี่ยง';
$hint    = isset($hint) ? (string) $hint : '';
$showClear = !isset($showClear) || (bool) $showClear;

$defaults = [
    ['code' => 'L', 'title' => 'ต่ำ',   'tone' => 'success'],
    ['code' => 'M', 'title' => 'กลาง', 'tone' => 'warning'],
    ['code' => 'H', 'title' => 'สูง',  'tone' => 'danger'],
];
$options = isset($options) && is_array($options) && $options ? $options : $defaults;

// Register CSS once per request (key dedup).
$css = <<<CSS
.risk-chip-group {
    --rc-ink-1: #1a202c;
    --rc-ink-2: #4a5568;
    --rc-ink-3: #718096;
    --rc-surface: #ffffff;
    --rc-surface-hover: #f1f5f9;
    --rc-line: rgba(15, 23, 42, 0.08);
    --rc-line-strong: rgba(15, 23, 42, 0.14);
    --rc-primary-soft: rgba(13, 110, 253, 0.08);
    --rc-primary-line: rgba(13, 110, 253, 0.22);
    --rc-success: #15803d;
    --rc-success-soft: rgba(21, 128, 61, 0.08);
    --rc-success-line: rgba(21, 128, 61, 0.30);
    --rc-warning: #b45309;
    --rc-warning-soft: rgba(180, 83, 9, 0.08);
    --rc-warning-line: rgba(180, 83, 9, 0.30);
    --rc-danger: #b91c1c;
    --rc-danger-soft: rgba(185, 28, 28, 0.08);
    --rc-danger-line: rgba(185, 28, 28, 0.30);
    --rc-radius: 8px;
    --rc-ease: cubic-bezier(0.16, 1, 0.3, 1);
    --rc-t-fast: 120ms var(--rc-ease);

    display: block;
}
.risk-chip-group > .form-label {
    font-weight: 500;
    color: var(--rc-ink-2);
    font-size: 0.86rem;
    margin-bottom: 0.35rem;
}
.risk-chip-row { align-items: center; }
.risk-chip {
    border: 1px solid var(--rc-line-strong);
    background: var(--rc-surface);
    border-radius: var(--rc-radius);
    padding: 0.4rem 0.85rem;
    font-weight: 500;
    font-size: 0.85rem;
    color: var(--rc-ink-2);
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: background-color var(--rc-t-fast), border-color var(--rc-t-fast), color var(--rc-t-fast), box-shadow var(--rc-t-fast);
    cursor: pointer;
    min-width: 80px;
    justify-content: center;
    line-height: 1.2;
}
.risk-chip:hover { background: var(--rc-surface-hover); border-color: var(--rc-line-strong); }
.risk-chip:focus-visible {
    outline: none;
    border-color: var(--rc-primary-line);
    box-shadow: 0 0 0 3px var(--rc-primary-soft);
}
.risk-chip-dot {
    width: 0.4rem; height: 0.4rem; border-radius: 50%;
    background: currentColor; opacity: 0.5;
    transition: opacity var(--rc-t-fast), transform var(--rc-t-fast);
}
.risk-chip.is-active .risk-chip-dot { opacity: 1; }

.risk-chip--success.is-active {
    background: var(--rc-success-soft);
    border-color: var(--rc-success-line);
    color: var(--rc-success);
    box-shadow: inset 0 0 0 1px var(--rc-success-line);
}
.risk-chip--warning.is-active {
    background: var(--rc-warning-soft);
    border-color: var(--rc-warning-line);
    color: var(--rc-warning);
    box-shadow: inset 0 0 0 1px var(--rc-warning-line);
}
.risk-chip--danger.is-active {
    background: var(--rc-danger-soft);
    border-color: var(--rc-danger-line);
    color: var(--rc-danger);
    box-shadow: inset 0 0 0 1px var(--rc-danger-line);
}

.risk-chip-clear {
    color: var(--rc-ink-3);
    background: transparent;
    border: 0;
    padding: 0.3rem 0.5rem;
    cursor: pointer;
    font-size: 0.78rem;
    line-height: 1.2;
    transition: color var(--rc-t-fast);
}
.risk-chip-clear:hover { color: var(--rc-ink-1); }
.risk-chip-clear:focus-visible {
    outline: none;
    border-radius: var(--rc-radius);
    box-shadow: 0 0 0 3px var(--rc-primary-soft);
    color: var(--rc-ink-1);
}

@media (prefers-reduced-motion: reduce) {
    .risk-chip, .risk-chip-dot, .risk-chip-clear { transition: none; }
}
CSS;
$this->registerCss($css, [], 'risk-chips-css');

$js = <<<JS
(function(){
    if (window.__riskChipsBound) return;
    window.__riskChipsBound = true;
    \$(document).on('click', '.risk-chip-group .risk-chip', function() {
        var \$btn = \$(this);
        var \$group = \$btn.closest('.risk-chip-group');
        var \$hidden = \$('#' + \$group.data('target'));
        if (!\$hidden.length) return;
        var val = String(\$btn.data('value'));
        if (\$btn.hasClass('is-active')) {
            \$btn.removeClass('is-active').attr('aria-pressed', 'false');
            \$hidden.val('').trigger('change');
        } else {
            \$group.find('.risk-chip').removeClass('is-active').attr('aria-pressed', 'false');
            \$btn.addClass('is-active').attr('aria-pressed', 'true');
            \$hidden.val(val).trigger('change');
        }
    });
    \$(document).on('click', '.risk-chip-group .risk-chip-clear', function() {
        var \$group = \$(this).closest('.risk-chip-group');
        var \$hidden = \$('#' + \$group.data('target'));
        \$group.find('.risk-chip').removeClass('is-active').attr('aria-pressed', 'false');
        if (\$hidden.length) \$hidden.val('').trigger('change');
    });
})();
JS;
$this->registerJs($js, View::POS_END, 'risk-chips-js');
?>

<div class="risk-chip-group" data-target="<?= Html::encode($id) ?>">
    <?php if ($label !== ''): ?>
        <label class="form-label"><?= Html::encode($label) ?></label>
    <?php endif; ?>
    <input type="hidden"
           name="<?= Html::encode($name) ?>"
           id="<?= Html::encode($id) ?>"
           value="<?= Html::encode($value) ?>">
    <div class="risk-chip-row d-flex flex-wrap gap-2">
        <?php foreach ($options as $opt):
            $isActive = $value !== '' && (string) $opt['code'] === $value;
            $tone = isset($opt['tone']) ? $opt['tone'] : 'success';
            $cls = 'risk-chip risk-chip--' . $tone . ($isActive ? ' is-active' : '');
        ?>
            <button type="button"
                    class="<?= Html::encode($cls) ?>"
                    data-value="<?= Html::encode((string) $opt['code']) ?>"
                    aria-pressed="<?= $isActive ? 'true' : 'false' ?>">
                <span class="risk-chip-dot"></span>
                <?= Html::encode((string) $opt['title']) ?>
            </button>
        <?php endforeach; ?>
        <?php if ($showClear): ?>
            <button type="button" class="risk-chip-clear" title="ล้างค่า" aria-label="ล้างค่าระดับความเสี่ยง">
                <i class="fa-regular fa-circle-xmark"></i> ล้างค่า
            </button>
        <?php endif; ?>
    </div>
    <?php if ($hint !== ''): ?>
        <div class="form-text text-muted small mt-1"><?= $hint ?></div>
    <?php endif; ?>
</div>
