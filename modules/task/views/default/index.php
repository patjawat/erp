<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * ปฏิทินงาน — สามคอลัมน์: ทีมงาน | ปฏิทิน | รายการงาน
 *
 * ชิปบนปฏิทินเป็นจำนวนงานที่ยังไม่ปิด ไม่ใช่ชื่องาน เพราะวันที่มีงานหลายชิ้น
 * จะกลายเป็นกำแพงข้อความจนอ่านปฏิทินไม่ออก
 *
 * งานที่เลยกำหนดและยังไม่ปิดจะทบมารวมกับตัวเลขของวันนี้ ไม่ค้างอยู่ในอดีต
 * เพราะงานที่จมอยู่ในวันที่ผ่านไปแล้วไม่มีใครเลื่อนปฏิทินกลับไปดู คือต้นเหตุของงานร้อน
 *
 * @var yii\web\View $this
 * @var app\modules\hr\models\Employees $me
 * @var app\modules\hr\models\Employees[] $people
 * @var int[] $selected
 * @var array $lists
 */
$this->title = 'ปฏิทินงาน';
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();

$this->registerJsFile('@web/libs/fullcalendar/th.global.min.js', ['depends' => [\app\assets\AppAsset::class]]);

$eventsUrl = Url::to(['/task/default/events']);
$listUrl = Url::to(['/task/default/list']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
?>

<style>
    /* ใช้เฉพาะสิ่งที่ Bootstrap utility ไม่มีให้ และอ้างสีผ่านตัวแปรของ Bootstrap เท่านั้น */
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    #task-page {
        --task-radius: 10px;
        --task-control-radius: 8px;
    }

    .task-workspace-card {
        border-radius: var(--task-radius);
        box-shadow: var(--bs-box-shadow-sm);
    }

    .task-toolbar { min-height: 2.5rem; }

    .task-toolbar .btn { min-height: 2.25rem; }

    .task-people-list { max-height: 31rem; }

    .task-person {
        border-radius: var(--task-control-radius);
        padding: .45rem .5rem !important;
        transition: background-color 120ms ease-out;
    }

    .task-person:hover { background: var(--bs-tertiary-bg); }

    .task-person:has(.task-person-check:checked) { background: var(--bs-primary-bg-subtle); }

    .task-person .form-check-label { min-width: 0; cursor: pointer; }

    .task-person-avatar {
        width: 1.75rem;
        height: 1.75rem;
        font-size: .75rem;
        font-weight: 600;
    }

    .task-panel-scroll { max-height: 39rem; overflow-y: auto; scrollbar-width: thin; }

    .task-item {
        position: relative;
        padding: .75rem .6rem;
        border-bottom: 1px solid var(--bs-border-color-translucent);
        border-radius: var(--task-control-radius);
        transition: background-color 120ms ease-out, box-shadow 120ms ease-out;
    }

    .task-item:last-child { border-bottom-color: transparent; }

    .task-item:hover { background: var(--bs-tertiary-bg); }

    .task-item:focus-within {
        box-shadow: 0 0 0 .2rem var(--bs-primary-border-subtle);
    }

    .task-check {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        flex: 0 0 2rem;
        border-radius: 50%;
        font-size: 1.05rem;
    }

    .task-complete-btn:hover,
    .task-complete-btn:focus-visible {
        color: var(--bs-success-text-emphasis) !important;
        background: var(--bs-success-bg-subtle);
    }

    .task-item-title { font-weight: 600; line-height: 1.35; }

    .task-item-detail { margin-top: .2rem; font-size: .78rem; line-height: 1.45; }

    .task-item-people {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        margin-top: .35rem;
        font-size: .74rem;
        line-height: 1.35;
    }

    .task-status-chip {
        display: inline-flex;
        align-items: center;
        padding: .2rem .45rem;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 500;
        line-height: 1.2;
    }

    .task-detail-hero {
        padding: 1rem;
        border-radius: var(--task-radius);
        background: var(--bs-tertiary-bg);
    }

    .task-detail-person {
        display: flex;
        align-items: flex-start;
        gap: .65rem;
        min-width: 0;
    }

    .task-detail-person-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        flex: 0 0 2.25rem;
        border-radius: 50%;
        background: var(--bs-secondary-bg);
        color: var(--bs-secondary-color);
    }

    .task-detail-section {
        padding: 1rem 0;
        border-top: 1px solid var(--bs-border-color-translucent);
    }

    .task-activity-line { position: relative; padding-left: 1.5rem; }

    .task-activity-line::before {
        content: '';
        position: absolute;
        left: .36rem;
        top: .4rem;
        bottom: -.65rem;
        width: 1px;
        background: var(--bs-border-color);
    }

    .task-activity-line:last-child::before { display: none; }

    .task-activity-line::after {
        content: '';
        position: absolute;
        left: .15rem;
        top: .35rem;
        width: .45rem;
        height: .45rem;
        border-radius: 50%;
        background: var(--bs-secondary-color);
    }

    .task-day-popup { padding: .15rem 0; }

    .task-day-popup .task-item {
        padding: .85rem .5rem;
        border-radius: 0;
    }

    #main-modal:has(.task-day-popup) .task-item:hover { background: var(--bs-secondary-bg); }

    #main-modal:has(.task-day-popup) .modal-content {
        background: var(--bs-tertiary-bg);
        border-color: var(--bs-border-color-translucent);
        border-radius: 14px;
        box-shadow: var(--bs-box-shadow-lg);
        overflow: hidden;
    }

    #main-modal:has(.task-day-popup) .modal-header {
        padding: 1rem 1.25rem .5rem;
        color: var(--bs-body-color);
        background: var(--bs-tertiary-bg) !important;
        border-bottom: 0;
    }

    #main-modal:has(.task-day-popup) .modal-title {
        font-size: 1.1rem;
        font-weight: 600;
    }

    #main-modal:has(.task-day-popup) .modal-body {
        padding: .5rem 1.25rem 1.25rem;
        background: var(--bs-tertiary-bg);
    }

    #main-modal:has(.task-day-popup) .task-day-list {
        border-color: var(--bs-border-color-translucent) !important;
    }

    #main-modal.task-modal-editing .modal-content,
    #main-modal:has(.task-form) .modal-content {
        border-radius: 12px;
        overflow: hidden;
    }

    @media (min-width: 576px) {
        #main-modal.task-modal-editing .modal-dialog,
        #main-modal:has(.task-form) .modal-dialog { max-width: 680px; }
    }

    .task-chevron { transition: transform .15s ease-in-out; }

    [aria-expanded="true"] > .task-chevron { transform: rotate(90deg); }

    #task-calendar .fc { --fc-border-color: var(--bs-border-color); }

    #task-calendar .fc .fc-daygrid-day.fc-day-today {
        background-color: var(--bs-primary-bg-subtle);
    }

    #task-calendar .fc .fc-daygrid-day.task-day-selected {
        box-shadow: inset 0 0 0 2px var(--bs-primary);
    }

    #task-calendar .fc .fc-col-header-cell-cushion,
    #task-calendar .fc .fc-daygrid-day-number {
        color: var(--bs-body-color);
        text-decoration: none;
    }

    #task-calendar .fc-event {
        border: 0;
        font-size: .78rem;
        font-weight: 500;
        padding: .1rem .3rem;
        cursor: pointer;
    }

    /* FullCalendar กำหนดสีตัวอักษรของ event ผ่านตัวแปรของตัวเอง
       ถ้าตั้งแค่ color ธรรมดา ตัวหนังสือจะยังเป็นสีขาวจนอ่านไม่ออกบนพื้นอ่อน */
    #task-calendar .fc-event .fc-event-main,
    #task-calendar .fc-event .fc-event-title,
    #task-calendar .fc-event .fc-event-time {
        color: inherit;
    }

    .task-ev-normal {
        --fc-event-text-color: var(--bs-primary-text-emphasis);
        background-color: var(--bs-primary-bg-subtle);
        color: var(--bs-primary-text-emphasis);
    }

    .task-ev-urgent {
        --fc-event-text-color: var(--bs-danger-text-emphasis);
        background-color: var(--bs-danger-bg-subtle);
        color: var(--bs-danger-text-emphasis);
    }

    .task-ev-done {
        --fc-event-text-color: var(--bs-success-text-emphasis);
        background-color: var(--bs-success-bg-subtle);
        color: var(--bs-success-text-emphasis);
        text-decoration: line-through;
    }

    .task-ev-cancelled {
        --fc-event-text-color: var(--bs-secondary-color);
        background-color: var(--bs-secondary-bg-subtle);
        color: var(--bs-secondary-color);
    }

    @media (min-width: 992px) {
        #task-people-col .task-workspace-card,
        #task-panel-col .task-workspace-card { position: sticky; top: 1rem; }
    }

    @media (max-width: 991.98px) {
        .task-people-list { max-height: 13rem; }
        .task-panel-scroll { max-height: none; }
        .task-toolbar #cal-title { order: -1; width: 100%; margin-left: 0 !important; }
        .task-toolbar .btn,
        .task-check,
        .task-person-check,
        .task-person .form-check-label { min-height: 2.75rem; }
        .task-check { width: 2.75rem; flex-basis: 2.75rem; }
        .task-person .form-check-label { align-items: center; }
    }

    @media (prefers-reduced-motion: reduce) {
        .task-item,
        .task-person,
        .task-chevron { transition: none; }
    }

</style>

<?php // ใช้แถบเมนูเดียวกับหน้ารออนุมัติ ให้เข้าออกระหว่างสองหน้าได้จากที่เดียวกัน ?>
<?= $this->render('@app/modules/approveV2/tab_menu', ['menu' => 'task']) ?>

<div class="container-fluid px-0" id="task-page">

    <?php
    // ชวนผูก Telegram — ขึ้นเฉพาะคนที่ยังไม่ผูก และหายไปเองเมื่อผูกแล้ว
    // จึงไม่เพิ่มเมนูถาวรให้หน้าจอรก
    $tgLinked = true;
    $tgReady = false;
    try {
        $tgUser = Yii::$app->user->identity;
        $tgLinked = $tgUser && trim((string) ($tgUser->telegram_id ?? '')) !== '';
        $tgReady = \app\modules\telegrambot\services\TelegramLinkService::botUsername() !== null;
    } catch (\Throwable $e) {
        $tgLinked = true;
    }
    ?>
    <?php if (!$tgLinked && $tgReady): ?>
        <div class="alert alert-primary d-flex flex-wrap align-items-center gap-2 py-2" role="status">
            <i class="bi bi-telegram" aria-hidden="true"></i>
            <span class="small flex-grow-1">รับแจ้งเตือนงานที่มอบหมายถึงคุณผ่าน Telegram — เชื่อมครั้งเดียว ใช้ได้ตลอด</span>
            <?= Html::a('เชื่อมต่อ', ['/profile/telegram-connect'], [
                'class' => 'btn btn-sm btn-primary',
                'data-pjax' => '0',
            ]) ?>
        </div>
    <?php endif ?>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $variant): ?>
        <?php if (Yii::$app->session->hasFlash($flash)): ?>
            <div class="alert alert-<?= $variant ?> alert-dismissible fade show" role="alert">
                <?= Html::encode(Yii::$app->session->getFlash($flash)) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
            </div>
        <?php endif ?>
    <?php endforeach ?>

    <div class="task-toolbar d-flex flex-wrap align-items-center gap-2 mb-3">
        <a href="<?= Url::to(['/task/default/create']) ?>" class="open-modal btn btn-sm btn-primary"
           data-size="modal-lg" data-pjax="0">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>เพิ่มงาน
        </a>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="cal-today">วันนี้</button>
        <div class="btn-group btn-group-sm" role="group" aria-label="เลื่อนเดือน">
            <button type="button" class="btn btn-outline-secondary" id="cal-prev" aria-label="เดือนก่อนหน้า">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" id="cal-next" aria-label="เดือนถัดไป">
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </button>
        </div>
        <h1 class="h5 mb-0 ms-1" id="cal-title">&nbsp;</h1>

        <button type="button" class="btn btn-sm btn-outline-primary ms-auto" id="task-panel-toggle"
                aria-expanded="true" aria-controls="task-panel-col">
            <i class="bi bi-list-task me-1" aria-hidden="true"></i><span id="task-panel-toggle-label">ซ่อนรายการงาน</span>
        </button>
    </div>

    <div class="row g-3" id="task-layout">

        <div class="col-12 col-lg-2" id="task-people-col">
            <section class="card bg-body border task-workspace-card">
                <div class="card-body p-2">
                    <?= $this->render('_panel_people', ['me' => $me, 'people' => $people, 'selected' => $selected]) ?>
                </div>
            </section>
        </div>

        <div class="col-12 col-lg-7" id="task-calendar-col">
            <section class="card bg-body border task-workspace-card">
                <div class="card-body p-2 p-md-3">
                    <div id="task-calendar"></div>
                </div>
            </section>
        </div>

        <div class="col-12 col-lg-3" id="task-panel-col">
            <section class="card bg-body border task-workspace-card">
                <div class="card-header bg-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h2 class="h6 mb-0">สิ่งที่ต้องทำ</h2>
                        <span class="text-body-secondary small">เรียงงานเร่งด่วนและเกินกำหนดก่อน</span>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis" id="task-open-count">
                        <?= count($lists['open']) ?>
                    </span>
                    <span class="visually-hidden" id="task-list-status" role="status" aria-live="polite"></span>
                </div>
                <div class="card-body p-2 task-panel-scroll" id="task-list-body">
                    <?= $this->render('_panel_list', ['lists' => $lists, 'date' => null]) ?>
                </div>
            </section>
        </div>

    </div>
</div>

<?php
$js = <<<JS
(function () {
    var EVENTS_URL   = '{$eventsUrl}';
    var LIST_URL     = '{$listUrl}';
    var CSRF_PARAM   = '{$csrfParam}';
    var CSRF_TOKEN   = '{$csrfToken}';

    var MONTHS = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                  'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];

    var calendar = null;
    var activeDate = null;

    function selectedEmpIds() {
        return Array.prototype.slice
            .call(document.querySelectorAll('.task-person-check:checked'))
            .map(function (el) { return el.value; });
    }

    // โหลดแผงรายการงานใหม่ตามตัวกรองปัจจุบัน
    function reloadList() {
        var body = document.getElementById('task-list-body');
        if (!body) { return; }
        var status = document.getElementById('task-list-status');
        var params = new URLSearchParams();
        selectedEmpIds().forEach(function (id) { params.append('emp[]', id); });
        if (activeDate) { params.set('date', activeDate); }

        body.setAttribute('aria-busy', 'true');
        if (status) { status.textContent = 'กำลังโหลดรายการงาน'; }

        return fetch(LIST_URL + '?' + params.toString(), {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                body.innerHTML = html;
                var count = body.querySelectorAll('.task-open-list .task-item').length;
                var badge = document.getElementById('task-open-count');
                if (badge) { badge.textContent = count; }
                if (status) {
                    status.textContent = activeDate
                        ? 'แสดงงานวันที่เลือก ' + count + ' รายการ'
                        : 'แสดงงานค้าง ' + count + ' รายการ';
                }
                bindList();
                return true;
            })
            .catch(function () {
                body.innerHTML = '<div class="alert alert-danger small mb-0" role="alert">โหลดรายการงานไม่สำเร็จ <button type="button" class="btn btn-sm btn-outline-danger ms-1" id="task-list-retry">ลองอีกครั้ง</button></div>';
                if (status) { status.textContent = 'โหลดรายการงานไม่สำเร็จ'; }
                return false;
            })
            .finally(function () {
                body.removeAttribute('aria-busy');
            });
    }

    function afterChange() {
        reloadList();
        if (calendar) { calendar.refetchEvents(); }
    }

    // ส่งคำสั่งเปลี่ยนสถานะงาน ใช้ร่วมกันทั้งปุ่มวงกลมและปุ่มใน popup
    function postAction(btn, url) {
        btn.disabled = true;
        var msg = document.getElementById('task-form-msg');
        if (msg) { msg.className = 'small align-self-center text-body-secondary'; msg.textContent = 'กำลังบันทึก...'; }
        var body = new FormData();
        body.append(CSRF_PARAM, CSRF_TOKEN);
        return fetch(url, {
            method: 'POST',
            body: body,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.status === 'success') {
                    afterChange();
                    return true;
                } else {
                    btn.disabled = false;
                    if (msg) { msg.className = 'small align-self-center text-danger-emphasis'; msg.textContent = data.message || 'ดำเนินการไม่สำเร็จ'; }
                    return false;
                }
            })
            .catch(function () {
                btn.disabled = false;
                if (msg) { msg.className = 'small align-self-center text-danger-emphasis'; msg.textContent = 'เชื่อมต่อไม่สำเร็จ กรุณาลองอีกครั้ง'; }
                return false;
            });
    }

    function syncSelectedDay() {
        document.querySelectorAll('#task-calendar .fc-daygrid-day').forEach(function (cell) {
            cell.classList.toggle('task-day-selected', !!activeDate && cell.dataset.date === activeDate);
        });
    }

    // ใช้ delegation เพราะเนื้อหาถูกใส่เข้ามาทีหลังทั้งในแผงขวา popup และ modal กลาง
    function bindList() {
        var clearBtn = document.getElementById('task-clear-date');
        if (clearBtn && clearBtn.dataset.bound !== '1') {
            clearBtn.dataset.bound = '1';
            clearBtn.addEventListener('click', function () {
                activeDate = null;
                syncSelectedDay();
                reloadList();
            });
        }
    }

    document.addEventListener('click', function (e) {
        var circle = e.target.closest ? e.target.closest('.task-complete-btn') : null;
        if (circle) {
            e.preventDefault();
            postAction(circle, circle.dataset.url);
            return;
        }
        var action = e.target.closest ? e.target.closest('.task-action-btn') : null;
        if (action) {
            e.preventDefault();
            postAction(action, action.dataset.url).then(function (success) {
                if (success) { closeMainModal(); }
            });
            return;
        }

        var retry = e.target.closest ? e.target.closest('#task-list-retry') : null;
        if (retry) {
            e.preventDefault();
            reloadList();
            return;
        }

        // คลิกงานที่อยู่ใน popup อยู่แล้ว ให้สลับเนื้อหาแทนการเปิด modal ซ้อน
        var edit = e.target.closest ? e.target.closest('.task-open-edit') : null;
        if (edit) {
            e.preventDefault();
            swapModalContent(edit.getAttribute('href'));
            return;
        }

        // ปุ่มลัดกำหนดเสร็จ ใช้ได้ทั้งฟอร์มเพิ่มงานและฟอร์มแก้ไข
        // ค่าที่ใส่เป็น วว/ดด/พ.ศ. ให้ตรงกับ DatepickerThai
        var quick = e.target.closest ? e.target.closest('.task-quick-date') : null;
        if (quick) {
            e.preventDefault();
            var qForm = quick.closest('form');
            var due = qForm ? qForm.querySelector('input[name="due_date"]') : null;
            if (due) { due.value = quick.dataset.date || ''; }
            if (qForm) {
                qForm.querySelectorAll('.task-quick-date').forEach(function (b) { b.classList.remove('active'); });
            }
            quick.classList.add('active');
        }
    });

    /**
     * แทนที่เนื้อหาใน modal เดิมด้วยฟอร์มแก้ไข พร้อมปุ่มย้อนกลับ
     * เลือกวิธีนี้แทนการซ้อน modal เพราะ backdrop สองชั้นทำให้กดปิดยากบนมือถือ
     */
    function swapModalContent(url) {
        var modal = document.getElementById('main-modal');
        if (!modal) { return; }
        var body = modal.querySelector('.modal-body');
        var label = modal.querySelector('#main-modal-label');
        var dialog = modal.querySelector('.modal-dialog');
        if (!body) { return; }

        var prevHtml = body.innerHTML;
        var prevTitle = label ? label.innerHTML : '';
        var sizeClasses = ['modal-sm', 'modal-md', 'modal-lg', 'modal-xl', 'modal-xxl'];
        var prevSize = dialog ? sizeClasses.find(function (name) { return dialog.classList.contains(name); }) : null;
        function restorePrevious() {
            modal.classList.remove('task-modal-editing');
            body.innerHTML = prevHtml;
            if (label) { label.innerHTML = prevTitle; }
            if (dialog) {
                sizeClasses.forEach(function (name) { dialog.classList.remove(name); });
                dialog.classList.add(prevSize || 'modal-md');
            }
        }
        if (dialog) {
            sizeClasses.forEach(function (name) { dialog.classList.remove(name); });
            dialog.classList.add('modal-lg');
        }
        modal.classList.add('task-modal-editing');
        body.innerHTML = '<div class="text-body-secondary py-3">กำลังโหลด...</div>';

        fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (label && data.title) { label.innerHTML = data.title; }
                body.innerHTML = '';

                var back = document.createElement('button');
                back.type = 'button';
                back.className = 'btn btn-sm btn-link text-decoration-none p-0 mb-2';
                back.innerHTML = '<i class="bi bi-arrow-left me-1"></i>กลับไปรายการ';
                back.addEventListener('click', function () {
                    restorePrevious();
                });
                body.appendChild(back);

                var wrap = document.createElement('div');
                body.appendChild(wrap);

                // ใช้ jQuery ใส่เนื้อหา เพราะ script ที่ Select2 ฝากมากับ renderAjax
                // จะไม่ทำงานถ้าใส่ด้วย innerHTML เฉย ๆ
                if (window.jQuery) {
                    jQuery(wrap).html(data.content);
                } else {
                    wrap.innerHTML = data.content;
                }
                modal.classList.remove('task-modal-editing');
            })
            .catch(function () {
                restorePrevious();
                body.insertAdjacentHTML('afterbegin', '<div class="alert alert-danger py-2 small" role="alert">เปิดฟอร์มไม่สำเร็จ รายการเดิมยังอยู่ กรุณาลองอีกครั้ง</div>');
            });
    }

    function closeMainModal() {
        var modal = document.getElementById('main-modal');
        if (modal && window.bootstrap) {
            var inst = bootstrap.Modal.getInstance(modal);
            if (inst) { inst.hide(); }
        }
    }

    // บันทึกฟอร์มงานที่อยู่ใน modal (ทั้งเพิ่มใหม่และแก้ไข)
    // ใช้ delegation เพราะฟอร์มถูกใส่เข้ามาทีหลัง
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.classList || !form.classList.contains('task-form')) { return; }
        e.preventDefault();

        var submit = form.querySelector('button[type="submit"]');
        var msg = document.getElementById('task-form-msg');
        if (submit) { submit.disabled = true; }
        if (msg) { msg.className = 'small align-self-center text-body-secondary'; msg.textContent = 'กำลังบันทึก...'; }

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.status === 'success') {
                    afterChange();
                    closeMainModal();
                } else {
                    if (msg) { msg.className = 'small align-self-center text-danger-emphasis'; msg.textContent = data.message || 'บันทึกไม่สำเร็จ'; }
                    if (submit) { submit.disabled = false; }
                }
            })
            .catch(function () {
                if (msg) { msg.className = 'small align-self-center text-danger-emphasis'; msg.textContent = 'เชื่อมต่อไม่สำเร็จ'; }
                if (submit) { submit.disabled = false; }
            });
    });

    /**
     * เปิดรายละเอียดงานผ่านระบบ modal กลาง (.open-modal ใน erp.js)
     * สร้างลิงก์ชั่วคราวแล้วสั่งคลิก เพื่อใช้เอนจินเดิมทั้งชุดโดยไม่เขียน modal ใหม่
     */
    function openDetail(url) {
        var a = document.getElementById('task-detail-proxy');
        if (!a) {
            a = document.createElement('a');
            a.id = 'task-detail-proxy';
            a.className = 'open-modal d-none';
            a.setAttribute('data-size', 'modal-md');
            document.body.appendChild(a);
        }
        a.setAttribute('href', url);
        if (window.jQuery) {
            jQuery(a).trigger('click');
        } else {
            a.click();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('task-calendar');
        if (!el || typeof FullCalendar === 'undefined') { return; }

        calendar = new FullCalendar.Calendar(el, {
            locale: 'th',
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: false,
            firstDay: 0,
            dayMaxEvents: 3,
            eventSources: [{
                url: EVENTS_URL,
                method: 'GET',
                extraParams: function () { return { 'emp': selectedEmpIds().join(',') }; }
            }],
            datesSet: function (info) {
                var d = info.view.currentStart;
                document.getElementById('cal-title').textContent =
                    MONTHS[d.getMonth()] + ' ' + (d.getFullYear() + 543);
                syncSelectedDay();
            },
            dateClick: function (info) {
                activeDate = info.dateStr.substring(0, 10);
                syncSelectedDay();
                reloadList();
            },
            // คลิกงานแล้วเปิด popup ผ่าน modal กลางของโปรเจกต์ ไม่เปลี่ยนหน้า
            // ชิปบนปฏิทินเป็นจำนวนงาน คลิกแล้วเปิดรายการของวันนั้น ไม่ใช่รายละเอียดงานเดียว
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                var props = info.event.extendedProps;
                if (props.dayUrl) {
                    activeDate = props.date;
                    syncSelectedDay();
                    reloadList();
                    openDetail(props.dayUrl);
                }
            }
        });
        calendar.render();

        document.getElementById('cal-today').addEventListener('click', function () { calendar.today(); });
        document.getElementById('cal-prev').addEventListener('click', function () { calendar.prev(); });
        document.getElementById('cal-next').addEventListener('click', function () { calendar.next(); });

        // ตัวกรองรายชื่อทีมงาน
        document.querySelectorAll('.task-person-check').forEach(function (cb) {
            cb.addEventListener('change', function () {
                if (!selectedEmpIds().length) { cb.checked = true; return; }
                activeDate = null;
                calendar.refetchEvents();
                reloadList();
            });
        });

        var search = document.getElementById('task-people-search');
        if (search) {
            search.addEventListener('input', function () {
                var q = search.value.trim().toLowerCase();
                var shown = 0;
                document.querySelectorAll('.task-person').forEach(function (row) {
                    var hit = !q || (row.dataset.name || '').indexOf(q) !== -1;
                    row.classList.toggle('d-none', !hit);
                    if (hit) { shown++; }
                });
                document.getElementById('task-people-empty').classList.toggle('d-none', shown > 0);
            });
        }

        var onlyMe = document.getElementById('task-people-only-me');
        if (onlyMe) {
            onlyMe.addEventListener('click', function () {
                var boxes = document.querySelectorAll('.task-person-check');
                boxes.forEach(function (cb, i) { cb.checked = (i === 0); });
                activeDate = null;
                calendar.refetchEvents();
                reloadList();
            });
        }

        // ซ่อน/แสดงแผงรายการงาน
        var toggle = document.getElementById('task-panel-toggle');
        var panelCol = document.getElementById('task-panel-col');
        var calCol = document.getElementById('task-calendar-col');
        toggle.addEventListener('click', function () {
            var hidden = panelCol.classList.toggle('d-none');
            toggle.setAttribute('aria-expanded', hidden ? 'false' : 'true');
            document.getElementById('task-panel-toggle-label').textContent =
                hidden ? 'แสดงรายการงาน' : 'ซ่อนรายการงาน';
            calCol.classList.toggle('col-lg-7', !hidden);
            calCol.classList.toggle('col-lg-10', hidden);
            setTimeout(function () { calendar.updateSize(); }, 50);
        });

        bindList();
    });
})();
JS;
$this->registerJs($js, View::POS_END);
