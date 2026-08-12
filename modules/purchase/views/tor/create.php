<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Tor $model */
/** @var app\modules\purchase\models\TorPrice[] $prices */

$this->title = 'สร้าง TOR ใหม่';
$this->params['breadcrumbs'][] = ['label' => 'เขียน TOR', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-file-earmark-plus"></i> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับทะเบียน', ['index'], [
    'class' => 'btn btn-sm btn-outline-secondary',
]) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
        <?= $this->render('_form', ['model' => $model, 'prices' => $prices]) ?>
    </div>
</div>
