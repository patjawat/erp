<?php
use yii\helpers\Html;
use app\components\AppHelper
?>

<div class="py-4 h-100">
    <div class="flex-grow-1 overflow-auto h-100 px-4">
        <div class="d-flex flex-column position-relative ms-2">
            
            <?php foreach($model->listComment() as $index => $item): ?>
                <?php
                $dateTime = $item->created_at;
                $createdAt = Yii::$app->thaiFormatter->asDate($dateTime, 'medium');
                $time = date('H:i', strtotime($dateTime));
                ?>

                <div class="position-relative ps-4 border-start border-2 timeline-item"
                    style="border-color: #f1f5f9 !important; padding-bottom: 2rem;">

                    <div class="position-absolute rounded-circle border-2"
                        style="width: 1rem; height: 1rem; left: -9px; top: 0; background-color: #f8fafc; border-color: #60a5fa !important; z-index: 1;">
                    </div>

                    <div class="card border-0 rounded-4 shadow-sm"
                        style="background-color: #f8fafc;">
                        <div class="card-body p-3"> <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <?php echo $item->getAvatar('xx', false)['avatar'] ?>
                                    <div>
                                        </div>
                                </div>
                                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3 py-2 fw-normal" style="font-size: 10px;">
                                    <i class="fa-regular fa-clock me-1"></i> <?php echo $createdAt . ' ' . $time; ?>
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex gap-1">
                                    <?php if($item->created_by == Yii::$app->user->id): ?>
                                        <?php if(isset($item->data_json['employee_tag']) && $item->data_json['employee_tag'] !== ""): ?>
                                            <span class="badge bg-secondary text-white"><i class="fa-solid fa-tag me-1"></i>
                                                <?php echo count($item->data_json['employee_tag']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="badge bg-primary text-white">จัดการ</span>
                                    <?php endif; ?>
                                </div>

                                <?php if($item->created_by == Yii::$app->user->id): ?>
                                <div class="dropdown">
                                    <a href="javascript:void(0)" class="text-secondary px-2" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <?php echo Html::a('<i class="fa-regular fa-pen-to-square me-2"></i>แก้ไข', ['/me/documents/update-comment', 'id' => $item->id], ['class' => 'dropdown-item update-comment']) ?>
                                        <?php echo Html::a('<i class="fa-classic fa-regular fa-trash-can me-2 text-danger"></i> ลบ', ['/me/documents/delete-comment', 'id' => $item->id], ['class' => 'dropdown-item delete-comment text-danger']) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- <div class="bg-white rounded-3 p-3 border border-light mb-2">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="fa-solid fa-tag text-muted mt-1" style="font-size: 0.8rem;"></i>
                                    <div class="text-secondary" style="font-size: 0.9rem; line-height: 1.5;">
                                        <?php // echo $item->StackSendTags(); ?>
                                    </div>
                                </div>
                            </div> -->

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>

<style>
    /* ลบเส้นขอบของรายการสุดท้ายเพื่อให้ timeline จบสวยๆ */
    .timeline-item:last-child {
        border-left-color: transparent !important;
        padding-bottom: 0 !important;
    }

    /* เลือกที่ Container ที่คุณใส่ overflow-auto */
.overflow-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.overflow-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.overflow-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>