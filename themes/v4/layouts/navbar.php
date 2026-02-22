<?php
use yii\helpers\Url;

// รับค่า Controller ID เพื่อเช็คว่าเมนูไหนกำลัง Active
// (ถ้า $c ถูกส่งมาจาก main.php แล้ว ก็ใช้ได้เลย หรือเรียกใหม่เพื่อความชัวร์ก็ได้)
$c = Yii::$app->controller->id; 
$moduleId = Yii::$app->controller->module->id;

$menuItems = [
    [
        'show' => Yii::$app->user->can('dashboard-admin') ? true : false,
        'label' => 'Dashboard (Admin)', 
        'url' => ['/dashboard-admin/index'], 
        'active' => 'dashboard-admin',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect></svg>'
    ],
    [
        
        'show' => Yii::$app->user->can('user') ? true : false,
        'label' => 'Dashboard (User)', 
        'url' => ['/me/default/index'], 
        'active' => 'me',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path><path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>'
    ],
    [
        'label' => 'บุคลากร', 
        'show' => Yii::$app->user->can('hr') ? true : false,
         'url' => ['/hr/default/dashboard'], 
        'active' => ['employees', 'organization','default'],
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><path d="M16 3.128a4 4 0 0 1 0 7.744"></path><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><circle cx="9" cy="7" r="4"></circle></svg>'
    ],
    [
        'label' => 'ข้อมูลสุขภาพ', 
        'show' => Yii::$app->user->can('health') ? true : false,
         'url' => ['/health/default/index'], 
        'active' => ['health'],
        'icon' => '<i data-lucide="heart-plus"></i>'
    ],
     [
         'show' => Yii::$app->user->can('vehicle') ? true : false,
        'label' => 'จองรถ', 
        'url' => ['/booking/vehicle/calendar'], 
        'active' => 'vehicle',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><path d="M9 17h6"></path><circle cx="17" cy="17" r="2"></circle></svg>'
    ],
    [
         'show' => Yii::$app->user->can('meeting') ? true : false,
        'label' => 'จองห้องประชุม', 
        'url' => ['/booking/meeting/calendar'], 
        'active' => 'meeting',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><path d="M3 10h18"></path><path d="M8 14h.01"></path><path d="M12 14h.01"></path><path d="M16 14h.01"></path><path d="M8 18h.01"></path><path d="M12 18h.01"></path><path d="M16 18h.01"></path></svg>'
    ],
    [
         'show' => Yii::$app->user->can('inventory') ? true : false,
        'label' => 'คลังพัสดุ', 
        'url' => ['/inventory'], 
        'active' => 'inventory',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><polyline points="3.29 7 12 12 20.71 7"></polyline><path d="m7.5 4.27 9 5.15"></path></svg>'
    ],
    [
         'show' => Yii::$app->user->can('asset') ? true : false,
        'label' => 'ทรัพย์สิน', 
        'url' => ['/am'], 
        'active' => 'am',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12h4"></path><path d="M10 8h4"></path><path d="M14 21v-3a2 2 0 0 0-4 0v3"></path><path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path></svg>'
    ],
    [
        'show' => Yii::$app->user->can('document') ? true : false,
        'label' => 'งานสารบรรณ', 
        'url' => ['/dms/dashboard'], 
        'active' => 'dms',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path><path d="M14 2v5a1 1 0 0 0 1 1h5"></path><path d="M10 9H8"></path><path d="M16 13H8"></path><path d="M16 17H8"></path></svg>'
    ],
    [
        
        'show' => Yii::$app->user->can('plan') ? true : false,
        'label' => 'แผนงาน/โครงการ', 
        'url' => ['/plan/dashboard'], 
        'active' => 'plan',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h20"></path><path d="M21 3v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3"></path><path d="m7 21 5-5 5 5"></path></svg>'
    ],
    [
         'show' => Yii::$app->user->can('hr') ? true : false,
        'label' => 'อบรม/ดูงาน', 
        'url' => ['/hr/development/dashboard'], 
        'active' => 'development',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"></path><path d="M22 10v6"></path><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"></path></svg>'
    ],
    [
        'show' => Yii::$app->user->can('leave') ? true : false,
        'label' => 'ระบบลา', 
        'url' => ['/hr/leave/index'], 
        'active' => ['leave'],
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="22" x2="16" y1="11" y2="11"></line></svg>'
    ],
    [
        'show' => Yii::$app->user->can('purchase') ? true : false,
        'label' => 'จัดซื้อจัดจ้าง', 
        'url' => ['/sm'], 
        'active' => 'sm',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>'
    ],
    [
        'show' => Yii::$app->user->can('technician') ? true : false,
        'label' => 'งานซ่อมบำรุง', 
        'url' => ['/helpdesk/general/dashboard'], 
        'active' => 'general',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.106-3.105c.32-.322.863-.22.983.218a6 6 0 0 1-8.259 7.057l-7.91 7.91a1 1 0 0 1-2.999-3l7.91-7.91a6 6 0 0 1 7.057-8.259c.438.12.54.662.219.984z"></path></svg>'
    ],
    [
        'show' => Yii::$app->user->can('computer') ? true : false,
        'label' => 'ศูนย์คอมพิวเตอร์', 
        'url' => ['/helpdesk/computer/dashboard'], 
        'active' => 'computer',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"></rect><line x1="8" x2="16" y1="21" y2="21"></line><line x1="12" x2="12" y1="17" y2="21"></line></svg>'
    ],
    [
        'show' => Yii::$app->user->can('medical') ? true : false,
        'label' => 'ศูนย์เครื่องมือแพทย์', 
        'url' => ['/helpdesk/medical/dashboard'], 
        'active' => 'medical',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"></path></svg>'
    ],
];

// 3. Logic หาชื่อระบบอัตโนมัติจากเมนู
$systemName = 'ระบบทั่วไป';
if (!empty($menuItems)) {
    foreach ($menuItems as $item) {
        if ($moduleId === $item['active']) {
            $systemName = $item['label'];
            break;
        }
    }
}
?>

<?php if (!empty($menuItems)): ?>
<?php foreach ($menuItems as $item): ?>
    <?php 
        $isActive = false;
        $activeRule = $item['active'];

        // กรณี Module 'booking' หรือ 'hr' ให้เช็ค Controller ID ($c)
        if ($moduleId === 'booking' || $moduleId === 'hr' || $moduleId === 'helpdesk') {
            if (is_array($activeRule)) {
                // ถ้า active เป็น array ให้เช็คว่า Controller ปัจจุบันอยู่ในลิสต์ไหม
                $isActive = in_array($c, $activeRule);
            } else {
                // ถ้าเป็น string ปกติ
                $isActive = ($c === $activeRule);
            }
        } else {
            // กรณีโมดูลอื่นๆ เช็คที่ Module ID ตามเดิม
            $isActive = ($moduleId === $activeRule);
        }

        if ($isActive) {
            $systemName = $item['label'];
        }
    ?>
    <?php if(isset($item['show']) && $item['show']):?>
    <a href="<?= Url::to($item['url']) ?>" class="erp-nav-item <?= $isActive ? 'active' : '' ?>">
        <div class="erp-icon-box">
            <?= $item['icon'] ?> 
        </div>
        <span class="erp-nav-text"><?= $item['label'] ?></span>
    </a>
    <?php endif?>
<?php endforeach; ?>
<?php endif; ?>