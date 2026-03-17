<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\modules\amSurvey\models\AssetSurvey|null $survey */
/** @var app\modules\amSurvey\models\AssetSurvey[] $surveys */

$this->title = 'สำรวจครุภัณฑ์ (Web)';
$this->params['breadcrumbs'][] = ['label' => 'การสำรวจครุภัณฑ์', 'url' => ['/am-survey/default/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="row g-3">
        <div class="col-12">
            <h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?></h4>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">เลือกโครงการสำรวจ</label>
                            <select id="survey_id" class="form-select" required>
                                <option value="">-- เลือก --</option>
                                <?php foreach ($surveys as $s): ?>
                                    <option value="<?= $s->id ?>" <?= $survey && $survey->id == $s->id ? 'selected' : '' ?>><?= Html::encode($s->survey_name) ?> (<?= $s->survey_year ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">หมายเลขครุภัณฑ์</label>
                            <div class="input-group">
                                <input type="text" id="scanned_asset_number" class="form-control" placeholder="สแกนหรือพิมพ์หมายเลข">
                                <button type="button" id="btn-search" class="btn btn-primary">ค้นหา</button>
                            </div>
                        </div>
                    </div>

                    <div id="search-result" class="mt-3 d-none"></div>

                    <div id="form-survey" class="mt-3 d-none">
                        <hr>
                        <h6 class="mb-3">ยืนยันผลสำรวจ</h6>
                        <input type="hidden" id="asset_id">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">สถานที่ตั้ง (ที่สำรวจ)</label>
                                <input type="text" id="survey_location" class="form-control" placeholder="อาคาร/ห้อง">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">หน่วยงาน (ที่สำรวจ)</label>
                                <select id="survey_department_id" class="form-select">
                                    <option value="">-- เลือก --</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">หมายเหตุ</label>
                                <input type="text" id="remark" class="form-control" placeholder="หมายเหตุ (ถ้ามี)">
                            </div>
                            <div class="col-12">
                                <button type="button" id="btn-save" class="btn btn-success btn-lg">
                                    <i class="fa-solid fa-check me-1"></i> บันทึกผลสำรวจ
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$searchUrl = Url::to(['/am-survey/scan/search']);
$saveUrl = Url::to(['/am-survey/scan/save']);
$deptUrl = Url::to(['/am-survey/scan/departments']);
$js = <<<JS
(function(){
    function loadDepartments() {
        $.get('$deptUrl', function(data) {
            var sel = $('#survey_department_id');
            sel.find('option:not(:first)').remove();
            data.forEach(function(r) {
                sel.append($('<option>').val(r.id).text(r.name));
            });
        });
    }
    loadDepartments();

    $('#btn-search').on('click', function() {
        var q = $('#scanned_asset_number').val().trim();
        if (!q) { alert('กรุณาระบุหมายเลขครุภัณฑ์'); return; }
        $('#search-result').removeClass('d-none').html('<p class="text-muted">กำลังค้นหา...</p>');
        $.get('$searchUrl', { q: q }, function(res) {
            if (!res.ok) {
                $('#search-result').html('<div class="alert alert-warning">' + (res.message || 'ไม่พบข้อมูล') + '</div>');
                $('#form-survey').addClass('d-none');
                return;
            }
            var statusBadge = res.found
                ? '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">พบครุภัณฑ์</span>'
                : '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1">ไม่พบ</span>';
            var html = '<div class="alert alert-light border">' + statusBadge + '<br><strong>' + (res.asset_name || res.code || '-') + '</strong>';
            if (res.current_department_name) html += '<br>หน่วยงานในระบบ: ' + res.current_department_name;
            if (res.current_location) html += '<br>สถานที่ในระบบ: ' + res.current_location;
            html += '</div>';
            $('#search-result').html(html);
            $('#form-survey').removeClass('d-none');
            $('#asset_id').val(res.asset_id || '');
        }, 'json');
    });

    $('#btn-save').on('click', function() {
        var surveyId = $('#survey_id').val();
        var assetNumber = $('#scanned_asset_number').val().trim();
        if (!surveyId || !assetNumber) { alert('กรุณาเลือกโครงการและระบุหมายเลขครุภัณฑ์'); return; }
        var \$btn = $('#btn-save').prop('disabled', true);
        $.post('$saveUrl', {
            survey_id: surveyId,
            scanned_asset_number: assetNumber,
            survey_department_id: $('#survey_department_id').val() || '',
            survey_location: $('#survey_location').val() || '',
            remark: $('#remark').val() || ''
        }, function(res) {
            \$btn.prop('disabled', false);
            if (res.ok) {
                alert(res.message);
                $('#scanned_asset_number').val('').focus();
                $('#survey_location, #remark').val('');
                $('#search-result, #form-survey').addClass('d-none');
            } else {
                alert(res.message || 'เกิดข้อผิดพลาด');
            }
        }, 'json');
    });
})();
JS;
$this->registerJs($js);
?>
