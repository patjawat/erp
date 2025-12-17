<?php
use yii\bootstrap5\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'ทรัพย์สิน';
$search = Yii::$app->request->get('search');
$currentType = $currentType ?? 'equipment';
?>

<div class="mx-n3 fade-in">
    
    <div class="px-3 pt-3">
        
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card border border-light-subtle shadow-sm h-100" style="border-radius: 12px; border-color: #e5e7eb !important;">
                    <div class="card-body px-3 py-2 d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 44px; height: 44px; background-color: #f0fdf4; color: #16a34a;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.106 5.553a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619v12.764a1 1 0 0 1-.553.894l-4.553 2.277a2 2 0 0 1-1.788 0l-4.212-2.106a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0z"/><path d="M15 5.764v15"/><path d="M9 3.236v15"/></svg>
                        </div>
                        <div style="line-height: 1.2;">
                            <p class="text-secondary mb-0" style="font-size: 12px;">มูลค่าที่ดินรวม</p>
                            <h5 class="fw-bold text-dark mb-0" style="font-size: 18px;">฿7.5M</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border border-light-subtle shadow-sm h-100" style="border-radius: 12px; border-color: #e5e7eb !important;">
                    <div class="card-body px-3 py-2 d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 44px; height: 44px; background-color: #eff6ff; color: #2563eb;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12h4"/><path d="M10 8h4"/><path d="M14 21v-3a2 2 0 0 0-4 0v3"/><path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"/><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/></svg>
                        </div>
                        <div style="line-height: 1.2;">
                            <p class="text-secondary mb-0" style="font-size: 12px;">มูลค่าสิ่งปลูกสร้างรวม</p>
                            <h5 class="fw-bold text-dark mb-0" style="font-size: 18px;">฿17.0M</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border border-light-subtle shadow-sm h-100" style="border-radius: 12px; border-color: #e5e7eb !important;">
                    <div class="card-body px-3 py-2 d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 44px; height: 44px; background-color: #faf5ff; color: #9333ea;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                        </div>
                        <div style="line-height: 1.2;">
                            <p class="text-secondary mb-0" style="font-size: 12px;">จำนวนครุภัณฑ์ (ปกติ)</p>
                            <div class="d-flex align-items-baseline gap-1">
                                <h5 class="fw-bold text-dark mb-0" style="font-size: 18px;">1,245</h5>
                                <span class="text-secondary" style="font-size: 12px;">รายการ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border border-light-subtle shadow-sm" style="border-radius: 12px; border-color: #e5e7eb !important;">        
            
            <div class="card-header bg-white border-bottom py-3 px-4" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    
                    <div class="d-flex p-1 bg-light rounded-3 border border-light-subtle">
                        <a href="<?= Url::to(['index', 'type' => 'land']) ?>" class="btn btn-sm <?= $currentType == 'land' ? 'bg-white text-primary fw-bold shadow-sm' : 'border-0 text-secondary fw-medium tab-hover' ?> px-3 py-2 rounded-2 d-flex align-items-center gap-2" style="<?= $currentType == 'land' ? 'border: 1px solid rgba(0,0,0,0.05);' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="<?= $currentType == 'land' ? '' : 'opacity-75' ?>"><path d="M14.106 5.553a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619v12.764a1 1 0 0 1-.553.894l-4.553 2.277a2 2 0 0 1-1.788 0l-4.212-2.106a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0z"/><path d="M15 5.764v15"/><path d="M9 3.236v15"/></svg>
                            ที่ดิน
                        </a>
                        <a href="<?= Url::to(['index', 'type' => 'building']) ?>" class="btn btn-sm <?= $currentType == 'building' ? 'bg-white text-primary fw-bold shadow-sm' : 'border-0 text-secondary fw-medium tab-hover' ?> px-3 py-2 rounded-2 d-flex align-items-center gap-2" style="<?= $currentType == 'building' ? 'border: 1px solid rgba(0,0,0,0.05);' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="<?= $currentType == 'building' ? '' : 'opacity-75' ?>"><path d="M10 12h4"/><path d="M10 8h4"/><path d="M14 21v-3a2 2 0 0 0-4 0v3"/><path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"/><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/></svg>
                            อาคาร/สิ่งปลูกสร้าง
                        </a>
                        <a href="<?= Url::to(['index', 'type' => 'equipment']) ?>" class="btn btn-sm <?= $currentType == 'equipment' ? 'bg-white text-primary fw-bold shadow-sm' : 'border-0 text-secondary fw-medium tab-hover' ?> px-3 py-2 rounded-2 d-flex align-items-center gap-2" style="<?= $currentType == 'equipment' ? 'border: 1px solid rgba(0,0,0,0.05);' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="<?= $currentType == 'equipment' ? '' : 'opacity-75' ?>"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                            ครุภัณฑ์
                        </a>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <form action="<?= Url::to(['index']) ?>" method="get" class="d-flex m-0">
                            <input type="hidden" name="type" value="<?= Html::encode($currentType) ?>">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted ps-3 rounded-start-3" style="border-color: #e5e7eb;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                </span>
                                <input type="text" name="search" value="<?= Html::encode($search) ?>" class="form-control border-start-0 ps-2 shadow-none text-secondary rounded-end-3" placeholder="ค้นหา..." style="border-color: #e5e7eb; font-size: 0.9rem; width: 200px;">
                            </div>
                        </form>

                        <a href="<?= Url::to(['create', 'type' => $currentType]) ?>" class="btn btn-primary btn-add-item d-flex align-items-center gap-2 px-4 py-2 rounded-3 shadow-sm border-0" style="font-weight: 500; font-size: 0.9rem;">
                            <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-25 rounded-circle" style="width: 20px; height: 20px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </div>
                            เพิ่มรายการ
                        </a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    
                    <?php if ($currentType == 'land'): ?>
                        <thead class="bg-white border-bottom">
                            <tr>
                                <th class="text-center py-2 text-dark" style="width: 50px;">#</th>
                                <th class="py-2 text-dark">รหัสพัสดุ / เลขโฉนด</th>
                                <th class="py-2 text-dark">ที่ตั้ง</th>
                                <th class="py-2 text-dark">เนื้อที่</th>
                                <th class="py-2 text-dark">การได้มา</th>
                                <th class="py-2 text-end text-dark">มูลค่า</th>
                                <th class="py-2 text-center text-dark">สถานะ</th>
                                <th class="py-2 text-center text-dark">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataProvider->getModels() as $index => $item): ?>
                            <tr class="clickable-row" data-href="<?= Url::to(['view', 'id' => $item['id']]) ?>" style="cursor: pointer;">
                                <td class="text-center text-muted fw-medium py-2"><?= $index + 1 ?></td>
                                <td class="py-2">
                                    <div class="fw-bold text-dark"><?= Html::encode($item['asset_code']) ?></div>
                                    <div class="text-muted small">โฉนด: <?= Html::encode($item['deed_no'] ?? '-') ?></div>
                                </td>
                                <td class="py-2 text-secondary"><?= Html::encode($item['location']) ?></td>
                                <td class="py-2 text-secondary"><?= Html::encode($item['area'] ?? '-') ?></td>
                                <td class="py-2">
                                    <div class="text-dark small"><?= Html::encode($item['acquire_method'] ?? '-') ?></div>
                                    <div class="text-muted small"><?= Html::encode($item['received_date']) ?></div>
                                </td>
                                <td class="text-end fw-bold text-dark py-2">฿<?= number_format($item['price'], 2) ?></td>
                                <td class="text-center py-2">
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1 fw-normal">ใช้งานปกติ</span>
                                </td>
                                <td class="text-center py-2" onclick="event.stopPropagation();">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="<?= Url::to(['view', 'id' => $item['id']]) ?>" class="btn btn-icon btn-ghost-secondary"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg></a>
                                        <a href="<?= Url::to(['update', 'id' => $item['id']]) ?>" class="btn btn-icon btn-ghost-secondary"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path></svg></a>
                                        <button class="btn btn-icon btn-ghost-secondary"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="5" height="5" x="3" y="3" rx="1"></rect><rect width="5" height="5" x="16" y="3" rx="1"></rect><rect width="5" height="5" x="3" y="16" rx="1"></rect><path d="M21 16h-3a2 2 0 0 0-2 2v3"></path><path d="M21 21v.01"></path><path d="M12 7v3a2 2 0 0 1-2 2H7"></path><path d="M3 12h.01"></path><path d="M12 3h.01"></path><path d="M12 16v.01"></path><path d="M16 12h1"></path><path d="M21 12v.01"></path><path d="M12 21v-1"></path></svg></button>
                                        <?= Html::a('<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path><path d="M3 6h18"></path><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>', ['delete', 'id' => $item['id'], 'type' => $currentType], ['class' => 'btn btn-icon btn-ghost-secondary', 'data' => ['method' => 'post', 'confirm' => 'ลบ?']]) ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>

                    <?php elseif ($currentType == 'building'): ?>
                        <thead class="bg-white border-bottom">
                            <tr>
                                <th class="text-center py-2 text-dark" style="width: 50px;">#</th>
                                <th class="py-2 text-dark">รหัสพัสดุ / ชื่ออาคาร</th>
                                <th class="py-2 text-dark">ประเภท</th>
                                <th class="py-2 text-dark">ที่ตั้ง / ปีสร้าง</th>
                                <th class="py-2 text-end text-dark">พื้นที่ (ตร.ม.)</th>
                                <th class="py-2 text-end text-dark">มูลค่า</th>
                                <th class="py-2 text-center text-dark">สถานะ</th>
                                <th class="py-2 text-center text-dark">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataProvider->getModels() as $index => $item): ?>
                            <tr class="clickable-row" data-href="<?= Url::to(['view', 'id' => $item['id']]) ?>" style="cursor: pointer;">
                                <td class="text-center text-muted fw-medium py-2"><?= $index + 1 ?></td>
                                <td class="py-2">
                                    <div class="fw-bold text-dark"><?= Html::encode($item['name']) ?></div>
                                    <div class="text-muted small"><?= Html::encode($item['asset_code']) ?></div>
                                </td>
                                <td class="py-2 text-secondary"><?= Html::encode($item['type_name'] ?? '-') ?></td>
                                <td class="py-2">
                                    <div class="text-dark small"><?= Html::encode($item['location'] ?? '-') ?></div>
                                    <div class="text-muted small">สร้างปี: <?= Html::encode($item['build_year'] ?? '-') ?> (<?= Html::encode($item['floors'] ?? '-') ?> ชั้น)</div>
                                </td>
                                <td class="text-end text-secondary py-2"><?= Html::encode($item['area'] ?? '-') ?></td>
                                <td class="text-end fw-bold text-dark py-2">฿<?= number_format($item['price'], 2) ?></td>
                                <td class="text-center py-2">
                                    <?php 
                                        $statusConfig = match($item['status']) {
                                            'Normal', 'ใช้งานปกติ' => ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#bbf7d0', 'label' => 'ใช้งานปกติ'],
                                            'Repair', 'ปรับปรุง' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'border' => '#fde047', 'label' => 'ปรับปรุง'],
                                            default => ['bg' => '#f3f4f6', 'text' => '#4b5563', 'border' => '#e5e7eb', 'label' => $item['status']],
                                        };
                                    ?>
                                    <span class="badge rounded-pill px-3 py-1 fw-normal" 
                                          style="background-color: <?= $statusConfig['bg'] ?>; color: <?= $statusConfig['text'] ?>; border: 1px solid <?= $statusConfig['border'] ?>; font-size: 0.85rem;">
                                        <?= $statusConfig['label'] ?>
                                    </span>
                                </td>
                                <td class="text-center py-2" onclick="event.stopPropagation();">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="<?= Url::to(['view', 'id' => $item['id']]) ?>" class="btn btn-icon btn-ghost-secondary"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg></a>
                                        <a href="<?= Url::to(['update', 'id' => $item['id']]) ?>" class="btn btn-icon btn-ghost-secondary"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path></svg></a>
                                        <button class="btn btn-icon btn-ghost-secondary"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="5" height="5" x="3" y="3" rx="1"></rect><rect width="5" height="5" x="16" y="3" rx="1"></rect><rect width="5" height="5" x="3" y="16" rx="1"></rect><path d="M21 16h-3a2 2 0 0 0-2 2v3"></path><path d="M21 21v.01"></path><path d="M12 7v3a2 2 0 0 1-2 2H7"></path><path d="M3 12h.01"></path><path d="M12 3h.01"></path><path d="M12 16v.01"></path><path d="M16 12h1"></path><path d="M21 12v.01"></path><path d="M12 21v-1"></path></svg></button>
                                        <?= Html::a('<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path><path d="M3 6h18"></path><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>', ['delete', 'id' => $item['id'], 'type' => $currentType], ['class' => 'btn btn-icon btn-ghost-secondary', 'data' => ['method' => 'post', 'confirm' => 'ลบ?']]) ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>

                    <?php else: ?>
                        <thead class="bg-white border-bottom">
                            <tr>
                                <th class="text-center py-2 text-dark" style="width: 50px;">#</th>
                                <th class="py-2 text-dark">รหัสครุภัณฑ์ / ชื่อรายการ</th>
                                <th class="py-2 text-dark">หมวดหมู่ / ยี่ห้อ</th>
                                <th class="py-2 text-dark">หน่วยงานรับผิดชอบ</th>
                                <th class="py-2 text-dark">วันที่รับ</th>
                                <th class="py-2 text-end text-dark">ราคา</th>
                                <th class="py-2 text-center text-dark">สถานะ</th>
                                <th class="py-2 text-center text-dark">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataProvider->getModels() as $index => $item): ?>
                            <tr class="clickable-row" data-href="<?= Url::to(['view', 'id' => $item['id']]) ?>" style="cursor: pointer;">
                                <td class="text-center text-muted fw-medium py-2"><?= $index + 1 ?></td>
                                <td class="py-2">
                                    <div class="fw-bold text-dark"><?= Html::encode($item['asset_code']) ?></div>
                                    <div class="text-muted small"><?= Html::encode($item['name']) ?></div>
                                </td>
                                <td class="py-2">
                                    <div class="text-dark fw-medium"><?= Html::encode($item['category'] ?? '-') ?></div>
                                    <div class="text-muted small"><?= Html::encode($item['brand']) ?> <?= Html::encode($item['model']) ?></div>
                                </td>
                                <td class="text-secondary py-2"><?= Html::encode($item['dept']) ?></td>
                                <td class="text-secondary py-2"><?= Html::encode($item['received_date']) ?></td>
                                <td class="text-end fw-bold text-dark py-2">฿<?= number_format($item['price'], 2) ?></td>
                                <td class="text-center py-2">
                                    <?php 
                                        $statusConfig = match($item['status']) {
                                            'Normal', 'ปกติ' => ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#bbf7d0', 'label' => 'ปกติ'],
                                            'Repair', 'ส่งซ่อม' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'border' => '#fde047', 'label' => 'ส่งซ่อม'],
                                            'Disposed', 'จำหน่าย' => ['bg' => '#f3f4f6', 'text' => '#4b5563', 'border' => '#e5e7eb', 'label' => 'จำหน่าย'],
                                            default => ['bg' => '#f3f4f6', 'text' => '#4b5563', 'border' => '#e5e7eb', 'label' => $item['status']],
                                        };
                                    ?>
                                    <span class="badge rounded-pill px-3 py-1 fw-normal" 
                                          style="background-color: <?= $statusConfig['bg'] ?>; color: <?= $statusConfig['text'] ?>; border: 1px solid <?= $statusConfig['border'] ?>; font-size: 0.85rem;">
                                        <?= $statusConfig['label'] ?>
                                    </span>
                                </td>
                                <td class="text-center py-2" onclick="event.stopPropagation();">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="<?= Url::to(['view', 'id' => $item['id']]) ?>" class="btn btn-icon btn-ghost-secondary"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg></a>
                                        <a href="<?= Url::to(['update', 'id' => $item['id']]) ?>" class="btn btn-icon btn-ghost-secondary"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path></svg></a>
                                        <button class="btn btn-icon btn-ghost-secondary"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="5" height="5" x="3" y="3" rx="1"></rect><rect width="5" height="5" x="16" y="3" rx="1"></rect><rect width="5" height="5" x="3" y="16" rx="1"></rect><path d="M21 16h-3a2 2 0 0 0-2 2v3"></path><path d="M21 21v.01"></path><path d="M12 7v3a2 2 0 0 1-2 2H7"></path><path d="M3 12h.01"></path><path d="M12 3h.01"></path><path d="M12 16v.01"></path><path d="M16 12h1"></path><path d="M21 12v.01"></path><path d="M12 21v-1"></path></svg></button>
                                        <?= Html::a('<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path><path d="M3 6h18"></path><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>', ['delete', 'id' => $item['id'], 'type' => $currentType], ['class' => 'btn btn-icon btn-ghost-secondary', 'data' => ['method' => 'post', 'confirm' => 'ลบ?']]) ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    <?php endif; ?>
                    
                </table>
            </div>
            
            <div class="card-footer bg-white py-3 px-4 border-top d-flex justify-content-between align-items-center" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <span class="text-muted small">
                    แสดง <?= $dataProvider->getCount() ?> จากทั้งหมด <?= $dataProvider->getTotalCount() ?> รายการ
                </span>
                <nav>
                    <?= \yii\bootstrap5\LinkPager::widget([
                        'pagination' => $dataProvider->pagination,
                        'hideOnSinglePage' => false, 
                        'options' => ['class' => 'pagination pagination-sm mb-0'],
                        'linkContainerOptions' => ['class' => 'page-item'],
                        'linkOptions' => ['class' => 'page-link border bg-white text-muted rounded-2 me-1'],
                        'activePageCssClass' => 'active',
                        'prevPageLabel' => 'ก่อนหน้า',
                        'nextPageLabel' => 'ถัดไป',
                        'disabledPageCssClass' => 'disabled',
                        'disabledListItemSubTagOptions' => ['class' => 'page-link border bg-light text-muted rounded-2 me-1'],
                    ]) ?>
                </nav>
            </div>
        </div>
    </div>
</div>

<?php
$script = <<< JS
    $(document).on('click', '.clickable-row', function(e) {
        if ($(e.target).closest('a, button, .btn').length > 0) {
            return;
        }
        window.location = $(this).data('href');
    });
JS;
$this->registerJs($script);
?>

<style>
    .tab-btn:hover { background-color: #e9ecef; color: #1f2937 !important; }
    
    .btn-add-item {
        background: linear-gradient(135deg, #1a508e 0%, #2563eb 100%);
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2), 0 2px 4px -1px rgba(37, 99, 235, 0.1) !important;
    }
    .btn-add-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3), 0 4px 6px -2px rgba(37, 99, 235, 0.15) !important;
        background: linear-gradient(135deg, #164075 0%, #1d4ed8 100%);
    }

    .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; transition: all 0.2s; border: none; background: transparent; color: #9ca3af; }
    .btn-icon:hover { background-color: #f3f4f6; color: #374151; }
    
    .table > :not(caption) > * > * { border-bottom-color: #f3f4f6; }
    
    .clickable-row:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }
</style>