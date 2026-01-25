<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentOrg $model */

$this->title = 'Update Document Org: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Document Orgs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="document-org-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
