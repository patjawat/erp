<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model yii\base\Model */
/* @var $type string */

// เรียกใช้ Tailwind CSS
$this->registerJsFile('https://cdn.tailwindcss.com');

// กำหนดชื่อประเภทและไอคอน
$typeName = match($type) {
    'land' => 'ที่ดิน',
    'building' => 'สิ่งปลูกสร้าง',
    default => 'ครุภัณฑ์'
};

$headerIcon = match($type) {
    'land' => '<path d="M3 21h18"/><path d="M5 21V7l8-4 8 4v14"/><path d="M9 10a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>',
    'building' => '<rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/>',
    default => '<rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/>'
};
?>

<main class="px-4 sm:px-6 py-6 max-w-[1600px] mx-auto w-full font-sans">
    <div class="space-y-6">
        
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="<?= Url::to(['index', 'type' => $type]) ?>" class="cursor-pointer hover:text-blue-500 no-underline text-gray-500">
                <?= $typeName ?>
            </a>
            <span>/</span>
            <span class="text-gray-800 dark:text-gray-200 font-medium">
                <?= Yii::$app->controller->action->id == 'create' ? 'เพิ่มข้อมูล' : 'แก้ไขข้อมูล' ?>
            </span>
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'asset-form',
            'options' => ['class' => 'animate-in fade-in slide-in-from-right-4 duration-300', 'enctype' => 'multipart/form-data'],
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['class' => 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1'],
                'inputOptions' => ['class' => 'w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-white'],
                'errorOptions' => ['class' => 'text-red-500 text-xs mt-1'],
            ],
        ]); ?>
        
        <?= Html::hiddenInput('type', $type) ?>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            
            <div class="px-6 py-4 flex justify-between items-center" style="background-color: #1E5C9B;">
                <h3 class="text-lg font-bold flex items-center gap-2" style="color: #FFFFFF;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white/80">
                        <?= $headerIcon ?>
                    </svg> 
                    <?= Yii::$app->controller->action->id == 'create' ? 'เพิ่มข้อมูล' : 'บันทึกข้อมูล' ?><?= $typeName ?>
                </h3>
                <div class="text-xs bg-white/20 px-2 py-1 rounded" style="color: #FFFFFF;">หมวดพัสดุ: <?= $typeName ?></div>
            </div>

            <div class="p-8 space-y-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <div class="lg:col-span-3">
                        <div class="sticky top-4">
                            <div class="aspect-square rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition mb-4 bg-gray-50 dark:bg-gray-800 relative">
                                <?php if (!empty($model->photo)): ?>
                                    <img src="<?= Yii::getAlias('@web/uploads/') . $model->photo ?>" class="w-full h-full object-cover rounded-lg absolute inset-0">
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 mb-2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">อัปโหลดรูปภาพ</p>
                                <?php endif; ?>
                                <?= $form->field($model, 'photo')->fileInput(['class' => 'absolute inset-0 w-full h-full opacity-0 cursor-pointer'])->label(false) ?>
                            </div>
                            <div class="text-xs text-center text-gray-400">รองรับ JPG, PNG ขนาดไม่เกิน 5MB</div>
                        </div>
                    </div>

                    <div class="lg:col-span-9 space-y-8">
                        
                        <section>
                            <h4 class="text-sm font-bold text-gray-800 dark:text-blue-400 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> 
                                ข้อมูลงบประมาณ
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div><?= $form->field($model, 'budget_type')->dropDownList(['งบลงทุน' => 'งบลงทุน', 'งบดำเนินงาน' => 'งบดำเนินงาน', 'เงินบำรุง' => 'เงินบำรุง'], ['class' => 'w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none'])->label('ประเภทงบ') ?></div>
                                <div><?= $form->field($model, 'life_year')->textInput(['placeholder' => '2568'])->label('ปีงบประมาณ') ?></div>
                                <div><?= $form->field($model, 'price')->textInput(['type' => 'number'])->label('จำนวนเงินรวม') ?></div>
                                <div class="md:col-span-3"><?= $form->field($model, 'budget_type')->textInput()->label('หน่วยงานภายใน/แหล่งเงิน') ?></div>
                            </div>
                        </section>

                        <section>
                            <h4 class="text-sm font-bold text-gray-800 dark:text-blue-400 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg> 
                                ข้อมูลทั่วไปของ<?= $typeName ?>
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <?= $form->field($model, 'name')->textInput(['required' => true])->label('ชื่อ' . $typeName . ' <span class="text-red-500">*</span>') ?>
                                </div>

                                <?php if ($type == 'land'): ?>
                                    <div><?= $form->field($model, 'deed_no')->textInput()->label('เลขที่โฉนด') ?></div>
                                    <div><?= $form->field($model, 'area')->textInput()->label('เนื้อที่ (ไร่-งาน-วา)') ?></div>
                                    <div class="md:col-span-2"><?= $form->field($model, 'location')->textInput()->label('ที่ตั้ง') ?></div>

                                <?php elseif ($type == 'building'): ?>
                                    <div><?= $form->field($model, 'type_name')->textInput()->label('ประเภทสิ่งปลูกสร้าง') ?></div>
                                    <div><?= $form->field($model, 'build_year')->textInput()->label('ปีที่สร้าง (พ.ศ.)') ?></div>
                                    <div><?= $form->field($model, 'floors')->textInput(['type' => 'number'])->label('จำนวนชั้น') ?></div>
                                    <div><?= $form->field($model, 'area')->textInput()->label('พื้นที่ใช้สอย (ตร.ม.)') ?></div>

                                <?php else: ?>
                                    <div><?= $form->field($model, 'category_id')->dropDownList(['1' => 'ครุภัณฑ์คอมพิวเตอร์', '2' => 'ครุภัณฑ์การแพทย์', '3' => 'ครุภัณฑ์สำนักงาน'], ['class' => 'w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none'])->label('ประเภทครุภัณฑ์') ?></div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">รหัส FSN (เลขหมวด)</label>
                                        <div class="flex gap-2">
                                            <?= Html::activeTextInput($model, 'fsn_code', ['class' => 'w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-white']) ?>
                                            <button type="button" class="px-3 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-600 dark:text-gray-300 hover:bg-gray-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div><?= $form->field($model, 'asset_code')->textInput()->label('เลขครุภัณฑ์เดิม') ?></div>
                                    <div><?= $form->field($model, 'brand')->textInput()->label('ยี่ห้อ (Brand)') ?></div>
                                    <div><?= $form->field($model, 'model')->textInput()->label('รุ่น / Model') ?></div>
                                    <div><?= $form->field($model, 'color')->textInput()->label('สี') ?></div>
                                    <div><?= $form->field($model, 'serial_no')->textInput()->label('S/N (Serial Number)') ?></div>
                                    <div><?= $form->field($model, 'unit')->textInput()->label('หน่วยนับ') ?></div>
                                <?php endif; ?>

                                <div class="md:col-span-2">
                                    <?= $form->field($model, 'name')->textarea(['rows' => 4, 'class' => 'w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none'])->label('คุณลักษณะเฉพาะ / รายละเอียดเพิ่มเติม') ?>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h4 class="text-sm font-bold text-gray-800 dark:text-blue-400 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/></svg> 
                                ข้อมูลการได้มา
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div><?= $form->field($model, 'budget_type')->dropDownList(['ตกลงราคา (ซื้อ/จ้าง)' => 'ตกลงราคา (ซื้อ/จ้าง)', 'สอบราคา' => 'สอบราคา', 'e-bidding' => 'e-bidding', 'รับบริจาค' => 'รับบริจาค'], ['class' => 'w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none'])->label('วิธีได้มา') ?></div>
                                <div><?= $form->field($model, 'supplier_id')->textInput()->label('ผู้ขาย / ผู้บริจาค') ?></div>
                                <div><?= $form->field($model, 'received_date')->textInput(['type' => 'date'])->label('วันที่ตรวจรับ') ?></div>
                                <?php if ($type != 'land'): ?>
                                    <div><?= $form->field($model, 'checkin_date')->textInput(['type' => 'date'])->label('วันที่รับเข้า') ?></div>
                                    <div><?= $form->field($model, 'warranty_date')->textInput(['type' => 'date'])->label('วันหมดประกัน') ?></div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <section>
                            <h4 class="text-sm font-bold text-gray-800 dark:text-blue-400 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg> 
                                สถานที่และสถานะ
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <?php if ($type != 'land'): ?>
                                    <div><?= $form->field($model, 'location')->textInput()->label('สถานที่ใช้งาน / ห้อง') ?></div>
                                    <div><?= $form->field($model, 'responsible_person')->textInput()->label('ผู้รับผิดชอบ') ?></div>
                                <?php else: ?>
                                    <div class="md:col-span-2"><?= $form->field($model, 'location')->textInput()->label('ที่ตั้งแปลงที่ดิน') ?></div>
                                <?php endif; ?>
                                <div>
                                    <?= $form->field($model, 'status')->dropDownList([
                                        'Normal' => 'ใช้งานปกติ', 
                                        'Repair' => 'ชำรุด/รอซ่อม', 
                                        'Disposed' => 'จำหน่าย/เสื่อมสภาพ'
                                    ], ['class' => 'w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none'])->label('สถานะ') ?>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 flex justify-end gap-4 border-t border-gray-200 dark:border-gray-700">
                <a href="<?= Url::to(['index', 'type' => $type]) ?>" class="px-4 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-gray-700 dark:text-gray-200 text-sm hover:bg-gray-50 no-underline">
                    ย้อนกลับ
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded text-sm font-medium hover:bg-blue-700 flex items-center gap-2 shadow-sm border-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/><path d="M7 3v4a1 1 0 0 0 1 1h7"/></svg> 
                    บันทึกข้อมูล
                </button>
            </div>

        </div>
        <?php ActiveForm::end(); ?>
    </div>
</main>