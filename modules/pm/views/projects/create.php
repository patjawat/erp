<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\Projects $model */
/** @var app\modules\pm\models\ProjectObjective[] $objectives */
/** @var app\modules\pm\models\ProjectIndicator[] $indicators */
/** @var app\modules\pm\models\ProjectResponsible[] $responsibles */

$this->title = 'เขียนโครงการใหม่';
$this->params['breadcrumbs'][] = ['label' => 'โครงการ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="projects-create container-fluid">
    <h4 class="mb-3"><?= Html::encode($this->title) ?></h4>
    <?= $this->render('_form', [
        'model' => $model,
        'objectives' => $objectives,
        'indicators' => $indicators,
        'responsibles' => $responsibles,
    ]) ?>
</div>
