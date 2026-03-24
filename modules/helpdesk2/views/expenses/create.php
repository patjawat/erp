<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\HelpdeskDetail $model */
/** @var app\modules\helpdesk2\models\HelpdeskDetail[] $expenseRows */

$this->title = 'Create Helpdesk Detail';
$this->params['breadcrumbs'][] = ['label' => 'Helpdesk Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="helpdesk-detail-create">
    <?= $this->render('_form', [
        'model' => $model,
        'expenseRows' => $expenseRows ?? [],
    ]) ?>

</div>
