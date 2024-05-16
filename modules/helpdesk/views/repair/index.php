<?php

use app\modules\helpdesk\models\Helpdesk;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;
use app\modules\hr\models\Employees;
/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\RepairSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Repairs';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'helpdesk-container','timeout' => 5000 ]); ?>

<div class="card">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">รายการ</th>
                    <th style="width:300px">ผู้ร่วมงาน </th>
                    <th class="text-center" style="width:200px">ความเร่งด่วน</th>
                    <th class="text-center" style="width:150px">สถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($dataProvider->getModels() as $model):?>
                <tr class="align-middle">
                    <td>
                        <div class="d-flex flex-row gap-3">
                        <?=$model->showAvatarCreate();?>
                            <div class="d-flex flex-column">
                                <?=Html::a($model->data_json['title'],['/helpdesk/repair/view','id' => $model->id])?>
                                <div>
                                    <span class="mb-0 fs-13 text-muted"><?=$model->data_json['location']?></span> | <?=$model->viewCreateDate()?>
                                </div>
                            </div>
                        </div>

                    </td>
                    <td><?=$model->avatarStack()?></td>
                    <td class="text-center"><?=$model->viewUrgency()?></td>
                    <td class="text-center"> <?=$model->viewStatus()?></td>
                </tr>
                <?php endforeach;?>
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
<?php Pjax::end(); ?>