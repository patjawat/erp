<?php
use yii\helpers\Html;

?>
<div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i
                                        class="bx bx-dots-vertical-rounded fw-bold"></i></button>
                                <div class="dropdown-menu" style="">
                                    <??>
                                    <?=Html::a('<i class="bi bi-eye me-1"></i> แสดง', ['/hr/employee-detail/view', 'id' => $model->id,'name' => $name,'title' => $title], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>
                                    <?=Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => $model->id, 'title' => '<i class="fa-solid fa-user-tag"></i> ประวัติการการรับทุน'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>

                                    <?=Html::a('<i class="bi bi-trash"></i>ลบ', ['/hr/employee-detail/delete', 'id' => $model->id], [
'class' => 'dropdown-item delete-item',
])?>
                                </div>
                            </div>