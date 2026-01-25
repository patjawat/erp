<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentOrg $model */

$this->title = 'Create Document Org';
$this->params['breadcrumbs'][] = ['label' => 'Document Orgs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="document-org-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
