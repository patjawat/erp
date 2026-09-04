<?php

use app\modules\finance\components\FinanceLoanDocumentCatalog;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $documentTypes */
/** @var array $loanOptions */
/** @var int|null $defaultLoanId */

$this->title = 'พิมพ์เอกสารเงินยืม';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนเงินยืม', 'url' => ['/finance/loan']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-printer" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>สร้างจากทะเบียน ตรวจแก้บนจอ แล้วสั่งพิมพ์เป็น PDF<?php $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/finance/menu', ['active' => 'loan']); $this->endBlock();
?>

<div class="card border shadow-sm">
    <div class="card-header bg-body-tertiary py-3">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-xl-6">
                <h5 class="mb-1">ชุดเอกสารเงินยืม</h5>
                <p class="text-body-secondary mb-0">เลือกใบยืมหนึ่งใบ แล้วเปิดแม่แบบที่ต้องการ</p>
            </div>
            <div class="col-12 col-xl-6">
                <label class="form-label fw-medium" for="loan-document-source">ใบยืมเงิน</label>
                <?= Select2::widget([
                    'name' => 'loan_id',
                    'value' => $defaultLoanId,
                    'data' => $loanOptions,
                    'options' => ['id' => 'loan-document-source', 'placeholder' => 'ค้นหาเลขที่สัญญา ผู้ยืม หรือวัตถุประสงค์'],
                    'pluginOptions' => ['allowClear' => false, 'width' => '100%'],
                ]) ?>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!$loanOptions): ?>
            <p class="text-center text-body-secondary py-5 mb-0">
                ยังไม่มีใบยืมในทะเบียน <?= Html::a('เพิ่มใบยืมก่อน', ['/finance/loan/create']) ?>
            </p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="ps-3">แม่แบบเอกสาร</th>
                        <th scope="col">การใช้งาน</th>
                        <th scope="col" class="text-end pe-3">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($documentTypes as $type): ?>
                    <?php
                    $perLetter = !in_array($type['code'], $selectableCodes, true);
                    $ready = $type['status'] === FinanceLoanDocumentCatalog::STATUS_SOURCE_READY && !$perLetter;
                    $baseUrl = Url::to(['/finance/loan-document/open', 'code' => $type['code']]);
                    $openUrl = $ready && $defaultLoanId !== null ? $baseUrl . '&loan_id=' . $defaultLoanId : '#';
                    ?>
                    <tr>
                        <td class="ps-3">
                            <div class="fw-medium"><?= Html::encode($type['name']) ?></div>
                            <div class="small text-body-secondary">
                                <?= Html::encode($type['orientation']) ?> · <?= (int) $type['pages'] ?> หน้า
                            </div>
                        </td>
                        <td class="text-body-secondary"><?= Html::encode($type['description']) ?></td>
                        <td class="text-end pe-3">
                            <?php if ($perLetter): ?>
                                <span class="badge bg-info-subtle text-info-emphasis">ออกทีละฉบับ</span>
                                <?= $defaultLoanId
                                    ? Html::a('<i class="bi bi-box-arrow-up-right me-1"></i>ไปหน้าใบยืม', ['/finance/loan/view', 'id' => $defaultLoanId], ['class' => 'btn btn-sm btn-outline-secondary ms-2'])
                                    : '' ?>
                            <?php else: ?>
                            <span class="badge <?= $ready ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?>">
                                <?= Html::encode(FinanceLoanDocumentCatalog::statusLabel($type['status'])) ?>
                            </span>
                            <?php endif; ?>
                            <?php if ($ready): ?>
                                <?= Html::a(
                                    '<i class="bi bi-pencil-square me-1" aria-hidden="true"></i>เปิดเอกสาร',
                                    $openUrl,
                                    [
                                        'class' => 'btn btn-sm btn-outline-primary ms-2 js-open-loan-document'
                                            . ($defaultLoanId !== null ? ' open-modal' : ' disabled'),
                                        'data' => ['base-url' => $baseUrl, 'size' => 'modal-xl'],
                                        'aria-disabled' => $defaultLoanId !== null ? 'false' : 'true',
                                    ]
                                ) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-footer bg-body py-3 text-body-secondary small">
        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
        ข้อมูลที่ทะเบียนยังไม่เก็บ เช่น เลขที่หนังสือ วันที่ลงนาม และรายชื่อคณะเดินทาง จะเว้นเป็นจุดไข่ปลาให้พิมพ์เองบนหน้าจอก่อนสั่งพิมพ์
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
(function () {
    var source = document.getElementById('loan-document-source');
    if (!source) { return; }
    var buttons = document.querySelectorAll('.js-open-loan-document');

    function sync() {
        var id = source.value;
        buttons.forEach(function (button) {
            if (!id) {
                button.href = '#';
                button.classList.add('disabled');
                button.classList.remove('open-modal');
                button.setAttribute('aria-disabled', 'true');
                return;
            }
            var separator = button.dataset.baseUrl.indexOf('?') === -1 ? '?' : '&';
            button.href = button.dataset.baseUrl + separator + 'loan_id=' + encodeURIComponent(id);
            button.classList.remove('disabled');
            button.classList.add('open-modal');
            button.setAttribute('aria-disabled', 'false');
        });
    }

    source.addEventListener('change', sync);
    if (window.jQuery) { window.jQuery(source).on('change', sync); }
    sync();
})();
JS);
