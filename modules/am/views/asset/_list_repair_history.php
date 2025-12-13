<?php

use app\modules\helpdesk2\models\Helpdesk;

$repairHistorys = Helpdesk::find()->where(['asset_number' => $model->code])->all();
?>


<table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-secondary small">
                                <tr>
                                    <th class="px-4 py-3 fw-medium">วันที่แจ้ง</th>
                                    <th class="px-4 py-3 fw-medium">รายการ / อาการ</th>
                                    <th class="px-4 py-3 fw-medium">ผู้ดำเนินการ</th>
                                    <th class="px-4 py-3 fw-medium text-end">ค่าใช้จ่าย</th>
                                    <th class="px-4 py-3 fw-medium text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <tr>
                                    <td class="px-4 py-3 text-dark fw-medium">2566-12-10</td>
                                    <td class="px-4 py-3">
                                        <div class="fw-medium text-dark">เปลี่ยนแบตเตอรี่</div>
                                        <div class="text-muted small">เปลี่ยนแบตเตอรี่เนื่องจากเสื่อมสภาพ</div>
                                    </td>
                                    <td class="px-4 py-3 text-secondary">ร้านอมร อิเล็คโทรนิคส์</td>
                                    <td class="px-4 py-3 text-end fw-medium">฿450.00</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-medium d-inline-flex align-items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="M22 4 12 14.01l-3-3"></path></svg>
                                            Completed
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>


<table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col" class="text-start">รหัสงานซ่อม</th>
                        <th scope="col">อุปกรณ์</th>
                        <th scope="col">ปัญหา</th>
                        <th scope="col">สถานที่</th>
                        <th scope="col">ผู้แจ้ง</th>
                        <th scope="col">วันที่แจ้ง</th>
                        <th scope="col">ความเร่งด่วน</th>
                        <th scope="col">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                     <?php foreach ($repairHistorys as $key => $item): ?>
                    <tr>
                       <td class="text-start"><?php echo $item->repair_number?></td>
                        <td><?=$item->deviceType->title ?? '-'?></td>
                        <td><?=$item->title?></td>
                        <td><?=$item->data_json['location']?></td>
                        <td><?=$item->emp->getInfo()['avatar']?></td>
                        <td><?=$item->viewCreateDateTime()?></td>
                        <td><?=$item->viewUrgent()['view']?></td>
                        <td><?=$item->repairStatus?->title ?? '-'?></td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>