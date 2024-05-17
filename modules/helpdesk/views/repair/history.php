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
<?php if($dataProvider->getTotalCount() > 0):?>

            <table class="table table-primary">
                <thead>
                    <tr>
                        <th scope="col">รายการ</th>
                        <th scope="col">สถานะงานซ่อม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dataProvider->getModels() as $model):?>
                    <tr>
                        <td>
                            <p class="mb-0"><i class="fa-solid fa-circle-exclamation text-danger"></i>
                                <?=Html::a($model->data_json['title'],['/helpdesk/repair/timeline','id' => $model->id,'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                            </p>
                            <p class="mb-0">ผู้แจ้ง นายปัจวัฒน์ ศรีบุญเรือง | <i class="bi bi-clock"></i>
                                <?=Yii::$app->thaiFormatter->asDateTime($model->created_at,'short')?></p>
                        </td>
                        <td class="align-middle">
                            <?php if($model->data_json['status_name'] == 'ร้องขอ'):?>
                            <label class="badge rounded-pill text-primary-emphasis bg-warning-subtle p-2 text-truncate"><i class="fa-regular fa-hourglass-half"></i> <?=$model->data_json['status_name']?></label>
                        <?php endif?>
                        <?php if($model->data_json['status_name'] == 'เสร็จสิ้น'):?>
                            <label class="badge rounded-pill text-success-emphasis bg-success-subtle p-2 text-truncate"><i class="bi bi-check2-circle fs-6"></i> <?=$model->data_json['status_name']?></label>
                        <?php endif?>
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