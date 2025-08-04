<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
?>

<?php  Pjax::begin(['id' => $container,'enablePushState' => false,'timeout' => 50000 ]); ?>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <p class="text-white mb-0">
                <i class="fa-solid fa-calendar-days"></i> <?= $title ?? '-' ?>
</p>
<p class="text-white mb-0"><?=$showDate?></p>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">รายการ/สถานที่</th>
                    <th scope="col">พขร.</th>
                    <th scope="col">รถที่จัดสรรค์</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $item): ?>
                <tr>
                    <td>
                        <a href="<?= Url::to(['approve', 'id' => $item->vehicle->id,'title' => 'เลขที่#'.$item->vehicle->code]) ?>" class="open-modal" data-size="modal-lg">
                        <p class="mb-0 fw-semibold"> <span class="text-danger"><?= ($item->vehicle->viewTime()['full'] ?? '-'); ?></span> <?php echo $item->vehicle->locationOrg?->title ?? '-' ?></p>
                        <p class="mb-0 fs-11"><?php echo $item->vehicle->reason ?? '-' ?></p>
                    </a>
                    </td>
                    <td>
                <?=Html::img('@web/img/placeholder-img.jpg', [
                    'class' => 'avatar-sm rounded-circle shadow lazyload blur-up',
                    'data' => [
                        'expand' => '-20',
                        'sizes' => 'auto',
                        'src' => $item->showDriver()['photo']
                    ]
                    ]);?></td>
                    <td class="">
                        <div class="d-flex">
                            <?php
                                try {
                                    echo $item->car ? Html::img($item->car?->ShowImg()['image'], ['class' => 'avatar rounded border-secondary']) : '';
                                } catch (\Throwable $th) {
                                    //throw $th;
                                }
                                ?>
                            <div class="avatar-detail">
                                <div class="d-flex flex-column">
                                    <p class="mb-0"><?= $item->car?->data_json['brand']; ?></p>
                                    <p class="mb-0 fw-semibold text-primary"><?= $item->license_plate ?></p>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
    <div class="body-footer">
        <div class="d-flex justify-content-center">
            <?= yii\bootstrap5\LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                     'maxButtonCount' => 5, // ✅ แสดงแค่ 5 หน้าพร้อมกัน
                    'options' => [
                        'class' => 'pagination pagination-sm',
                    ],
                ]); ?>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>