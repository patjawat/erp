<?php

use app\modules\helpdesk2\models\Helpdesk;

$repairHistorys = Helpdesk::find()->where(['asset_number' => $model->code])->all();
?>
<table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col" class="text-start fw-semibold">รหัสงานซ่อม</th>
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
                       <td class="text-start fw-semibold"><?php echo $item->repair_number?></td>
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