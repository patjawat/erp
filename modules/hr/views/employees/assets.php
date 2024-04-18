<?php
use app\modules\am\models\Asset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\components\AppHelper;
$assets = Asset::find()->where(['asset_group' => 3,'owner' => $model->cid])->all();
?>


<h6>ครุภัณฑ์ <?=count($assets)?> รายการ</h6>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col" style="text-align: center;">#</th>
                        <th scope="col" style="width: 450px;">ครุภัณฑ์</th>
                        <th scope="col">ผู้รับผิดชอบ</th>
                        <th scope="col" style="text-align: center;">มูลค่า</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($assets as $key => $asset):?>
                    <tr>
                        <th scope="row" style="text-align: center;"><?=$key+1?></th>
                        <td class="align-middle">
                            <?= $this->render('@app/modules/am/views/asset/show/item_list.php',['model' => $asset])?>
                        </td>
                        <td>

                         
                            <ul class="list-inline">
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">ประจำหน่วยงาน</span>
                                <?php if(isset($asset->data_json['department_name']) && $asset->data_json['department_name'] == ''):?>
                                    <?= $asset->data_json['department_name_old']?>
                                    <?php else:?>
                                        <?php // $asset->data_json['department_name']?>
                                        <?php endif;?>
                                    </li>
                                </ul>
                                <?=$asset->getOwner()?>
                        </td>
                        <td class="align-middle" align="right">
                            <div class="d-flex justify-content-between">

                                <div>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">วันเดือนปีทีซื้อ</span>
                                    <span class="text-danger"><?=$asset->receive_date?><span>
                                </div>

                                <span
                                    class="text-white bg-primary badge rounded-pill fs-6"><?=isset($asset->price) ? number_format($asset->price,2) : ''?></span>

                            </div>
                            <?php if(isset($asset->Retire()['progress'])):?>
                            <div class="progress progress-sm mt-3 w-100">
                                <div class="progress-bar" role="progressbar"
                                    <?= "style='width:". $asset->Retire()['progress'] ."%; background-color:".$asset->Retire()['color'].";  '" ?>
                                    aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-2" style="font-size: 10px; width:100%;">
                                <div>
                                    เหลือเวลา
                                    <?= AppHelper::CountDown($asset->Retire()['date'])[0] != '-' ? AppHelper::CountDown($asset->Retire()['date']) : "หมดอายุการใช้งาน" ?>
                                </div>
                                |
                                <div>
                                    <?=$asset->Retire()['date'];?>
                                </div>
                            </div>
                            <?php endif;?>
                        </td>

                    </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>
