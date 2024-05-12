<?php
use yii\helpers\Html;
?>
<div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col" style="text-align: center;">#</th>
                            <th scope="col">หมวดหมู่ครุภัณฑ์</th>
                            <th class="text-center">อายุการใช้งาน(ปี)</th>
                            <th scope="col" style="text-align: center;">อัตราค่าเสื่อมราคาต่อปี(%)</th>
                            <th scope="col" style="text-align: center;">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dataProvider->getModels() as $key => $model):?>
                        <tr>
                            <td scope="row" style="text-align: center;"><?=$key+1?></td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                            <?= Html::a(Html::img($searchModel->showLogoImg($model->ref),['class' => 'product']), ['view', 'id' => $model->id]) ?>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <?= html::a($model->title,['view','id' => $model->id]);?>
                                        <br>
                                        <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1">
                                            <?=$model->code?></label>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="align-middle text-center">
                                <span class="badge rounded-pill bg-primary text-white"><?=isset($model->data_json['service_life']) ? $model->data_json['service_life'] : ''?> </span>
                            </td>

                            <td class="align-middle text-center">
                            <span class="badge rounded-pill bg-primary text-white"><?=isset($model->data_json['depreciation']) ? $model->data_json['depreciation'] : ''?> </span>
                            </td>
                            <td class="align-middle text-center">
                                <?= $this->render('../action',['model' => $model]);?>
                            </td>

                        </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
