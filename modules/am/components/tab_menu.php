<?php
use yii\helpers\Url;
?>
<div class="bg-light p-2 rounded-3 d-flex gap-2">
    <a href="<?= Url::to(['/am/land']) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $tabs === 'land'  ? 'btn-primary' : 'bg-body' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.106 5.553a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619v12.764a1 1 0 0 1-.553.894l-4.553 2.277a2 2 0 0 1-1.788 0l-4.212-2.106a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0z"></path>
            <path d="M15 5.764v15"></path>
            <path d="M9 3.236v15"></path>
        </svg>
        ที่ดิน
    </a>
     <a href="<?= Url::to(['/am/building']) ?>" class="btn fw-medium d-flex align-items-center gap-2 px-3 border-0 rounded-3 tab-btn <?= $tabs === 'building'  ? 'btn-primary' : 'bg-body' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 12h4"></path>
            <path d="M10 8h4"></path>
            <path d="M14 21v-3a2 2 0 0 0-4 0v3"></path>
            <path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path>
            <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path>
        </svg>
        อาคาร/สิ่งปลูกสร้าง
</a>

    <a href="<?= Url::to(['/am/asset']) ?>" class="btn shadow-sm d-flex align-items-center gap-2 px-3 border-0 rounded-3 <?= $tabs === 'asset' ? 'btn-primary' : 'bg-body'?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="20" height="14" x="2" y="3" rx="2"></rect>
            <line x1="8" x2="16" y1="21" y2="21"></line>
            <line x1="12" x2="12" y1="17" y2="21"></line>
        </svg>
        ครุภัณฑ์
</a>
</div>