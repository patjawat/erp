<?php 
use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                <div class="dropdown-menu">
                                    <??>
                                    <?=Html::a('<i class="fa-solid fa-eye me-1"></i>', ['/settings/categorise/view', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])?>

                                   
                                </div>
                            </div>