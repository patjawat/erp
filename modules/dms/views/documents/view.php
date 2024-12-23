<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */

$this->title = $model->topic;
$this->params['breadcrumbs'][] = ['label' => 'Documents', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="card">
    <div class="card-body">


    <h5><i class="bi bi-journal-text fs-4"></i> <?= Html::encode($this->title) ?></h5>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'org_id',
                'value' => $model->documentOrg->title ?? '-'
            ],
            [
                'attribute' => 'doc_type_id',
                'value' => $model->documentType->title ?? '-'
            ],

            'thai_year',
            'doc_regis_number',
            'doc_number',
            'urgent',
            'secret',
        ],
    ]) ?>


</div>
</div>
<?php echo $model->Upload('document')?>

