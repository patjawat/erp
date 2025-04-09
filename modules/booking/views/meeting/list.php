<?php
use yii\helpers\Html;
?>
<div class="meeting-table table-responsive">
                <table class="table table-borderless mb-0">
                    <thead>
                        <tr>
                            <th>ผู้จอง</th>
                            <th>ห้องประชุม</th>
                            <th>วันที่</th>
                            <th>เวลา</th>
                            <th>สถานะ</th>
                            <th class="text-end">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row 1 -->
                        <?php foreach($dataProvider->getModels() as $key => $item):?>
                        <tr>
                            <td>
                                <?=$item->getUserReq()['avatar']?>
                            </td>
                            <td><?=$item->room->title?></td>
                            <td><?=$item->viewMeetingDate()?></td>
                            <td><?=$item->viewMeetingTime()?></td>
                            <td><?=$item->viewStatus()['view']?></td>
                            <td class="text-end">

                                <?=Html::a('<i class="fa-solid fa-eye fa-2x"></i>',[$url.'view','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-md']])?>
                                <?php if($item->status == 'Pending'):?>
                                <div class="action-icon approve d-inline-flex confirm-meeting" data-id="<?=$item->id?>"
                                    data-status="Pass" data-text="อนุมัติการจอง">
                                    <i class="fa-solid fa-circle-check fa-2x"></i>
                                </div>
                                <?php endif;?>
                                <?php if($item->status == 'Pending'):?>
                                <div class="action-icon reject d-inline-flex confirm-meeting" data-id="<?=$item->id?>"
                                    data-status="Cancel" data-text="ยกเลิกการจอง">
                                    <i class="fa-solid fa-circle-xmark fa-2x"></i>
                                </div>
                                <?php endif;?>
                            </td>
                        </tr>
                        <?php endforeach;?>
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

            </div>