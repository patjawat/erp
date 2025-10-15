<?php
use yii\helpers\Html;
?>
<div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    จัดการ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1" style="">
                                    <li><?=Html::a('<i class="bi bi-eye me-2"></i> บันทึกงานซ่อม',['/helpdesk/service/view','id' => $item->id,'title' => 'รายละเอียดการแจ้งซ่อม #'.$item->repair_number],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-xl']])?></li>
                                    <li><?=Html::a('<i class="fa-regular fa-file-lines me-2"></i>เบิกอะไหล่',['/helpdesk/repair-parts/create','helpdesk_id' => $item->id,'title' => 'รายละเอียดการแจ้งซ่อม #'.$item->repair_number],['class' => 'dropdown-item','data' => ['size' => 'modal-xl']])?></li>
                                    <li><?=Html::a('<i class="fa-solid fa-print me-2"></i>พิมพ์ใบส่งซ่อม',['/helpdesk/service/print','id' => $item->id,'title' => 'รายละเอียดการแจ้งซ่อม #'.$item->repair_number],['class' => 'dropdown-item','target' => '_blank',])?></li>
                                    <li><?=Html::a('<i class="bi bi-pencil me-2"></i>แก้ไข',['/helpdesk/service/update','id' => $item->id,'title' => '<i class="bi bi-pencil me-2"></i>แก้ไข'],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg']])?></li>
                                    <li><?=Html::a('<i class="fa-solid fa-ban me-2"></i>ยกเลิก',['/helpdesk/service/cancel','id' => $item->id,'title' => '<i class="bi bi-pencil me-2"></i>แก้ไข'],['class' => 'dropdown-item cancel-order'])?></li>
                                    <li><?=Html::a('<i class="fa-solid fa-trash me-2"></i>ลบ',['/helpdesk/service/delete','id' => $item->id,'title' => '<i class="bi bi-pencil me-2"></i>แก้ไข'],['class' => 'dropdown-item delete-repair-item'])?></li>
                                </ul>
                            </div>