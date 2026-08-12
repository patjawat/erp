<?php 
use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
                                <div class="dropdown-menu">
                                    <??>
                                    <?=Html::a('<i class="bi bi-pencil-square me-1"></i>แก้ไข', ['/sm/vendor/update', 'id' => $model->id, 'title' => '<i class="bi bi-pencil-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>

                                    <?=Html::a('<i class="bi bi-trash me-1"></i>ลบ', ['/sm/vendor/delete', 'id' => $model->id], [
                                        'class' => 'dropdown-item delete-item',
                                        ])?>
                                </div>
                            </div>