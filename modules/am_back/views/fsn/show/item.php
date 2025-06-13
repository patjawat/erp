<?php
use yii\helpers\Html;
?>
<div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <?= Html::a(Html::img($model->showImg(),['class' => 'product']), ['view','id' => $model->id],['class' => 'open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <?= html::a($model->title,['view','id' => $model->id],['class' => 'open-modal', 'data' => ['size' => 'modal-md']]);?>
                                    <br>
                                    <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1">
                                        <?=$model->code?></label>
                                    </span>
                                </div>
                            </div>