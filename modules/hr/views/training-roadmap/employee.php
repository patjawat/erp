<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\hr\models\EmployeeTrainingPlan;

$this->title = 'Training Roadmap · ' . $employee->fullname;
echo $this->render('_styles');
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$isSelfProfile = $isSelfProfile ?? false;
?>
<div class="trm-shell">
    <div class="trm-page-head">
        <div><h1>Training Roadmap</h1><p>แผนพัฒนา ความก้าวหน้า และจุดประเมินของ <?= Html::encode($employee->fullname) ?></p></div>
        <?php if ($isSelfProfile): ?><?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับข้อมูลส่วนบุคคล', ['/profile'], ['class' => 'btn btn-outline-secondary']) ?><?php endif ?>
    </div>
    <div class="trm-card">
    <?php if ($plans): foreach ($plans as $plan):
        $done = 0; foreach ($plan->results as $result) if (in_array($result->status, ['passed', 'completed'], true)) $done++;
        $total = count($plan->results);
    ?>
        <div class="trm-person-plan">
            <div class="d-flex justify-content-between gap-3 flex-wrap">
                <div><a class="trm-code" href="<?= Url::to(['plan', 'id' => $plan->id]) ?>"><?= Html::encode($plan->roadmap->code) ?></a><h2 class="h6 fw-semibold mb-1"><?= Html::encode($plan->roadmap->title) ?></h2><div class="trm-meta"><?= Html::encode($plan->start_date) ?> ถึง <?= Html::encode($plan->target_end_date ?: 'ยังไม่กำหนด') ?></div></div>
                <span class="trm-status trm-status--<?= Html::encode($plan->status) ?>"><?= Html::encode(EmployeeTrainingPlan::statusOptions()[$plan->status] ?? $plan->status) ?></span>
            </div>
            <div class="d-flex justify-content-between trm-meta mt-3 mb-1"><span>ความก้าวหน้า</span><span><?= $done ?> จาก <?= $total ?> กิจกรรม</span></div>
            <div class="trm-progress" role="progressbar" aria-valuenow="<?= (float) $plan->progress_percent ?>" aria-valuemin="0" aria-valuemax="100"><span style="width:<?= min(100, (float) $plan->progress_percent) ?>%"></span></div>
            <div class="mt-3 d-flex gap-2 flex-wrap"><?= Html::a('เปิดแผนรายบุคคล', ['plan', 'id' => $plan->id], ['class' => 'btn btn-sm btn-outline-primary']) ?><?= Html::a('<i class="bi bi-file-earmark-pdf me-1"></i>พิมพ์ PDF', ['pdf', 'id' => $plan->id], ['class' => 'btn btn-sm btn-outline-danger', 'target' => '_blank', 'data-pjax' => '0']) ?></div>
        </div>
    <?php endforeach; else: ?><div class="trm-empty"><h3>ยังไม่ได้รับมอบหมาย Training Roadmap</h3><p>เมื่อ HR หรือหัวหน้างานมอบหมาย แผนและความก้าวหน้าจะแสดงที่นี่</p></div><?php endif ?>
    </div>
</div>
