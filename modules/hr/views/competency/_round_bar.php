<?php

use yii\helpers\Html;
use app\modules\hr\models\AppraisalRound;

/** @var yii\web\View $this */
/** @var int $fiscalYear */
/** @var AppraisalRound[] $rounds */
/** @var AppraisalRound|null $round */
/** @var AppraisalRound[] $copySourceRounds */
/** @var bool $showActions ปุ่มจัดการรอบ (หน้ารายชื่อเท่านั้น) */

$showActions = $showActions ?? true;

/** ช่วงวันที่แบบสั้น เช่น "1 ต.ค. 68 – 31 มี.ค. 69" */
$formatRange = static function (AppraisalRound $item): string {
    if (!$item->start_date || !$item->end_date) {
        return 'ยังไม่กำหนดช่วงวันที่';
    }
    $thaiMonths = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $short = static function (string $date) use ($thaiMonths): string {
        [$year, $month, $day] = array_map('intval', explode('-', $date));
        return $day . ' ' . $thaiMonths[$month] . ' ' . substr((string) ($year + 543), -2);
    };
    return $short($item->start_date) . ' – ' . $short($item->end_date);
};

$statusClass = [
    AppraisalRound::STATUS_DRAFT => 'cp-round__chip--draft',
    AppraisalRound::STATUS_OPEN => 'cp-round__chip--open',
    AppraisalRound::STATUS_CLOSED => 'cp-round__chip--closed',
];
?>
<section class="cp-round">
    <div class="cp-round__tabs" role="tablist" aria-label="รอบประเมิน">
        <?php foreach ($rounds as $item): ?>
            <?php $isActive = $round && (int) $item->id === (int) $round->id ?>
            <?= Html::a(
                '<strong>รอบที่ ' . (int) $item->round_no . '</strong>'
                . '<small>' . Html::encode($formatRange($item)) . '</small>'
                . '<span class="cp-round__chip ' . ($statusClass[$item->status] ?? '') . '">'
                . Html::encode($item->getStatusLabel()) . '</span>',
                ['/hr/competency/index', 'fy' => $fiscalYear, 'rd' => $item->round_no],
                ['class' => 'cp-round__tab' . ($isActive ? ' is-active' : ''), 'data-pjax' => '0']
            ) ?>
        <?php endforeach ?>

        <?php if (count($rounds) < 2): ?>
            <?= Html::a('<i class="bi bi-plus-lg"></i> สร้างรอบ', ['/hr/competency/round', 'fy' => $fiscalYear], [
                'class' => 'cp-round__tab cp-round__tab--add open-modal', 'data-size' => 'modal-lg',
            ]) ?>
        <?php endif ?>
    </div>

    <?php if ($round && $showActions): ?>
        <div class="cp-round__actions">
            <?php if ($copySourceRounds !== []): ?>
                <?= Html::button('<i class="bi bi-files"></i> คัดลอกการกำหนดจากรอบอื่น', [
                    'class' => 'btn btn-outline-secondary btn-sm', 'id' => 'cp-copyround-open',
                ]) ?>
            <?php endif ?>
            <?= Html::a('<i class="bi bi-pencil"></i> แก้ไขรอบ', ['/hr/competency/round', 'id' => $round->id], [
                'class' => 'btn btn-outline-secondary btn-sm open-modal', 'data-size' => 'modal-lg',
            ]) ?>
            <?php if ($round->status !== AppraisalRound::STATUS_OPEN): ?>
                <?= Html::beginForm(['/hr/competency/round-status', 'id' => $round->id], 'post', ['class' => 'd-inline']) ?>
                <?= Html::hiddenInput('status', AppraisalRound::STATUS_OPEN) ?>
                <?= Html::submitButton('<i class="bi bi-play-fill"></i> เปิดรอบให้ประเมิน', ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::endForm() ?>
            <?php else: ?>
                <?= Html::beginForm(['/hr/competency/round-status', 'id' => $round->id], 'post', ['class' => 'd-inline']) ?>
                <?= Html::hiddenInput('status', AppraisalRound::STATUS_CLOSED) ?>
                <?= Html::submitButton('<i class="bi bi-lock-fill"></i> ปิดรอบ', [
                    'class' => 'btn btn-outline-danger btn-sm',
                    'data-confirm' => 'ปิดรอบแล้วผู้ประเมินจะแก้ไขคะแนนไม่ได้ ยืนยันหรือไม่',
                ]) ?>
                <?= Html::endForm() ?>
            <?php endif ?>
        </div>
    <?php endif ?>
</section>

<?php if ($round && $showActions && $copySourceRounds !== []): ?>
<div class="modal fade" id="cp-copyround-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <?= Html::beginForm(['/hr/competency/copy-round'], 'post', ['class' => 'modal-content']) ?>
            <div class="modal-header">
                <h5 class="modal-title">คัดลอกการกำหนดจากรอบอื่น</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <label class="form-label" for="cp-copyround-from">คัดลอกจาก</label>
                <?= Html::dropDownList('from', null, array_reduce($copySourceRounds,
                    static function (array $carry, AppraisalRound $item): array {
                        $carry[$item->id] = $item->getTitle();
                        return $carry;
                    }, []), ['class' => 'form-select', 'id' => 'cp-copyround-from']) ?>
                <?= Html::hiddenInput('to', $round->id) ?>
                <p class="text-muted small mt-3 mb-0">
                    คัดลอก <strong>ผู้ประเมิน</strong> และ <strong>ระดับที่คาดหวัง</strong> มาที่
                    <strong><?= Html::encode($round->getTitle()) ?></strong>
                    — คนที่กำหนดไว้แล้วในรอบนี้จะถูกข้าม ไม่เขียนทับ
                    ถ้าคัดลอกข้ามปี ระบบจับคู่สมรรถนะผ่านทะเบียนกลาง และลดระดับให้อัตโนมัติถ้าปีปลายทางมีระดับน้อยกว่า
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">คัดลอกมารอบนี้</button>
            </div>
        <?= Html::endForm() ?>
    </div>
</div>
<?php
$this->registerJs(<<<JS
\$(document).off('click.cpCopyRound').on('click.cpCopyRound', '#cp-copyround-open', function () {
    new bootstrap.Modal(document.getElementById('cp-copyround-modal')).show();
});
JS);
?>
<?php endif ?>
