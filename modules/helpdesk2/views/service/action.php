<?php
use yii\helpers\Html;

$viewV2Params = ['/helpdesk/service/view-v2', 'id' => $item->id];
$back = \Yii::$app->request->url;
if (is_string($back) && $back !== '' && $back[0] === '/' && strpos($back, '://') === false && substr($back, 0, 2) !== '//' && preg_match('#^/helpdesk/#', $back)) {
    $viewV2Params['returnUrl'] = $back;
}
?>
<div class="d-flex flex-wrap gap-2 justify-content-end">
    <?= Html::a('<i class="bi bi-eye me-1"></i> บันทึกงานซ่อม', $viewV2Params, ['class' => 'btn btn-sm btn-outline-primary']) ?>
    <?= Html::a('<i class="fa-solid fa-file-pdf me-1"></i> พิมพ์ใบส่งซ่อม', ['/helpdesk/service/print-send-repair-pdf', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank']) ?>
    <?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['/helpdesk/service/update', 'id' => $item->id, 'title' => '<i class="bi bi-pencil me-2"></i>แก้ไข'], ['class' => 'btn btn-sm btn-outline-info open-modal', 'data' => ['size' => 'modal-lg']]) ?>
    <?= Html::a('<i class="fa-solid fa-ban me-1"></i> ยกเลิก', ['/helpdesk/service/cancel', 'id' => $item->id, 'title' => '<i class="bi bi-pencil me-2"></i>แก้ไข'], ['class' => 'btn btn-sm btn-outline-warning cancel-order']) ?>
    <?php //  Html::a('<i class="fa-solid fa-trash me-1"></i> ลบ', ['/helpdesk/service/delete', 'id' => $item->id, 'title' => '<i class="bi bi-pencil me-2"></i>แก้ไข'], ['class' => 'btn btn-sm btn-outline-danger delete-repair-item']) ?>
</div>