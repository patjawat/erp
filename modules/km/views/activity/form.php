<?php

use app\components\AppHelper;
use app\widgets\datepicker\DatepickerThai;
use app\modules\km\models\KmActivity;
use kartik\time\TimePicker;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\km\models\KmActivity $model */
/** @var app\modules\km\models\KmCategory[] $categories */
/** @var array<int,array{id:int,name:string}> $units */
/** @var int[] $years */

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'เพิ่มกิจกรรม' : 'แก้ไขกิจกรรม';
$dateThai = $model->activity_date ? AppHelper::DateFormDb($model->activity_date) : '';
$err = $model->getErrors();

/** ป้าย error ใต้ช่อง (ถ้ามี) */
$feedback = static function (array $err, string $attr): string {
    if (empty($err[$attr])) {
        return '';
    }
    return '<div class="text-danger small mt-1">' . Html::encode(implode(' ', $err[$attr])) . '</div>';
};
$invalid = static fn (array $err, string $attr): string => empty($err[$attr]) ? '' : ' is-invalid';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>คลังกิจกรรม KM<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/km/menu', ['active' => 'activity']) ?></div>

    <div class="card border shadow-sm">
        <div class="card-body">
            <h1 class="h5 fw-semibold mb-3"><i class="bi bi-<?= $isNew ? 'plus-lg' : 'pencil' ?> me-1"></i> <?= Html::encode($this->title) ?></h1>

            <?php if ($err): ?>
                <div class="alert alert-danger py-2 small mb-3"><i class="bi bi-exclamation-triangle me-1"></i>กรุณาตรวจสอบข้อมูลที่กรอก</div>
            <?php endif; ?>

            <?= Html::beginForm('', 'post', ['id' => 'km-activity-form']) ?>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">ชื่อกิจกรรม <span class="text-danger">*</span></label>
                        <?= Html::textInput('KmActivity[title]', $model->title, ['class' => 'form-control' . $invalid($err, 'title'), 'maxlength' => 500, 'autofocus' => $isNew]) ?>
                        <?= $feedback($err, 'title') ?>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">หมวดหมู่</label>
                        <?= Html::dropDownList('KmActivity[category_id]', $model->category_id, \app\modules\km\models\KmCategory::dropdownMap(), ['class' => 'form-select', 'prompt' => '— เลือกหมวด —']) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ปีงบประมาณ <span class="text-danger">*</span></label>
                        <?= Html::dropDownList('KmActivity[fiscal_year]', $model->fiscal_year, array_combine($years, $years), ['class' => 'form-select' . $invalid($err, 'fiscal_year')]) ?>
                        <?= $feedback($err, 'fiscal_year') ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">หน่วยงานเจ้าภาพ</label>
                        <?= Html::dropDownList('KmActivity[owner_unit_id]', $model->owner_unit_id, array_column($units, 'name', 'id'), ['class' => 'form-select', 'prompt' => '— เลือกหน่วยงาน —']) ?>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">วันที่จัด</label>
                        <?= DatepickerThai::widget(['name' => 'activity_date_thai', 'value' => $dateThai]) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">เวลาเริ่ม</label>
                        <?= TimePicker::widget([
                            'name' => 'KmActivity[start_time]',
                            'value' => $model->start_time ? substr($model->start_time, 0, 5) : '',
                            'pluginOptions' => ['showMeridian' => false],
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">เวลาสิ้นสุด</label>
                        <?= TimePicker::widget([
                            'name' => 'KmActivity[end_time]',
                            'value' => $model->end_time ? substr($model->end_time, 0, 5) : '',
                            'pluginOptions' => ['showMeridian' => false],
                        ]) ?>
                    </div>

                    <div class="col-12">
                        <label class="form-label">สถานที่</label>
                        <?= Html::textInput('KmActivity[location]', $model->location, ['class' => 'form-control', 'maxlength' => 255]) ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label">สรุปย่อ <span class="text-body-secondary small">(แสดงบนการ์ด)</span></label>
                        <?= Html::textarea('KmActivity[summary]', $model->summary, ['class' => 'form-control', 'rows' => 2, 'data-km-rte' => '1']) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">วัตถุประสงค์</label>
                        <?= Html::textarea('KmActivity[objective]', $model->objective, ['class' => 'form-control', 'rows' => 4, 'data-km-rte' => '1']) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">รายละเอียด / ถอดบทเรียน</label>
                        <?= Html::textarea('KmActivity[detail]', $model->detail, ['class' => 'form-control', 'rows' => 4, 'data-km-rte' => '1']) ?>
                    </div>

                    <div class="col-12">
                        <label class="form-label d-block">สถานะ</label>
                        <?php foreach (KmActivity::statusLabels() as $val => $label): ?>
                            <div class="form-check form-check-inline">
                                <?= Html::radio('KmActivity[status]', $model->status === $val, ['value' => $val, 'class' => 'form-check-input', 'id' => 'st-' . $val]) ?>
                                <label class="form-check-label" for="st-<?= $val ?>"><?= Html::encode($label) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('ยกเลิก', $isNew ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <?php if (!$isNew): ?>
        <div class="row g-3 mt-1">
            <div class="col-lg-8">
                <?= $this->render('_photos', ['activity' => $model, 'canManage' => true]) ?>
            </div>
            <div class="col-lg-4">
                <?= $this->render('_links', ['activity' => $model, 'canManage' => true]) ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-light border mt-3 small text-body-secondary">
            <i class="bi bi-info-circle me-1"></i> บันทึกกิจกรรมก่อน จึงจะแนบรูปภาพและผูกหลักฐาน (งาน/KPI/ความเสี่ยง) ได้
        </div>
    <?php endif; ?>
</div>

<?php
$rteCss = <<<'CSS'
.km-rte{border:1px solid var(--bs-border-color);border-radius:.5rem;overflow:hidden;background:var(--bs-body-bg)}
.km-rte:focus-within{border-color:var(--bs-primary);box-shadow:0 0 0 .2rem rgba(var(--bs-primary-rgb),.15)}
.km-rte__toolbar{display:flex;flex-wrap:wrap;gap:.15rem;padding:.3rem;border-bottom:1px solid var(--bs-border-color);background:var(--bs-tertiary-bg)}
.km-rte__btn{min-width:34px;height:32px;border:0;border-radius:.35rem;background:transparent;color:var(--bs-secondary-color);font-size:.95rem;line-height:1}
.km-rte__btn:hover{background:var(--bs-secondary-bg);color:var(--bs-emphasis-color)}
.km-rte__area{min-height:110px;padding:.6rem .75rem;outline:0;line-height:1.65}
.km-rte__area:empty::before{content:attr(data-placeholder);color:var(--bs-secondary-color)}
.km-rte__area ul,.km-rte__area ol{margin:0 0 .5rem 1.25rem}
.km-rte__area p:last-child{margin-bottom:0}
.km-richtext ul,.km-richtext ol{margin:0 0 .5rem 1.25rem}
.km-richtext table{border-collapse:collapse;width:100%;margin-bottom:.5rem}
.km-richtext th,.km-richtext td{border:1px solid var(--bs-border-color);padding:.35rem .5rem}
CSS;
$this->registerCss($rteCss);

$rteJs = <<<'JS'
(function(){
    var HTML_PROBE=/<(?:p|br|h4|h5|ul|ol|li|strong|em|b|i|u|s|blockquote|a|table|tr|th|td)\b[^>]*>/i;
    var TOOLS=[
        {c:'bold',i:'<b>B</b>',t:'ตัวหนา'},
        {c:'italic',i:'<i>I</i>',t:'ตัวเอียง'},
        {c:'underline',i:'<u>U</u>',t:'ขีดเส้นใต้'},
        {c:'formatBlock:h4',i:'H',t:'หัวข้อ'},
        {c:'insertUnorderedList',i:'&bull;',t:'รายการจุด'},
        {c:'insertOrderedList',i:'1.',t:'รายการเลข'},
        {c:'removeFormat',i:'✕',t:'ล้างรูปแบบ'}
    ];
    function esc(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML;}
    function enhance(ta){
        if(ta.dataset.rteReady) return; ta.dataset.rteReady='1';
        var wrap=document.createElement('div'); wrap.className='km-rte';
        var bar=document.createElement('div'); bar.className='km-rte__toolbar'; bar.setAttribute('role','toolbar');
        TOOLS.forEach(function(tool){
            var b=document.createElement('button'); b.type='button'; b.className='km-rte__btn';
            b.innerHTML=tool.i; b.title=tool.t; b.dataset.cmd=tool.c;
            bar.appendChild(b);
        });
        var area=document.createElement('div'); area.className='km-rte__area'; area.contentEditable='true';
        area.setAttribute('data-placeholder', ta.getAttribute('placeholder')||'');
        var v=ta.value||'';
        area.innerHTML = HTML_PROBE.test(v) ? v : esc(v).replace(/\n/g,'<br>');
        ta.style.display='none';
        ta.parentNode.insertBefore(wrap, ta);
        wrap.appendChild(bar); wrap.appendChild(area); wrap.appendChild(ta);
        function sync(){ ta.value = area.innerHTML.replace(/<br>\s*$/,''); }
        area.addEventListener('input', sync);
        area.addEventListener('blur', sync);
        bar.addEventListener('mousedown', function(e){
            var btn=e.target.closest('.km-rte__btn'); if(!btn) return;
            e.preventDefault(); area.focus();
            var cmd=btn.dataset.cmd;
            if(cmd.indexOf('formatBlock:')===0){ document.execCommand('formatBlock',false,cmd.split(':')[1]); }
            else { document.execCommand(cmd,false,null); }
            sync();
        });
        var form=ta.closest('form'); if(form){ form.addEventListener('submit', sync); }
    }
    document.querySelectorAll('textarea[data-km-rte]').forEach(enhance);
})();
JS;
$this->registerJs($rteJs, \yii\web\View::POS_END);
?>
