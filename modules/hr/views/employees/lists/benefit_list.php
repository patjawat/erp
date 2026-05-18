<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
$this->title = "ข้อมูลสวัสดิการ";
?>
<?php Pjax::begin(['id' => 'award']);?>
<div class="card border-0">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fa-solid fa-heart-circle-plus text-primary"></i>
                <?= $this->title ?>
            </h5>

            <?= Html::a(
                '<i class="fa-solid fa-circle-plus"></i> เพิ่มสวัสดิการ',
                [
                    '/hr/employee-detail/create',
                    'emp_id' => $model->id,
                    'name' => 'benefit',
                    'title' => '<i class="fa-solid fa-heart-circle-plus"></i> ' . $this->title
                ],
                [
                    'class' => 'btn btn-primary rounded-pill shadow open-modal',
                    'data' => ['size' => 'modal-lg']
                ]
            ) ?>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mt-3" style="margin-bottom: 100px;">
                <thead>
                    <tr>
                        <th style="width: 32px;">#</th>
                        <th style="width: 130px;">วันที่ได้รับ</th>
                        <th>ประเภทสวัสดิการ</th>
                        <th>รายละเอียด</th>
                        <th class="text-end" style="width: 120px;">จำนวนเงิน</th>
                        <th class="text-center" style="width: 120px;">สถานะ</th>
                        <th class="text-center" style="width: 100px;">ดำเนินการ</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($model->benefits as $key => $item): ?>

                        <?php
                        $data = $item->data_json ?? [];

                        $benefitType = $data['benefit_type'] ?? '-';
                        $benefitTitle = $data['benefit_title'] ?? $benefitType;

                        $receiveDate = $data['receive_date'] ?? ($data['date_start'] ?? '-');
                        if ($receiveDate !== '-' && is_string($receiveDate) && preg_match('/^\d{4}-\d{2}-\d{2}/', $receiveDate)) {
                            $receiveDate = AppHelper::convertToThai($receiveDate) ?: $receiveDate;
                        }
                        $amount = $data['amount'] ?? 0;
                        $status = $data['status'] ?? '-';
                        $detail = $data['detail'] ?? '-';

                        if ($benefitType === 'house') {
                            $houseName = $data['house_name'] ?? '';
                            $houseNo = $data['house_no'] ?? '';
                            $houseStatus = $data['house_status'] ?? '';

                            $detail = trim($houseName . ' ' . $houseNo);

                            if ($houseStatus) {
                                $detail .= ' (' . $houseStatus . ')';
                            }
                        }

                        $statusText = [
                            'active' => 'ใช้งาน',
                            'expired' => 'สิ้นสุด',
                            'cancel' => 'ยกเลิก',
                            'stay' => 'กำลังพัก',
                            'move_out' => 'ย้ายออก',
                        ][$status] ?? $status;

                        $statusClass = [
                            'active' => 'success',
                            'expired' => 'secondary',
                            'cancel' => 'danger',
                            'stay' => 'primary',
                            'move_out' => 'warning',
                        ][$status] ?? 'secondary';
                        ?>

                        <tr>
                            <td><?= $key + 1 ?></td>

                            <td>
                                <?= $receiveDate ?>
                            </td>

                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">
                                    <?= $benefitTitle ?>
                                </span>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    <?= $detail ?: '-' ?>
                                </div>

                                <?php if (!empty($data['doc_ref'])): ?>
                                    <div class="small text-muted">
                                        เอกสารอ้างอิง: <?= $data['doc_ref'] ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($data['comment'])): ?>
                                    <div class="small text-muted">
                                        หมายเหตุ: <?= $data['comment'] ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="text-end">
                                <?= $amount > 0 ? number_format($amount, 2) : '-' ?>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-<?= $statusClass ?> rounded-pill">
                                    <?= $statusText ?>
                                </span>
                            </td>

                            <td class="text-center align-middle">
                                <div class="dropdown">
                                    <button type="button"
                                        class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>

                                    <div class="dropdown-menu">
                                        <?= Html::a(
                                            '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข',
                                            [
                                                '/hr/employee-detail/update',
                                                'id' => $item->id,
                                                'title' => '<i class="fa-solid fa-heart-circle-plus"></i> ' . $this->title
                                            ],
                                            [
                                                'class' => 'dropdown-item open-modal',
                                                'data' => ['size' => 'modal-lg']
                                            ]
                                        ) ?>

                                        <?= Html::a(
                                            '<i class="fa-solid fa-trash me-1"></i> ลบ',
                                            [
                                                '/hr/employee-detail/delete',
                                                'id' => $item->id
                                            ],
                                            [
                                                'class' => 'dropdown-item delete-item text-danger',
                                            ]
                                        ) ?>
                                    </div>
                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
<?php Pjax::end();?>
