<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\pm\models\Projects;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\ProjectsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'แผนงาน/โครงการ';
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'projects']) ?><?php $this->endBlock();
?>
<div class="projects-index container-fluid">

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <?php Pjax::begin(); ?>

    <?= $this->render('_search', ['model' => $searchModel]) ?>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="text-muted small">พบ <?= $dataProvider->getTotalCount() ?> โครงการ</div>
        <?= Html::a('<i class="fa-solid fa-plus me-1"></i> เขียนโครงการ', ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <div class="card">
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover align-middle'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'code',
                        'headerOptions' => ['style' => 'width:110px'],
                    ],
                    [
                        'attribute' => 'name',
                        'format' => 'raw',
                        'value' => function (Projects $m) {
                            return Html::a(Html::encode($m->name), ['view', 'id' => $m->id], ['class' => 'fw-semibold text-decoration-none']);
                        },
                    ],
                    [
                        'attribute' => 'org_unit_id',
                        'label' => 'หน่วยงาน',
                        'value' => function (Projects $m) {
                            return $m->departmentPath();
                        },
                    ],
                    [
                        'attribute' => 'thai_year',
                        'headerOptions' => ['style' => 'width:90px'],
                    ],
                    [
                        'attribute' => 'budget_total',
                        'format' => ['decimal', 2],
                        'headerOptions' => ['style' => 'width:120px'],
                        'contentOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'filter' => Projects::statusList(),
                        'headerOptions' => ['style' => 'width:130px'],
                        'value' => function (Projects $m) {
                            return '<span class="badge ' . $m->statusBadgeClass() . '">' . Html::encode($m->statusLabel()) . '</span>';
                        },
                    ],
                    [
                        'class' => ActionColumn::class,
                        'headerOptions' => ['style' => 'width:100px'],
                        'urlCreator' => function ($action, Projects $model, $key, $index, $column) {
                            return Url::toRoute([$action, 'id' => $model->id]);
                        },
                        'buttons' => [
                            'delete' => function ($url, Projects $model) {
                                return Html::a('<i class="fa-solid fa-trash"></i>', $url, [
                                    'class' => 'text-danger',
                                    'data-confirm' => 'ยืนยันการลบโครงการนี้?',
                                    'data-method' => 'post',
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
    <?php Pjax::end(); ?>

</div>
