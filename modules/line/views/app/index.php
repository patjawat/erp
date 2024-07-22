<?php
/** @var yii\web\View $this */
use yii\helpers\Url;
?>
<div class="row">
    <div class="col-6">
        <a href="<?= Url::to(['/helpdesk/default/repair-select', 'title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม']); ?>"
            class="open-modal shadow" data-title="xxx">
            <div class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                <i class="fa-solid fa-triangle-exclamation fs-3"></i>
                <div>แจ้งซ่อม</div>
            </div>
        </a>
    </div>
    
    <div class="col-6">
        <a href="<?= Url::to(['/helpdesk/default/repair-select', 'title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม']); ?>"
            class="open-modal" data-title="xxx">
            <div class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                <i class="fa-solid fa-triangle-exclamation fs-3"></i>
                <div>ความเสี่ยง</div>
            </div>
        </a>
    </div>

</div>