<?php
use app\modules\am\models\Asset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\components\AppHelper;

?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col" style="text-align: center;">#</th>
                        <th scope="col" style="width: 250px;">รายการทรัพย์สิน</th>
                        <th scope="col"style="width: 350px;" >ผู้รับผิดชอบ</th>
                        <th scope="col">วิธีการได้มา</th>
                        <th scope="col">สถานะ/การรับเข้า</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dataProvider->getModels() as $key => $model):?>
                    <tr>
                        <th scope="row" style="text-align: center;"><?=$key+1?></th>
                        <td class="align-middle">
                            <?=$this->render('item_list',['model' => $model])?>
                        </td>
                        <td class="align-middle">

                         
                            <ul class="list-inline">
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">ประจำหน่วยงาน</span>
                                <?php if(isset($model->data_json['department_name']) && $model->data_json['department_name'] == ''):?>
                                    <?= isset($model->data_json['department_name_old']) ? $model->data_json['department_name_old'] : ''?>
                                    <?php else:?>
                                        <?= isset($model->data_json['department_name']) ?  $model->data_json['department_name'] : ''?>
                                        <?php endif;?>
                                    </li>
                                </ul>
                                <?=$model->getOwner()?>
                        </td>
                        <td class="align-middle">
                        <ul class="list-inline">
                        <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">วิธีได้มา</span> <?=$model->method_get?>
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">ประเภทเงิน</span> <?=isset($model->data_json['budget_type_text']) ? $model->data_json['budget_type_text'] : ''?>
                                </li>
                            <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">การจัดซื้อ</span> <?=$model->purchase_text?>
                                </li>
                               
                      
                         
                            </ul>
                        </td>
                        
                        <td class="align-middle" align="left">
                            <ul class="list-inline">
                            <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">ปีงบประมาณ</span>  <span class="text-danger"><?=$model->on_year?><span>
                                        
                                </li>
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">วันที่ตรวจรับ</span>  <span class="text-danger"><?=Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium')?><span>
                                </li>
           
                              
                            <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">สถานะ</span> :
                                    <?=$model->statusName()?>
                                </li>
                            </ul>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>