<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
?>
<div class="card border">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h6><i class="fa-solid fa-user-tag"></i> ส่งต่อรายบุคคล</h6>
                                </div>

                                <?php Pjax::begin(['id' => 'document-tag']); ?>
                                <?php echo $model->StackDocumentTags('employee_tag')?>
                                <?php Pjax::end(); ?>
                            </div>
                            <div class="card-footer d-flex justify-content-end">
                                
                                <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> เพิ่มบุคคล', ['/dms/document-tags/create', 'document_id' => $model->id,'ref' => $model->ref, 'name' => 'employee_tag', 'tilte' => '<i class="fa-solid fa-user-tag"></i> ส่งต่อรายบุคคล'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                            </div>
                        </div>