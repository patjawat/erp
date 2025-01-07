<?php
use yii\helpers\Html;
use app\components\AppHelper
?>

<div class="py-4">
    <?php foreach($model->listComment() as $item):?>

    <div class="d-flex justify-content-between d-flex align-items-start mb-2">
        <?php  echo $item->getAvatar('xx',false)['avatar']?>
        <div class="d-flex align-items-start">
            <?php
             $dateTime = $item->created_at;
             $createdAt = Yii::$app->thaiFormatter->asDate($dateTime, 'medium');
             $time = explode(' ', $dateTime)[1]; // แยกเวลาจากวันที่
            ?>

            <?php if($item->created_by == Yii::$app->user->id):?>
                <?php if(isset($item->data_json['employee_tag']) && $item->data_json['employee_tag'] !==""):?>
            <span class="badge bg-secondary text-white me-1"><i class="fa-solid fa-tag"></i> 
            <?php echo count($item->data_json['employee_tag']); ?>
        </span>
        <?php endif;?>
            <span class="badge bg-primary text-white me-1"><?php echo $createdAt;?></span>
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
            <?php else:?>
            <span class="badge bg-primary text-white me-2"><?php echo $createdAt;?></span>
            <?php endif;?>

        </div>

    </div>
    <?php endforeach;?>
</div>