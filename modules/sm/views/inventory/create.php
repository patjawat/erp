<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */

$this->title = 'Create Inventory';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="inventory-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
