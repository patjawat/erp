<?php

use app\modules\health\models\Checkup;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\health\models\CheckupSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Checkups';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="scan-heart"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/health/menu', ['active' => 'list'])
?>
<?php $this->endBlock(); ?>


    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
<div class="table-responsive shadow-sm rounded">
    <table class="table table-hover align-middle bg-white">
        <thead class="table-dark">
            <tr>
                <th class="text-center">ปีที่ตรวจ</th>
                <th>พนักงาน (ID)</th>
                <th class="text-center">BMI / ผลประเมิน</th>
                <th>พฤติกรรมเสี่ยง (บุหรี่/สุรา)</th>
                <th>โรคประจำตัว/ความเสี่ยง</th>
                <th class="text-center">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dataProvider->getModels() as $item): ?>
                <?php 
                    $data = $item->data_json ?? [];
                    $bmiVal = $data['bmi'] ?? 0;
                    // เรียก Method จาก Model เพื่อหา Label/Color ของ BMI
                    $bmiInfo = $item->getBmiResult(); 
                ?>
                <tr>
                    <td class="text-center fw-bold">
                        <?= Html::encode($data['thai_year'] ?? '-') ?>
                    </td>
                    <td>
                        <div class="fw-bold text-primary"><?= Html::encode($item->emp_id) ?></div>
                        <small class="text-muted">วันที่ตรวจ: <?= Html::encode($data['screening_date'] ?? '-') ?></small>
                    </td>
                    <td class="text-center">
                        <div class="fs-5 fw-bold"><?= number_format((float)$bmiVal, 1) ?></div>
                        <span class="badge rounded-pill bg-<?= $bmiInfo['color'] ?? 'secondary' ?>">
                            <?= $bmiInfo['label'] ?? 'ไม่ทราบค่า' ?>
                        </span>
                    </td>
                    <td>
                        <div class="mb-1">
                            <i class="fas fa-smoking me-1"></i>
                            <?php if (($data['smoking_status'] ?? '') === 'smoke'): ?>
                                <span class="text-danger small">สูบ (<?= $data['smoke_qty'] ?? 0 ?> มวน/วัน)</span>
                            <?php else: ?>
                                <span class="text-success small">ไม่สูบ/เลิกแล้ว</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <i class="fas fa-glass-whiskey me-1"></i>
                            <?php if (($data['alcohol_status'] ?? '') === 'drink'): ?>
                                <span class="text-warning small">ดื่ม (<?= $data['alcohol_qty'] ?? 0 ?> ครั้ง/สัปดาห์)</span>
                            <?php else: ?>
                                <span class="text-success small">ไม่ดื่ม</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <?php if (!empty($data['family_history'])): ?>
                                <span class="badge bg-outline-info text-info border border-info">มีประวัติครอบครัว</span>
                            <?php endif; ?>

                            <?php if (($data['h_diabetes'] ?? 0) == 1): ?>
                                <span class="badge bg-danger">เบาหวาน</span>
                            <?php endif; ?>
                            <?php if (($data['h_hypertension'] ?? 0) == 1): ?>
                                <span class="badge bg-danger">ความดันสูง</span>
                            <?php endif; ?>
                            <?php if (($data['h_heart'] ?? 0) == 1): ?>
                                <span class="badge bg-danger">โรคหัวใจ</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="text-center">
                        <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-warning']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

