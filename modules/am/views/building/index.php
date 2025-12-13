<?php
use yii\helpers\Html;

$this->title = 'อาคาร';
$this->params['breadcrumbs'][] = ['label' => 'ทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package text-primary">
            <path d="m7.5 4.27 9 5.15"></path>
            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
            <path d="m3.3 7 8.7 5 8.7-5"></path>
            <path d="M12 22V12"></path>
        </svg> ระบบบริหารทรัพย์สิน</h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'asset'])?>
<?php $this->endBlock(); ?>


<?= $this->render('@app/modules/am/views/default/car_summary_price') ?>
<?= $this->render('@app/modules/am/views/asset/_list', [
    'tabs' => $tabs,
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
]) ?>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white">
                <i class="bi bi-ui-checks"></i> ทะเบียน<?= $this->title ?>
                <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?> </span> รายการ
            </h6>
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/am/land/create'], ['class' => 'btn btn-light shadow']) ?>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col" style="text-align: center;width:100px">ลำดับ</th>
                    <th scope="col" style="width:70px;">รหัส</th>
                    <th scope="col" style="width:200px;">เลขที่โฉนด</th>
                    <th scope="col">ที่ตั้ง</th>
                    <th scope="col">เนื้อที่</th>
                    <th class="fw-semibold text-center" scope="col" style="width: 100px;">จัดการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-middle">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                        <td class="fw-semibold text-primary"><?= $item->code ?></td>
                        <td class="align-middle"><?= $item->data_json['lan_number'] ?? '-' ?></td>
                        <td class="align-middle"><?=$item->landSize()?></td>
                        <td class="align-middle"></td>

                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    จัดการ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><?= Html::a('<i class="fa-solid fa-eye me-1"></i>แสดง', ['view', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                                    <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['update', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                                </ul>
                            </div>
                        </td>


                        <!-- <td class="align-middle text-center">
                            <div class="d-flex gap-3">
                                <?= Html::a('<i class="fa-solid fa-eye fa-2x"></i>', ['view', 'id' => $item->id]) ?>
                                <?= Html::a('<i class="fa-solid fa-pen-to-square fa fa-2x text-warning"></i>', ['update', 'id' => $item->id]) ?>
                                <?= Html::a('<i class="fa-solid fa-trash fa-2x text-danger"></i>', ['delete', 'id' => $item->id], ['class' => 'delete-asset']) ?>
                            </div>
                        </td> -->
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>

