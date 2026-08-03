<?php
use yii\helpers\Html;

$this->title = 'สรุปผลการทดลองงาน';
echo $this->render('_styles');
$round = $model->rounds[2];
$evaluations = $round->evaluations;
$managementScores = [];
foreach ($evaluations as $evaluation) {
    if (in_array($evaluation->role, ['supervisor', 'group_head'], true) && $evaluation->status === 'submitted') {
        $managementScores[(int)$evaluation->evaluator_employee_id] = (float)$evaluation->percent_score;
    }
}
$average = $managementScores ? array_sum($managementScores) / count($managementScores) : 0;
$recommendation = $average >= 60 ? 'hire' : 'no_hire';
?>
<div class="probation-shell">
    <header class="probation-head"><div><h1><?= Html::encode($this->title) ?></h1><p class="text-body-secondary"><?= Html::encode($model->employee->fullname) ?> · ผลการประเมินเดือนที่ 3</p></div></header>
    <form method="post">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
        <input type="hidden" name="recommendation" value="<?= $recommendation ?>">
        <div class="probation-card p-3 p-md-4">
            <div class="probation-summary mb-4">
                <?php foreach ($evaluations as $evaluation): ?>
                    <div><small class="text-body-secondary"><?= Html::encode($evaluation->roleLabel) ?><?= $evaluation->role === 'self' ? ' (ไม่รวมคำนวณ)' : '' ?></small><strong class="d-block fs-4 probation-numeric"><?= number_format($evaluation->percent_score, 2) ?>%</strong></div>
                <?php endforeach ?>
            </div>
            <div class="alert <?= $average >= 60 ? 'alert-success' : 'alert-danger' ?>">
                <strong>คะแนนตัดสินจากผู้บังคับบัญชา <?= number_format($average, 2) ?>%</strong> · <?= $average >= 60 ? 'ผ่านเกณฑ์ เสนอจ้างต่อ' : 'ต่ำกว่าเกณฑ์ เสนอไม่จ้างต่อ' ?>
            </div>
            <div class="mb-3"><span class="text-body-secondary d-block small">ข้อเสนอการจ้างตามเกณฑ์ร้อยละ 60</span><strong><?= $average >= 60 ? 'เสนอจ้างต่อ' : 'เสนอไม่จ้างต่อ' ?></strong></div>
            <div><label class="form-label" for="summary-comment">ความเห็นสรุป</label><textarea class="form-control" id="summary-comment" name="summary_comment" rows="5" required></textarea><div class="form-text">โปรดสรุปผลการปฏิบัติงานและเหตุผลประกอบ ก่อนเสนอให้ ผอ. รับทราบ</div></div>
            <div class="probation-form-actions"><?= Html::a('ยกเลิก', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?><button class="btn btn-primary" type="submit">บันทึกและเสนอ ผอ.</button></div>
        </div>
    </form>
</div>
