<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Store $model */

$this->title = 'Create Store';
$this->params['breadcrumbs'][] = ['label' => 'Stores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

        <div class="card">
            <div class="card-body">
                <?= $this->render('_form', [
                'model' => $model,
                ]) ?>
            </div>
        </div>