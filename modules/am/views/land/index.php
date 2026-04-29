<?php

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'ที่ดิน';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.106 5.553a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619v12.764a1 1 0 0 1-.553.894l-4.553 2.277a2 2 0 0 1-1.788 0l-4.212-2.106a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0z"></path>
            <path d="M15 5.764v15"></path>
            <path d="M9 3.236v15"></path>
        </svg>
        ทะเบียน<?= $this->title ?>
    </h4>
</div>

<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'land']) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white d-flex justify-content-between">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>
<div class="card">
    <div class="card-header bg-primary-gradient">
        <div class="d-flex justify-content-between">
            <h6 class="text-white">
                <i class="bi bi-ui-checks"></i> ทะเบียน<?= $this->title ?>
                <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?> </span> รายการ
            </h6>
            <?= Html::a('<i data-lucide="circle-plus"></i> สร้างใหม่', ['create'], ['class' => 'btn btn-light']) ?>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="text-center py-2 text-dark" style="width: 50px;">#</th>
                     <th class="text-center" style="width:80px;">รูปภาพ</th>
                    <th class="py-2 text-dark">รหัสพัสดุ / เลขโฉนด</th>
                    <th class="py-2 text-dark">ที่ตั้ง</th>
                    <th class="py-2 text-dark">เนื้อที่</th>
                    <th class="py-2 text-dark">การได้มา</th>
                    <th class="py-2 text-end text-dark">มูลค่า</th>
                    <th class="text-center" style="width: 130px;">สถานะ</th>
                    <th class="text-center" style="width:200px;">จัดการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-middle">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                         <td style="width:70px;">
                            <?= Html::a(
                                Html::img(
                                    $item->showImg()['image'],
                                    [
                                        'class' => 'rounded mx-auto d-block text-white lazyautosizes ls-is-cached lazyloaded',
                                        'style' => 'max-width:60px; max-height:60px; object-fit:cover;',
                                        'alt' => $item->asset_name
                                    ]
                                ),
                                ['view', 'id' => $item->id],
                                ['class' => '']
                            ) ?>
                        </td>
                        <td class="py-2">
                            <div class="fw-semibold text-dark"><?= $item->code ?></div>
                            <div class="text-muted small">โฉนด: <?= $item->data_json['lan_number'] ?? 'ไม่ระบุ' ?></div>
                        </td>
                        <td class="align-middle"><?= $item->data_json['address'] ?? '-' ?></td>
                        <td class="align-middle"><?= $item->landSize() ?></td>
                        <td class="align-middle"><?= $item->purchaseName->title ?? '-' ?></td>
                        <td class="align-middle text-end">
                            <span class="fw-semibold">
                                <?= number_format($item->price ?? 0, 2) ?>
                        </td>
                        </span>
                        <td class="align-middle text-center">
                            <?= $item->viewStatus() ?>
                        </td>

                        <td class="text-center align-middle equip-actions-cell px-2 px-md-3">
                            <div class="equip-actions-inner d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
                                <?= Html::a('<i class="fa-regular fa-eye"></i>', ['view', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-primary',
                                    'title' => 'ดูรายละเอียด',
                                    'data-pjax' => 0,
                                ]) ?>
                                <?php if (Yii::$app->user->can('asset')): ?>
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-warning',
                                        'title' => 'แก้ไข',
                                        'data-pjax' => 0,
                                    ]) ?>
                                <?php endif; ?>
                                  <?= Html::a('<i class="bi bi-qr-code-scan"></i>', ['/am/asset/view-qr-pdf', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-light',
                                    'title' => 'พิมพ์',
                                    'data-pjax' => 0,
                                    'target' => '_blank',
                                ]) ?>
                                <?php if (Yii::$app->user->can('admin')): ?>
                                    <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['delete', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-danger delete-asset',
                                        'title' => 'ลบ',
                                        'data-pjax' => 0,
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                        </td>

                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>