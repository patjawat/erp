<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeeDetail $model */

$this->title = 'Create Employee Detail';
$this->params['breadcrumbs'][] = ['label' => 'Employee Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="employee-detail-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
