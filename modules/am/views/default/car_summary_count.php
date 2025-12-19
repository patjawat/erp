<?php
/** @var yii\web\View $this */
use app\modules\am\models\Asset;

// Query ครั้งเดียวเพื่อหาจำนวนแยกตาม asset_group_id
$counts = Asset::find()
    ->select([
        'land' => 'SUM(CASE WHEN asset_group_id = 1 THEN 1 ELSE 0 END)',
        'building' => 'SUM(CASE WHEN asset_group_id = 2 THEN 1 ELSE 0 END)',
        'equip' => 'SUM(CASE WHEN asset_group_id = 4 THEN 1 ELSE 0 END)',
    ])
    ->where(['asset_status' => 1, 'deleted_at' => null])
    ->asArray()
    ->one();

// จัดรูปแบบตัวเลขด้วย Yii::$app->formatter
$countLan = Yii::$app->formatter->asInteger($counts['land'] ?? 0);
$countBuilding = Yii::$app->formatter->asInteger($counts['building'] ?? 0);
$countEquip = Yii::$app->formatter->asInteger($counts['equip'] ?? 0);
?>
<div class="row g-3 mb-4">
      <div class="col-12 col-md-4">
        <div class="card border border-light-subtle shadow-sm h-100" style="border-radius: 12px; border-color: #e5e7eb !important;">
          <div class="card-body px-3 py-2 d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 44px; height: 44px; background-color: #f0fdf4; color: #16a34a;">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.106 5.553a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619v12.764a1 1 0 0 1-.553.894l-4.553 2.277a2 2 0 0 1-1.788 0l-4.212-2.106a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0z"></path>
                <path d="M15 5.764v15"></path>
                <path d="M9 3.236v15"></path>
              </svg>
            </div>
            <div style="line-height: 1.2;">
              <p class="text-secondary mb-0" style="font-size: 12px;">จำนวนที่ดินรวม</p>
              <div class="d-flex align-items-baseline gap-1">
                <h5 class="text-dark mb-0"><?= $countLan ?></h5>
                <span class="text-secondary" style="font-size: 12px;">รายการ</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card border border-light-subtle shadow-sm h-100" style="border-radius: 12px; border-color: #e5e7eb !important;">
          <div class="card-body px-3 py-2 d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 44px; height: 44px; background-color: #eff6ff; color: #2563eb;">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 12h4"></path>
                <path d="M10 8h4"></path>
                <path d="M14 21v-3a2 2 0 0 0-4 0v3"></path>
                <path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path>
                <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path>
              </svg>
            </div>
            <div style="line-height: 1.2;">
              <p class="text-secondary mb-0" style="font-size: 12px;">จำนวนอาคาร/สิ่งปลูกสร้าง</p>
              <div class="d-flex align-items-baseline gap-1">
                <h5 class="text-dark mb-0"><?= $countBuilding ?></h5>
                <span class="text-secondary" style="font-size: 12px;">รายการ</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="card border border-light-subtle shadow-sm h-100" style="border-radius: 12px; border-color: #e5e7eb !important;">
          <div class="card-body px-3 py-2 d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 44px; height: 44px; background-color: #faf5ff; color: #9333ea;">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="3" rx="2"></rect>
                <line x1="8" x2="16" y1="21" y2="21"></line>
                <line x1="12" x2="12" y1="17" y2="21"></line>
              </svg>
            </div>
            <div style="line-height: 1.2;">
              <p class="text-secondary mb-0" style="font-size: 12px;">จำนวนครุภัณฑ์ (ปกติ)</p>
              <div class="d-flex align-items-baseline gap-1">
                <h5 class="text-dark mb-0"><?= $countEquip ?></h5>
                <span class="text-secondary" style="font-size: 12px;">รายการ</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>