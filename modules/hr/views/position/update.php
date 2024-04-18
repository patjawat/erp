<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\Categorise $model */

$this->title = 'Update Categorise: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Categorises', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<?php Pjax::begin(['id' => 'hr-container'])?>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

<?php Pjax::end()?>
