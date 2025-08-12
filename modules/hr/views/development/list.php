<?php

use yii\helpers\Url;
use yii\helpers\Html;
?>

<table class="table">
    <thead>
        <tr>
            <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
            <th>เรื่อง</th>
            <th>ประเภท</th>
            <th style="width: 200px;">วันที่</th>
            <th class="fw-semibold" scope="col">ผู้ขอ</th>
            <th class="fw-semibold" scope="col">คณะเดินทาง</th>
            <th class="fw-semibold text-center" scope="col">สถานะ</th>
            <th class="fw-semibold text-end">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($dataProvider->getModels() as $key => $item): ?>
            <tr>
                <td class="text-center fw-semibold">
                    <?php echo (($dataProvider->pagination->offset + 1) + $key) ?>
                </td>
                <td>
                    <p class="mb-0"><?= $item->topic ?></p>
                    <p class="mb-0">สถานที่ <span class="fw-semibold"><?= $item->data_json['location'] ?? 'ไม่ระบุ' ?><span></p>
                </td>
                <td>
                    <?= $item->developmentType?->title ?? '-' ?>
                </td>
                <td>
                    <p class="mb-0 fw-semibold"> <?= $item->showDateRange() ?></p>

                </td>
                <td>
                    <?php 
                    
                    try {
                        echo $item->userRequest()['avatar'] ?? '';
                    } catch (\Throwable $th) {
                        //throw $th;
                    }?>
                </td>
                <td>
                    <?= $item->StackMember() ?></td>
                <td class="text-center">

                    <?= $item->getStatus($item->status)['view'] ?? '-' ?></td>
                <td class="text-end">

                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            จัดการ
                        </button>
                        <ul class="dropdown-menu">
                            <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= Html::a('<i class="fa-solid fa-eye me-1"></i> แสดงรายละเอียด', ['view', 'id' => $item->id], ['class' => 'dropdown-item']) ?>
                            </li>
                            <li><?= $item->development_type_id == 'dev3' ? Html::a('<i class="fa-solid fa-user-check me-1"></i> ตอบรับเป็นวิทยากร', ['/me/development/response-dev', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-check"></i> การตอบรับเป็นวิทยากร'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) : '' ?>
                            </li>
                            <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบขอไปราชการ', ['/me/development/form-official', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                            </li>
                            <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบขออนุญาต', ['/me/development/permit-request', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                            </li>
                            <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบตอบรับเป็นวิทยากร', ['/me/development/form-academic', 'id' => $item->id], ['class' => 'dropdown-item  open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                            </li>
                            <li><?= Html::a('<i class="fa-solid fa-triangle-exclamation me-1"></i> แจ้งยกเลิก', ['view', 'id' => $item->id], ['class' => 'dropdown-item']) ?>
                            </li>
                            </ui>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
    <?= yii\bootstrap5\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'firstPageLabel' => 'หน้าแรก',
        'lastPageLabel' => 'หน้าสุดท้าย',
        'options' => [
            'listOptions' => 'pagination pagination-sm',
            'class' => 'pagination-sm',
        ],
    ]); ?>
</div>