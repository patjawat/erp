<?php
use yii\helpers\Html;
?>

                <table class="table  m-b-0 transcations mt-2">
                    <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                        <tr class="align-middle">
                            <td class="align-middle" style="width:15px;">
                                    <?=$model->showAvatarCreate();?>
                            </td>
                            <td>
                                <div class="d-flex align-middle ms-3">
                                    <div class="d-inline-block">
                                        <?=Html::a($model->data_json['title'],['/helpdesk/repair/view','id' => $model->id,'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม'],['class' => 'h6 mb-1','data' => ['pjax' => false]])?>
                                        <p class="mb-0 fs-13 text-muted"><?=$model->data_json['location']?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-block">
                                    <h6 class="mb-2 fs-15 fw-semibold"><?php echo $model->viewUrgency()?></h6>
                                    <p class="mb-0 fs-11 text-muted"><?=$model->viewCreateDate()?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach?>
                    </tbody>
                </table>
