<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\hr\models\EmployeeTrainingPlan;

echo $this->render('_styles');
?>
<section class="trm-shell" aria-labelledby="training-roadmap-heading">
    <div class="trm-page-head">
        <div>
            <h1 id="training-roadmap-heading">Training Roadmap</h1>
            <p>แผนพัฒนา ความก้าวหน้า และจุดประเมินของคุณ</p>
        </div>
    </div>
    <div class="trm-card">
        <?php if ($plans): ?>
            <?php foreach ($plans as $plan):
                $done = 0;
                foreach ($plan->results as $result) {
                    if (in_array($result->status, ['passed', 'completed'], true)) {
                        $done++;
                    }
                }
                $total = count($plan->results);
            ?>
                <article class="trm-person-plan">
                    <div class="d-flex justify-content-between gap-3 flex-wrap">
                        <div>
                            <a class="trm-code" href="<?= Url::to(['/hr/training-roadmap/plan', 'id' => $plan->id]) ?>" data-pjax="0"><?= Html::encode($plan->roadmap->code) ?></a>
                            <h2 class="h6 fw-semibold mb-1"><?= Html::encode($plan->roadmap->title) ?></h2>
                            <div class="trm-meta"><?= Html::encode($plan->start_date) ?> ถึง <?= Html::encode($plan->target_end_date ?: 'ยังไม่กำหนด') ?></div>
                        </div>
                        <span class="trm-status trm-status--<?= Html::encode($plan->status) ?>"><?= Html::encode(EmployeeTrainingPlan::statusOptions()[$plan->status] ?? $plan->status) ?></span>
                    </div>
                    <div class="d-flex justify-content-between trm-meta mt-3 mb-1">
                        <span>ความก้าวหน้า</span>
                        <span><?= $done ?> จาก <?= $total ?> กิจกรรม</span>
                    </div>
                    <div class="trm-progress" role="progressbar" aria-label="ความก้าวหน้า <?= number_format((float) $plan->progress_percent, 0) ?> เปอร์เซ็นต์" aria-valuenow="<?= (float) $plan->progress_percent ?>" aria-valuemin="0" aria-valuemax="100">
                        <span style="width:<?= min(100, (float) $plan->progress_percent) ?>%"></span>
                    </div>
                    <div class="mt-3"><?= Html::a('ดูรายละเอียดแผน', ['/hr/training-roadmap/plan', 'id' => $plan->id], ['class' => 'btn btn-sm btn-outline-primary', 'data-pjax' => '0']) ?></div>
                </article>
            <?php endforeach ?>
        <?php else: ?>
            <div class="trm-empty">
                <h3>ยังไม่ได้รับมอบหมาย Training Roadmap</h3>
                <p>เมื่อ HR หรือหัวหน้างานมอบหมาย แผนและความก้าวหน้าจะแสดงในเมนูนี้</p>
            </div>
        <?php endif ?>
    </div>
</section>
