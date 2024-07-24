<?php 
use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i
                                        class="bx bx-dots-vertical-rounded fw-bold"></i></button>
                                <div class="dropdown-menu" style="">
                                    <??>
                                    <?php echo  Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/am/fsn/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>

                                    <?php Html::a('<i class="bi bi-trash"></i>ลบ', ['/am/fsn/delete', 'id' => $model->id], [
                                        'class' => 'dropdown-item delete-item',
                                        ])?>
                                </div>
                            </div>