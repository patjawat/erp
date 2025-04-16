<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\TeamGroupDetail $model */

$this->title = 'Create Team Group Detail';
$this->params['breadcrumbs'][] = ['label' => 'Team Group Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="team-group-detail-create">
    <?php if($model->name == 'committee'):?>
        <?= $this->render('_form_committee', [
        'model' => $model,
    ]) ?>
        <?php else:?>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
<?php endif;?>
</div>
