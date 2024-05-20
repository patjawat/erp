<?php

use app\modules\helpdesk\models\Helpdesk;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
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
        <h6>การแจ้งซ่อม</h6>

<?php if($dataProvider->getTotalCount() > 0):?>

            <table class="table table-primary">
                <thead>
                    <tr>
                        <th scope="col">รายการ</th>
                        <th scope="col">สถานะงานซ่อม</th>
                        <th scope="col">การให้คะแนน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dataProvider->getModels() as $model):?>
                    <tr>
                        <td>
                            <p class="mb-0"><i class="fa-solid fa-circle-exclamation text-danger"></i>
                                <?=Html::a($model->data_json['title'],['/helpdesk/repair/timeline','id' => $model->id,'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                            </p>
                            <p class="mb-0">ผู้แจ้ง <?=$model->ViewCreateUser?> | <i class="bi bi-clock"></i>
                                <?=Yii::$app->thaiFormatter->asDateTime($model->created_at,'short')?></p>
                        </td>
                        <td class="align-middle">
                           <?=$model->viewStatus()?>
                        </td>
                        <td class="align-middle">
                        <?php
                                                echo kartik\widgets\StarRating::widget([
                                                    'name' => 'rating',
                                                    'value' => $model->rating,
                                                    'disabled' => true,
                                                    'pluginOptions' => [
                                                        'step' => 1,
                                                        'size' => 'sm',
                                                        'starCaptions' => $model->listRating(),
                                                        'starCaptionClasses' => [
                                                            1 => 'text-danger',
                                                            2 => 'text-warning',
                                                            3 => 'text-info',
                                                            4 => 'text-success',
                                                            5 => 'text-success',
                                                        ],
                                                    ],
                                                ]);
                                                ?>
                                                </td>

                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>


            <?php else:?>
            <div
                class="d-flex flex-column justify-content-center align-items-center bg-success bg-opacity-10  p-5 rounded">

                <h3 class="text-center">ไม่พบการส่งซ่อม</h3>
                <i class="fa-regular fa-circle-check text-success fs-1"></i>
            </div>

            <?php endif;?>
            <?php // Pjax::end(); ?>

            </div>
</div>
