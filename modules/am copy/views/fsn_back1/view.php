<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Fsns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php Pjax::begin(['id' => 'fsn-container','timeout' => 5000]); ?>
<div class="fsn-view">
    <?php if($btn):?>
    <div class="card">
        <div class="card-body">
            <h4 class="card-title"><?= Html::encode($model->title) ?></h4>
        </div>
    </div>
    <?= Html::a('ย้อนกลับ', ['index'], ['class' => 'btn btn-primary']) ?>
    <?php endif;?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'category_id',
            'code',
            'emp_id',
            'name',
            'title',
            'description',
            'active',
        ],
    ]) ?>

</div>
<?php Pjax::end(); ?>