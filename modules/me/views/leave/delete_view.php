<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */

$this->title = 'ระบบลา';
$this->params['breadcrumbs'][] = ['label' => 'Leaves', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

                <?= $this->render('@app/modules/hr/views/leave/view_detail', ['model' => $model]) ?>

        <?=$this->render('view_summary',['model' => $model])?>
<div class="d-flex justify-content-center gap-3">
    <?php echo Html::a('<i class="fa-solid fa-circle-check"></i> เห็นชอบ',['/'],['class' => 'btn btn-primary shadow rounded-pill'])?>
    <?php echo Html::a('ไม่เห็นชอบ',['/'],['class' => 'btn btn-secondary shadow rounded-pill'])?>
    
</div>
