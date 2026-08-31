<?php

use app\components\AppHelper;
use app\modules\task\models\Task;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * ฟอร์มสร้างงานจากหนังสือ — โหลดผ่าน AJAX เข้า offcanvas ในหน้าอ่านหนังสือ
 *
 * ทุกช่องถูกเติมล่วงหน้าจากตัวหนังสือแล้ว เป้าหมายคือผู้ใช้แค่ตรวจแล้วกดมอบหมาย
 * กำหนดเสร็จใช้ปุ่มลัดแทนปฏิทิน เพราะ doc_expire มีข้อมูลแค่ 11% ของหนังสือรับ
 * เดาไม่ได้ จึงทุ่มไปที่ทำให้กรอกเร็วแทน
 *
 * @var yii\web\View $this
 * @var app\modules\dms\models\Documents $document
 * @var array $targets
 * @var string $priority
 * @var string|null $dueDate
 * @var int $existing จำนวนงานที่เคยสร้างจากหนังสือฉบับนี้
 */
// ปุ่มลัดต้องใส่ค่าเป็น วว/ดด/พ.ศ. ให้ตรงกับรูปแบบที่ DatepickerThai ใช้
$quickDates = [
    'พรุ่งนี้' => AppHelper::convertToThai(date('Y-m-d', strtotime('+1 day'))),
    'อีก 3 วัน' => AppHelper::convertToThai(date('Y-m-d', strtotime('+3 days'))),
    'สัปดาห์หน้า' => AppHelper::convertToThai(date('Y-m-d', strtotime('+7 days'))),
    'สิ้นเดือน' => AppHelper::convertToThai(date('Y-m-t')),
];
?>
<form id="task-from-doc-form" method="post"
      action="<?= Url::to(['/task/document/create', 'id' => $document->id]) ?>"
      class="d-flex flex-column gap-3">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <?php if ($existing > 0): ?>
        <div class="alert alert-info py-2 mb-0" role="status">
            <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
            หนังสือฉบับนี้เคยสร้างงานไปแล้ว <?= (int) $existing ?> รายการ
        </div>
    <?php endif ?>

    <div>
        <label class="form-label" for="task-title">ชื่องาน</label>
        <input type="text" class="form-control" id="task-title" name="title"
               value="<?= Html::encode($document->topic) ?>" required maxlength="255">
        <div class="form-text">เติมจากชื่อเรื่องของหนังสือ แก้ได้</div>
    </div>

    <div>
        <label class="form-label" for="task-detail">รายละเอียดเพิ่มเติม</label>
        <textarea class="form-control" id="task-detail" name="detail" rows="2"
                  placeholder="ไม่บังคับ"></textarea>
    </div>

    <div>
        <span class="form-label d-block">กำหนดเสร็จ</span>
        <div class="btn-group btn-group-sm flex-wrap mb-2" role="group" aria-label="ปุ่มลัดกำหนดเสร็จ">
            <?php foreach ($quickDates as $label => $value): ?>
                <button type="button" class="btn btn-outline-secondary task-quick-date"
                        data-date="<?= $value ?>"><?= Html::encode($label) ?></button>
            <?php endforeach ?>
        </div>
        <?= DatepickerThai::widget([
            'name' => 'due_date',
            'value' => (string) $dueDate,
            'options' => [
                'id' => 'task-due-date',
                'class' => 'form-control',
                'autocomplete' => 'off',
                'placeholder' => 'วว/ดด/พ.ศ.',
            ],
        ]) ?>
        <div class="form-text">
            <?= $priority === Task::PRIORITY_URGENT
                ? 'เสนอเป็นพรุ่งนี้ เพราะหนังสือชั้นความเร็วด่วน แก้ได้'
                : 'เสนอเป็นอีก 7 วัน แก้ได้' ?>
        </div>
    </div>

    <div>
        <span class="form-label d-block">ความสำคัญ</span>
        <div class="btn-group" role="group" aria-label="ความสำคัญ">
            <input type="radio" class="btn-check" name="priority" id="priority-normal"
                   value="<?= Task::PRIORITY_NORMAL ?>" <?= $priority === Task::PRIORITY_NORMAL ? 'checked' : '' ?>>
            <label class="btn btn-outline-secondary" for="priority-normal">ปกติ</label>

            <input type="radio" class="btn-check" name="priority" id="priority-urgent"
                   value="<?= Task::PRIORITY_URGENT ?>" <?= $priority === Task::PRIORITY_URGENT ? 'checked' : '' ?>>
            <label class="btn btn-outline-danger" for="priority-urgent">ด่วน</label>
        </div>
        <?php if ($priority === Task::PRIORITY_URGENT): ?>
            <div class="form-text">ตั้งเป็นด่วนอัตโนมัติจากชั้นความเร็วของหนังสือ</div>
        <?php endif ?>
    </div>

    <div>
        <span class="form-label d-block">มอบหมายให้</span>
        <?php if (!$targets): ?>
            <div class="alert alert-warning py-2 mb-0" role="alert">
                ไม่พบหน่วยงานปลายทางของหนังสือฉบับนี้ และบัญชีของคุณยังไม่ได้ผูกกับหน่วยงาน
            </div>
        <?php endif ?>

        <?php foreach ($targets as $target): ?>
            <div class="card bg-body-tertiary border mb-2">
                <div class="card-body py-2 px-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input task-unit-check" type="checkbox"
                               name="units[]" value="<?= (int) $target['id'] ?>"
                               id="unit-<?= (int) $target['id'] ?>" checked
                               data-unit="<?= (int) $target['id'] ?>">
                        <label class="form-check-label fw-semibold" for="unit-<?= (int) $target['id'] ?>">
                            <?= Html::encode($target['name']) ?>
                        </label>
                    </div>

                    <?php if ($target['canPickPerson']): ?>
                        <select class="form-select form-select-sm"
                                name="assignees[<?= (int) $target['id'] ?>]"
                                aria-label="ผู้รับผิดชอบของ <?= Html::encode($target['name']) ?>">
                            <option value="">ส่งถึงหน่วยงาน ให้หัวหน้าจ่ายเอง</option>
                            <?php foreach ($target['members'] as $member): ?>
                                <option value="<?= (int) $member->id ?>"
                                    <?= (int) $member->id === (int) $target['leaderEmpId'] ? 'selected' : '' ?>>
                                    <?= Html::encode(trim($member->fname . ' ' . $member->lname)) ?>
                                    <?= (int) $member->id === (int) $target['leaderEmpId'] ? ' (หัวหน้าหน่วย)' : '' ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    <?php else: ?>
                        <p class="small text-body-secondary mb-0">
                            <i class="bi bi-send me-1" aria-hidden="true"></i>
                            ส่งถึงหน่วยงาน หัวหน้าหน่วยจะเป็นผู้จ่ายงานให้คนในหน่วย
                        </p>
                    <?php endif ?>
                </div>
            </div>
        <?php endforeach ?>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-grow-1" <?= $targets ? '' : 'disabled' ?>>
            <i class="bi bi-send-check me-1" aria-hidden="true"></i>มอบหมาย
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">ยกเลิก</button>
    </div>

    <div id="task-form-message" class="small" role="status" aria-live="polite"></div>
</form>
<?php // การผูก event อยู่ที่ตัวโหลดในหน้าอ่านหนังสือ เพราะ script ใน HTML ที่ inject ผ่าน innerHTML จะไม่ทำงานเอง ?>
