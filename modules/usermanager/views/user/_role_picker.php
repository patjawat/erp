<?php

use yii\helpers\Html;

/** @var app\modules\usermanager\models\User $model */
/** @var bool $excludeDirector */

$excludeDirector = $excludeDirector ?? false;
$auth = Yii::$app->authManager;
$roles = [];
$templates = [];
$selected = array_values((array) $model->roles);
$selectedTemplate = null;
foreach ($auth->getRoles() as $role) {
    if (strncmp($role->name, 'template.', 9) === 0) {
        $children = array_filter($auth->getChildren($role->name), static fn ($child) => $child->type === 1);
        $templates[$role->name] = ['label' => $role->description ?: $role->name, 'roles' => array_keys($children)];
        if (in_array($role->name, $selected, true)) {
            $selectedTemplate = $role->name;
        }
    } else {
        $roles[$role->name] = $role;
    }
}
$groups = [
    'ระบบบุคลากร' => [],
    'ระบบลงเวลา' => [],
    'ระบบการลา' => [],
    'ระบบสารบรรณ' => [],
    'ระบบห้องประชุม' => [],
    'ระบบยานพาหนะ' => [],
    'ระบบงานทรัพย์สิน' => [],
    'ระบบงานพัสดุ' => [],
    'ระบบบัญชี' => [],
    'ระบบการเงิน' => [],
    'ระบบคลัง' => [],
    'ระบบคอมพิวเตอร์' => [],
    'แผนงานและเครื่องมือ' => [],
    'คุณภาพและความเสี่ยง' => [],
    'บริการและสนับสนุน' => [],
    'ระบบทั่วไป' => [],
    'อื่น ๆ' => [],
];

foreach ($roles as $role) {
    if ($excludeDirector && $role->name === 'director') {
        continue;
    }
    $name = strtolower($role->name);
    if (preg_match('/^accounting/', $name)) {
        $group = 'ระบบบัญชี';
    } elseif (preg_match('/^(purchase|sm)/', $name)) {
        $group = 'ระบบงานพัสดุ';
    } elseif (preg_match('/^(inventory|warehouse|stock)/', $name)) {
        $group = 'ระบบคลัง';
    } elseif (preg_match('/^(finance|payroll)/', $name)) {
        $group = 'ระบบการเงิน';
    } elseif (preg_match('/^asset/', $name)) {
        $group = 'ระบบงานทรัพย์สิน';
    } elseif (preg_match('/^leave/', $name)) {
        $group = 'ระบบการลา';
    } elseif (preg_match('/^(attendance|roster)/', $name)) {
        $group = 'ระบบลงเวลา';
    } elseif (preg_match('/^(hr|training|probation|employee|jd)/', $name)) {
        $group = 'ระบบบุคลากร';
    } elseif (preg_match('/^(plan|pm|swot|flowchart|kpi)/', $name)) {
        $group = 'แผนงานและเครื่องมือ';
    } elseif (preg_match('/^(document|dms)/', $name)) {
        $group = 'ระบบสารบรรณ';
    } elseif (preg_match('/^(qms|quality|medsop|ha12|km|iac|risk|complaint)/', $name)) {
        $group = 'คุณภาพและความเสี่ยง';
    } elseif (preg_match('/^(booking-conference|meeting)/', $name)) {
        $group = 'ระบบห้องประชุม';
    } elseif (preg_match('/^(booking-car|vehicle|driver)/', $name)) {
        $group = 'ระบบยานพาหนะ';
    } elseif (preg_match('/^(computer|technician|helpdesk)/', $name)) {
        $group = 'ระบบคอมพิวเตอร์';
    } elseif (preg_match('/^(booking|medical|housing)/', $name)) {
        $group = 'บริการและสนับสนุน';
    } elseif (preg_match('/^(admin|user|branch|director|executive|ai)/', $name)) {
        $group = 'ระบบทั่วไป';
    } else {
        $group = 'อื่น ๆ';
    }
    $groups[$group][] = $role;
}
foreach ($groups as &$items) {
    usort($items, static fn ($a, $b) => strcasecmp($a->name, $b->name));
}
unset($items);

$groupIcons = [
    'ระบบบัญชี' => 'bi-journal-text',
    'ระบบงานพัสดุ' => 'bi-box-seam',
    'ระบบคลัง' => 'bi-boxes',
    'ระบบการเงิน' => 'bi-cash-coin',
    'ระบบงานทรัพย์สิน' => 'bi-building-gear',
    'ระบบบุคลากร' => 'bi-people-fill',
    'ระบบลงเวลา' => 'bi-clock-history',
    'ระบบการลา' => 'bi-calendar3',
    'แผนงานและเครื่องมือ' => 'bi-diagram-3',
    'ระบบสารบรรณ' => 'bi-journal-bookmark-fill',
    'คุณภาพและความเสี่ยง' => 'bi-patch-check',
    'ระบบห้องประชุม' => 'bi-display',
    'ระบบยานพาหนะ' => 'bi-truck',
    'ระบบคอมพิวเตอร์' => 'bi-pc-display',
    'บริการและสนับสนุน' => 'bi-truck',
    'ระบบทั่วไป' => 'bi-grid-3x3-gap',
    'อื่น ๆ' => 'bi-shield-check',
];

$pickerId = 'erp-role-picker-' . ($excludeDirector ? 'page' : 'modal');
?>
<style>
.erp-role-picker .erp-role-group[hidden], .erp-role-picker .erp-role-row[hidden] { display: none !important; }
.erp-role-picker { container-type: inline-size; }
.erp-role-picker .erp-role-groups { display: grid; grid-template-columns: minmax(0, 1fr); }
.erp-role-picker .erp-role-group { min-width: 0; padding: 1.5rem 1rem; border-bottom: 1px solid var(--bs-border-color); }
.erp-role-picker .erp-role-icon { color: var(--bs-primary); font-size: 2rem; line-height: 1; }
.erp-role-picker .erp-role-heading { color: var(--bs-primary); font-size: 1rem; font-weight: 700; }
.erp-role-picker .erp-role-items { display: grid; gap: .35rem; margin-top: .6rem; }
.erp-role-picker .erp-role-row { min-width: 0; overflow-wrap: anywhere; padding: .15rem .25rem; border-radius: .375rem; }
.erp-role-picker .erp-role-row:hover { background: var(--bs-tertiary-bg); }
.erp-role-picker .erp-role-row .form-check { min-height: 1.5rem; }
.erp-role-picker .erp-role-row .form-check-label { line-height: 1.4; cursor: pointer; }
.erp-role-picker .erp-role-row[data-inherited="true"] .form-check-label { color: var(--bs-primary); }
.erp-role-picker .erp-role-inherited {
    background: color-mix(in srgb, var(--bs-primary) 12%, var(--bs-body-bg));
    color: var(--bs-primary);
    font-weight: 400;
}
.erp-role-picker .erp-role-row:has(.erp-role-check:checked) {
    background: color-mix(in srgb, var(--bs-primary) 7%, var(--bs-body-bg));
}
.erp-role-picker .erp-role-total {
    background: color-mix(in srgb, var(--bs-primary) 12%, var(--bs-body-bg));
    color: var(--bs-body-color);
}
.erp-role-picker .form-check-input:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}
.erp-role-picker .form-check-input:focus,
.erp-role-picker .erp-role-search:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 .25rem color-mix(in srgb, var(--bs-primary) 25%, transparent);
    outline: 0;
}
@container (min-width: 44rem) {
    .erp-role-picker .erp-role-groups { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@container (min-width: 70rem) {
    .erp-role-picker .erp-role-groups { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}
</style>
<div class="erp-role-picker" id="<?= $pickerId ?>">
    <?= Html::hiddenInput(Html::getInputName($model, 'roles'), '') ?>
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-2 mb-3">
        <div>
            <h2 class="h5 mb-1">สิทธิการใช้งาน</h2>
            <p class="small text-body-secondary mb-0">เลือกบทบาทตามระบบที่รับผิดชอบ</p>
        </div>
        <span class="badge align-self-start erp-role-total" aria-live="polite"></span>
    </div>
    <div class="border rounded-3 p-3 mb-3 bg-body-tertiary">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <label class="form-label fw-semibold mb-0" for="<?= $pickerId ?>-template">Template กลุ่มสิทธิ</label>
            <?php if (Yii::$app->user->can('admin')): ?>
                <?= Html::a('จัดการ template', ['/usermanager/template/index'], ['class' => 'small']) ?>
            <?php endif; ?>
        </div>
        <select id="<?= $pickerId ?>-template" class="form-select erp-role-template" <?= $templates ? '' : 'disabled' ?>>
            <option value="">กำหนดสิทธิเอง</option>
            <?php foreach ($templates as $name => $template): ?>
                <option value="<?= Html::encode($name) ?>" data-roles="<?= Html::encode(json_encode($template['roles'])) ?>" <?= $selectedTemplate === $name ? 'selected' : '' ?>>
                    <?= Html::encode($template['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="hidden" class="erp-role-template-value" name="<?= Html::encode(Html::getInputName($model, 'roles')) ?>[]"
            value="<?= Html::encode($selectedTemplate ?? '') ?>" <?= $selectedTemplate === null ? 'disabled' : '' ?>>
        <div class="form-text"><?= $templates ? 'เมื่อเลือก template ระบบจะล้างสิทธิที่เลือกเดิมก่อน แล้วคุณเลือกสิทธิพิเศษเพิ่มได้ด้านล่าง' : 'ยังไม่มี template — ผู้ดูแลระบบสร้างได้จากหน้าจัดการ template' ?></div>
    </div>
    <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
        <label class="visually-hidden" for="<?= $pickerId ?>-search">ค้นหาบทบาท</label>
        <input id="<?= $pickerId ?>-search" class="form-control erp-role-search" type="search" placeholder="ค้นหาชื่อบทบาทหรือคำอธิบาย" autocomplete="off">
        <label class="form-check d-flex align-items-center gap-2 mb-0 flex-shrink-0">
            <input class="form-check-input mt-0 erp-role-selected-only" type="checkbox">
            <span class="form-check-label">เฉพาะที่เลือก</span>
        </label>
    </div>
    <div class="erp-role-groups">
        <?php foreach ($groups as $groupName => $items): ?>
            <?php if (!$items) continue; ?>
            <div class="erp-role-group">
                <div class="erp-role-icon" aria-hidden="true"><i class="bi <?= Html::encode($groupIcons[$groupName] ?? 'bi-shield-check') ?>"></i></div>
                <div class="d-flex align-items-baseline gap-2 mt-2">
                    <h3 class="erp-role-heading mb-0"><?= Html::encode($groupName) ?></h3>
                    <span class="small text-body-secondary erp-role-group-count" aria-live="polite"></span>
                </div>
                <div class="erp-role-items">
                    <?php foreach ($items as $role): ?>
                        <?php $id = $pickerId . '-' . substr(md5($role->name), 0, 12); ?>
                        <div class="erp-role-row" data-search="<?= Html::encode(mb_strtolower($role->name . ' ' . (string) $role->description, 'UTF-8')) ?>">
                            <div class="form-check mb-0">
                                <input class="form-check-input erp-role-check" type="checkbox" id="<?= Html::encode($id) ?>"
                                    name="<?= Html::encode(Html::getInputName($model, 'roles')) ?>[]"
                                    value="<?= Html::encode($role->name) ?>" <?= in_array($role->name, $selected, true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="<?= Html::encode($id) ?>" title="<?= Html::encode($role->name) ?>">
                                    <?= Html::encode($role->description ?: $role->name) ?>
                                    <span class="badge erp-role-inherited d-none">จาก template</span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="text-body-secondary text-center py-3 d-none erp-role-empty">ไม่พบบทบาทที่ค้นหา</p>
</div>
<?php
$this->registerJs(<<<'JS'
document.querySelectorAll('.erp-role-picker').forEach(function (picker) {
    if (picker.dataset.ready) return;
    picker.dataset.ready = '1';
    const search = picker.querySelector('.erp-role-search');
    const selectedOnly = picker.querySelector('.erp-role-selected-only');
    const groups = [...picker.querySelectorAll('.erp-role-group')];
    const checks = [...picker.querySelectorAll('.erp-role-check')];
    const templateSelect = picker.querySelector('.erp-role-template');
    const templateValue = picker.querySelector('.erp-role-template-value');
    function applyTemplate(clearExtras) {
        if (clearExtras) {
            checks.forEach(input => { input.checked = false; });
            search.value = '';
            selectedOnly.checked = false;
        }
        templateValue.value = templateSelect.value;
        templateValue.disabled = !templateSelect.value;
        const option = templateSelect.selectedOptions[0];
        const inherited = new Set(JSON.parse(option?.dataset.roles || '[]'));
        checks.forEach(input => {
            const row = input.closest('.erp-role-row');
            const included = inherited.has(input.value);
            if (included) input.checked = false;
            input.disabled = included;
            row.dataset.inherited = String(included);
            row.querySelector('.erp-role-inherited').classList.toggle('d-none', !included);
        });
    }
    function render() {
        const query = search.value.trim().toLocaleLowerCase();
        let visible = 0;
        groups.forEach(function (group) {
            const rows = [...group.querySelectorAll('.erp-role-row')];
            let count = 0;
            rows.forEach(function (row) {
                const checked = row.querySelector('input').checked;
                const included = checked || row.dataset.inherited === 'true';
                const match = row.dataset.search.includes(query) && (!selectedOnly.checked || included);
                row.hidden = !match;
                if (match) visible++;
                if (included) count++;
            });
            group.hidden = !rows.some(row => !row.hidden);
            group.querySelector('.erp-role-group-count').textContent = count ? count + ' เลือก' : '';
        });
        picker.querySelector('.erp-role-total').textContent = 'ได้รับ ' + checks.filter(input => input.checked || input.closest('.erp-role-row').dataset.inherited === 'true').length + ' บทบาท';
        picker.querySelector('.erp-role-empty').classList.toggle('d-none', visible !== 0);
    }
    search.addEventListener('input', render);
    selectedOnly.addEventListener('change', render);
    templateSelect.addEventListener('change', function () {
        applyTemplate(true);
        render();
    });
    picker.addEventListener('change', function (event) {
        if (event.target.classList.contains('erp-role-check')) render();
    });
    applyTemplate(false);
    render();
});
JS);
?>
