<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItem $model */


$this->title = 'ฟอร์มบันทึกข้อมูลที่ดิน';
$this->params['breadcrumbs'][] = ['label' => 'ระบบทรัพย์สิน', 'url' => ['/am/land']];
$this->params['breadcrumbs'][] = ['label' => 'ที่ดิน', 'url' => ['/am/land']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <?= Html::a(
        '<i class="fa-solid fa-angle-left"></i>',
        Yii::$app->request->referrer ?: ['/am/land'],
        [
            'class' => 'btn btn-light btn-sm rounded-circle p-1 d-flex align-items-center justify-content-center',
            'style' => 'width:32px; height:32px;'
        ]
    ) ?>
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <i data-lucide="circle-plus"></i>   สร้างใหม่</h4>
</div>

<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/components/ui/btnReturn') ?>
<?php $this->endBlock(); ?>

<div class="asset-item-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
