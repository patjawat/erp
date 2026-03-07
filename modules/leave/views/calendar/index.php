<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\hr\models\Organization;

/** @var yii\web\View $this */
/** @var int   $myDeptId */
/** @var array $leaveTypes */

$this->title = 'ปฏิทินการลา';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$eventsUrl    = Url::to(['/leave/calendar/events']);
$createUrl    = Url::to(['/leave/leave/create']);
$eventsUrlJs  = json_encode($eventsUrl);
$createUrlJs  = json_encode($createUrl);
$myDeptIdJs   = json_encode($myDeptId ?: 0);

$this->registerCss('
/* FullCalendar overrides */
.fc { font-family: inherit !important; }
.fc .fc-col-header-cell-cushion { font-size: .75rem !important; font-weight: 700 !important; color: #495057 !important; text-decoration: none !important; }
.fc .fc-daygrid-day-number { font-size: .8rem !important; color: #495057 !important; font-weight: 600 !important; text-decoration: none !important; }
.fc .fc-day-today { background: rgba(var(--bs-primary-rgb), .04) !important; }
.fc .fc-event { border-radius: 4px !important; font-size: .72rem !important; padding: 1px 5px !important; cursor: pointer !important; border: none !important; }
.fc .fc-event-title { font-weight: 600 !important; overflow: hidden; text-overflow: ellipsis; }
.fc td, .fc th { border-color: #dee2e6 !important; }
.fc .fc-scrollgrid { border: none !important; }
.fc .fc-scrollgrid-section > td { border: none !important; }
.fc .fc-daygrid-day-frame { min-height: 72px; }
.fc .fc-more-link { font-size: .7rem; font-weight: 700; text-decoration: none; }
.fc .fc-list-day-cushion { background: #f8f9fa !important; font-size: .78rem !important; }
.fc .fc-list-event-title a { text-decoration: none; color: inherit; }
/* view tab active */
.cal-view-tab.active { background: #fff !important; color: var(--bs-primary) !important; }
.cal-view-tab { color: rgba(255,255,255,.85); background: transparent; border: none; border-radius: 6px; padding: 3px 12px; font-size: .78rem; font-weight: 600; cursor: pointer; }
.cal-view-tab:hover:not(.active) { background: rgba(255,255,255,.15); color: #fff; }
');
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-calendar3"></i>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/leave/views/menu', ['active' => 'calendar']) ?>
<?php $this->endBlock(); ?>

<div class="row g-3">

    <!-- ── Sidebar ── -->
    <div class="col-md-3">

        <!-- เลือกหน่วยงาน -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary-gradient py-2 px-3">
                <h6 class="text-white mb-0 small fw-normal"><i class="bi bi-diagram-3 me-1"></i> เลือกหน่วยงาน</h6>
            </div>
            <div class="card-body p-2">
                <div class="d-flex gap-2 mb-2">
                    <button id="btn-select-all" class="btn btn-outline-secondary flex-fill" style="font-size:.8rem;padding:4px 0;">ทั้งหมด</button>
                    <button id="btn-select-mine" class="btn btn-outline-primary flex-fill" style="font-size:.8rem;padding:4px 0;"><i class="bi bi-person-fill me-1"></i>ของฉัน</button>
                </div>
                <?= \kartik\tree\TreeViewInput::widget([
                    'query'          => Organization::find()->addOrderBy('root, lft'),
                    'value'          => $myDeptId ?: '',
                    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                    'rootOptions'    => ['label' => '<i class="bi bi-building"></i>'],
                    'fontAwesome'    => false,
                    'multiple'       => false,
                    'name'           => 'dept_filter',
                    'options'        => [
                        'id'    => 'departmentFilter',
                        'class' => '',
                    ],
                    'pluginOptions'  => [
                        'allowClear' => true,
                    ],
                ]) ?>
                <div class="mt-2 small text-muted px-1">
                    กำลังแสดง: <span class="fw-bold text-primary" id="selected-dept-name">ทั้งหมด</span>
                </div>
            </div>
        </div>

        <!-- สถิติ -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary-gradient py-2 px-3">
                <h6 class="text-white mb-0 small fw-normal"><i class="bi bi-bar-chart me-1"></i> สถิติการลา</h6>
            </div>
            <div class="card-body p-0">
                <div class="row g-0 text-center">
                    <div class="col-6 p-3 border-end border-bottom">
                        <div class="fw-bold fs-5 text-body" id="stat-total">—</div>
                        <div class="small text-muted">รายการทั้งหมด</div>
                    </div>
                    <div class="col-6 p-3 border-bottom">
                        <div class="fw-bold fs-5 text-success" id="stat-approved">—</div>
                        <div class="small text-muted">อนุมัติแล้ว</div>
                    </div>
                    <div class="col-6 p-3 border-end">
                        <div class="fw-bold fs-5 text-warning" id="stat-pending">—</div>
                        <div class="small text-muted">รออนุมัติ</div>
                    </div>
                    <div class="col-6 p-3">
                        <div class="fw-bold fs-5 text-primary" id="stat-days">—</div>
                        <div class="small text-muted">วันลาสะสม</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- สถานะ -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary-gradient py-2 px-3">
                <h6 class="text-white mb-0 small fw-normal"><i class="bi bi-circle-half me-1"></i> สถานะการลา</h6>
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle flex-shrink-0 bg-success" style="width:10px;height:10px;display:inline-block;"></span>
                        <span class="small">อนุมัติแล้ว</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle flex-shrink-0 bg-warning" style="width:10px;height:10px;display:inline-block;"></span>
                        <span class="small">รอตรวจสอบ / รอผ่าน</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle flex-shrink-0 bg-secondary" style="width:10px;height:10px;display:inline-block;"></span>
                        <span class="small">รอดำเนินการ</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Calendar ── -->
    <div class="col-md-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary-gradient py-2 px-3 d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <button id="btn-prev" class="btn btn-sm btn-light px-2"><i class="bi bi-chevron-left"></i></button>
                    <button id="btn-today" class="btn btn-sm btn-light px-3">วันนี้</button>
                    <button id="btn-next" class="btn btn-sm btn-light px-2"><i class="bi bi-chevron-right"></i></button>
                    <span class="text-white fw-bold small" id="cal-title">—</span>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="small text-white text-opacity-90 d-none d-md-inline"><i class="bi bi-hand-index me-1"></i> คลิกวันที่เพื่อขอลา</span>
                    <div class="d-flex gap-1 bg-white bg-opacity-25 rounded-2 p-1">
                        <button class="cal-view-tab active" data-view="dayGridMonth">เดือน</button>
                        <button class="cal-view-tab" data-view="listMonth">รายการ</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-3 position-relative">
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                     id="cal-loading" style="display:none !important; z-index:10; background:rgba(255,255,255,.7);">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                </div>
                <div id="leave-calendar"></div>
            </div>
        </div>
    </div>

</div>

<!-- ── Event Modal ── -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
            <div class="modal-header py-3 px-3" id="eventModalHeader">
                <div class="d-flex align-items-center gap-2 flex-fill">
                    <div class="rounded-2 d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                         id="eventModalAvatar"
                         style="width:36px;height:36px;font-size:.9rem;background:rgba(255,255,255,.25);">A</div>
                    <div>
                        <div class="fw-bold text-white lh-1 mb-1 small" id="eventModalTitle"></div>
                        <div class="text-white text-opacity-75" id="eventModalDept" style="font-size:.72rem;"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" id="eventModalBody"></div>
        </div>
    </div>
</div>

<?php
$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js',
    ['position' => \yii\web\View::POS_END]
);

$this->registerJs(<<<JS
(function() {
    var EVENTS_URL    = {$eventsUrlJs};
    var CREATE_URL   = {$createUrlJs};
    var MY_DEPT_ID   = {$myDeptIdJs};
    var selectedDeptId = MY_DEPT_ID;
    var calendar;
    var currentEvents = [];

    var MONTHS = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                  'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
    var MONTHS_SHORT = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.',
                        'ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];

    function formatThDate(d) {
        if (!d) return '—';
        return d.getDate() + ' ' + MONTHS_SHORT[d.getMonth()] + ' ' + (d.getFullYear() + 543);
    }

    function setLoading(on) {
        var overlay = document.getElementById('cal-loading');
        if (!overlay) return;
        overlay.style.setProperty('display', on ? 'flex' : 'none', 'important');
    }

    function updateStats(events) {
        var total = events.length, approved = 0, pending = 0, days = 0;
        events.forEach(function(ev) {
            var p = ev.extendedProps || {};
            if (p.status === 'Approve') approved++;
            else pending++;
            days += parseFloat(p.total_days || 0);
        });
        document.getElementById('stat-total').textContent    = total;
        document.getElementById('stat-approved').textContent = approved;
        document.getElementById('stat-pending').textContent  = pending;
        document.getElementById('stat-days').textContent     = days % 1 === 0 ? days : days.toFixed(1);
    }

    function stringToColor(str) {
        var colors = ['#0d6efd','#6610f2','#d63384','#fd7e14','#198754','#0dcaf0','#dc3545','#0d6efd'];
        var hash = 0;
        for (var i = 0; i < str.length; i++) hash = str.charCodeAt(i) + ((hash << 5) - hash);
        return colors[Math.abs(hash) % colors.length];
    }

    // ── FullCalendar ───────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('leave-calendar');
        if (!el || typeof FullCalendar === 'undefined') return;

        calendar = new FullCalendar.Calendar(el, {
            locale: 'th',
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: false,
            firstDay: 1,
            nowIndicator: true,
            dayMaxEvents: 4,
            eventSources: [{
                url: EVENTS_URL,
                method: 'GET',
                extraParams: function() { return { dept: selectedDeptId || 'all' }; },
                failure: function() { setLoading(false); }
            }],
            loading: function(isLoading) { setLoading(isLoading); },
            eventsSet: function(events) {
                currentEvents = events;
                updateStats(events);
            },
            datesSet: function(info) {
                var d = info.view.currentStart;
                var title = MONTHS[d.getMonth()] + ' ' + (d.getFullYear() + 543);
                document.getElementById('cal-title').textContent = title;
            },
            eventContent: function(arg) {
                var e = arg.event;
                var initial = (e.title || '?').charAt(0).toUpperCase();
                var html = '<div style="display:flex;align-items:center;gap:3px;overflow:hidden;width:100%;">'
                    + '<span style="width:14px;height:14px;border-radius:3px;background:rgba(255,255,255,.3);display:flex;align-items:center;justify-content:center;font-size:.58rem;font-weight:800;flex-shrink:0;">' + initial + '</span>'
                    + '<span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + e.title + '</span>'
                    + '</div>';
                return { html: html };
            },
            dateClick: function(info) {
                var dateStr = info.dateStr ? info.dateStr.substring(0, 10) : '';
                if (!dateStr || !CREATE_URL) return;
                var dateLabel = formatThDate(info.date);
                var url = CREATE_URL + (CREATE_URL.indexOf('?') >= 0 ? '&' : '?') + 'date=' + encodeURIComponent(dateStr);
                if (typeof Swal === 'undefined') {
                    window.location.href = url;
                    return;
                }
                Swal.fire({
                    title: 'ขอลา',
                    html: 'คุณต้องการ<b>ขอลาวันที่ ' + dateLabel + '</b> ใช่หรือไม่?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'ใช่ ขอลา',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d'
                }).then(function(result) {
                    if (result && result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            },
            eventClick: function(info) {
                var e = info.event;
                var p = e.extendedProps;
                var initial = (e.title || '?').charAt(0).toUpperCase();
                var color = e.backgroundColor || stringToColor(e.title);
                var statusColors = { success:'#198754', warning:'#fd7e14', secondary:'#6c757d', primary:'#0d6efd' };
                var sc = statusColors[p.status_color] || '#0d6efd';

                var header = document.getElementById('eventModalHeader');
                header.style.background = 'linear-gradient(135deg,' + color + ',' + color + 'cc)';

                var av = document.getElementById('eventModalAvatar');
                av.textContent = initial;

                document.getElementById('eventModalTitle').textContent = e.title;
                document.getElementById('eventModalDept').textContent  = p.dept_name || '';

                var endDate = e.end ? new Date(e.end.getTime() - 86400000) : e.start;
                document.getElementById('eventModalBody').innerHTML =
                    '<div class="d-flex flex-column gap-2">'
                    + row('bi-tag',       'ประเภทลา', '<strong>' + (p.leave_type || '—') + '</strong>')
                    + row('bi-calendar',  'วันที่',   formatThDate(e.start) + (endDate > e.start ? ' – ' + formatThDate(endDate) : ''))
                    + row('bi-clock',     'จำนวนวัน', (p.total_days || '—') + ' วัน')
                    + row('bi-circle-fill','สถานะ',
                        '<span style="display:inline-flex;align-items:center;gap:4px;background:' + sc + '18;color:' + sc + ';border-radius:20px;padding:2px 10px;font-weight:700;font-size:.72rem;">'
                        + '<span style="width:6px;height:6px;border-radius:50%;background:' + sc + ';display:inline-block;"></span>'
                        + (p.status_label || p.status) + '</span>')
                    + '</div>';

                new bootstrap.Modal(document.getElementById('eventModal')).show();
            }
        });
        calendar.render();

        // toolbar
        document.getElementById('btn-prev').addEventListener('click',  function(){ calendar.prev(); });
        document.getElementById('btn-next').addEventListener('click',  function(){ calendar.next(); });
        document.getElementById('btn-today').addEventListener('click', function(){ calendar.today(); });

        document.querySelectorAll('.cal-view-tab').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.cal-view-tab').forEach(function(b){ b.classList.remove('active'); });
                btn.classList.add('active');
                calendar.changeView(btn.dataset.view);
            });
        });
    });

    function row(icon, label, value) {
        return '<div class="d-flex gap-2 align-items-start py-1 border-bottom">'
            + '<span class="text-muted flex-shrink-0" style="width:72px;font-size:.72rem;padding-top:1px;"><i class="bi ' + icon + ' me-1"></i>' + label + '</span>'
            + '<span style="font-size:.78rem;font-weight:500;">' + value + '</span>'
            + '</div>';
    }

    // ── Department Filter ──────────────────────────────
    function selectDept(id, name) {
        selectedDeptId = id;
        var el = document.getElementById('selected-dept-name');
        if (el) el.textContent = name || 'ทั้งหมด';
        if (calendar) calendar.refetchEvents();
    }

    document.getElementById('btn-select-all').addEventListener('click', function() {
        selectedDeptId = 0;
        var el = document.getElementById('selected-dept-name');
        if (el) el.textContent = 'ทั้งหมด';
        if (window.jQuery) jQuery('#departmentFilter').val('').trigger('change.select2');
        if (calendar) calendar.refetchEvents();
    });

    document.getElementById('btn-select-mine').addEventListener('click', function() {
        if (!MY_DEPT_ID) return;
        if (window.jQuery) jQuery('#departmentFilter').val(MY_DEPT_ID).trigger('change.select2');
        selectDept(MY_DEPT_ID, 'หน่วยงานของฉัน');
    });

    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery) {
            jQuery(document).on('change', '#departmentFilter', function() {
                var val = parseInt(jQuery(this).val()) || 0;
                selectDept(val, val ? 'หน่วยงานที่เลือก' : 'ทั้งหมด');
            });
        }
        if (MY_DEPT_ID > 0) {
            setTimeout(function() {
                if (window.jQuery) jQuery('#departmentFilter').val(MY_DEPT_ID).trigger('change.select2');
                selectDept(MY_DEPT_ID, 'หน่วยงานของฉัน');
            }, 400);
        }
    });
})();
JS
, \yii\web\View::POS_END);
?>
