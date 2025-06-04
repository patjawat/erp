<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */

$this->title = 'สร้างหนังสือ';
$this->params['breadcrumbs'][] = ['label' => 'สารบรรณ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="documents-create">
    <?= $this->render('_form_'.$model->document_group, [
        'model' => $model
    ]) ?>

</div>
