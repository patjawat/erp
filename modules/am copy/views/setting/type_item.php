<?php
use yii\helpers\Html;
use app\models\Categorise;
$category_id = Yii::$app->request->get('category_id');
$total = Categorise::find()->where(['name' => 'asset_item','category_id' => $model->code])->count('id');
?>

<div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    รหัส  <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1">
                                        <?=$model->code?>
                                    </label>
                                <?= html::a($model->title,['view','id' => $model->id],['class' => 'open-modalx', 'data' => ['size' => 'modal-md']]);?> 
                                <?php if($total > 0):?>
                                (จำนวน  <label class="badge rounded-pill text-primary-emphasis bg-danger-subtle">
                                       <?=$total?>
                                    </label>
                                    รายการ)
                                    <?php endif;?>
                                    <?php /* Html::a($model->title,['/sm/asset-type/view-type','id' => $model->id,'name' => $model->name,'category_id' => $category_id,'title' => $model->title]) */?><br>
                                </div>
                            </div>

                           