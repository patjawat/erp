<?php

/* @var yii\web\View $this */
/* @var app\modules\lm\models\Leave $model */

$this->title = 'แก้ไขวันลา';
$this->params['breadcrumbs'][] = ['label' => 'Leaves', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="leave-update">
    
    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
