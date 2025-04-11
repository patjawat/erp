<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use app\modules\am\models\Asset;

?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="fw-semibold" scope="col" style="text-align: center;">ลำดับ</th>
                        <th class="fw-semibold" scope="col">รายการทรัพย์สิน</th>
                        <th class="fw-semibold" scope="col" style="width: 350px;" >ประจำหน่วยงาน</th>
                        <th class="fw-semibold" scope="col">วิธีได้มา</th>
                        <th class="fw-semibold" scope="col">ปีงบประมาณ</th>
                        <th class="fw-semibold" scope="col">สถานะ</th>
                        <th class="fw-semibold text-center" scope="col" style="width: 150px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach($dataProvider->getModels() as $key => $model):?>
                        <tr>
                        <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                        <td class="align-middle">
                            <?=$this->render('item_list',['model' => $model])?>
                        </td>
                        <td class="align-middle">
                            <ul class="list-inline">
                                <?php if(isset($model->data_json['department_name']) && $model->data_json['department_name'] == ''):?>
                                    <?= isset($model->data_json['department_name_old']) ? $model->data_json['department_name_old'] : ''?>
                                    <?php else:?>
                                        <?= isset($model->data_json['department_name']) ?  $model->data_json['department_name'] : ''?>
                                        <?php endif;?>
                                    </li>
                                </ul>
                        </td>
                        <td class="align-middle">
                            <?=$model->method_get?>
                        
                        </td>
                        
                        <td class="align-middle"><?=$model->on_year?></td>
                        <td><?=$model->statusName()?></td>
                        <td class="align-middle text-center">
                            <div class="d-flex gap-3">
                                <?=Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/am/asset/view','id' => $model->id])?>
                                <?=Html::a('<i class="fa-solid fa-pen-to-square fa fa-2x text-warning"></i>',['/am/asset/update','id' => $model->id])?>
                                <?= Html::a('<i class="fa-solid fa-trash fa-2x text-danger"></i>', ['/am/asset/delete', 'id' => $model->id], ['class' => 'delete-asset']) ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>