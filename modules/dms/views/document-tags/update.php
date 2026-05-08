<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentTags $model */

$this->title = 'แก้ไข tag: ' . $model->name;
?>
<div class="document-tags-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
