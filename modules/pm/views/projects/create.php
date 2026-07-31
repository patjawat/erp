<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\Projects $model */
/** @var app\modules\pm\models\ProjectObjective[] $objectives */
/** @var app\modules\pm\models\ProjectIndicator[] $indicators */
/** @var app\modules\pm\models\ProjectResponsible[] $responsibles */

$this->title = 'เขียนโครงการใหม่';
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน/โครงการ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'projects']) ?><?php $this->endBlock();
?>
<div class="projects-create container-fluid">
    <?= $this->render('_form', [
        'model' => $model,
        'objectives' => $objectives,
        'indicators' => $indicators,
        'responsibles' => $responsibles,
    ]) ?>
</div>
