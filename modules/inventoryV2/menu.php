<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2 flex-wrap">
    <a href="<?= Url::to(['/inventory-v2/default/navigation']) ?>" class="btn <?= $active !== 'navigation' ? 'btn-outline-info' : 'btn-info' ?>">
        <i data-lucide="map"></i>  
        เมนูนำทาง
    </a>
    <a href="<?= Url::to(['/inventory-v2/warehouse/index']) ?>" class="btn <?= $active !== 'dashboard' ? 'btn-outline-primary' : 'btn-primary' ?>">
<i data-lucide="layout-grid"></i>  
        ภาพรวม
    </a>

    <!-- <a href="<?= Url::to(['/inventory/stock/in-stock']) ?>" class="btn <?= $active !== 'stock' ? 'btn-outline-primary' : 'btn-primary' ?>">
      <i data-lucide="blocks"></i>  
        สตอ๊ก(ตัวเดิม)
    </a> -->
     <a href="<?= Url::to(['/inventory/stock/stock-card']) ?>" class="btn <?= $active !== 'stock' ? 'btn-outline-primary' : 'btn-primary' ?>">
      <i data-lucide="blocks"></i>  
        สตอ๊ก
    </a>


    <a href="<?= Url::to(['/inventory/stock-in']) ?>" class="btn <?= $active !== 'stock-in' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-plus-corner-icon lucide-file-plus-corner">
            <path d="M11.35 22H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.706.706l3.588 3.588A2.4 2.4 0 0 1 20 8v5.35" />
            <path d="M14 2v5a1 1 0 0 0 1 1h5" />
            <path d="M14 19h6" />
            <path d="M17 16v6" />
        </svg>
        ทะเบียนรับเข้า
    </a>

    <a href="<?= Url::to(['/inventory-v2/requisition/index']) ?>" class="btn <?= $active !== 'list' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-minus-icon lucide-file-minus">
            <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z" />
            <path d="M14 2v5a1 1 0 0 0 1 1h5" />
            <path d="M9 15h6" />
        </svg>
        ทะเบียนขอเบิกวัสดุ
    </a>
    <a href="<?= Url::to(['/inventory-v2/issue/index']) ?>" class="btn <?= $active !== 'issue' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i class="bi bi-box-seam"></i>
        รายการจ่ายพัสดุ
    </a>
    <a href="<?= Url::to(['/inventory-v2/stock-adjust/index']) ?>" class="btn <?= ($active ?? '') !== 'stock-adjust' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i class="bi bi-wrench-adjustable"></i>
        ปรับยอด stock
    </a>
    <a href="<?= Url::to(['/inventory-v2/report/material-summary']) ?>" class="btn <?= ($active ?? '') !== 'report-material' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i class="bi bi-file-earmark-bar-graph"></i>
        สรุปรายงานวัสดุคงคลัง
    </a>
    <a href="<?= Url::to(['/inventory-v2/report/procurement-plan']) ?>" class="btn <?= ($active ?? '') !== 'report-procurement-plan' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i class="bi bi-clipboard2-data"></i>
        การใช้งานวัสดุรายตัว
    </a>
    <a href="<?= Url::to(['/inventory-v2/report/balance-by-warehouse']) ?>" class="btn <?= ($active ?? '') !== 'report-balance' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i class="bi bi-boxes"></i>
        ยอดคงเหลือตามคลัง
    </a>
    <a href="<?= Url::to(['/inventory-v2/default/setting']) ?>" class="btn <?= $active !== 'setting' ? 'btn-outline-secondary' : 'btn-secondary' ?>">
        <i class="bi bi-building"></i>
        ตั้งค่าคลังสินค้า
    </a>

</div>
