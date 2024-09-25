<h1>Position</h1>
<?php Pjax::begin()?>

<p>
    <?= Html::a('Create Categorise', ['create'], ['class' => 'btn btn-success']) ?>
</p>

<?php // echo $this->render('_search', ['model' => $searchModel]); ?>

<?= GridView::widget([
    'dataProvider' => $dataProviderPositionLevel,
    'filterModel' => $searchModel,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'code',
        'title',
        [
            'class' => ActionColumn::className(),
            'urlCreator' => function ($action, Categorise $model, $key, $index, $column) {
                return Url::toRoute([$action, 'id' => $model->id]);
             }
        ],
    ],
]); ?>
<?php Pjax::end()?>