<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Categorise;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem;

try {
    $countReceive = $model->CountType('receive') ?? 0;
    $countSend = $model->CountType('send') ?? 0;
    $countAppointment = $model->CountType('appointment') ?? 0;
    $countAnnounce = $model->CountType('announce') ?? 0;
} catch (\Throwable $th) {
    $countReceive =  0;
    $countSend =  0;
    $countAppointment =  0;
    $countAnnounce =  0;
}


$layout = app\components\SiteHelper::getInfo()['layout'];
?>


<div class="d-flex gap-2">
    <a href="<?= Url::to(['/dms/dashboard']) ?>" class="btn <?= $active !== 'dashboard' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
        </svg>
        ภาพรวม
    </a>
    <a href="<?= Url::to(['/dms/documents/receive']) ?>" class="btn <?= $active !== 'receive' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download-icon lucide-download">
            <path d="M12 15V3" />
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
            <path d="m7 10 5 5 5-5" />
        </svg>
        หนังสือรับ
    </a>
    <a href="<?= Url::to(['/dms/documents/send']) ?>" class="btn <?= $active !== 'send' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send-icon lucide-send">
            <path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z" />
            <path d="m21.854 2.147-10.94 10.939" />
        </svg>
        หนังสือส่ง
    </a>
    <a href="<?= Url::to(['/dms/documents/appointment']) ?>" class="btn <?= $active !== 'appointment' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-signature-icon lucide-signature">
            <path d="m21 17-2.156-1.868A.5.5 0 0 0 18 15.5v.5a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1c0-2.545-3.991-3.97-8.5-4a1 1 0 0 0 0 5c4.153 0 4.745-11.295 5.708-13.5a2.5 2.5 0 1 1 3.31 3.284" />
            <path d="M3 21h18" />
        </svg>
        คำสั่ง
    </a>

    <a href="<?= Url::to(['/dms/documents/announce']) ?>" class="btn <?= $active !== 'announce' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-megaphone-icon lucide-megaphone">
            <path d="M11 6a13 13 0 0 0 8.4-2.8A1 1 0 0 1 21 4v12a1 1 0 0 1-1.6.8A13 13 0 0 0 11 14H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z" />
            <path d="M6 14a12 12 0 0 0 2.4 7.2 2 2 0 0 0 3.2-2.4A8 8 0 0 1 10 14" />
            <path d="M8 6v8" />
        </svg>
        ประกาศ/นโยบาย
    </a>
</div>