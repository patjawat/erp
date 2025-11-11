<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
$this->title = 'อนุมัติอบรม/ประชุม/ดูงาน';
$msg = 'ขอ';
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/me/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu', ['active' => 'approve']) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white"><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title ?> <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?> </span> รายการ</h6>
            <?php echo Html::a('อนุมัติทั้งหมด', ['/approve/development/approve-all'], ['class' => 'btn btn-light shadow approve-all']); ?>
        </div>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between">
        </div>
        <div class="d-flex justify-content-between  align-top align-items-center">
            <div class="d-flex flex-column">
                <div class="d-flex justify-content-between">
                </div>
            </div>
        </div>

        <div class="table-responsive pb-5">
            <table class="table">
                <thead>
                    <tr>
                        <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                        <th class="fw-semibold">เรื่อง</th>
                        <th class="fw-semibold">ประเภท</th>
                        <th class="fw-semibold">วันที่</th>
                        <th class="fw-semibold" scope="col">ผู้ขอ</th>
                        <th class="fw-semibold" scope="col">คณะเดินทาง</th>
                        <th class="fw-semibold" scope="col">สถานะ</th>
                        <th class="fw-semibold text-center">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider align-middle" id="list-data">
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                        <tr data-id="<?=$item->id?>">
                            <td class="text-center fw-semibold">
                                <?php echo (($dataProvider->pagination->offset + 1) + $key) ?>
                            </td>
                            <td>
                                <p class="mb-0"><?= $item->development->topic ?></p>
                                <p class="mb-0">สถานที่ <span class="fw-semibold"><?= $item->development->data_json['location'] ?? 'ไม่ระบุ' ?><span></p>
                            </td>
                            <td><?= $item->developmentType?->title ?? '-' ?></td>
                            <td>
                                <p class="mb-0 fw-semibold"> <?= $item->development->showDateRange() ?></p>
                            </td>
                            <td>
                                <?php

                                try {
                                    echo $item->development->userRequest()['avatar'] ?? '';
                                } catch (\Throwable $th) {
                                    //throw $th;
                                } ?>
                            </td>

                            <td> <?= $item->development->StackMember() ?></td>
                            <td>
                                <?=$item->development->status;?>
                            <?= $item->development->getStatus($item->development->status)['view'] ?? '-' ?>    
                            </td>

                            <td class="text-center" style="width:120px">
                                <div class="btn-group">
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-light w-100 open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
</div>

<?php
$js = <<< JS


JS;
$this->registerJS($js, View::POS_END);
?>