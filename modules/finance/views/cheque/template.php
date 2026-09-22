<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinanceChequeTemplate[] $templates */

$this->title = 'แม่แบบเช็ค';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'พิมพ์เช็ค', 'url' => ['/finance/cheque']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'cheque']);
$this->endBlock();
?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>ธนาคาร</th><th>ชื่อแม่แบบ</th><th class="text-center">ขนาด (มม.)</th><th class="text-center">ใช้งาน</th><th></th></tr>
            </thead>
            <tbody>
            <?php if (!$templates): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีแม่แบบเช็ค</td></tr>
            <?php else: foreach ($templates as $t): ?>
                <tr>
                    <td><?= Html::encode($t->bank_name) ?></td>
                    <td><?= Html::encode($t->name) ?></td>
                    <td class="text-center"><?= (int) $t->page_width_mm ?> × <?= (int) $t->page_height_mm ?></td>
                    <td class="text-center">
                        <?= $t->is_active ? '<span class="badge bg-success-subtle text-success-emphasis">ใช้งาน</span>' : '<span class="badge bg-secondary-subtle text-secondary-emphasis">ปิด</span>' ?>
                    </td>
                    <td class="text-end">
                        <a href="<?= Url::to(['calibrate', 'id' => $t->id]) ?>" class="btn btn-sm btn-primary"><i class="bi bi-sliders me-1"></i>ปรับตำแหน่ง</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
