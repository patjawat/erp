<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'ประเภทวัสดุ';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-item-type-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-grid-fill text-primary"></i> <?= Html::encode($this->title) ?></h4>
        <?= Html::a('<i class="bi bi-plus-circle"></i> เพิ่มประเภทวัสดุ', ['create'], [
            'class' => 'btn btn-primary',
            'data' => ['pjax' => 0],
        ]) ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{items}\n<div class='p-2'>{pager}</div>",
                'tableOptions' => ['class' => 'table table-hover table-striped mb-0'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'code:raw:รหัส',
                    'title:raw:ชื่อประเภท',
                    'description:raw:รายละเอียด',
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{update} {delete}',
                        'urlCreator' => function ($action, $model, $key, $index) {
                            return Url::to([$action, 'id' => $model->id]);
                        },
                        'buttons' => [
                            'update' => function ($url, $model, $key) {
                                return Html::a('<i class="bi bi-pencil"></i>', $url, [
                                    'class' => 'btn btn-sm btn-outline-primary',
                                    'title' => 'แก้ไข',
                                ]);
                            },
                            'delete' => function ($url, $model, $key) {
                                return Html::a('<i class="bi bi-trash text-danger"></i>', $url, [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'title' => 'ลบ',
                                    'data' => [
                                        'confirm' => 'ต้องการลบประเภทวัสดุนี้หรือไม่?',
                                        'method' => 'post',
                                    ],
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
