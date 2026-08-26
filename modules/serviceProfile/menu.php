<?php
use yii\helpers\Html;
use app\components\UserHelper;
use app\modules\serviceProfile\services\InboxService;

$employee = UserHelper::GetEmployee();
$actionCount = $employee ? (new InboxService())->count($employee) : 0;
?>
<div class="d-flex flex-wrap gap-2">
    <?= Html::a('<i class="bi bi-journal-text me-1" aria-hidden="true"></i> Service Profile', ['/service-profile/default/index'], [
        'class' => 'btn ' . (($active ?? '') === 'profile' ? 'btn-primary' : 'btn-outline-primary'),
    ]) ?>
    <?= Html::a(
        '<i class="bi bi-inbox me-1" aria-hidden="true"></i> งานที่รอดำเนินการ <span class="badge ' . (($active ?? '') === 'action' ? 'text-bg-light' : 'text-bg-primary') . ' ms-1">' . (int) $actionCount . '</span>',
        ['/service-profile/default/index', 'scope' => 'action'],
        [
            'class' => 'btn ' . (($active ?? '') === 'action' ? 'btn-primary' : 'btn-outline-primary'),
            'aria-label' => 'งานที่รอดำเนินการ ' . (int) $actionCount . ' รายการ',
        ]
    ) ?>
    <?= Html::a('<i class="bi bi-file-earmark-ruled me-1" aria-hidden="true"></i> Template Service Profile', ['/service-profile/template/index'], [
        'class' => 'btn ' . (($active ?? '') === 'template' ? 'btn-primary' : 'btn-outline-primary'),
    ]) ?>
    <?php if (Yii::$app->user->can('serviceProfileAdmin')): ?>
        <?= Html::a('<i class="bi bi-people me-1" aria-hidden="true"></i> ผู้แทนคุณภาพ', ['/service-profile/setting/reviewers'], [
            'class' => 'btn ' . (($active ?? '') === 'reviewers' ? 'btn-primary' : 'btn-outline-primary'),
        ]) ?>
    <?php endif; ?>
</div>
