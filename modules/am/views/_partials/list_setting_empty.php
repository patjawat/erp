<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $theme */
/** @var string $icon */
/** @var string $title */
/** @var string $subtitle */
/** @var array $createUrl */
/** @var string $createLabel */
?>

<div class="list-setting-empty">
    <div class="list-setting-empty__icon list-setting-empty__icon--<?= Html::encode($theme) ?>">
        <i data-lucide="<?= Html::encode($icon) ?>" style="width:1.75rem;height:1.75rem;"></i>
    </div>
    <h6 class="fw-semibold text-body mb-1"><?= Html::encode($title) ?></h6>
    <p class="text-muted mb-3 small"><?= Html::encode($subtitle) ?></p>
    <?= Html::a('<i class="fa-solid fa-plus me-1"></i> ' . Html::encode($createLabel), $createUrl, [
        'class' => 'btn btn-sm btn-' . ($theme === 'emerald' ? 'success' : ($theme === 'indigo' ? 'primary' : 'warning')) . ' open-modal',
        'data' => ['size' => 'modal-md'],
    ]) ?>
</div>
