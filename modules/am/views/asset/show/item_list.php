<?php
use yii\helpers\Html;
?>

<div class="d-flex align-items-center">
    <?= Html::a(Html::img($model->showImg(),['class' => 'avatar avatar-sm bg-primary text-white lazyautosizes ls-is-cached lazyloaded']), ['view','id' => $model->id],['class' => '', ]) ?>
<div class="avatar-detail">
                <h6 class="mb-0 fs-13"><?=$model->AssetitemName()?></h6>
                <p class="text-muted mb-0 fs-13"><?=$model->code?></p>
            </div>
</div>

