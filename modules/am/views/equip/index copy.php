<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\components\SiteHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array{total:int,good:int,damaged:int,total_value:float} $equipStats */

$this->title = 'ครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;

$viewQuery = array_merge(Yii::$app->request->queryParams, []);
$viewListUrl = Url::to(array_merge(['/am/equip/index'], $viewQuery, ['view' => 'list']));
$viewGridUrl = Url::to(array_merge(['/am/equip/index'], $viewQuery, ['view' => 'grid']));
$isTableView = SiteHelper::getDisplay() !== 'grid';

?>




<div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-1">
    <div class="d-flex flex-column gap-2">
        <div class="d-flex align-items-center gap-2 fw-medium" style="font-size: 12px; color: rgb(100, 116, 139);"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-dashboard" aria-hidden="true">
                <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                <rect width="7" height="5" x="3" y="16" rx="1"></rect>
            </svg><span style="cursor: pointer;">หน้าหลัก</span><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right" aria-hidden="true">
                <path d="m9 18 6-6-6-6"></path>
            </svg><span style="cursor: pointer;">ระบบบริหารทรัพย์สิน</span><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right" aria-hidden="true">
                <path d="m9 18 6-6-6-6"></path>
            </svg><span class="fw-bold" style="color: rgb(30, 78, 145);">ครุภัณฑ์</span></div>
        <h2 class="m-0 fw-bold d-flex align-items-center gap-2" style="font-size: 24px; color: rgb(30, 41, 59);"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1E4E91" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-monitor" aria-hidden="true">
                <rect width="20" height="14" x="2" y="3" rx="2"></rect>
                <line x1="8" x2="16" y1="21" y2="21"></line>
                <line x1="12" x2="12" y1="17" y2="21"></line>
            </svg>ทะเบียนครุภัณฑ์</h2>
    </div>
    <div class="d-flex p-1 rounded-3 border" style="background-color: rgba(226, 232, 240, 0.5); border-color: rgb(226, 232, 240);"><button class="btn btn-sm d-flex align-items-center gap-2 fw-bold border-0 shadow-sm" style="background-color: white; color: rgb(30, 41, 59); font-size: 12px; padding: 6px 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1E4E91" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-grid" aria-hidden="true">
                <rect width="7" height="7" x="3" y="3" rx="1"></rect>
                <rect width="7" height="7" x="14" y="3" rx="1"></rect>
                <rect width="7" height="7" x="14" y="14" rx="1"></rect>
                <rect width="7" height="7" x="3" y="14" rx="1"></rect>
            </svg>ภาพรวม</button><button class="btn btn-sm d-flex align-items-center gap-2 fw-bold border-0 " style="background-color: transparent; color: rgb(100, 116, 139); font-size: 12px; padding: 6px 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star" aria-hidden="true">
                <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
            </svg>ทะเบียนทรัพย์สิน</button><button class="btn btn-sm d-flex align-items-center gap-2 fw-bold border-0 " style="background-color: transparent; color: rgb(100, 116, 139); font-size: 12px; padding: 6px 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text" aria-hidden="true">
                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                <path d="M10 9H8"></path>
                <path d="M16 13H8"></path>
                <path d="M16 17H8"></path>
            </svg>งานครุภัณฑ์</button><button class="btn btn-sm d-flex align-items-center gap-2 fw-bold border-0 " style="background-color: transparent; color: rgb(100, 116, 139); font-size: 12px; padding: 6px 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings" aria-hidden="true">
                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>ตั้งค่า</button></div>
</div>
<div class="row g-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="bg-white p-4 rounded-4 border shadow-sm d-flex align-items-center justify-content-between transition-all" style="border-color: rgb(219, 234, 254);">
            <div class="d-flex flex-column gap-1">
                <p class="m-0 fw-bold text-uppercase" style="font-size: 11.5px; color: rgb(100, 116, 139); letter-spacing: 0.025em;">ทรัพย์สินทั้งหมด (รายการ)</p>
                <h3 class="m-0 fw-bolder" style="font-size: 24px; color: rgb(30, 41, 59); letter-spacing: -0.025em;">7</h3>
            </div>
            <div class="p-3 rounded-4" style="background-color: rgb(239, 246, 255); color: rgb(37, 99, 235);"><svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-box" aria-hidden="true">
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                    <path d="m3.3 7 8.7 5 8.7-5"></path>
                    <path d="M12 22V12"></path>
                </svg></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="bg-white p-4 rounded-4 border shadow-sm d-flex align-items-center justify-content-between transition-all" style="border-color: rgb(167, 243, 208);">
            <div class="d-flex flex-column gap-1">
                <p class="m-0 fw-bold text-uppercase" style="font-size: 11.5px; color: rgb(100, 116, 139); letter-spacing: 0.025em;">สภาพดี (รายการ)</p>
                <h3 class="m-0 fw-bolder" style="font-size: 24px; color: rgb(30, 41, 59); letter-spacing: -0.025em;">6</h3>
            </div>
            <div class="p-3 rounded-4" style="background-color: rgb(236, 253, 245); color: rgb(5, 150, 105);"><svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="m9 12 2 2 4-4"></path>
                </svg></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="bg-white p-4 rounded-4 border shadow-sm d-flex align-items-center justify-content-between transition-all" style="border-color: rgb(253, 230, 138);">
            <div class="d-flex flex-column gap-1">
                <p class="m-0 fw-bold text-uppercase" style="font-size: 11.5px; color: rgb(100, 116, 139); letter-spacing: 0.025em;">ชำรุด / รอซ่อม (รายการ)</p>
                <h3 class="m-0 fw-bolder" style="font-size: 24px; color: rgb(30, 41, 59); letter-spacing: -0.025em;">1</h3>
            </div>
            <div class="p-3 rounded-4" style="background-color: rgb(255, 251, 235); color: rgb(217, 119, 6);"><svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wrench" aria-hidden="true">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="bg-white p-4 rounded-4 border shadow-sm d-flex align-items-center justify-content-between transition-all" style="border-color: rgb(207, 250, 254);">
            <div class="d-flex flex-column gap-1">
                <p class="m-0 fw-bold text-uppercase" style="font-size: 11.5px; color: rgb(100, 116, 139); letter-spacing: 0.025em;">รวมราคารับแรก (บาท)</p>
                <h3 class="m-0 fw-bolder" style="font-size: 24px; color: rgb(30, 41, 59); letter-spacing: -0.025em;">165,000.00</h3>
            </div>
            <div class="p-3 rounded-4" style="background-color: rgb(236, 254, 255); color: rgb(8, 145, 178);"><svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-dollar-sign" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path>
                    <path d="M12 18V6"></path>
                </svg></div>
        </div>
    </div>
</div>

<div class="d-flex flex-column gap-3 mt-1">
    <div class="bg-white p-3 rounded-4 border shadow-sm d-flex flex-column flex-xl-row align-items-center gap-3" style="border-color: rgb(226, 232, 240);">
        <div class="d-flex flex-column flex-sm-row align-items-center gap-3 w-100 flex-xl-grow-1">
            <div class="position-relative w-100 flex-sm-grow-1" style="max-width: 320px;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search position-absolute top-50 translate-middle-y" aria-hidden="true" style="left: 14px;">
                    <path d="m21 21-4.34-4.34"></path>
                    <circle cx="11" cy="11" r="8"></circle>
                </svg><input type="text" placeholder="ค้นหารหัส หรือชื่อครุภัณฑ์..." class="form-control rounded-3" value="" style="padding: 10px 16px 10px 40px; font-size: 14px; background-color: rgb(248, 250, 252); border-color: rgb(226, 232, 240);"></div>
            <div class="d-flex flex-column flex-sm-row w-100 gap-3 w-sm-auto flex-grow-1 flex-sm-grow-0"><select class="form-select rounded-3 flex-grow-1" style="font-size: 14px; background-color: rgb(248, 250, 252); border-color: rgb(226, 232, 240); color: rgb(71, 85, 105); padding: 10px 16px; min-width: 160px;">
                    <option>ทุกประเภท (หมวดหลัก)</option>
                </select><select class="form-select rounded-3 flex-grow-1" style="font-size: 14px; background-color: rgb(248, 250, 252); border-color: rgb(226, 232, 240); color: rgb(71, 85, 105); padding: 10px 16px; min-width: 160px;">
                    <option value="ทุกหมวด">ทุกหมวด</option>
                    <option value="ครุภัณฑ์สำนักงาน">ครุภัณฑ์สำนักงาน</option>
                    <option value="เครื่องปรับอากาศ">เครื่องปรับอากาศ</option>
                    <option value="คอมพิวเตอร์">คอมพิวเตอร์</option>
                </select><select class="form-select rounded-3 flex-grow-1" style="font-size: 14px; background-color: rgb(248, 250, 252); border-color: rgb(226, 232, 240); color: rgb(71, 85, 105); padding: 10px 16px; min-width: 120px;">
                    <option value="ทุกสภาพ">ทุกสภาพ</option>
                    <option value="ปกติ">ปกติ</option>
                    <option value="ชำรุด">ชำรุด</option>
                </select></div>
        </div>
        <div class="d-flex align-items-center justify-content-end gap-2 w-100 w-xl-auto"><button class="btn text-white fw-semibold d-flex align-items-center justify-content-center gap-2 rounded-3 shadow-sm w-100 w-xl-auto" style="background-color: rgb(30, 78, 145); font-size: 14px; padding: 10px 24px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search" aria-hidden="true">
                    <path d="m21 21-4.34-4.34"></path>
                    <circle cx="11" cy="11" r="8"></circle>
                </svg> <span>ค้นหา</span></button>
            <div class="d-none d-sm-block mx-1" style="width: 1px; height: 32px; background-color: rgb(226, 232, 240);"></div><button class="btn bg-white fw-medium d-flex align-items-center justify-content-center gap-2 rounded-3 border" style="border-color: rgb(226, 232, 240); color: rgb(71, 85, 105); font-size: 14px; padding: 10px 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-funnel" aria-hidden="true">
                    <path d="M10 20a1 1 0 0 0 .553.895l2 1A1 1 0 0 0 14 21v-7a2 2 0 0 1 .517-1.341L21.74 4.67A1 1 0 0 0 21 3H3a1 1 0 0 0-.742 1.67l7.225 7.989A2 2 0 0 1 10 14z"></path>
                </svg> <span class="d-none d-sm-inline">ตัวกรองเพิ่มเติม</span></button><button class="btn fw-semibold d-flex align-items-center justify-content-center gap-2 rounded-3 border" style="background-color: rgb(236, 253, 245); border-color: rgb(167, 243, 208); color: rgb(4, 120, 87); font-size: 14px; padding: 10px 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-down" aria-hidden="true">
                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                    <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                    <path d="M12 18v-6"></path>
                    <path d="m9 15 3 3 3-3"></path>
                </svg> <span>Excel</span></button>
        </div>
    </div>
    <div class="bg-white rounded-4 border shadow-sm d-flex flex-column overflow-hidden" style="border-color: rgb(226, 232, 240);">
        <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3" style="border-color: rgb(241, 245, 249); background-color: rgba(248, 250, 252, 0.5);">
            <div class="d-flex align-items-center gap-2">
                <div class="p-2 rounded-3" style="background-color: rgb(219, 234, 254);"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1E4E91" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-list" aria-hidden="true">
                        <rect width="8" height="4" x="8" y="2" rx="1" ry="1"></rect>
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <path d="M12 11h4"></path>
                        <path d="M12 16h4"></path>
                        <path d="M8 11h.01"></path>
                        <path d="M8 16h.01"></path>
                    </svg></div>
                <h3 class="m-0 fw-bold" style="font-size: 16px; color: rgb(30, 41, 59);">รายการทะเบียนคุมครุภัณฑ์</h3><span class="badge rounded-pill fw-bold" style="background-color: rgb(226, 232, 240); color: rgb(71, 85, 105); font-size: 10px; padding: 4px 8px;">7 รายการ</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex p-1 rounded-3 border" style="background-color: rgb(241, 245, 249); border-color: rgb(226, 232, 240);"><button class="btn btn-sm d-flex align-items-center gap-1 fw-bold rounded-2 border-0" style="font-size: 12px; background-color: white; color: rgb(37, 99, 235); box-shadow: rgba(0, 0, 0, 0.05) 0px 1px 2px;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list" aria-hidden="true">
                            <path d="M3 12h.01"></path>
                            <path d="M3 18h.01"></path>
                            <path d="M3 6h.01"></path>
                            <path d="M8 12h13"></path>
                            <path d="M8 18h13"></path>
                            <path d="M8 6h13"></path>
                        </svg> ตาราง</button><button class="btn btn-sm d-flex align-items-center gap-1 fw-bold rounded-2 border-0" style="font-size: 12px; background-color: transparent; color: rgb(100, 116, 139); box-shadow: none;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-grid" aria-hidden="true">
                            <rect width="7" height="7" x="3" y="3" rx="1"></rect>
                            <rect width="7" height="7" x="14" y="3" rx="1"></rect>
                            <rect width="7" height="7" x="14" y="14" rx="1"></rect>
                            <rect width="7" height="7" x="3" y="14" rx="1"></rect>
                        </svg> การ์ด</button></div><button class="btn text-white fw-semibold d-flex align-items-center gap-2 rounded-3 shadow-sm" style="background-color: rgb(30, 78, 145); font-size: 14px; padding: 8px 20px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus" aria-hidden="true">
                        <path d="M5 12h14"></path>
                        <path d="M12 5v14"></path>
                    </svg> <span>ลงทะเบียนครุภัณฑ์</span></button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 border-0" style="min-width: 1100px;">
                <thead style="background-color: white;">
                    <tr style="border-bottom: 1px solid rgb(226, 232, 240);">
                        <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">ข้อมูลครุภัณฑ์</th>
                        <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="width: 160px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">หมวดหมู่</th>
                        <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="width: 224px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">สถานที่ตั้ง / ผู้รับผิดชอบ</th>
                        <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 128px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">วันที่รับ</th>
                        <th class="px-4 py-3 border-0 text-uppercase fw-bold text-end" style="width: 144px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">ราคาแรกรับ (฿)</th>
                        <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 112px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">สถานะ</th>
                        <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 144px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">การจัดการ</th>
                    </tr>
                </thead>
                <tbody style="font-size: 13px; color: rgb(51, 65, 85); border-top: 0px;">
                    <tr style="border-bottom: 1px solid rgb(241, 245, 249);">
                        <td class="px-4 py-3 border-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 border" style="width: 40px; height: 40px; background-color: rgb(248, 250, 252); border-color: rgb(226, 232, 240); color: rgb(148, 163, 184);"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package" aria-hidden="true">
                                        <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                        <path d="M12 22V12"></path>
                                        <polyline points="3.29 7 12 12 20.71 7"></polyline>
                                        <path d="m7.5 4.27 9 5.15"></path>
                                    </svg></div>
                                <div><span class="fw-bold d-block text-truncate" style="color: rgb(30, 41, 59); cursor: pointer; max-width: 200px;">เครื่องดูดฝุ่น</span>
                                    <div class="d-flex align-items-center mt-1 font-monospace" style="font-size: 11px; color: rgb(148, 163, 184);"><span>7910-003-0003/66.01</span></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 border-0"><span class="badge rounded-2 fw-medium border" style="background-color: rgb(241, 245, 249); color: rgb(71, 85, 105); border-color: rgb(226, 232, 240); font-size: 11px; padding: 4px 10px;">ครุภัณฑ์สำนักงาน</span></td>
                        <td class="px-4 py-3 border-0">
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex align-items-center gap-2" style="color: rgb(30, 41, 59);"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin flex-shrink-0" aria-hidden="true">
                                        <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg><span class="fw-semibold text-truncate" style="font-size: 14px; max-width: 180px;">งานรักษาความสะอาด</span></div>
                                <div class="d-flex align-items-center gap-2" style="color: rgb(100, 116, 139);"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user flex-shrink-0" aria-hidden="true">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg><span class="text-truncate" style="font-size: 12px; max-width: 180px;">นางสุธาสินี สายบุญตั้ง</span></div>
                            </div>
                        </td>
                        <td class="px-4 py-3 border-0 text-center fw-medium" style="color: rgb(100, 116, 139); font-size: 12px;">26 ธ.ค. 2565</td>
                        <td class="px-4 py-3 border-0 text-end fw-bold font-monospace" style="color: rgb(30, 41, 59);">15,500.00</td>
                        <td class="px-4 py-3 border-0 text-center"><span class="badge rounded-pill fw-bold border d-inline-flex align-items-center justify-content-center gap-1" style="background-color: rgb(236, 253, 245); color: rgb(4, 120, 87); border-color: rgb(167, 243, 208); font-size: 11px; padding: 4px 10px;"><span class="rounded-circle" style="width: 6px; height: 6px; background-color: rgb(16, 185, 129);"></span>ปกติ</span></td>
                        <td class="px-4 py-3 border-0 text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1"><button class="btn btn-sm border-0 d-flex align-items-center justify-content-center p-1 m-0 transition-colors" title="ดูข้อมูล" style="background-color: rgb(239, 246, 255); color: rgb(37, 99, 235); width: 28px; height: 28px;"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye" aria-hidden="true">
                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg></button><button class="btn btn-sm border-0 d-flex align-items-center justify-content-center p-1 m-0 transition-colors" title="แก้ไข" style="background-color: rgb(255, 251, 235); color: rgb(217, 119, 6); width: 28px; height: 28px;"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pen-line" aria-hidden="true">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"></path>
                                    </svg></button><button class="btn btn-sm border-0 d-flex align-items-center justify-content-center p-1 m-0 transition-colors" title="QR" style="background-color: rgb(236, 254, 255); color: rgb(8, 145, 178); width: 28px; height: 28px;"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-qr-code" aria-hidden="true">
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
                                    </svg></button>
                                <div class="mx-1" style="width: 1px; height: 16px; background-color: rgb(226, 232, 240);"></div><button class="btn btn-sm border-0 d-flex align-items-center justify-content-center p-1 m-0 transition-colors" title="ลบ" style="background-color: rgb(254, 242, 242); color: rgb(220, 38, 38); width: 28px; height: 28px;"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2 lucide-trash-2" aria-hidden="true">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                        <line x1="10" x2="10" y1="11" y2="17"></line>
                                        <line x1="14" x2="14" y1="11" y2="17"></line>
                                    </svg></button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-top d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3" style="border-color: rgb(241, 245, 249); background-color: rgb(248, 250, 252);">
            <p class="m-0 fw-medium" style="font-size: 12px; color: rgb(100, 116, 139);">แสดง <span class="fw-bold" style="color: rgb(30, 41, 59);">1</span> ถึง <span class="fw-bold" style="color: rgb(30, 41, 59);">5</span> จากทั้งหมด <span class="fw-bold" style="color: rgb(30, 41, 59);">7</span> รายการ</p>
            <div class="d-flex align-items-center gap-1"><button disabled="" class="btn btn-sm bg-white border d-flex align-items-center justify-content-center p-2" style="border-color: rgb(226, 232, 240); color: rgb(71, 85, 105); opacity: 0.5;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left" aria-hidden="true">
                        <path d="m15 18-6-6 6-6"></path>
                    </svg></button><button class="btn btn-sm d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 32px; height: 32px; font-size: 12px; background-color: rgb(30, 78, 145); color: white; border-color: rgb(226, 232, 240);">1</button><button class="btn btn-sm d-flex align-items-center justify-content-center fw-bold bg-white border" style="width: 32px; height: 32px; font-size: 12px; background-color: white; color: rgb(71, 85, 105); border-color: rgb(226, 232, 240);">2</button><button class="btn btn-sm bg-white border d-flex align-items-center justify-content-center p-2" style="border-color: rgb(226, 232, 240); color: rgb(71, 85, 105); opacity: 1;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg></button></div>
        </div>
    </div>
</div>




<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 w-100">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <span class="text-primary"><i class="fa-solid fa-desktop"></i></span>
        ทะเบียนครุภัณฑ์
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap gap-2 align-items-center justify-content-center justify-content-lg-end">
    <?= $this->render('@app/modules/am/menu', ['active' => 'equip']) ?>
</div>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'title-container', 'timeout' => 50000]); ?>
<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('../default/menu', ['active' => 'asset']) ?>
<?php $this->endBlock(); ?>
<?php Pjax::end(); ?>

<div class="card">
    <div class="card-body p-3">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>


<?= $this->render('kpi_summary', ['equipStats' => $equipStats]) ?>

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-body border-bottom py-3 px-3 px-md-4">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-lg-between gap-3">
                    <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2 text-body">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-pill">
                            <i data-lucide="file-text"></i>
                        </div>
                        ทะเบียนคุมครุภัณฑ์
                    </h6>
                    <div class="d-flex flex-wrap align-items-center gap-2 w-50 w-lg-auto justify-content-start justify-content-lg-end ms-lg-auto">
                        <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> ลงทะเบียน', ['create'], [
                            'class' => 'btn btn-sm btn-primary text-white shadow-sm',
                            'data-pjax' => 0,
                        ]) ?>
                        <div class="btn-group btn-group-sm" role="group" aria-label="มุมมอง">
                            <?= Html::a('<i class="fa-solid fa-table me-1"></i> ตาราง', $viewListUrl, [
                                'class' => 'btn ' . ($isTableView ? 'btn-primary' : 'btn-outline-primary'),
                                'data-pjax' => 0,
                            ]) ?>
                            <?= Html::a('<i class="fa-solid fa-grip me-1"></i> การ์ด', $viewGridUrl, [
                                'class' => 'btn ' . (!$isTableView ? 'btn-primary' : 'btn-outline-primary'),
                                'data-pjax' => 0,
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <?php if ($isTableView): ?>
                    <?= $this->render('_list', [
                        'dataProvider' => $dataProvider,
                    ]) ?>
                <?php else: ?>
                    <?= $this->render('_grid', [
                        'dataProvider' => $dataProvider,
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<span id="totalCount" class="d-none"><?= (int) $dataProvider->getTotalCount(); ?></span>

<?php
$equipIndexUrl = Json::encode(Url::to(['/am/equip/index']));
$js = <<< JS
$('#am-container').on('pjax:success', function() {
    $('#showTotalCount').text($('#totalCount').text());
    $.pjax.reload({ container:'#title-container', history:false, replace: false});
});

$('.delete-asset').click(function (e) {
    e.preventDefault();
    let url = $(this).attr('href');

    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "ข้อมูลนี้จะถูกลบและไม่สามารถกู้คืนได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: url,
                dataType: "json",
                success: function (res) {
                    if (res.status == 'success') {
                        Swal.fire({
                            title: 'ลบข้อมูลสำเร็จ!',
                            text: 'รายการถูกลบเรียบร้อยแล้ว',
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = $equipIndexUrl;
                        });
                    } else {
                        Swal.fire(
                            'เกิดข้อผิดพลาด!',
                            res.message || 'ไม่สามารถลบข้อมูลได้',
                            'error'
                        );
                    }
                },
                error: function () {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                        'error'
                    );
                }
            });
        }
    });
});
JS;
$this->registerJs($js);
?>