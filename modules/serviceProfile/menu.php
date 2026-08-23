<?php
use yii\helpers\Html;
?>
<div class="d-flex flex-wrap gap-2">
    <?= Html::a('<i class="bi bi-journal-text me-1" aria-hidden="true"></i> Service Profile', ['/service-profile/default/index'], [
        'class' => 'btn ' . (($active ?? '') === 'profile' ? 'btn-primary' : 'btn-outline-primary'),
    ]) ?>
    <?= Html::a('<i class="bi bi-file-earmark-ruled me-1" aria-hidden="true"></i> Template Service Profile', ['/service-profile/template/index'], [
        'class' => 'btn ' . (($active ?? '') === 'template' ? 'btn-primary' : 'btn-outline-primary'),
    ]) ?>
    <?php if (Yii::$app->user->can('serviceProfileAdmin')): ?>
        <?= Html::a('<i class="bi bi-people me-1" aria-hidden="true"></i> ผู้แทนคุณภาพ', ['/service-profile/setting/reviewers'], [
            'class' => 'btn ' . (($active ?? '') === 'reviewers' ? 'btn-primary' : 'btn-outline-primary'),
        ]) ?>
    <?php endif; ?>
</div>
