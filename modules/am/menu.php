<?php

use yii\helpers\Html;
use yii\helpers\Url;
$canSetup = Yii::$app->user->can('depreciationSetup');
?>
<div class="am-action-menu d-flex flex-wrap gap-2">
    <a href="<?= Url::to(['/am']) ?>" class="btn <?= $active === 'dashboard' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i data-lucide="layout-grid"></i>
        ภาพรวม
    </a>
    <div class="dropdown d-inline-block">
        <button class="btn <?= in_array($active, ['land','building','structure','equip'], true) ? 'btn-primary' : 'btn-outline-primary' ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-star text-warning me-1"></i>
            <span class="d-none d-sm-inline">ทะเบียนทรัพย์สิน</span>
        </button>
        <ul class="dropdown-menu">
            <li><?= Html::a('<i data-lucide="map-pin-house" class="me-2" style="width:1rem;height:1rem;"></i> ที่ดิน', ['/am/land'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="building-2" class="me-2" style="width:1rem;height:1rem;"></i> อาคาร', ['/am/building'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="construction" class="me-2" style="width:1rem;height:1rem;"></i> สิ่งปลูกสร้าง', ['/am/structure'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="package" class="me-2" style="width:1rem;height:1rem;"></i> ครุภัณฑ์', ['/am/equip'], ['class' => 'dropdown-item']) ?></li>
        </ul>
    </div>

    <div class="dropdown d-inline-block">
        <button class="btn <?= $active === 'work' ? 'btn-primary' : 'btn-outline-primary' ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 3v12" />
                <path d="m8 11 4 4 4-4" />
                <path d="M8 5H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-4" />
            </svg>
            งานครุภัณฑ์
        </button>
        <ul class="dropdown-menu">
            <li><?= Html::a('<i data-lucide="package-plus" class="me-2" style="width:1rem;height:1rem;"></i> รับครุภัณฑ์หลายเครื่อง', ['/am/asset-bulk/bulk-create'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="shopping-cart" class="me-2" style="width:1rem;height:1rem;"></i> นำเข้าครุภัณฑ์จากการสั่งซื้อ', ['/am/asset-bulk/bulk-create'], ['class' => 'dropdown-item']) ?></li>
            <!-- <li>
                <hr class="dropdown-divider">
            </li> -->
            <!-- <li><?= Html::a('<i data-lucide="arrow-left-right" class="me-2" style="width:1rem;height:1rem;"></i> โอนย้าย', ['/am/asset/transfer'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="wrench" class="me-2" style="width:1rem;height:1rem;"></i> ส่งซ่อม', ['/am/asset/repair'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="trash-2" class="me-2" style="width:1rem;height:1rem;"></i> จำหน่าย', ['/am/asset/dispose'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="qr-code" class="me-2" style="width:1rem;height:1rem;"></i> พิมพ์ QR', ['/am/asset/print-qr'], ['class' => 'dropdown-item']) ?></li> -->
            <!-- <li>
                <hr class="dropdown-divider">
            </li> -->
            <li><?= Html::a('<i data-lucide="file-check" class="me-2" style="width:1rem;height:1rem;"></i> ตรวจนับพัสดุประจำปี', ['/am/audit'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="file-search" class="me-2" style="width:1rem;height:1rem;"></i> รายงานครุภัณฑ์คงเหลือ', ['/am/report/register'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="trash-2" class="me-2" style="width:1rem;height:1rem;"></i> จำหน่ายพัสดุ', ['/am/disposal'], ['class' => 'dropdown-item']) ?></li>
        </ul>
    </div>

    <div class="dropdown d-inline-block">
        <button class="btn <?= $active === 'depreciation' ? 'btn-primary' : 'btn-outline-primary' ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i data-lucide="trending-down" style="width:1rem;height:1rem;"></i>
            <span class="d-none d-sm-inline">ค่าเสื่อมราคา</span>
        </button>
        <ul class="dropdown-menu">
            <li><?= Html::a('<i data-lucide="layout-list" class="me-2" style="width:1rem;height:1rem;"></i> ภาพรวม / เริ่มที่นี่', ['/am/asset-depreciation/overview'], ['class' => 'dropdown-item fw-semibold']) ?></li>
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-header small text-muted">1 · ตั้งค่าเกณฑ์</li>
            <li><?= Html::a('<i data-lucide="percent" class="me-2" style="width:1rem;height:1rem;"></i> เกณฑ์ค่าเสื่อม', ['/am/depreciation-profile/index'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="link" class="me-2" style="width:1rem;height:1rem;"></i> ผูกเกณฑ์เข้าลำดับชั้น', ['/am/depreciation-binding/index'], ['class' => 'dropdown-item']) ?></li>
            <?php if ($canSetup): ?>
                <li><?= Html::a('<i data-lucide="anchor" class="me-2" style="width:1rem;height:1rem;"></i> ตรึงเกณฑ์ให้ทะเบียนเดิม', ['/am/asset-depreciation/backfill'], ['class' => 'dropdown-item']) ?></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-header small text-muted">2 · จัดการงวดบัญชี</li>
            <li><?= Html::a('<i data-lucide="calendar-range" class="me-2" style="width:1rem;height:1rem;"></i> ดู / จัดการงวดบัญชี', ['/am/accounting-period/index'], ['class' => 'dropdown-item']) ?></li>
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-header small text-muted">3 · ประมวลผลรายเดือน</li>
            <li><?= Html::a('<i data-lucide="calculator" class="me-2" style="width:1rem;height:1rem;"></i> คำนวณงวดรายเดือน', ['/am/accounting-period/index'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="flask-conical" class="me-2" style="width:1rem;height:1rem;"></i> ทดลองคำนวณรายชิ้น', ['/am/asset-depreciation/preview-asset'], ['class' => 'dropdown-item']) ?></li>
            <?php if ($canSetup): ?>
                <li><?= Html::a('<i data-lucide="replace" class="me-2" style="width:1rem;height:1rem;"></i> เปลี่ยนเกณฑ์ทรัพย์สิน', ['/am/asset-depreciation-change/form'], ['class' => 'dropdown-item']) ?></li>
            <?php endif; ?>
            <li><?= Html::a('<i data-lucide="history" class="me-2" style="width:1rem;height:1rem;"></i> ประวัติการเปลี่ยนเกณฑ์', ['/am/asset-depreciation-change/history'], ['class' => 'dropdown-item']) ?></li>
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-header small text-muted">4 · ตรวจสอบและรายงาน</li>
            <li><?= Html::a('<i data-lucide="file-bar-chart" class="me-2" style="width:1rem;height:1rem;"></i> รายงาน (เดือน/ไตรมาส/ปีงบ)', ['/am/depreciation-report/index'], ['class' => 'dropdown-item']) ?></li>
        </ul>
    </div>

    <!-- <a href="<?= Url::to(['/am/report']) ?>" class="btn <?= $active !== 'report' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 12h4"></path>
            <path d="M10 8h4"></path>
            <path d="M14 21v-3a2 2 0 0 0-4 0v3"></path>
            <path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path>
            <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path>
        </svg>
        รายงานค่าเสื่อม
    </a> -->


    <div class="dropdown">
        <button class="btn <?= $active === 'setting' ? 'btn-primary' : 'btn-outline-secondary' ?> dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings-icon lucide-settings">
                <path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915" />
                <circle cx="12" cy="12" r="3" />
            </svg>
            <span class="d-none d-sm-inline">ตั้งค่า</span>
        </button>

        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
            <li>
                <a href="#" class="dropdown-item js-am-setting-trigger" data-bs-toggle="offcanvas" data-bs-target="#am-setting-offcanvas"
                    data-entity="group" data-title="กลุ่มทรัพย์สิน" data-icon="bi bi-diagram-3"
                    data-panel-url="<?= Url::to(['/am/asset-group/setting-panel']) ?>"
                    data-items-url="<?= Url::to(['/am/asset-group/setting-panel-items']) ?>">
                    <i class="bi bi-ui-checks text-primary me-1"></i> กลุ่ม
                </a>
            </li>
            <li>
                <a href="#" class="dropdown-item js-am-setting-trigger" data-bs-toggle="offcanvas" data-bs-target="#am-setting-offcanvas"
                    data-entity="type" data-title="ประเภททรัพย์สิน" data-icon="bi bi-collection"
                    data-panel-url="<?= Url::to(['/am/asset-type/setting-panel']) ?>"
                    data-items-url="<?= Url::to(['/am/asset-type/setting-panel-items']) ?>">
                    <i class="bi bi-ui-checks text-primary me-1"></i> ประเภท
                </a>
            </li>
            <li>
                <a href="#" class="dropdown-item js-am-setting-trigger" data-bs-toggle="offcanvas" data-bs-target="#am-setting-offcanvas"
                    data-entity="category" data-title="หมวดหมู่ครุภัณฑ์" data-icon="bi bi-tags"
                    data-panel-url="<?= Url::to(['/am/asset-category/setting-panel']) ?>"
                    data-items-url="<?= Url::to(['/am/asset-category/setting-panel-items']) ?>">
                    <i class="bi bi-ui-checks text-primary me-1"></i> หมวดหมู่
                </a>
            </li>
            <li>
                <?= Html::a(' <i class="bi bi-ui-checks text-primary me-1"></i> FSN', ['/am/fsn'], ['class' => 'dropdown-item']) ?>
            </li>
            <!-- <li> -->
                <?php // Html::a(' <i class="bi bi-ui-checks text-primary me-1"></i> กำหนดชื่อครุภัณฑ์', ['/am/asset-item'], ['class' => 'dropdown-item']) ?>
            <!-- </li> -->
            <li>
                <hr class="dropdown-divider">
            </li>
            <li>
                <?= Html::a('<i class="fa-solid fa-hashtag me-1"></i> รูปแบบ FSN ครุภัณฑ์', ['/am/setting/fsn-format'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a('<i data-lucide="receipt-text" class="me-1"></i> สภาพครุภัณฑ์', ['/am/asset-condition'], ['class' => 'dropdown-item']) ?>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-header small text-uppercase text-muted">การได้มาของทรัพย์สิน</li>
            <li>
                <?= Html::a('<i data-lucide="wallet" class="me-1" style="width:1rem;height:1rem;color:#047857;"></i> ประเภทเงิน', ['/am/budgetdetail'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a('<i data-lucide="package-search" class="me-1" style="width:1rem;height:1rem;color:#4338ca;"></i> วิธีได้มา', ['/am/methodget'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a('<i data-lucide="hand-coins" class="me-1" style="width:1rem;height:1rem;color:#b45309;"></i> วิธีการได้มา', ['/am/purchase'], ['class' => 'dropdown-item']) ?>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <?= Html::a('<i class="fa-solid fa-gear me-1"></i> ตั้งค่าทรัพย์สิน (ทั้งหมด)', ['/am/setting'], ['class' => 'dropdown-item']) ?>
            </li>

        </ul>
    </div>


</div>

<?php
// ===== Offcanvas ตั้งค่า กลุ่ม/ประเภท/หมวดหมู่ (ใช้ร่วมจากเมนู) =====
// เมนูถูก render ทุกหน้า am → offcanvas + JS พร้อมใช้ทุกที่ ไม่ต้องแยกหน้า index เต็ม (ยังคงลิงก์ไว้ในปุ่ม "เปิดหน้าเต็ม")
?>
<style>
    /* เว้นด้านบนให้พ้น header สูง (.header-fixed ของธีม) ไม่ให้ทับหัว offcanvas */
    #am-setting-offcanvas {
        top: 72px;
        height: calc(100% - 72px);
    }
</style>
<div class="offcanvas offcanvas-end" tabindex="-1" id="am-setting-offcanvas" aria-labelledby="am-setting-offcanvas-label">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title js-setting-quick__title" id="am-setting-offcanvas-label">
            <i class="bi bi-gear me-2"></i> ตั้งค่า
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="ปิด"></button>
    </div>
    <div class="offcanvas-body">
        <div class="text-center text-muted py-4">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <div class="small mt-2">กำลังโหลดรายการ...</div>
        </div>
    </div>
</div>

<?php
$js = <<<'JS'
(function () {
    var $oc = $('#am-setting-offcanvas');
    if (!$oc.length) { return; }

    function ctx() { return $oc.data('amCtx') || {}; }

    var loadingHtml = '<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm" role="status"></div><div class="small mt-2">กำลังโหลดรายการ...</div></div>';

    // เก็บ context (urls) จากปุ่มเมนูที่กด — ทำก่อน offcanvas show เพื่อไม่พึ่ง relatedTarget (รองรับทุกเวอร์ชัน Bootstrap)
    $(document).off('click.amSetting', '.js-am-setting-trigger')
        .on('click.amSetting', '.js-am-setting-trigger', function () {
            var t = this;
            $oc.data('amCtx', {
                panelUrl: t.getAttribute('data-panel-url'),
                itemsUrl: t.getAttribute('data-items-url'),
                entity: t.getAttribute('data-entity')
            });
            var icon = t.getAttribute('data-icon') || 'bi bi-gear';
            var title = t.getAttribute('data-title') || 'ตั้งค่า';
            $oc.find('.js-setting-quick__title').html('<i class="' + icon + ' me-2"></i> ' + title);
        });

    function loadPanel() {
        var c = ctx();
        if (!c.panelUrl) { return; }
        var $body = $oc.find('.offcanvas-body');
        $body.html(loadingHtml);
        $.ajax({ url: c.panelUrl, type: 'GET', dataType: 'json', cache: false }).done(function (res) {
            if (res && res.content) {
                $body.html(res.content);
                if (typeof lucide !== 'undefined') { lucide.createIcons(); }
            }
        });
    }

    function loadItems() {
        var c = ctx();
        if (!c.itemsUrl) { return; }
        $.ajax({
            url: c.itemsUrl, type: 'GET', dataType: 'json', cache: false,
            data: {
                q: $oc.find('.js-setting-quick__search').val() || '',
                filter: $oc.find('.js-setting-quick__filter').val() || ''
            }
        }).done(function (res) {
            if (res && res.content) {
                $oc.find('.js-setting-quick__items').html(res.content);
                if (typeof lucide !== 'undefined') { lucide.createIcons(); }
            }
        });
    }

    $oc.on('show.bs.offcanvas', function () { loadPanel(); });

    var searchTimer = null;
    $oc.on('input', '.js-setting-quick__search', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadItems, 300);
    });
    $oc.on('change', '.js-setting-quick__filter', loadItems);
    $oc.on('click', '.js-setting-quick__refresh', loadItems);

    $oc.on('click', '.js-setting-quick__delete', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (typeof Swal === 'undefined') { return; }
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: 'ลบรายการนี้ ไม่สามารถย้อนกลับได้',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ใช่, ลบเลย',
            cancelButtonText: 'ยกเลิก'
        }).then(function (result) {
            if (!result.isConfirmed) { return; }
            $.post(url, function (res) {
                if (res && res.status === 'success') {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'ลบสำเร็จ', showConfirmButton: false, timer: 1600 });
                    loadItems();
                } else {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'ลบไม่สำเร็จ', showConfirmButton: false, timer: 2200 });
                }
            }, 'json');
        });
    });

    // สลับเปิด/ปิดใช้งานหมวด (สวิตช์ inline) → POST สถานะที่เลือก แล้วรีเฟรชรายการให้ badge อัพเดต
    $oc.on('change', '.js-setting-quick__toggle', function () {
        var $sw = $(this);
        var url = $sw.data('url');
        var active = $sw.is(':checked') ? 1 : 0;
        $sw.prop('disabled', true);
        $.post(url, { active: active }, function (res) {
            if (res && res.status === 'success') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: active ? 'เปิดใช้งานแล้ว' : 'ปิดใช้งาน (ร่าง)', showConfirmButton: false, timer: 1400 });
                }
                loadItems();
            } else {
                $sw.prop('checked', !active).prop('disabled', false);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'อัพเดตไม่สำเร็จ', showConfirmButton: false, timer: 2200 });
                }
            }
        }, 'json').fail(function () {
            $sw.prop('checked', !active).prop('disabled', false);
        });
    });

    // บันทึกใน modal เพิ่ม/แก้ไข (.open-modal) แล้วปิด ขณะ offcanvas เปิด → รีเฟรชรายการเห็นผลทันที
    $(document).off('hidden.bs.modal.amSetting', '#main-modal')
        .on('hidden.bs.modal.amSetting', '#main-modal', function () {
            if ($oc.hasClass('show')) { loadItems(); }
        });
})();
JS;
$this->registerJs($js, \yii\web\View::POS_READY, 'am-setting-offcanvas');
?>
