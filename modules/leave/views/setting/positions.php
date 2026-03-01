<?php
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $fields */
/** @var string $templateUrl */

$this->title = 'กำหนดตำแหน่งข้อมูลบน PDF';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'แบบฟอร์มใบลา', 'url' => ['/leave/setting/leave-template']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-geo-alt"></i> <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/leave/views/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-primary bg-opacity-10 text-primary border-0 py-3 px-4 rounded-top-3">
        <h6 class="mb-0 small fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-info-circle"></i> หน่วยเป็นมิลลิเมตร (mm) — ปรับ X, Y และขนาดตัวอักษรให้ตรงกับเทมเพลต
        </h6>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-12 col-lg-5">
                <div id="positions-alert" class="alert d-none mb-3"></div>
                <form id="positions-form">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">ฟิลด์</th>
                                <th class="fw-semibold">X</th>
                                <th class="fw-semibold">Y</th>
                                <th class="fw-semibold">ขนาดตัวอักษร</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider">
                            <?php foreach ($fields as $key => $field): ?>
                            <?php
                                $x = (float) ($field['x'] ?? 0);
                                $y = (float) ($field['y'] ?? 0);
                                $fontSize = (int) ($field['fontSize'] ?? 11);
                                $label = $field['label'] ?? $key;
                            ?>
                            <tr>
                                <td class="small"><?= Html::encode($label) ?></td>
                                <td>
                                    <input type="number" step="0.5" name="positions[<?= Html::encode($key) ?>][x]" value="<?= Html::encode($x) ?>" class="form-control form-control-position">
                                </td>
                                <td>
                                    <input type="number" step="0.5" name="positions[<?= Html::encode($key) ?>][y]" value="<?= Html::encode($y) ?>" class="form-control form-control-position">
                                </td>
                                <td>
                                    <input type="number" min="6" max="24" name="positions[<?= Html::encode($key) ?>][fontSize]" value="<?= Html::encode($fontSize) ?>" class="form-control form-control-position">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary rounded-3 px-3" id="btn-save-positions">
                            <i class="bi bi-check-lg me-1"></i> บันทึกตำแหน่ง
                        </button>
                        <?= Html::a('ย้อนกลับ', ['/leave/setting/leave-template'], ['class' => 'btn btn-outline-secondary rounded-3 ms-2']) ?>
                    </div>
                </form>
            </div>
            <div class="col-12 col-lg-7">
                <p class="small text-muted mb-2">เทมเพลตอ้างอิง</p>
                <div class="border rounded-3 overflow-hidden bg-secondary bg-opacity-10">
                    <iframe src="<?= Html::encode($templateUrl) ?>#toolbar=0" class="w-100" style="height: 520px;" title="เทมเพลต PDF"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$saveUrl = Url::to(['/leave/setting/save-positions']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$this->registerJs(<<<JS
(function() {
    var form = document.getElementById('positions-form');
    var btn = document.getElementById('btn-save-positions');
    var alertEl = document.getElementById('positions-alert');
    if (!form || !btn) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(form);
        var positions = {};
        form.querySelectorAll('input[name^="positions["]').forEach(function(input) {
            var m = input.name.match(/positions\[([^\]]+)\]\[(x|y|fontSize)\]/);
            if (m) {
                var key = m[1];
                var prop = m[2];
                if (key !== 'key' && (prop === 'x' || prop === 'y' || prop === 'fontSize')) {
                    if (!positions[key]) positions[key] = {};
                    positions[key][prop] = prop === 'fontSize' ? parseInt(input.value, 10) : parseFloat(input.value);
                }
            }
        });
        var data = {};
        data.positions = positions;
        data['{$csrfParam}'] = '{$csrfToken}';

        btn.disabled = true;
        alertEl.classList.add('d-none');
        fetch('{$saveUrl}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                alertEl.className = 'alert alert-success alert-dismissible fade show mb-3';
                alertEl.innerHTML = 'บันทึกตำแหน่งเรียบร้อย <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                alertEl.classList.remove('d-none');
            } else {
                alertEl.className = 'alert alert-danger alert-dismissible fade show mb-3';
                alertEl.innerHTML = (res.message || 'บันทึกไม่สำเร็จ') + ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                alertEl.classList.remove('d-none');
            }
        })
        .catch(function() {
            alertEl.className = 'alert alert-danger alert-dismissible fade show mb-3';
            alertEl.innerHTML = 'เกิดข้อผิดพลาด <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            alertEl.classList.remove('d-none');
        })
        .finally(function() { btn.disabled = false; });
    });
})();
JS
);
?>
