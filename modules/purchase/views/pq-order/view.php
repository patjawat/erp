<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Order $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?= $this->render('../default/menu2') ?>



<div class="d-flex justify-content-center">
<div class="col-lg-8 col-md-8 col-sm-12">


    <?= $this->render('../pr-order/step2', ['model' => $model]) ?>
</div>
</div>