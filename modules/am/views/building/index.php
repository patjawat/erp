<?php
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'อาคาร/สิ่งปลูกสร้าง';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 12h4"></path>
            <path d="M10 8h4"></path>
            <path d="M14 21v-3a2 2 0 0 0-4 0v3"></path>
            <path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path>
            <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path>
        </svg>
        ทะเบียน<?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'building']) ?>
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
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white">
                <i class="bi bi-ui-checks"></i> ทะเบียน<?= $this->title ?>
                <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?> </span> รายการ
            </h6>
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create'], ['class' => 'btn btn-light shadow']) ?>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="text-center py-2 text-dark" style="width: 50px;">#</th>
                      <th class="text-center" style="width:80px;">รูปภาพ</th>
                    <th class="py-2 text-dark">รหัสพัสดุ / ชื่ออาคาร</th>
                    <th class="py-2 text-dark">ประเภท</th>
                    <th class="py-2 text-dark">ที่ตั้ง / ปีสร้าง</th>
                    <th class="py-2 text-end text-dark">พื้นที่ (ตร.ม.)</th>
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
                         <td class="align-middle">
                            <div class="fw-semibold text-dark"><?= $item->code ?></div>
                            <div class="text-muted small"><?= $item->asset_name ?></div>
                        </td>
                        <td class="align-middle"><?= $item->data_json['building_type_name'] ?? '-' ?></td>
                        <td class="align-middle">
                            <div class="fw-semibold text-dark"><?= $item->on_year ?></div>
                            <div class="text-muted small"><?= $item->data_json['location'] ?? 'ไม่ระบุ' ?></div>
                        </td>
                        <td class="align-middle">
                            <?= $item->data_json['area'] ?? 'ไม่ระบุ' ?>
                        </td>
                        <td class="align-middle text-end">
                            <span class="fw-semibold">
                                <?= number_format($item->price ?? 0, 2) ?>
                        </td>
                        </span>
                        <td class="align-middle text-center">
                            <?= $item->viewStatus() ?>
                        </td>

                        <td class="text-center py-2">
                            <div class="d-flex justify-content-center">
                                <a href="<?= Url::to(['view', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary" title="ดูรายละเอียด">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
                                <a href="<?= Url::to(['update', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary" title="ดูรายละเอียด">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path>
                                    </svg>
                                </a>
                                <a href="<?= Url::to(['qrcode', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary" title="ดูรายละเอียด">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="5" height="5" x="3" y="3" rx="1"></rect>
                                        <rect width="5" height="5" x="16" y="3" rx="1"></rect>
                                        <rect width="5" height="5" x="3" y="16" rx="1"></rect>
                                        <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
                                        <path d="M21 21v.01"></path>
                                        <path d="M12 7v3a2 2 0 0 1-2 2H7"></path>
                                        <path d="M3 12h.01"></path>
                                        <path d="M12 3h.01"></path>
                                        <path d="M12 16v.01"></path>
                                        <path d="M16 12h1"></path>
                                        <path d="M21 12v.01"></path>
                                        <path d="M12 21v-1"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>


                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>