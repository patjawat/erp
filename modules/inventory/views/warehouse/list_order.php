<?php

use yii\helpers\Html;
use yii\bootstrap5\LinkPager;
// คำนวณค่าเริ่มต้นของลำดับที
?>
<table class="table table-striped table-sm">
    <thead>
        <tr>
            <th class="fw-semibold text-center" style="width:30px">ลำดับ</th>
            <th style="width:210px">รหัส/วันที่ขอ</th>
            <th scope="col">ประเถท</th>
            <th scope="col">ผู้เบิก</th>
            <th>หัวหน้าตรวจสอบ</th>
            <th class="fw-semibold text-end">มูลค่า</th>
            <th class="fw-semibold text-center" style="width:160px">วันที่จ่าย</th>
            <th class="fw-semibold text-center" style="width:160px">สถานะ</th>
            <th style="width:100px">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <?php foreach ($dataProvider->getModels() as $key => $item): ?>
            <tr>
                <td class="text-center">
                    <?php
                    if ($dataProvider->pagination !== false) {
                        echo (($dataProvider->pagination->offset + 1) + $key);
                    } else {
                        echo ($key + 1);
                    }
                    ?>
                </td>
                <td>
                    <p class="fw-semibold mb-0"><?= $item->code ?></p>
                    <p class="text-muted mb-0 fs-13"><?= $item->viewCreatedAt() ?></p>
                </td>
                <td><?= $item->assetType?->title ?? '-' ?></td>
                <td>
                    <?php
                    try {
                        echo $item->UserReq($item->fromWarehouse->warehouse_name . ' | ' . $item->viewCreated())['avatar'];
                    } catch (\Throwable $th) {
                        echo '';
                    }
                    ?>
                </td>
                <td><?= $item->viewChecker()['avatar'] ?></td>
                <td class="text-end">
                    <span class="fw-semibold">
                        <?php echo $item->order_status == 'success' ? number_format($item->getTotalOrderPriceSuccess(), 2) : number_format($item->getTotalOrderPrice(), 2) ?>
                    </span>
                </td>
                <td class="text-center"><?= $item->viewMoveMentDate() ?></td>
                <td class="text-center"><?= $item->viewstatus() ?></td>
                <td>

                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                            id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            จัดการ
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><?= Html::a('<i class="fa-regular fa-file-lines me-1"></i> แสดง', ['/inventory/stock-order/view', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                            <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์เอกสาร', ['/inventory/document/stock-order', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data-pjax' => '0', 'data' => ['size' => 'modal-xl']]) ?></li>
                            <?php if (!in_array($item->order_status, ['success', 'cancel'])): ?>
                                <!-- ถ้ายังไม่ถูกยกเลิกหรทอถูกนำเข้าคลัง -->
                                <li>
                                    <?= Html::a(
                                        '<i class="fa-solid fa-xmark me-1"></i> ยกเลิก',
                                        [
                                            '/inventory/stock-order/cancel-order',
                                            'id' => $item->id,
                                            'title' => '<i class="fa-solid fa-triangle-exclamation"></i> ต้องการยกเลิกกรุณาบอกเหตุผล'
                                        ],
                                        [
                                            'class' => 'dropdown-item open-modal',
                                            'data-pjax' => '0',
                                            'data' => ['size' => 'modal-md']
                                        ]
                                    ) ?>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="d-flex justify-content-center mt-5">
    <div class="text-muted">
        <?= ($dataProvider->pagination !== false) ?  LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'firstPageLabel' => 'หน้าแรก',
            'lastPageLabel' => 'หน้าสุดท้าย',
            'options' => [
                'listOptions' => 'pagination pagination-sm',
                'class' => 'pagination-sm',
            ],
        ]) : ''; ?>
    </div>
</div>