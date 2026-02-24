<?php

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'รายละเอียดแจ้งเตือน';
$this->params['breadcrumbs'][] = ['label' => 'แจ้งเตือน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white py-2 px-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 small fw-normal"><?= Html::encode($model->getTypeLabel()) ?></h6>
        <span class="small opacity-75"><?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d/m/Y H:i') ?></span>
    </div>
    <div class="card-body">
        <h5 class="card-title mb-3"><?= Html::encode($model->title) ?></h5>
        <?php if ($model->message): ?>
            <p class="text-muted mb-3"><?= nl2br(Html::encode($model->message)) ?></p>
        <?php endif; ?>
        <p class="small text-muted mb-0">
            สร้างเมื่อ <?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d/m/Y H:i:s') ?>
            <?php if ($model->read_at): ?>
                · อ่านเมื่อ <?= Yii::$app->formatter->asDatetime($model->read_at, 'php:d/m/Y H:i:s') ?>
            <?php endif; ?>
        </p>
        <hr class="my-3">
        <?= Html::a('กลับไปรายการแจ้งเตือน', ['index'], ['class' => 'btn btn-outline-primary rounded-3']) ?>
    </div>
</div>
