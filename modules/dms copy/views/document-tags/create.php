<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentTags $model */

$this->title = 'Create Document Tags';
$this->params['breadcrumbs'][] = ['label' => 'Document Tags', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="document-tags-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
