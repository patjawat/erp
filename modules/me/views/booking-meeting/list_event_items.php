<?php
use yii\helpers\Url;
use yii\widgets\Pjax;
?>
<?php Pjax::begin(['id' => 'listEvent', 'enablePushState' => false, 'timeout' => 500000]); ?>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white">
                <i class="fa-solid fa-calendar-days"></i> ปฏิทินทั้งหมด
            </h6>
        </div>
    </div>
    <div class="card-body">
        <table
            class="table table-striped">
            <thead>
                <tr>
                    <th>วัน/เวลา</th>
                    <th>กิจกรรม</th>
                    <th class="text-end">สถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $item): ?>
                    <tr>
                        <td>
                        <p class="mb-0"><?=$item->viewMeetingDate()?></p>    
                        <p class="mb-0 fs-11"><?= $item->viewTime()['full'] ?? '-' ?></p>
                    </td>
                        <td>
                            <a href="<?=Url::to(['view','id' => $item->id])?>" class="open-modal" data-size="modal-lg">
                                <p class="mb-0 fw-semibold"><?= $item->room->title ?></p>
                                <p class="mb-0 fs-12"><?= $item->title ?></p>
                            </a>
                        </td>
                        <td class="text-end fs-12"><?=$item->viewStatus()['title']?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'maxButtonCount' => 3, // ✅ ใส่จำนวนหน้าที่จะแสดงทีละ 5 หน้า
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination pagination-sm', // ✅ ปรับตรงนี้แทน listOptions
                ],
            ]); ?>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>