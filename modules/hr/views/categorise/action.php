<?php 
use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                <div class="dropdown-menu" style="">
                                    <??>
                                    <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/hr/categorise/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>

                                    <?=Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['/hr/categorise/delete', 'id' => $model->id], [
                                        'class' => 'dropdown-item delete-item',
                                        ])?>
                                </div>
                            </div>