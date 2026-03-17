<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;
use app\widgets\datepicker\DatepickerThai;

/** @var array $draft */
/** @var array $provinces */

$this->title = 'บันทึกข้อความขอไปราชการ';
$this->params['breadcrumbs'] = [];
$members = $draft['members'] ?? [];
?>
<div class="travel-request-wizard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="<?= Url::to(['/development/default/dashboard']) ?>" class="btn btn-link text-decoration-none text-body p-0">
            <i class="bi bi-arrow-left me-1"></i><?= Html::encode($this->title) ?>
        </a>
        <span class="text-muted small">Step 1 of 4</span>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <h5 class="fw-bold text-body mb-4">1. ข้อมูลการเดินทาง</h5>

            <?php $form = ActiveForm::begin([
                'id' => 'travel-step1-form',
                'action' => ['index'],
                'method' => 'post',
                'fieldConfig' => [
                    'labelOptions' => ['class' => 'form-label'],
                    'inputOptions' => ['class' => 'form-control'],
                ],
            ]); ?>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">ตั้งแต่วันที่</label>
                    <?= DatepickerThai::widget([
                        'name' => 'date_start',
                        'value' => $draft['date_start'] ?? '',
                        'options' => ['id' => 'travel-date_start', 'class' => 'form-control', 'placeholder' => 'ว/ด/พ.ศ.'],
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ถึงวันที่</label>
                    <?= DatepickerThai::widget([
                        'name' => 'date_end',
                        'value' => $draft['date_end'] ?? '',
                        'options' => ['id' => 'travel-date_end', 'class' => 'form-control', 'placeholder' => 'ว/ด/พ.ศ.'],
                    ]) ?>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <p id="travel-total-days" class="text-primary mb-0 small"></p>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">ไปราชการเพื่อ (เหตุผล)</label>
                <?= Html::textInput('topic', $draft['topic'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => 'ระบุเหตุผล',
                ]) ?>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">ณ สถานที่</label>
                    <?= Html::textInput('location', $draft['location'] ?? '', [
                        'class' => 'form-control',
                        'placeholder' => 'ระบุสถานที่',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">จังหวัด/ต่างประเทศ</label>
                    <?= Html::dropDownList('province_name', $draft['province_name'] ?? '', ['' => '-- เลือกจังหวัด --'] + $provinces, [
                        'class' => 'form-select',
                    ]) ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">ผู้ร่วมเดินทาง (ถ้ามี)</label>
                <div id="member-tags" class="d-flex flex-wrap gap-2 mb-2">
                    <?php foreach ($members as $i => $m): ?>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">
                        <?= Html::encode($m['label'] ?? $m['emp_id'] ?? '') ?>
                        <button type="button" class="btn btn-link p-0 text-danger text-decoration-none remove-member" data-index="<?= $i ?>">&times;</button>
                    </span>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <input type="text" id="member-search" class="form-control flex-grow-1" style="max-width: 320px;" placeholder="พิมพ์ชื่อเพื่อค้นหา หรือ เพิ่มคนนอก...">
                    <button type="button" class="btn btn-outline-secondary" id="btn-add-outsider">เพิ่มคนนอก</button>
                </div>
                <input type="hidden" name="members_json" id="members-json" value="<?= Html::encode(json_encode($members)) ?>">
            </div>

            <div class="d-flex justify-content-end pt-2">
                <?= Html::submitButton('ถัดไป <i class="bi bi-arrow-right ms-1"></i>', ['class' => 'btn btn-primary rounded-3 px-4']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
(function() {
    var members = JSON.parse(document.getElementById('members-json').value || '[]');
    function renderTags() {
        var html = '';
        members.forEach(function(m, i) {
            var label = m.label || m.emp_id || '';
            html += '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">' +
                (label.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')) + ' <button type="button" class="btn btn-link p-0 text-danger text-decoration-none remove-member" data-index="' + i + '">&times;</button></span>';
        });
        document.getElementById('member-tags').innerHTML = html || '';
        document.getElementById('members-json').value = JSON.stringify(members);
    }
    document.getElementById('member-tags').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-member')) {
            var i = parseInt(e.target.getAttribute('data-index'), 10);
            members.splice(i, 1);
            renderTags();
        }
    });
    document.getElementById('btn-add-outsider').onclick = function() {
        var q = document.getElementById('member-search').value.trim();
        if (!q) return;
        members.push({ label: q });
        document.getElementById('member-search').value = '';
        renderTags();
    };
    document.getElementById('member-search').onkeydown = function(e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btn-add-outsider').click(); }
    };
    document.getElementById('travel-step1-form').onsubmit = function() {
        document.getElementById('members-json').value = JSON.stringify(members);
    };
    function calcDays() {
        var d1 = document.getElementById('travel-date_start').value, d2 = document.getElementById('travel-date_end').value;
        if (!d1 || !d2) { document.getElementById('travel-total-days').textContent = ''; return; }
        var p = function(s) { var t = s.split('/'); if (t.length !== 3) return null; return new Date(parseInt(t[2],10)-543, parseInt(t[1],10)-1, parseInt(t[0],10)); };
        var a = p(d1), b = p(d2);
        if (!a || !b) { document.getElementById('travel-total-days').textContent = ''; return; }
        var days = Math.round((b - a) / 86400000) + 1;
        document.getElementById('travel-total-days').textContent = 'รวมเวลา ' + (days > 0 ? days : 0) + ' วัน';
    }
    document.getElementById('travel-date_start').addEventListener('change', calcDays);
    document.getElementById('travel-date_end').addEventListener('change', calcDays);
    calcDays();
})();
JS
);
?>
