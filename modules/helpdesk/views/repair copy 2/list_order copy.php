<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\bootstrap5\LinkPager;
use app\modules\hr\models\Employees;
use app\modules\helpdesk\models\Helpdesk;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\RepairSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Repairs';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php // Pjax::begin(['id' => 'helpdesk-container','timeout' => 5000 ]); ?>

<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
    <h6><i class="bi bi-ui-checks"></i> ทะเบียนงานซ่อม <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
    <?=$this->render('_search', ['model' => $searchModel])?>
    </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">รายการ</th>
                    <th scope="col">ผู้แจ้งซ่อม</th>
                    <th style="width:300px">ผู้ร่วมงานซ่อม </th>
                    <th class="text-center" style="width:150px">สถานะ</th>
                    <th class="text-center" style="width:150px">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $key => $model): ?>
                <tr class="align-middle">
                    <td><?php echo ($key+1)?></td>
                    <td>
                        
                        <a href="<?php echo Url::to(['/helpdesk/repair/view', 'id' => $model->id])?>" class="text-dark open-modal-fullscree-xn">
                            <div>
                                <p class="text-primary fw-semibold fs-13 mb-0">
                                    <?= $model->viewUrgency() ?>
                                    <?php echo Yii::$app->thaiFormatter->asDate($model->created_at, 'long')?>
                                </p>
                                <p style="width:600px" class="text-truncate fw-semibold fs-6 mb-0"><?php echo $model->data_json['title']?></p>
                            </div>
                        </a>
                        
                        <!-- <div class="d-flex flex-row gap-3">
                            <?= $model->showAvatarCreate(); ?>
                            <div class="d-flex flex-column">
                                <?= Html::a($model->data_json['title'], ['/helpdesk/repair/view', 'id' => $model->id]) ?>
                                <div>
                                    <span class="mb-0 fs-13 text-muted"><?= $model->data_json['location'] ?></span> | <?= $model->viewCreateDate() ?>
                                </div>
                            </div>
                        </div> -->
                    </td>
                    <td> <?= $model->showAvatarCreate(); ?></td>
                    <td><?= $model->avatarStack() ?></td>
                    <td class="text-center"> <?= $model->viewStatus() ?></td>
                    <td class="text-center">
                        <?php echo Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>',['/helpdesk/general/update','view' => $model->id],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                    </td>
                </tr>
    
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            <div class="text-muted">
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'firstPageLabel' => 'หน้าแรก',
                    'lastPageLabel' => 'หน้าสุดท้าย',
                    'options' => [
                        'listOptions' => 'pagination pagination-sm',
                        'class' => 'pagination-sm',
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
<?php // Pjax::end(); ?>