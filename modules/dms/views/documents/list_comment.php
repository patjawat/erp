<?php
use yii\helpers\Html;
use app\components\AppHelper
?>
<h6><i class="fa-regular fa-comments fs-2"></i> การลงความเห็น</h6>

<div class="p-4">
    <?php foreach($model->listComment() as $item):?>

    <div class="d-flex justify-content-between d-flex align-items-start mb-2">
        <?php echo $item->getAvatar('xx',false)['avatar']?>
        <div class="d-flex align-items-start gap-2">
            <?php
             $dateTime = $item->created_at;
             $createdAt = Yii::$app->thaiFormatter->asDate($dateTime, 'medium');
             $time = explode(' ', $dateTime)[1]; // แยกเวลาจากวันที่
            ?>
            <span class="badge bg-primary text-white"><?php echo $createdAt;?></span>

            <?php if($item->created_by == Yii::$app->user->id):?>
            <div class="dropdown float-end">
                <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">

                    <?php echo Html::a('<i class="fa-regular fa-pen-to-square me-2"></i>แก้ไข', ['/dms/documents/update-comment', 'id' => $item->id], ['class' => 'dropdown-item update-comment', 'data' => ['size' => 'modal-md']]) ?>
                    <?php echo Html::a('<i class="fa-classic fa-regular fa-trash-can me-2"></i> ลบ', ['/dms/documents/delete-comment', 'id' => $item->id], ['class' => 'dropdown-item delete-comment', 'data' => ['size' => 'modal-md']]) ?>
                    
                </div>
            </div>
            <?php endif;?>
            
        </div>

    </div>
    <?php endforeach;?>
</div>