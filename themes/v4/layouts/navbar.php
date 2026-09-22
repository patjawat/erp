<?php
use yii\helpers\Url;
use yii\helpers\Html;

// รับค่า Controller ID เพื่อเช็คว่าเมนูไหนกำลัง Active
// (ถ้า $c ถูกส่งมาจาก main.php แล้ว ก็ใช้ได้เลย หรือเรียกใหม่เพื่อความชัวร์ก็ได้)
$c = Yii::$app->controller->id; 
$moduleId = Yii::$app->controller->module->id;

$menuItems = [
    [
        'label' => 'AI Assistant',
        'url' => ['/ai/chat/index'],
        'active' => 'ai',
        'icon' => '<i class="fa-solid fa-robot"></i>',
        'show' => !Yii::$app->user->isGuest && Yii::$app->user->can('ai.chat.use'),
    ],
    [
        'show' => Yii::$app->user->can('executiveDashboardView'),
        'label' => 'Dashboard ผู้บริหาร',
        'url' => ['/executive/dashboard/index'],
        'active' => 'executive',
        'icon' => '<i class="bi bi-graph-up-arrow"></i>'
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
         'show' =>  (Yii::$app->user->can('inventory') ? true : false),
        'label' => 'คลังพัสดุ',
        'url' => !empty(env('INVENTORY_URL')) ? env('INVENTORY_URL') : ['/inventory'],
        'active' => 'inventory',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><polyline points="3.29 7 12 12 20.71 7"></polyline><path d="m7.5 4.27 9 5.15"></path></svg>'
    ],
    [
        'show' => Yii::$app->user->can('laundry.view'),
        'label' => 'งานซักฟอก',
        'url' => ['/laundry/dashboard/index'],
        'active' => 'laundry',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="2" width="18" height="20" rx="2"></rect><path d="M3 8h18"></path><circle cx="7" cy="5" r=".5" fill="currentColor" stroke="none"></circle><circle cx="10" cy="5" r=".5" fill="currentColor" stroke="none"></circle><circle cx="12" cy="15" r="4"></circle><path d="M10 15c1-1 2 1 4 0"></path></svg>'
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
        'show' => (new app\modules\medsop\services\DocumentAccessService())->canEnterModule(),
        'label' => 'คลัง SOP/WI',
        'url' => ['/medsop/document/dashboard'],
        'active' => 'medsop',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path><path d="M8 7h8"></path><path d="M8 11h6"></path></svg>'
    ],
    [
        'show' => !Yii::$app->user->isGuest,
        'label' => 'งานคุณภาพ',
        'url' => ['/qms/default/index'],
        'active' => 'qms',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path><path d="m9 12 2 2 4-4"></path></svg>'
    ],
    [
        
        'show' => Yii::$app->user->can('plan') ? true : false,
        'label' => 'แผนงบประมาณ',
        'url' => ['/plan/dashboard'],
        'active' => 'plan',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h20"></path><path d="M21 3v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3"></path><path d="m7 21 5-5 5 5"></path></svg>'
    ],
    [
        // ระยะต้นแบบเปิดทางเข้าให้ผู้ใช้ที่เข้าสู่ระบบเห็นก่อน
        // ก่อนใช้งานจริงต้องเปลี่ยนเป็น RBAC accounting โดยเฉพาะ
        'show' => Yii::$app->user->can('accountingView'),
        'label' => 'บัญชี',
        'url' => ['/accounting/dashboard'],
        'active' => 'accounting',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path><path d="M8 7h8"></path><path d="M8 11h8"></path></svg>'
    ],
    [
        // ระยะต้นแบบเปิดทางเข้าให้ผู้ใช้ที่เข้าสู่ระบบเห็นก่อน
        // ก่อนใช้งานจริงต้องเปลี่ยนเป็น RBAC finance โดยเฉพาะ
        'show' => Yii::$app->user->can('financeView'),
        'label' => 'การเงิน',
        'url' => ['/finance/dashboard'],
        'active' => 'finance',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line><path d="M6 15h2"></path><path d="M14 15h4"></path></svg>'
    ],
    [
        'show' => Yii::$app->user->can('pm'),
        'label' => 'แผนงาน/โครงการ',
        'url' => ['/pm/default/index'],
        'active' => 'pm',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path></svg>'
    ],
    [
        'show' => !Yii::$app->user->isGuest,
        'label' => 'เครื่องมือ',
        'url' => ['/tools/default/index'],
        'active' => 'tools',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>'
    ],
    [
        'show' =>  (Yii::$app->user->can('hr') ? true : false),
        'label' => 'อบรม/ดูงาน',
        'url' => ['/hr/development'],
        'active' => 'development',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"></path><path d="M22 10v6"></path><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"></path></svg>'
    ],
    [
        'show' =>  (Yii::$app->user->can('leave') ? true : false),
        'label' => 'ระบบลา',
        'url' => ['/leave/approver/index'],
        'active' => ['leave'],
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="22" x2="16" y1="11" y2="11"></line></svg>'
    ],
    [
        // สิทธิ์ตารางเวรมาจากผังองค์กร ไม่ใช่ RBAC — หัวหน้าหอผู้ป่วยเป็น role user ธรรมดา
        // ถ้า gate ด้วย role อย่างเดียว หัวหน้าหน่วยจะไม่เห็นเมนูนี้เลย แล้วสรุปว่าเข้าระบบไม่ได้
        'show' => \app\modules\roster\helpers\RosterAccess::canEnter(),
        'label' => 'ตารางเวร',
        'url' => ['/roster/period/index'],
        'active' => 'roster',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><path d="M3 10h18"></path><path d="M8 14h.01"></path><path d="M12 14h.01"></path><path d="M16 14h.01"></path></svg>'
    ],
    [
        'show' => Yii::$app->user->can('hr'),
        'label' => 'ระบบลงเวลา',
        'url' => ['/attendance/default/index'],
        'active' => 'attendance',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>'
    ],
    [
        'show' => Yii::$app->user->can('purchase') ? true : false,
        'label' => 'งานพัสดุ',
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
    [
        'show' => Yii::$app->user->can('housing.staff')
            || Yii::$app->user->can('housing.admin'),
        'label' => 'บ้านพัก',
        'url' => Yii::$app->user->can('housing.staff') || Yii::$app->user->can('housing.admin')
            ? ['/housing/dashboard/index']
            : ['/profile', 'name' => 'housing'],
        'active' => 'housing',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"></path><path d="M6 21V8l6-5 6 5v13"></path><path d="M9 21v-6h6v6"></path><path d="M9 10h.01"></path><path d="M15 10h.01"></path></svg>'
    ],
];

$groups = [
    'งานส่วนตัว' => ['me', 'booking', 'leave'],
    'บริหารและแผนงาน' => ['executive', 'pm', 'plan', 'qms', 'tools', 'ai'],
    'บุคลากรและเวร' => ['hr', 'development', 'roster', 'attendance'],
    'การเงินและพัสดุ' => ['accounting', 'finance', 'inventory', 'am', 'sm'],
    'บริการและสนับสนุน' => ['dms', 'medsop', 'helpdesk', 'housing', 'laundry'],
];
$visible = [];
foreach ($menuItems as $item) {
    if (empty($item['show'])) {
        continue;
    }
    $rule = $item['active'];
    $isActive = in_array($moduleId, ['booking', 'hr', 'helpdesk'], true)
        ? (is_array($rule) ? in_array($c, $rule, true) : $c === $rule)
        : $moduleId === $rule;
    $item['isActive'] = $isActive;
    $item['key'] = is_array($rule) ? implode('-', $rule) : $rule;
    $item['group'] = 'อื่น ๆ';
    $route = is_array($item['url']) ? $item['url'][0] : '';
    $prefix = $route ? explode('/', trim($route, '/'))[0] : (is_string($rule) ? $rule : '');
    if ($prefix === 'booking') $prefix = 'booking';
    if ($prefix === 'helpdesk') $prefix = 'helpdesk';
    if (in_array($rule, ['development', 'roster', 'attendance'], true)) $prefix = $rule;
    foreach ($groups as $group => $modules) {
        if (in_array($prefix, $modules, true)) {
            $item['group'] = $group;
            break;
        }
    }
    $visible[] = $item;
}
$storageKey = 'erp-nav-favorites-' . (int) Yii::$app->user->id;
?>
<button class="erp-nav-item erp-nav-launcher flex-shrink-0" type="button" data-bs-toggle="offcanvas"
    data-bs-target="#erpSystemChooser" aria-controls="erpSystemChooser" aria-label="ระบบทั้งหมด" title="ระบบทั้งหมด">
    <span class="erp-icon-box erp-icon-box-square" aria-hidden="true"><i class="bi bi-grid-3x3-gap-fill"></i></span>
    <span class="erp-nav-text">ระบบทั้งหมด</span>
</button>
<div class="erp-nav-shortcuts d-flex align-items-center overflow-auto h-100" id="erpNavShortcuts" aria-label="ระบบที่ปักหมุด">
    <?php foreach ($visible as $item): ?>
        <a href="<?= Html::encode(Url::to($item['url'])) ?>" class="erp-nav-item <?= $item['isActive'] ? 'active' : '' ?>"
            data-system-key="<?= Html::encode($item['key']) ?>" hidden>
            <span class="erp-icon-box" aria-hidden="true"><?= $item['icon'] ?></span>
            <span class="erp-nav-text"><?= Html::encode($item['label']) ?></span>
        </a>
    <?php endforeach; ?>
</div>
<div class="offcanvas offcanvas-start" tabindex="-1" id="erpSystemChooser" aria-labelledby="erpSystemChooserTitle">
    <div class="offcanvas-header border-bottom">
        <h2 class="offcanvas-title h5 mb-0" id="erpSystemChooserTitle">ระบบทั้งหมด</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="ปิด"></button>
    </div>
    <div class="offcanvas-body">
        <label class="visually-hidden" for="erpSystemSearch">ค้นหาระบบ</label>
        <input class="form-control mb-3" id="erpSystemSearch" type="search" placeholder="ค้นหาระบบ..." autocomplete="off">
        <p class="small text-body-secondary">กดดาวเพื่อปักหมุดระบบที่ใช้บ่อยบนแถบด้านบน</p>
        <div id="erpSystemGroups">
            <?php foreach (array_keys($groups + ['อื่น ๆ' => []]) as $group): ?>
                <?php $items = array_values(array_filter($visible, static fn ($item) => $item['group'] === $group)); ?>
                <?php if (!$items) continue; ?>
                <section class="erp-system-group mb-4" aria-label="<?= Html::encode($group) ?>">
                    <h3 class="h6 text-body-secondary border-bottom pb-2"><?= Html::encode($group) ?></h3>
                    <?php foreach ($items as $item): ?>
                        <div class="erp-system-row d-flex align-items-center gap-2 border-bottom py-1" data-search="<?= Html::encode(mb_strtolower($item['label'])) ?>">
                            <a class="flex-grow-1 text-body text-decoration-none py-2" href="<?= Html::encode(Url::to($item['url'])) ?>">
                                <span class="text-primary me-2" aria-hidden="true"><?= $item['icon'] ?></span><?= Html::encode($item['label']) ?>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary erp-pin-button" data-system-key="<?= Html::encode($item['key']) ?>"
                                aria-label="ปักหมุด <?= Html::encode($item['label']) ?>" aria-pressed="false" title="ปักหมุด">
                                <i class="bi bi-star" aria-hidden="true"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endforeach; ?>
        </div>
        <p class="text-body-secondary d-none" id="erpSystemEmpty">ไม่พบระบบที่ค้นหา</p>
    </div>
</div>
<script>
(function () {
    const key = <?= json_encode($storageKey) ?>;
    const bar = document.getElementById('erpNavShortcuts');
    const chooser = document.getElementById('erpSystemChooser');
    const links = [...bar.querySelectorAll('[data-system-key]')];
    const allowed = links.map(link => link.dataset.systemKey);
    let saved;
    try { saved = JSON.parse(localStorage.getItem(key)); } catch (_) { saved = null; }
    let pins = Array.isArray(saved) ? saved.filter(id => allowed.includes(id)) : allowed.slice(0, 5);
    function render() {
        links.forEach(link => { link.hidden = !pins.includes(link.dataset.systemKey); });
        pins.forEach(id => {
            const link = links.find(node => node.dataset.systemKey === id);
            if (link) bar.appendChild(link);
        });
        chooser.querySelectorAll('.erp-pin-button').forEach(button => {
            const pinned = pins.includes(button.dataset.systemKey);
            button.setAttribute('aria-pressed', String(pinned));
            button.setAttribute('aria-label', (pinned ? 'เลิกปักหมุด ' : 'ปักหมุด ') + button.closest('.erp-system-row').querySelector('a').textContent.trim());
            button.title = pinned ? 'เลิกปักหมุด' : 'ปักหมุด';
            button.querySelector('i').className = pinned ? 'bi bi-star-fill' : 'bi bi-star';
            button.classList.toggle('btn-primary', pinned);
            button.classList.toggle('btn-outline-secondary', !pinned);
        });
    }
    chooser.addEventListener('click', event => {
        const button = event.target.closest('.erp-pin-button');
        if (!button) return;
        const id = button.dataset.systemKey;
        pins = pins.includes(id) ? pins.filter(value => value !== id) : [...pins, id];
        try { localStorage.setItem(key, JSON.stringify(pins)); } catch (_) {}
        render();
    });
    document.getElementById('erpSystemSearch').addEventListener('input', event => {
        const query = event.target.value.trim().toLocaleLowerCase();
        let count = 0;
        chooser.querySelectorAll('.erp-system-group').forEach(group => {
            let found = 0;
            group.querySelectorAll('.erp-system-row').forEach(row => {
                const match = row.dataset.search.includes(query);
                row.hidden = !match;
                if (match) found++;
            });
            group.hidden = found === 0;
            count += found;
        });
        document.getElementById('erpSystemEmpty').classList.toggle('d-none', count !== 0);
    });
    render();
})();
</script>
