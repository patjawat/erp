<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductType $model */

$this->title = 'Create Product Type';
$this->params['breadcrumbs'][] = ['label' => 'Product Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-type-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
