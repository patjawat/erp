<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\pm\models\StrategyIndicatorYear;

/** @var \app\modules\pm\models\StrategyIndicator $indicator @var StrategyIndicatorYear $entry */
/** @var \app\modules\pm\models\StrategyPlan $plan @var int $year @var array $goals */
/** @var \app\modules\pm\models\StrategyIndicatorScore[] $scores */
/** @var \app\modules\pm\models\StrategyIndicatorPeriod[] $periods */
/** @var \app\modules\pm\models\StrategyIndicatorBaseline[] $baselines */

app\assets\RichTextAsset::register($this);
$rich = fn(string $label, int $rows = 4) => ['rows' => $rows, 'data-richtext' => '1', 'data-rte-label' => $label];
$this->title = ($entry->isNewRecord ? 'เพิ่ม' : 'แก้ไข') . 'รายละเอียดตัวชี้วัด (KPI Template) ปี ' . $year;
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'indicator']) ?><?php $this->endBlock();
$form = ActiveForm::begin();
?>
<div class="mb-4">
    <h2 class="h5 mb-1"><?= Html::encode($this->title) ?></h2>
    <p class="text-muted mb-0"><?= Html::encode($plan->name) ?> · รุ่น <?= (int) $plan->version ?></p>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-body-tertiary px-4 py-3">
        <div class="fw-semibold">ข้อมูลตัวชี้วัด (ใช้ร่วมกันทุกปีของแผน)</div>
        <div class="small text-muted">แก้ไขที่นี่จะมีผลกับทุกปีงบประมาณที่ใช้ตัวชี้วัดนี้</div>
    </div>
    <div class="card-body p-4"><div class="row g-3">
        <div class="col-12 col-md-4"><?= $form->field($indicator, 'code')->textInput() ?></div>
        <div class="col-12 col-md-4"><?= $form->field($indicator, 'level')->dropDownList($indicator::levelList()) ?></div>
        <div class="col-12 col-md-4"><?= $form->field($indicator, 'unit')->textInput() ?></div>
        <div class="col-12"><?= $form->field($indicator, 'goal_id')->dropDownList($goals, ['prompt' => 'ไม่ผูกเป้าประสงค์']) ?></div>
        <div class="col-12"><?= $form->field($indicator, 'name')->textarea(['rows' => 2]) ?></div>
        <div class="col-12"><?= $form->field($indicator, 'is_active')->checkbox() ?></div>
    </div></div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-body-tertiary px-4 py-3">
        <div class="fw-semibold">นิยามตัวชี้วัด ปีงบประมาณ <?= $year ?></div>
        <div class="small text-muted">นิยามผูกกับปี ปรับได้ปีต่อปี และจะถูกคัดลอกไปพร้อมชุดเมื่อขึ้นปีใหม่</div>
    </div>
    <div class="card-body p-4"><div class="row g-3">
        <div class="col-12 col-md-4"><?= $form->field($entry, 'owner_team')->textInput()->hint('เช่น PCT, ENV, HRD') ?></div>
        <div class="col-12 col-md-4"><?= $form->field($entry, 'status')->dropDownList(StrategyIndicatorYear::statusList()) ?></div>
        <div class="col-12 col-md-4"><?= $form->field($entry, 'unit_override')->textInput()->hint('เว้นว่างหากใช้หน่วยเดียวกับตัวชี้วัดหลัก') ?></div>
        <div class="col-12"><?= $form->field($entry, 'name_override')->textarea(['rows' => 2])->hint('กรอกเมื่อปีนี้ใช้ชื่อต่างจากตัวชี้วัดหลัก') ?></div>
        <div class="col-12"><?= $form->field($entry, 'target_population')->textarea($rich('ประชากรกลุ่มเป้าหมาย', 3)) ?></div>
        <div class="col-12"><?= $form->field($entry, 'definition')->textarea($rich('คำจำกัดความ')) ?></div>
        <div class="col-12"><?= $form->field($entry, 'formula')->textarea($rich('สูตรคำนวณตัวชี้วัด', 3)) ?></div>
        <div class="col-12 col-lg-6"><?= $form->field($entry, 'evaluation_method')->textarea($rich('วิธีการประเมินผล'))->hint('ใช้ปุ่มรายการเพื่อจัดเป็นข้อ') ?></div>
        <div class="col-12 col-lg-6"><?= $form->field($entry, 'data_source')->textarea($rich('แหล่งข้อมูล'))->hint('ใช้ปุ่มรายการเพื่อจัดเป็นข้อ') ?></div>
    </div></div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-body-tertiary px-4 py-3"><div class="fw-semibold">ค่าเป้าหมายและผลงานปี <?= $year ?></div></div>
    <div class="card-body p-4"><div class="row g-3">
        <div class="col-6 col-md-3"><?= $form->field($entry, 'operator')->dropDownList(StrategyIndicatorYear::operatorList(), ['prompt' => 'ไม่ระบุ']) ?></div>
        <div class="col-6 col-md-3"><?= $form->field($entry, 'target_value')->textInput(['type' => 'number', 'step' => 'any']) ?></div>
        <div class="col-6 col-md-3"><?= $form->field($entry, 'baseline_value')->textInput(['type' => 'number', 'step' => 'any']) ?></div>
        <div class="col-6 col-md-3"><?= $form->field($entry, 'actual_value')->textInput(['type' => 'number', 'step' => 'any']) ?></div>
        <div class="col-6 col-md-3"><?= $form->field($entry, 'weight')->textInput(['type' => 'number', 'step' => 'any']) ?></div>
        <div class="col-6 col-md-3"><?= $form->field($entry, 'sort_order')->textInput(['type' => 'number', 'min' => 0]) ?></div>
        <div class="col-12 col-md-6"><?= $form->field($entry, 'baseline_label')->textInput()->hint('เช่น ฐานจากค่าเฉลี่ยปี 65-67') ?></div>
    </div></div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-body-tertiary px-4 py-3">
        <div class="fw-semibold">รอบการประเมินและเกณฑ์รายรอบ</div>
        <div class="small text-muted">ติ๊กรอบที่ใช้ประเมิน แล้วกรอกเกณฑ์ของรอบนั้น</div>
    </div>
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead class="table-light"><tr><th class="ps-4">รอบ</th><th>ประเมินรอบนี้</th><th>เกณฑ์</th><th>ผลงาน</th><th class="pe-4">หมายเหตุ</th></tr></thead>
        <tbody>
        <?php foreach ($periods as $i => $period): ?>
            <tr>
                <td class="ps-4 fw-semibold text-nowrap"><?= Html::encode($period->label()) ?></td>
                <td><?= Html::activeCheckbox($period, "[$i]is_selected", ['label' => null, 'class' => 'form-check-input']) ?></td>
                <td><?= Html::activeTextInput($period, "[$i]target_value", ['class' => 'form-control form-control-sm', 'type' => 'number', 'step' => 'any']) ?></td>
                <td><?= Html::activeTextInput($period, "[$i]actual_value", ['class' => 'form-control form-control-sm', 'type' => 'number', 'step' => 'any']) ?></td>
                <td class="pe-4"><?= Html::activeTextInput($period, "[$i]note", ['class' => 'form-control form-control-sm']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-body-tertiary px-4 py-3">
        <div class="fw-semibold">เกณฑ์การให้คะแนน 5 ระดับ</div>
        <div class="small text-muted">ระบุคำอธิบายของแต่ละระดับ ช่วงค่าจะกรอกหรือไม่ก็ได้</div>
    </div>
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead class="table-light"><tr><th class="ps-4" style="width:6rem">ระดับ</th><th>เกณฑ์</th><th style="width:9rem">ค่าต่ำสุด</th><th class="pe-4" style="width:9rem">ค่าสูงสุด</th></tr></thead>
        <tbody>
        <?php foreach ($scores as $i => $score): ?>
            <tr>
                <td class="ps-4 fw-semibold">ระดับ <?= (int) $score->level ?><?= Html::activeHiddenInput($score, "[$i]level") ?></td>
                <td><?= Html::activeTextarea($score, "[$i]description", ['class' => 'form-control', 'rows' => 2, 'data-richtext' => '1', 'data-rte-label' => 'เกณฑ์ระดับ ' . (int) $score->level]) ?></td>
                <td><?= Html::activeTextInput($score, "[$i]min_value", ['class' => 'form-control form-control-sm', 'type' => 'number', 'step' => 'any']) ?></td>
                <td class="pe-4"><?= Html::activeTextInput($score, "[$i]max_value", ['class' => 'form-control form-control-sm', 'type' => 'number', 'step' => 'any']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-body-tertiary px-4 py-3">
        <div class="fw-semibold">ข้อมูลพื้นฐาน (Baseline Data)</div>
        <div class="small text-muted">ผลการดำเนินงานย้อนหลังที่ใช้อ้างอิงในการตั้งค่าเป้าหมาย</div>
    </div>
    <div class="card-body p-4"><div class="row g-3">
        <?php foreach ($baselines as $i => $baseline): ?>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold mb-1">ปี <?= Html::activeTextInput($baseline, "[$i]fiscal_year", ['class' => 'form-control form-control-sm d-inline-block', 'type' => 'number', 'style' => 'width:6rem']) ?></label>
                <?= Html::activeTextInput($baseline, "[$i]value", ['class' => 'form-control form-control-sm', 'type' => 'number', 'step' => 'any', 'placeholder' => 'ผลการดำเนินงาน']) ?>
            </div>
        <?php endforeach; ?>
        <div class="col-6 col-md-3"><?= $form->field($entry, 'baseline_average')->textInput(['type' => 'number', 'step' => 'any']) ?></div>
    </div></div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-body-tertiary px-4 py-3"><div class="fw-semibold">ผู้รับผิดชอบและหมายเหตุ</div></div>
    <div class="card-body p-4"><div class="row g-3">
        <div class="col-12 col-md-3"><?= $form->field($entry, 'supervisor_name')->textInput() ?></div>
        <div class="col-12 col-md-3"><?= $form->field($entry, 'supervisor_phone')->textInput() ?></div>
        <div class="col-12 col-md-3"><?= $form->field($entry, 'owner_name')->textInput() ?></div>
        <div class="col-12 col-md-3"><?= $form->field($entry, 'owner_phone')->textInput() ?></div>
        <div class="col-12"><?= $form->field($entry, 'note')->textarea($rich('หมายเหตุ'))->hint('ใช้ปุ่มรายการลำดับเลขเพื่อจัดเป็นข้อ') ?></div>
        <div class="col-12"><?= $form->field($entry, 'cancelled_reason')->textarea(['rows' => 2])->hint('ใช้เมื่อเลือกสถานะ "ยกเลิกในปีนี้"') ?></div>
    </div></div>
    <div class="card-footer bg-body d-flex justify-content-between p-3">
        <?= Html::a('ยกเลิก', ['index', 'type' => 'indicator', 'planId' => $plan->id, 'year' => $year], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::submitButton('บันทึก', ['class' => 'btn btn-primary']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
