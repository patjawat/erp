<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

$this->title = 'จุดลงเวลา (บริเวณที่อนุญาต)';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มจุดลงเวลา', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => '{items} {pager}',
            'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
            'columns' => [
                ['class' => 'kartik\grid\SerialColumn'],
                'name',
                [
                    'attribute' => 'lat',
                    'value' => function ($m) { return $m->lat !== null ? $m->lat : '-'; },
                ],
                [
                    'attribute' => 'lng',
                    'value' => function ($m) { return $m->lng !== null ? $m->lng : '-'; },
                ],
                [
                    'attribute' => 'radius_m',
                    'label' => 'รัศมี (ม.)',
                ],
                [
                    'attribute' => 'active',
                    'value' => function ($m) { return $m->active ? 'เปิด' : 'ปิด'; },
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{view} {update} {delete}',
                    'urlCreator' => function ($action, $model) {
                        return Url::to([$action, 'id' => $model->id]);
                    },
                ],
            ],
        ]) ?>
    </div>
</div>
