<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Categorise $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'ประเภทวัสดุ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title');
?>
<span class="d-inline-flex align-items-center gap-2 fw-semibold text-body">
    <i class="bi bi-eye text-primary"></i>
    <span><?= Html::encode($this->title) ?></span>
</span>
<?php
$this->endBlock();

$description = trim((string) $model->description);
?>

<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-body border-bottom d-flex justify-content-between align-items-center gap-2">
            <div>
                <div class="fw-semibold text-body"><?= Html::encode($model->title) ?></div>
                <div class="text-secondary small">รายละเอียดประเภทวัสดุ</div>
            </div>
            <span class="badge <?= (int) $model->active === 1 ? 'bg-success' : 'bg-secondary' ?>">
                <?= (int) $model->active === 1 ? 'ใช้งาน' : 'ไม่ใช้งาน' ?>
            </span>
        </div>

        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'table table-bordered table-striped mb-0'],
                'attributes' => [
                    [
                        'attribute' => 'code',
                        'format' => 'raw',
                        'value' => Html::tag('span', Html::encode($model->code), [
                            'class' => 'badge bg-light text-dark border fw-semibold font-monospace px-2 py-2',
                        ]),
                    ],
                    'title',
                    [
                        'attribute' => 'description',
                        'value' => $description !== '' ? $description : '-',
                    ],
                    [
                        'attribute' => 'active',
                        'format' => 'raw',
                        'value' => Html::tag(
                            'span',
                            (int) $model->active === 1 ? 'ใช้งาน' : 'ไม่ใช้งาน',
                            ['class' => (int) $model->active === 1 ? 'badge bg-success' : 'badge bg-secondary']
                        ),
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>
