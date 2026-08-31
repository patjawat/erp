<?php

use app\components\AppHelper;
use app\modules\task\models\Task;
use app\widgets\datepicker\DatepickerThai;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * ฟอร์มเพิ่มงานเองจากหน้าปฏิทิน
 *
 * ปลายทางเลือกได้ทั้งโรงพยาบาลแบบเดียวกับที่สารบรรณส่งหนังสือ
 * แต่ละหน่วยเลือกได้สองแบบ: ส่งถึงหน่วยงาน (ใครก็ทำได้)
 * หรือระบุตัวคน (ข้ามหน่วยต้องมีสิทธิ์ taskAssignCrossUnit)
 *
 * เลือกได้หลายปลายทาง แต่ละอันกลายเป็นงานหนึ่งชิ้น
 * ตามกติกา 1 งาน = 1 ผู้รับผิดชอบ จะได้รู้ชัดว่าใครรับผิดชอบอะไร
 *
 * @var yii\web\View $this
 * @var app\modules\hr\models\Employees $me
 * @var array $groups หน่วยงานทั้งหมดพร้อมรายชื่อคน หน่วยของตัวเองอยู่บนสุด
 * @var bool $canCrossUnit ระบุตัวคนข้ามหน่วยได้หรือไม่
 * @var string|null $date วันที่ตั้งต้น (มาจากการกดเพิ่มงานบนวันใดวันหนึ่ง)
 */
// ปุ่มลัดต้องใส่ค่าเป็น วว/ดด/พ.ศ. ให้ตรงกับรูปแบบที่ DatepickerThai ใช้
$quickDates = [
    'วันนี้' => AppHelper::convertToThai(date('Y-m-d')),
    'พรุ่งนี้' => AppHelper::convertToThai(date('Y-m-d', strtotime('+1 day'))),
    'อีก 3 วัน' => AppHelper::convertToThai(date('Y-m-d', strtotime('+3 days'))),
    'สัปดาห์หน้า' => AppHelper::convertToThai(date('Y-m-d', strtotime('+7 days'))),
];
$defaultDate = AppHelper::convertToThai($date ?: date('Y-m-d', strtotime('+7 days')));

// จัดข้อมูลเป็น optgroup ตามหน่วยงาน และล็อกตัวเลือกที่ไม่มีสิทธิ์เลือก
$targetData = [];
$targetOptions = [];
$usedLabels = [];
foreach ($groups as $group) {
    $unit = $group['unit'];
    $label = $unit->name . ($group['inScope'] ? '' : ' (ข้ามหน่วยงาน)');
    if (isset($usedLabels[$label])) {
        $label .= ' #' . $unit->id;
    }
    $usedLabels[$label] = true;

    $items = ['unit:' . (int) $unit->id => 'ส่งถึงหน่วยงาน — ให้หัวหน้าจ่ายงานเอง'];
    foreach ($group['people'] as $person) {
        $key = 'emp:' . (int) $person->id;
        $isMe = (int) $person->id === (int) $me->id;
        $items[$key] = trim($person->fname . ' ' . $person->lname) . ($isMe ? ' (ฉัน)' : '');
        if (!$group['inScope'] && !$canCrossUnit) {
            $targetOptions[$key] = ['disabled' => true];
        }
    }
    $targetData[$label] = $items;
}
?>
<form class="task-form" method="post" action="<?= Url::to(['/task/default/create']) ?>">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <div class="mb-3">
        <label class="form-label" for="task-c-title">ชื่องาน</label>
        <input type="text" class="form-control" id="task-c-title" name="title" maxlength="255" required
               placeholder="เช่น ตอบหนังสือ สสจ. เรื่องรายงานประจำเดือน">
    </div>

    <div class="mb-3">
        <label class="form-label" for="task-c-detail">รายละเอียด</label>
        <textarea class="form-control" id="task-c-detail" name="detail" rows="2" placeholder="ไม่บังคับ"></textarea>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6">
            <label class="form-label" for="task-f-due">กำหนดเสร็จ</label>
            <div class="btn-group btn-group-sm flex-wrap mb-2" role="group" aria-label="ปุ่มลัดกำหนดเสร็จ">
                <?php foreach ($quickDates as $label => $value): ?>
                    <button type="button" class="btn btn-outline-secondary task-quick-date"
                            data-date="<?= $value ?>"><?= Html::encode($label) ?></button>
                <?php endforeach ?>
            </div>
            <?= DatepickerThai::widget([
                'name' => 'due_date',
                'value' => $defaultDate,
                'options' => [
                    'id' => 'task-c-due',
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                    'placeholder' => 'วว/ดด/พ.ศ.',
                ],
            ]) ?>
        </div>

        <div class="col-12 col-sm-6">
            <span class="form-label d-block">ความสำคัญ</span>
            <div class="btn-group" role="group" aria-label="ความสำคัญ">
                <input type="radio" class="btn-check" name="priority" id="task-c-normal"
                       value="<?= Task::PRIORITY_NORMAL ?>" checked>
                <label class="btn btn-outline-secondary" for="task-c-normal">ปกติ</label>

                <input type="radio" class="btn-check" name="priority" id="task-c-urgent"
                       value="<?= Task::PRIORITY_URGENT ?>">
                <label class="btn btn-outline-danger" for="task-c-urgent">ด่วน</label>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label" for="task-c-target">มอบหมายให้</label>
        <?= Select2::widget([
            'name' => 'targets',
            'value' => ['emp:' . (int) $me->id],
            'data' => $targetData,
            'theme' => Select2::THEME_KRAJEE_BS5,
            'options' => [
                'id' => 'task-c-target',
                'placeholder' => 'พิมพ์ชื่อคนหรือหน่วยงานเพื่อค้นหา',
                'multiple' => true,
                'options' => $targetOptions,
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '100%',
                // จำเป็นเมื่ออยู่ใน modal ไม่งั้นกล่องค้นหาจะถูก modal บัง
                'dropdownParent' => '#main-modal',
            ],
        ]) ?>
        <div class="form-text">
            เลือกได้หลายราย — <strong>แต่ละรายจะได้งานของตัวเองหนึ่งชิ้น</strong> จะได้รู้ว่าใครรับผิดชอบอะไร<br>
            <?php if ($canCrossUnit): ?>
                ระบุตัวคนได้ทุกหน่วยงาน
            <?php else: ?>
                หน่วยงานอื่นเลือกได้เฉพาะ "ส่งถึงหน่วยงาน" — คนในหน่วยนั้นจะถูกจ่ายงานโดยหัวหน้าของเขา
            <?php endif ?>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 align-items-center">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>เพิ่มงาน
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <span class="small" id="task-form-msg" role="status" aria-live="polite"></span>
    </div>
</form>

<script>
// widget DatepickerThai ใช้ registerJs ซึ่งไม่ทำงานตอน inject ผ่าน AJAX จึงต้อง init เอง
(function () {
    if (typeof thaiDatepicker === 'function') {
        try { thaiDatepicker('#task-c-due'); } catch (e) {}
    }
})();
</script>
