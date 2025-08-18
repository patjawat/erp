<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'รายการแผน';
?>
<div class="plan-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('เพิ่มแผน', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'title',
            'plan_type',
            'budget_total',
            'budget_used',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function($model) {
                    $statusColors = [
                        'draft' => 'secondary',
                        'submitted' => 'primary',
                        'approved' => 'success',
                        'completed' => 'dark'
                    ];
                    return Html::tag('span', $model->status, [
                        'class' => 'badge bg-' . ($statusColors[$model->status] ?? 'light')
                    ]);
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete} {status}',
                'buttons' => [
                    'status' => function ($url, $model) {
                        return Html::dropDownList(
                            'status',
                            $model->status,
                            [
                                'draft' => 'ฉบับร่าง',
                                'submitted' => 'ยื่นคำขอ',
                                'approved' => 'อนุมัติ',
                                'completed' => 'เสร็จสิ้น'
                            ],
                            [
                                'class' => 'form-select form-select-sm change-status',
                                'data-id' => $model->id,
                                'style' => 'width:120px'
                            ]
                        );
                    }
                ]
            ],
        ],
    ]); ?>
</div>

<?php
$changeUrl = Url::to(['change-status']);
$js = <<<JS
$(".change-status").on("change", function() {
    var id = $(this).data("id");
    var status = $(this).val();
    $.post("$changeUrl", {id: id, status: status}, function() {
        location.reload();
    });
});
JS;
$this->registerJs($js);
?>
