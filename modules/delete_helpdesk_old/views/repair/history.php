<?php

use app\modules\helpdesk\models\Helpdesk;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\RepairSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Repairs';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php // Pjax::begin(['id' => 'repair-container','timeout' => 5000 ]); ?>
<div class="card">
    <div class="card-body">
<?php if ($dataProvider->getTotalCount() > 0): ?>

            <table class="table">
                <thead>
                <tr>
                    <th scope="col">รายการ</th>
                    <th style="width:300px">ผู้ร่วมงาน </th>
                    <th class="text-center" style="width:200px">ความเร่งด่วน</th>
                    <th class="text-center" style="width:150px">สถานะ</th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                        <tr class="align-middle">
                    <td>
                        <div class="d-flex flex-row gap-3">
                        <?= $model->showAvatarCreate(); ?>
                            <div class="d-flex flex-column">
                                <?= Html::a($model->data_json['title'], ['/helpdesk/repair/timeline', 'id' => $model->id, 'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม'], ['class' => 'open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                <div>
                                    <span class="mb-0 fs-13 text-muted"><?= $model->data_json['location'] ?></span> | <?= $model->viewCreateDate() ?>
                                </div>
                            </div>
                        </div>

                    </td>
                    <td><?= $model->avatarStack() ?></td>
                    <td class="text-center"><?= $model->viewUrgency() ?></td>
                    <td class="text-center"> <?= $model->viewStatus() ?></td>
                </tr>
                   
                    <?php endforeach; ?>
                </tbody>
            </table>


            <?php else: ?>
            <div
                class="d-flex flex-column justify-content-center align-items-center bg-success bg-opacity-10  p-5 rounded">

                <h3 class="text-center">ไม่พบการส่งซ่อม</h3>
                <i class="fa-regular fa-circle-check text-success fs-1"></i>
            </div>

            <?php endif; ?>
            <?php // Pjax::end(); ?>

            </div>
</div>
