<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model yii\base\DynamicModel */
/* @var $maintenanceData array */
/* @var $filesData array */

$this->title = $model->name;
$this->registerJsFile('https://cdn.tailwindcss.com');

$statusConfig = match($model->status) {
    'Normal', 'ใช้งานปกติ' => ['class' => 'bg-green-100 text-green-700 border-green-200', 'label' => 'Normal'],
    'Repair', 'ชำรุด', 'ส่งซ่อม' => ['class' => 'bg-amber-100 text-amber-700 border-amber-200', 'label' => 'Repair'],
    'Disposed', 'จำหน่าย' => ['class' => 'bg-gray-100 text-gray-600 border-gray-200', 'label' => 'Disposed'],
    default => ['class' => 'bg-gray-100 text-gray-600 border-gray-200', 'label' => $model->status]
};
$typeName = match($model->type) { 'land' => 'ที่ดิน', 'building' => 'สิ่งปลูกสร้าง', default => 'ครุภัณฑ์' };
?>

<div class="w-full px-6 py-6 mx-auto font-sans">
    <div class="space-y-6 fade-in">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-2">
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <a href="<?= Url::to(['index', 'type' => $model->type]) ?>" class="cursor-pointer hover:text-blue-500 no-underline text-gray-500"><?= $typeName ?></a>
                <span>/</span><span class="text-gray-800 font-medium">รายละเอียด</span>
            </div>
            <div class="flex flex-wrap gap-3">
                 <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><rect width="5" height="5" x="3" y="3" rx="1"></rect><rect width="5" height="5" x="16" y="3" rx="1"></rect><rect width="5" height="5" x="3" y="16" rx="1"></rect><path d="M21 16h-3a2 2 0 0 0-2 2v3"></path><path d="M21 21v.01"></path><path d="M12 7v3a2 2 0 0 1-2 2H7"></path><path d="M3 12h.01"></path><path d="M12 3h.01"></path><path d="M12 16v.01"></path><path d="M16 12h1"></path><path d="M21 12v.01"></path><path d="M12 21v-1"></path></svg> QR Code</button>
                 <a href="<?= Url::to(['update', 'id' => $model->id, 'type' => $model->type]) ?>" class="inline-flex items-center px-4 py-2 border border-amber-500 bg-amber-50 text-amber-700 rounded-md shadow-sm text-sm font-medium hover:bg-amber-100 transition-colors no-underline"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path></svg> แก้ไขข้อมูล</a>
                 <a href="<?= Url::to(['index', 'type' => $model->type]) ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors no-underline"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg> ย้อนกลับ</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 flex flex-col md:flex-row gap-6">
                <div class="w-full md:w-64 flex-shrink-0">
                    <div class="aspect-square rounded-lg bg-gray-50 overflow-hidden border border-gray-200 flex items-center justify-center">
                        <?php if(!empty($model->photo)): ?>
                            <img src="<?= Yii::getAlias('@web/uploads/') . $model->photo ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="text-gray-400 flex flex-col items-center"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg><span class="text-xs mt-2">No Image</span></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex-1 space-y-4">
                    <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                        <div>
                            <span class="inline-block px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-semibold mb-2"><?= $typeName ?></span>
                            <h2 class="text-2xl font-bold text-gray-800"><?= Html::encode($model->name) ?></h2>
                            <p class="text-gray-500 text-sm mt-1 flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"></path><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle></svg> <?= Html::encode($model->asset_code) ?></p>
                        </div>
                        <div class="text-left md:text-right">
                            <div class="text-sm text-gray-500">มูลค่าทรัพย์สิน</div>
                            <div class="text-2xl font-bold text-blue-600">฿<?= number_format($model->price, 2) ?></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-4 border-y border-gray-100">
                        <div><div class="text-xs text-gray-500">วันที่ได้มา</div><div class="font-medium text-gray-800 text-sm mt-1"><?= $model->received_date ? Html::encode($model->received_date) : '-' ?></div></div>
                        <div>
                             <?php if ($model->type == 'land'): ?><div class="text-xs text-gray-500">เนื้อที่</div><div class="font-medium text-gray-800 text-sm mt-1"><?= Html::encode($model->area ?? '-') ?></div>
                             <?php else: ?><div class="text-xs text-gray-500">อายุการใช้งาน</div><div class="font-medium text-gray-800 text-sm mt-1"><?= $model->life_year ? $model->life_year.' ปี' : '-' ?></div><?php endif; ?>
                        </div>
                        <div><div class="text-xs text-gray-500">สถานะ</div><div class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-1 border <?= $statusConfig['class'] ?>"><?= $statusConfig['label'] ?></div></div>
                        <div><div class="text-xs text-gray-500">ผู้รับผิดชอบ/ที่ตั้ง</div><div class="font-medium text-gray-800 text-sm mt-1"><?= Html::encode($model->location ?? '-') ?></div></div>
                    </div>
                    <div class="flex gap-3">
                        <button class="flex-1 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 font-medium hover:bg-gray-50">พิมพ์ทะเบียนคุม</button>
                        <button class="flex-1 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 shadow-sm">ส่งซ่อม / แจ้งปัญหา</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
             <div class="flex border-b border-gray-200 overflow-x-auto bg-gray-50/50">
                <button onclick="switchTab('details')" id="tab-btn-details" class="tab-btn flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 border-blue-600 text-blue-600 bg-white"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path><path d="M14 2v5a1 1 0 0 0 1 1h5"></path><path d="M10 9H8"></path><path d="M16 13H8"></path><path d="M16 17H8"></path></svg> รายละเอียด</button>
                <button onclick="switchTab('maintenance')" id="tab-btn-maintenance" class="tab-btn flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.106-3.105c.32-.322.863-.22.983.218a6 6 0 0 1-8.259 7.057l-7.91 7.91a1 1 0 0 1-2.999-3l7.91-7.91a6 6 0 0 1 7.057-8.259c.438.12.54.662.219.984z"></path></svg> ประวัติซ่อมบำรุง</button>
                <button onclick="switchTab('depreciation')" id="tab-btn-depreciation" class="tab-btn flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 17h6v-6"></path><path d="m22 17-8.5-8.5-5 5L2 7"></path></svg> ค่าเสื่อมราคา</button>
                <button onclick="switchTab('files')" id="tab-btn-files" class="tab-btn flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 6-8.414 8.586a2 2 0 0 0 2.829 2.829l8.414-8.586a4 4 0 1 0-5.657-5.657l-8.379 8.551a6 6 0 1 0 8.485 8.485l8.379-8.551"></path></svg> เอกสารแนบ</button>
            </div>

            <div class="p-6 min-h-[400px]">
                 <div id="tab-content-details" class="tab-content block space-y-6 animate-in fade-in">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                        <div>
                            <h4 class="font-bold text-gray-800 mb-4 border-l-4 border-blue-600 pl-3">ข้อมูลทั่วไป</h4>
                            <dl class="space-y-4 text-sm">
                                <div class="grid grid-cols-3 gap-4"><dt class="text-gray-500">รหัสทรัพย์สิน</dt><dd class="col-span-2 font-medium text-gray-900"><?= Html::encode($model->asset_code) ?></dd></div>
                                <div class="grid grid-cols-3 gap-4"><dt class="text-gray-500">ชื่อรายการ</dt><dd class="col-span-2 font-medium text-gray-900"><?= Html::encode($model->name) ?></dd></div>
                                <?php if ($model->type == 'land'): ?>
                                    <div class="grid grid-cols-3 gap-4"><dt class="text-gray-500">เลขที่โฉนด</dt><dd class="col-span-2 font-medium text-gray-900"><?= Html::encode($model->deed_no ?? '-') ?></dd></div>
                                    <div class="grid grid-cols-3 gap-4"><dt class="text-gray-500">เนื้อที่</dt><dd class="col-span-2 font-medium text-gray-900"><?= Html::encode($model->area ?? '-') ?></dd></div>
                                <?php else: ?>
                                    <div class="grid grid-cols-3 gap-4"><dt class="text-gray-500">ยี่ห้อ / รุ่น</dt><dd class="col-span-2 font-medium text-gray-900"><?= Html::encode($model->brand ?? '-') ?> <?= Html::encode($model->model) ?></dd></div>
                                    <div class="grid grid-cols-3 gap-4"><dt class="text-gray-500">Serial No.</dt><dd class="col-span-2 font-medium text-gray-900"><?= Html::encode($model->serial_no ?? '-') ?></dd></div>
                                <?php endif; ?>
                            </dl>
                        </div>
                        <div>
                             <h4 class="font-bold text-gray-800 mb-4 border-l-4 border-green-500 pl-3">ข้อมูลการได้มา</h4>
                             <dl class="space-y-4 text-sm">
                                <div class="grid grid-cols-3 gap-4"><dt class="text-gray-500">วันที่รับ</dt><dd class="col-span-2 font-medium text-gray-900"><?= Html::encode($model->received_date ?? '-') ?></dd></div>
                                <div class="grid grid-cols-3 gap-4"><dt class="text-gray-500">แหล่งงบ</dt><dd class="col-span-2 font-medium text-gray-900"><?= Html::encode($model->budget_type ?? '-') ?></dd></div>
                                <div class="grid grid-cols-3 gap-4"><dt class="text-gray-500">ผู้จำหน่าย</dt><dd class="col-span-2 font-medium text-gray-900"><?= Html::encode($model->supplier ?? '-') ?></dd></div>
                            </dl>
                        </div>
                    </div>
                    <div class="pt-6 border-t border-gray-100 mt-6"><h4 class="font-bold text-gray-800 mb-4">รายละเอียดเพิ่มเติม</h4><p class="text-sm text-gray-600 bg-gray-50 p-4 rounded-lg">ไม่มีข้อมูลรายละเอียดเพิ่มเติม</p></div>
                 </div>

                 <div id="tab-content-maintenance" class="tab-content hidden space-y-4 animate-in fade-in">
                    <div class="flex justify-between items-center mb-4"><h4 class="font-bold text-gray-800">ประวัติการซ่อมบำรุง</h4><button class="text-sm text-blue-600 hover:underline flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg> บันทึกการซ่อม</button></div>
                    <?php if(empty($maintenanceData)): ?>
                        <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg">ไม่มีประวัติการซ่อมบำรุง</div>
                    <?php else: ?>
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full text-sm text-left"><thead class="bg-gray-50 text-gray-700"><tr><th class="px-4 py-3">วันที่แจ้ง</th><th class="px-4 py-3">รายการ / อาการ</th><th class="px-4 py-3">ผู้ดำเนินการ</th><th class="px-4 py-3 text-right">ค่าใช้จ่าย</th><th class="px-4 py-3 text-center">สถานะ</th></tr></thead><tbody class="divide-y divide-gray-100">
                            <?php foreach($maintenanceData as $m): ?>
                            <tr class="hover:bg-gray-50"><td class="px-4 py-3 font-medium text-gray-900"><?= $m['date'] ?></td><td class="px-4 py-3"><div class="font-medium text-gray-800"><?= $m['issue'] ?></div><div class="text-xs text-gray-500"><?= $m['desc'] ?></div></td><td class="px-4 py-3 text-gray-600"><?= $m['provider'] ?></td><td class="px-4 py-3 text-right font-medium">฿<?= number_format($m['cost'], 2) ?></td><td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200"><?= $m['status'] ?></span>
                            </td></tr>
                            <?php endforeach; ?>
                        </tbody></table>
                    </div>
                    <?php endif; ?>
                 </div>

                 <div id="tab-content-depreciation" class="tab-content hidden space-y-4 animate-in fade-in">
                    <?php if ($model->type == 'land'): ?>
                         <div class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200 text-gray-500"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-gray-400"><path d="M16 17h6v-6"/><path d="m22 17-8.5-8.5-5 5L2 7"/></svg>ที่ดินไม่มีการคำนวณค่าเสื่อมราคา</div>
                    <?php else: 
                        $cost = $model->price;
                        $life = $model->life_year > 0 ? $model->life_year : 1;
                        $depPerYear = $cost / $life;
                        $startYear = $model->received_date ? (int)date('Y', strtotime($model->received_date)) : (int)date('Y');
                        $currentYear = (int)date('Y');
                    ?>
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-4 flex gap-8">
                            <div><div class="text-xs text-gray-500">ราคาทุน</div><div class="text-lg font-bold text-gray-800">฿<?= number_format($cost, 2) ?></div></div>
                            <div><div class="text-xs text-gray-500">อายุการใช้งาน</div><div class="text-lg font-bold text-gray-800"><?= $life ?> ปี</div></div>
                            <div><div class="text-xs text-gray-500">ค่าเสื่อม/ปี</div><div class="text-lg font-bold text-red-600">฿<?= number_format($depPerYear, 2) ?></div></div>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="w-full text-sm text-left"><thead class="bg-gray-50 text-gray-700"><tr><th class="px-4 py-3">ปี พ.ศ.</th><th class="px-4 py-3 text-right">ค่าเสื่อมราคาประจำปี</th><th class="px-4 py-3 text-right">ค่าเสื่อมราคาสะสม</th><th class="px-4 py-3 text-right">ราคาตามบัญชี (Book Value)</th></tr></thead><tbody class="divide-y divide-gray-100">
                                <tr><td class="px-4 py-3"><?= $startYear ?> (วันรับ)</td><td class="px-4 py-3 text-right">-</td><td class="px-4 py-3 text-right">-</td><td class="px-4 py-3 text-right font-medium">฿<?= number_format($cost, 2) ?></td></tr>
                                <?php $accumulated = 0; for($i = 1; $i <= $life; $i++): $year = $startYear + $i; $accumulated += $depPerYear; $bookValue = max(0, $cost - $accumulated); $isCurrent = ($year == $currentYear); ?>
                                <tr class="<?= $isCurrent ? 'bg-yellow-50 font-medium border-l-4 border-l-yellow-400' : '' ?>"><td class="px-4 py-3"><?= $year ?> <?= $isCurrent ? '<span class="text-xs text-yellow-600 ml-2">(ปัจจุบัน)</span>' : '' ?></td><td class="px-4 py-3 text-right">฿<?= number_format($depPerYear, 2) ?></td><td class="px-4 py-3 text-right text-gray-500">฿<?= number_format($accumulated, 2) ?></td><td class="px-4 py-3 text-right text-gray-800">฿<?= number_format($bookValue, 2) ?></td></tr>
                                <?php endfor; ?>
                            </tbody></table>
                        </div>
                    <?php endif; ?>
                 </div>

                 <div id="tab-content-files" class="tab-content hidden space-y-4 animate-in fade-in">
                    <div class="flex justify-between items-center mb-4"><h4 class="font-bold text-gray-800">เอกสารประกอบ</h4><button class="text-sm text-blue-600 hover:underline flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"></path><path d="m17 8-5-5-5 5"></path><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path></svg> อัปโหลดเอกสาร</button></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach($filesData as $file): 
                            $icon = ($file['type'] == 'image') 
                                ? '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>'
                                : '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path></svg>';
                        ?>
                        <div class="p-4 border border-gray-200 rounded-lg flex items-start gap-3 hover:shadow-md transition bg-white"><div class="p-2 bg-red-50 rounded text-red-600"><?= $icon ?></div><div class="flex-1 min-w-0"><div class="font-medium text-gray-800 text-sm truncate"><?= $file['name'] ?></div><div class="text-xs text-gray-500 mt-0.5"><?= $file['size'] ?> • <?= $file['date'] ?></div></div><button class="p-1.5 text-gray-400 hover:text-blue-600"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15V3"></path><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><path d="m7 10 5 5 5-5"></path></svg></button></div>
                        <?php endforeach; ?>
                    </div>
                 </div>
            </div>
        </div>

    </div> 
</div> 

<script>
function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => { el.classList.add('hidden'); el.classList.remove('block'); });
    document.getElementById('tab-content-' + tabName).classList.remove('hidden');
    document.getElementById('tab-content-' + tabName).classList.add('block');
    document.querySelectorAll('.tab-btn').forEach(el => { el.classList.remove('border-blue-600', 'text-blue-600', 'bg-white'); el.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:bg-gray-50'); });
    const activeBtn = document.getElementById('tab-btn-' + tabName);
    activeBtn.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:bg-gray-50');
    activeBtn.classList.add('border-blue-600', 'text-blue-600', 'bg-white');
}
</script>