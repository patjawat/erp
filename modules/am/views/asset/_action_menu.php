<?php
use yii\helpers\Html;
?>
<div class="d-flex flex-wrap gap-2">

    <?php if(Yii::$app->user->can('asset')):?>
        <?= Html::a('<i class="fa-solid fa-copy me-2"></i> ทำสำเนา', ['create', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>

        <?= Yii::$app->user->can('asset') ?  Html::a('<i class="fa-solid fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-warning'])  : ''?>
        <?= Yii::$app->user->can('asset') ? Html::a('<i class="fa-solid fa-trash"></i> ลบ', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?',
                'method' => 'post',
            ],
        ]) : '' ?>
        <?php endif;?>
        <?= Html::a(' <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="5" height="5" x="3" y="3" rx="1"></rect>
                <rect width="5" height="5" x="16" y="3" rx="1"></rect>
                <rect width="5" height="5" x="3" y="16" rx="1"></rect>
                <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
                <path d="M21 21v.01"></path>
                <path d="M12 7v3a2 2 0 0 1-2 2H7"></path>
                <path d="M3 12h.01"></path>
                <path d="M12 3h.01"></path>
                <path d="M12 16v.01"></path>
                <path d="M16 12h1"></path>
                <path d="M21 12v.01"></path>
                <path d="M12 21v-1"></path>
            </svg> QR-Code', ['/am/asset/view-qr-pdf', 'id' => $model->id], [
                                    'class' => 'btn btn-white border shadow-sm text-secondary d-flex align-items-center gap-2 btn-sm px-3 py-2 bg-white',
                                    'title' => 'พิมพ์ QR-Code',
                                    'data-pjax' => 0,
                                    'target' => '_blank',
                                ]) ?>
      
       <?= $this->render('@app/components/ui/btnReturn') ?>
    </div>