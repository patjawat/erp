<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
    <a href="<?= Url::to(['/hr/default/index']) ?>" class="btn <?= $active !== 'dashboard' ? 'btn-outline-primary' : 'btn-primary' ?>">
<i data-lucide="layout-grid"></i>  
        ภาพรวม
    </a>


    
</div>