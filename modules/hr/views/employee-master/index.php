<?php

use app\components\widgets\DataSummaryWidget;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */

$this->title = 'ตั้งค่าข้อมูลหลักพนักงาน';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/hr/employees']];
$this->params['breadcrumbs'][] = $this->title;

$activeTab = in_array($activeTab ?? 'type', ['type', 'group', 'position'], true) ? $activeTab : 'type';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column gap-1">
    <div class="d-inline-flex align-items-center gap-2">
        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary">HR Master</span>
        <span class="text-muted small text-uppercase">Settings</span>
    </div>
    <h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?></h4>
    <p class="text-muted mb-0">จัดการประเภทพนักงาน กลุ่มตำแหน่ง และตำแหน่งที่ใช้สรุปข้อมูลบุคลากรจากหน้าเดียว</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/hr/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>


<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3 p-lg-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
            <div>
                <h5 class="fw-semibold mb-0">เลือกหมวดข้อมูลที่ต้องการจัดการ</h5>
            </div>
            <div class="text-muted small">ปุ่มด้านล่างช่วยสลับระหว่าง master data แต่ละชุด</div>
        </div>

        <ul class="nav nav-pills gap-2 flex-wrap" id="employeeMasterTabs" role="tablist">
            <?php foreach ($masters as $key => $master): ?>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link rounded-3 <?= $activeTab === $key ? 'active' : '' ?>"
                        id="employee-master-<?= $key ?>-tab"
                        data-bs-toggle="pill"
                        data-bs-target="#employee-master-<?= $key ?>-pane"
                        type="button"
                        role="tab"
                        aria-controls="employee-master-<?= $key ?>-pane"
                        aria-selected="<?= $activeTab === $key ? 'true' : 'false' ?>"
                    >
                        <i class="fa-solid <?= Html::encode($master['icon']) ?> me-1"></i>
                        <?= Html::encode($master['label']) ?>
                        <span class="badge rounded-pill bg-white text-primary ms-1">
                            <?= (int) $master['count'] ?>
                        </span>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>


<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
            <div>
                <h5 class="fw-semibold mb-0">ค้นหาข้อมูล</h5>
            </div>
            <div class="text-muted small">ค้นหาจากชื่อรายการหรือกลุ่มตำแหน่ง และกรองตามสถานะใช้งาน</div>
        </div>

        <?= Html::beginForm(['/hr/employee-master'], 'get', ['id' => 'employee-master-filter-form']) ?>
            <?= Html::hiddenInput('tab', $activeTab, ['id' => 'employee-master-filter-tab']) ?>
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-6">
                    <label class="form-label fw-semibold">คำค้นหา</label>
                    <?= Html::textInput('q', $filters['q'] ?? '', [
                        'class' => 'form-control',
                        'placeholder' => 'ค้นหาจากชื่อรายการหรือกลุ่มตำแหน่ง',
                    ]) ?>
                </div>

                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label fw-semibold">สถานะ</label>
                    <?= Html::dropDownList('active', $filters['active'] ?? '', [
                        '' => 'ทั้งหมด',
                        '1' => 'ใช้งาน',
                        '0' => 'ปิดใช้งาน',
                    ], [
                        'class' => 'form-select',
                    ]) ?>
                </div>

                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary rounded-3 fw-semibold w-100">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> ค้นหา
                    </button>
                </div>

                <div class="col-12 col-md-auto">
                    <?= Html::a('ล้างค่า', ['/hr/employee-master', 'tab' => $activeTab], [
                        'class' => 'btn btn-outline-secondary rounded-3 fw-semibold w-100',
                    ]) ?>
                </div>
            </div>
        <?= Html::endForm() ?>
    </div>
</div>


<?php
$this->registerJs(<<<JS
(function () {
    var tabInput = document.getElementById('employee-master-filter-tab');
    if (!tabInput) {
        return;
    }

    function syncTabUrl(tabKey) {
        var url = new URL(window.location.href);
        url.searchParams.set('tab', tabKey);
        window.history.replaceState({}, '', url.toString());
    }

    function syncTabLinks(tabKey) {
        var pane = document.getElementById('employee-master-' + tabKey + '-pane');
        if (!pane) {
            return;
        }

        pane.querySelectorAll('a[href]').forEach(function (link) {
            var href = link.getAttribute('href');
            if (!href || href.indexOf('javascript:') === 0 || href.charAt(0) === '#') {
                return;
            }

            try {
                var url = new URL(href, window.location.href);
                url.searchParams.set('tab', tabKey);
                link.setAttribute('href', url.toString());
            } catch (error) {
                // Ignore malformed URLs and keep the original link intact.
            }
        });
    }

    function syncTabState(tabKey) {
        if (!tabKey) {
            return;
        }

        tabInput.value = tabKey;
        syncTabUrl(tabKey);
        syncTabLinks(tabKey);
    }

    var tabButtons = document.querySelectorAll('#employeeMasterTabs [data-bs-toggle="pill"]');
    tabButtons.forEach(function (button) {
        button.addEventListener('shown.bs.tab', function (event) {
            var target = event.target.getAttribute('data-bs-target') || '';
            var match = target.match(/employee-master-([a-z]+)-pane/);
            if (match) {
                syncTabState(match[1]);
            }
        });
    });

    $(document).on('pjax:end', '#employee-master-type-container, #employee-master-group-container, #employee-master-position-container', function () {
        syncTabState(tabInput.value);
    });

    syncTabState(tabInput.value);
})();
JS);
?>

<div class="tab-content">
    <?php foreach ($masters as $key => $master): ?>
        <?php $pjaxId = ltrim((string) $master['container'], '#'); ?>
        <div
            class="tab-pane fade <?= $activeTab === $key ? 'show active' : '' ?>"
            id="employee-master-<?= $key ?>-pane"
            role="tabpanel"
            aria-labelledby="employee-master-<?= $key ?>-tab"
            tabindex="0"
        >
            <div class="card border-0 shadow-sm rounded-4 overflow-visible">
                <div class="card-header bg-body-tertiary border-0 py-3 px-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 flex-shrink-0">
                                <i class="fa-solid <?= Html::encode($master['icon']) ?> fs-5"></i>
                            </div>
                            <div>

                                <p class="text-muted small mb-0"><?= Html::encode($master['description']) ?></p>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row flex-wrap gap-2">
                            <?= Html::a(
                                '<i class="fa-solid fa-circle-plus me-1"></i> สร้าง' . Html::encode($master['shortLabel']),
                                ['/hr/employee-master/create', 'type' => $key, 'title' => 'สร้าง' . $master['shortLabel']],
                                [
                                    'class' => 'btn btn-primary rounded-3 fw-semibold open-modal',
                                    'data' => ['size' => $master['modalSize']],
                                ]
                            ) ?>
                        </div>
                    </div>
                </div>

                <?php Pjax::begin(['id' => $pjaxId, 'timeout' => 50000]); ?>
                <div class="card-body p-0">
                    <?= $this->render('_grid', [
                        'type' => $key,
                        'dataProvider' => $master['dataProvider'],
                    ]) ?>
                </div>
                <div class="card-footer bg-body border-top py-3 px-4">
                    <?= DataSummaryWidget::widget([
                        'dataProvider' => $master['dataProvider'],
                        'pagerOptions' => [],
                    ]) ?>
                </div>
                <?php Pjax::end(); ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
