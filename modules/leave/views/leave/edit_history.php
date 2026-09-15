<?php

use app\models\Categorise;
use app\modules\leave\models\Leave;
use app\modules\leave\models\LeaveEditHistory;
use app\modules\leave\models\LeaveType;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Leave $model */
/** @var LeaveEditHistory[] $items */

$typeMap = ArrayHelper::map(LeaveType::find()->asArray()->all(), 'id', 'title');
$statusMap = ArrayHelper::map(
    Categorise::find()->where(['name' => 'leave_status'])->asArray()->all(),
    'code',
    'title'
);
$dateTypeMap = ['0' => 'เต็มวัน', '0.5' => 'ครึ่งวัน'];

/** จัดรูปแบบค่าให้อ่านง่ายตามชนิดฟิลด์ */
$fmt = function (string $field, $value) use ($typeMap, $statusMap, $dateTypeMap) {
    if ($value === null || $value === '') {
        return '<span class="text-muted">-</span>';
    }
    switch ($field) {
        case 'date_start':
        case 'date_end':
            try {
                return Html::encode(Yii::$app->thaiFormatter->asDate($value, 'long'));
            } catch (\Throwable $e) {
                return Html::encode((string) $value);
            }
        case 'date_start_type':
        case 'date_end_type':
            $key = (string) (float) $value;
            return Html::encode($dateTypeMap[$key] ?? (string) $value);
        case 'leave_type_id':
            return Html::encode($typeMap[$value] ?? (string) $value);
        case 'status':
            return Html::encode($statusMap[$value] ?? (string) $value);
        case 'total_days':
            return Html::encode((float) $value . ' วัน');
        default:
            return Html::encode((string) $value);
    }
};
?>
<div class="d-flex align-items-center gap-2 mb-3">
    <div class="p-2 bg-warning bg-opacity-10 rounded-circle text-warning"><i class="bi bi-clock-history fs-5"></i></div>
    <div>
        <h6 class="fw-bold mb-0 text-body">ประวัติการแก้ไขใบลา</h6>
        <small class="text-muted"><?= Html::encode($model->employee->fullname() ?? '-') ?> · <?= $model->showLeaveDate() ?></small>
    </div>
</div>

<?php if (empty($items)): ?>
    <div class="alert alert-light border text-center text-muted mb-0">
        <i class="bi bi-inbox me-1"></i> ยังไม่มีประวัติการแก้ไข
    </div>
<?php else: ?>
    <div class="vstack gap-3">
        <?php foreach ($items as $item): ?>
            <div class="border rounded-3 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                    <span class="fw-semibold text-body">
                        <i class="bi bi-person-circle me-1 text-primary"></i><?= Html::encode($item->editorName()) ?>
                    </span>
                    <span class="badge bg-light text-secondary border">
                        <i class="bi bi-calendar-event me-1"></i>
                        <?php try {
                            echo Html::encode(Yii::$app->thaiFormatter->asDatetime($item->edited_at, 'medium'));
                        } catch (\Throwable $e) {
                            echo Html::encode((string) $item->edited_at);
                        } ?>
                    </span>
                </div>
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:32%">รายการ</th>
                            <th style="width:34%">ค่าเดิม</th>
                            <th style="width:34%">แก้ไขเป็น</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ((array) $item->changes as $field => $pair): ?>
                            <tr>
                                <td class="text-muted"><?= Html::encode(LeaveEditHistory::FIELD_LABELS[$field] ?? $field) ?></td>
                                <td><?= $fmt($field, $pair['old'] ?? null) ?></td>
                                <td class="fw-semibold text-success"><?= $fmt($field, $pair['new'] ?? null) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
