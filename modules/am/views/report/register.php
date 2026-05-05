<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $thaiYear */
/** @var array $reportData */

$this->title = 'รายงานครุภัณฑ์คงเหลือประจำปี';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('<i class="fa-solid fa-file-excel me-1"></i> Export Excel', ['register', 'format' => 'xlsx', 'year' => $thaiYear], ['class' => 'btn btn-outline-success']) ?>
            <?= Html::a('<i class="fa-solid fa-file-csv me-1"></i> Export CSV', ['register', 'format' => 'csv', 'year' => $thaiYear], ['class' => 'btn btn-outline-primary']) ?>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-3 align-items-end mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-medium">ปีงบประมาณ (พ.ศ.)</label>
                    <form method="get" action="<?= Url::to(['register']) ?>" class="d-flex gap-2">
                        <input type="number" name="year" class="form-control" value="<?= Html::encode($thaiYear) ?>" min="2500" max="2999">
                        <button type="submit" class="btn btn-primary">แสดงรายงาน</button>
                    </form>
                </div>
                <div class="col-12 col-md-8">
                    <div class="alert alert-info mb-0">
                        รายงานจะสร้างชีตตามประเภทอาคาร/สิ่งปลูกสร้างที่พบในข้อมูล และคำนวณยอดคงเหลือ ณ วันที่ 30 กันยายน <?= Html::encode($thaiYear) ?>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>หมวด</th>
                            <th class="text-end">จำนวนรายการ</th>
                            <th class="text-end">ราคาทุน</th>
                            <th class="text-end">ค่าเสื่อมสะสม</th>
                            <th class="text-end">คงเหลือ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalCount = 0;
                        $totalCost = 0;
                        $totalAccumulated = 0;
                        $totalRemaining = 0;
                        ?>
                        <?php foreach ($reportData['summary'] as $row): ?>
                            <?php
                            $totalCount += (int) $row['count'];
                            $totalCost += (float) $row['cost'];
                            $totalAccumulated += (float) $row['accumulated'];
                            $totalRemaining += (float) $row['remaining'];
                            ?>
                            <tr>
                                <td><?= Html::encode($row['title']) ?></td>
                                <td class="text-end"><?= number_format((float) $row['count']) ?></td>
                                <td class="text-end"><?= number_format((float) $row['cost'], 2) ?></td>
                                <td class="text-end"><?= number_format((float) $row['accumulated'], 2) ?></td>
                                <td class="text-end"><?= number_format((float) $row['remaining'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td>รวมทั้งหมด</td>
                            <td class="text-end"><?= number_format($totalCount) ?></td>
                            <td class="text-end"><?= number_format($totalCost, 2) ?></td>
                            <td class="text-end"><?= number_format($totalAccumulated, 2) ?></td>
                            <td class="text-end"><?= number_format($totalRemaining, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
