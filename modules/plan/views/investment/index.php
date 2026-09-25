<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use app\components\SiteHelper;
use app\modules\plan\models\PlanInvestment;

/** @var yii\web\View $this */
/** @var int $year */
/** @var string $tab */
/** @var array $groups [budget_type => PlanInvestment[]] */
/** @var int $count */
/** @var float $total */
/** @var array $bySource */
/** @var bool $hasAny */
/** @var int $prevCount */

$is3 = $tab === '3';
$years = [$year, $year + 1, $year + 2];
$this->title = $is3 ? "แผนการลงทุนด้วยเงินบำรุง 3 ปี ปีงบประมาณ {$year}–" . ($year + 2) : "แผนการลงทุนด้วยเงินบำรุง 1 ปี ปีงบประมาณ {$year}";
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน', 'url' => ['/plan/dashboard']];
$this->params['breadcrumbs'][] = 'แผนลงทุน';

$hospital = (string) (SiteHelper::getInfo()['company_name'] ?? '');
$fmt = fn ($v) => number_format((float) $v, 2);
$qfmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
$sources = PlanInvestment::sources();
$types = PlanInvestment::types();
$policies = PlanInvestment::policies();
$pct = fn ($v) => $total > 0 ? number_format($v / $total * 100, 1) . '%' : '0%';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0"><i class="bi bi-building-gear"></i>แผนการลงทุนด้วยเงินบำรุง</h4>
</div>
<div class="small text-body-secondary">แผนการลงทุนครุภัณฑ์และสิ่งก่อสร้าง ตามแบบฟอร์มระบบแผนเงินบำรุง สป.สธ. (เมนู 1.3 / 1.4)</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'investment']) ?>
<?php $this->endBlock(); ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show d-print-none"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border mb-3 d-print-none"><div class="card-body d-flex flex-wrap justify-content-between align-items-end gap-2">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <a href="<?= Url::to(['index', 'year' => $year, 'tab' => '1']) ?>" class="btn btn-sm rounded-pill <?= $is3 ? 'btn-outline-secondary' : 'btn-primary' ?>">แผนลงทุน 1 ปี</a>
        <a href="<?= Url::to(['index', 'year' => $year, 'tab' => '3']) ?>" class="btn btn-sm rounded-pill <?= $is3 ? 'btn-primary' : 'btn-outline-secondary' ?>">แผนลงทุนระยะ 3 ปี (<?= $year ?>–<?= $year + 2 ?>)</a>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-end">
        <form method="get" class="d-flex gap-2 align-items-end">
            <?= Html::hiddenInput('tab', $tab) ?>
            <div><label class="form-label mb-0 small">ปีงบเริ่มแผน (พ.ศ.)</label>
                <input type="number" class="form-control form-control-sm" name="year" value="<?= $year ?>" style="width:110px"></div>
            <button type="submit" class="btn btn-sm btn-outline-primary">ดู</button>
        </form>
        <?php if (!$hasAny && $prevCount > 0): ?>
            <?= Html::beginForm(['copy-prev', 'year' => $year], 'post', ['class' => 'd-inline']) ?>
            <button type="submit" class="btn btn-sm btn-outline-secondary" data-confirm="คัดลอกรายการต่อเนื่องจากแผน 3 ปีของปี <?= $year - 1 ?> (<?= $prevCount ?> รายการ) มาตั้งต้นแผนปีนี้?"><i class="bi bi-copy me-1"></i>ตั้งต้นจากแผนปี <?= $year - 1 ?></button>
            <?= Html::endForm() ?>
        <?php endif; ?>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i>พิมพ์</button>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#invModal" data-item=""><i class="bi bi-plus-lg me-1"></i>เพิ่มรายการลงทุน<?= $is3 ? ' 3 ปี' : ' 1 ปี' ?></button>
    </div>
</div></div>

<div class="row g-3 mb-3 d-print-none">
    <div class="col-6 col-lg-3"><div class="card border h-100"><div class="card-body">
        <div class="small text-body-secondary">วงเงินลงทุน<?= $is3 ? " {$year}–" . ($year + 2) . ' (3 ปี)' : " ปี {$year} (1 ปี)" ?></div>
        <div class="fs-4 fw-semibold text-primary"><?= $fmt($total) ?></div>
        <div class="small text-body-secondary">รวม <?= $count ?> รายการ</div>
    </div></div></div>
    <?php foreach ($sources as $sk => $sl): ?>
        <div class="col-6 col-lg-3"><div class="card border h-100"><div class="card-body">
            <div class="small text-body-secondary">จาก<?= Html::encode($sl) ?></div>
            <div class="fs-4 fw-semibold"><?= $fmt($bySource[$sk] ?? 0) ?></div>
            <div class="small text-body-secondary">สัดส่วน <?= $pct($bySource[$sk] ?? 0) ?></div>
        </div></div></div>
    <?php endforeach; ?>
</div>

<div class="card border">
    <div class="card-body">
        <div class="text-center mb-3">
            <div class="fw-semibold fs-5">แบบฟอร์ม <?= Html::encode($this->title) ?></div>
            <div class="small">ตามนโยบายการลงทุน Environment, Modernization And Smart Service : EMS</div>
            <div class="small text-body-secondary">หน่วยบริการ: <?= Html::encode($hospital ?: '...................') ?> • วงเงินลงทุน<?= $is3 ? "ปีงบประมาณ {$year}–" . ($year + 2) : "ปีงบประมาณ {$year}" ?>: <span class="fw-semibold"><?= $fmt($total) ?></span> บาท</div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0" style="font-size:.9rem">
                <thead class="table-light text-center align-middle">
                    <?php if ($is3): ?>
                        <tr>
                            <th rowspan="2" style="width:44px">ลำดับ</th>
                            <th rowspan="2">รายการ</th>
                            <th rowspan="2" style="width:70px">หน่วยนับ</th>
                            <th rowspan="2" style="width:110px">ราคาต่อหน่วย</th>
                            <?php foreach ($years as $y): ?><th colspan="2">ปีงบประมาณ <?= $y ?></th><?php endforeach; ?>
                            <th colspan="2">รวม <?= $year ?>–<?= $year + 2 ?></th>
                            <th rowspan="2" style="width:100px">แหล่งเงิน</th>
                            <th rowspan="2" style="width:170px">สอดคล้องนโยบายด้านใด</th>
                            <th rowspan="2" class="d-print-none" style="width:74px"></th>
                        </tr>
                        <tr>
                            <?php for ($i = 0; $i < 4; $i++): ?><th style="width:60px">จำนวน</th><th style="width:110px">เป็นเงิน</th><?php endfor; ?>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <th style="width:44px">ลำดับ</th>
                            <th>รายการ</th>
                            <th style="width:120px">ราคาต่อหน่วย (บาท)</th>
                            <th style="width:80px">จำนวนหน่วย</th>
                            <th style="width:130px">รวมเป็นเงิน (บาท)</th>
                            <th style="width:110px">แหล่งเงิน</th>
                            <th style="width:100px">ประเภทงบ</th>
                            <th style="width:190px">สอดคล้องนโยบายด้านใด</th>
                            <th class="d-print-none" style="width:74px"></th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                <?php
                $no = 0;
                $grand = ['q' => [0, 0, 0, 0], 'a' => [0, 0, 0, 0]];
                $cols = $is3 ? 15 : 9;
                if ($count === 0): ?>
                    <tr><td colspan="<?= $cols ?>" class="text-center text-body-secondary py-4">ยังไม่มีรายการแผนลงทุน คลิก "+ เพิ่มรายการลงทุน" เพื่อเริ่มต้นกรอกข้อมูล</td></tr>
                <?php endif;
                foreach ($groups as $type => $items):
                    if (!$items) {
                        continue;
                    }
                    $sub = ['q' => [0, 0, 0, 0], 'a' => [0, 0, 0, 0]];
                    ?>
                    <tr class="table-light"><td></td><td colspan="<?= $cols - 1 ?>" class="fw-semibold"><?= Html::encode($types[$type]) ?></td></tr>
                    <?php foreach ($items as $it):
                        $no++;
                        $data = Json::htmlEncode($it->getAttributes(['id', 'plan_year', 'budget_type', 'name', 'unit', 'unit_price', 'qty_y1', 'qty_y2', 'qty_y3', 'source', 'policy', 'note']));
                        for ($i = 1; $i <= 3; $i++) {
                            $sub['q'][$i - 1] += $it->qty($i);
                            $sub['a'][$i - 1] += $it->amount($i);
                        }
                        $sub['q'][3] += $it->totalQty();
                        $sub['a'][3] += $it->totalAmount();
                        ?>
                        <tr>
                            <td class="text-center"><?= $no ?></td>
                            <td><?= Html::encode($it->name) ?><?php if ($it->note): ?><div class="small text-body-secondary"><?= Html::encode($it->note) ?></div><?php endif; ?></td>
                            <?php if ($is3): ?>
                                <td class="text-center"><?= Html::encode((string) $it->unit) ?></td>
                                <td class="text-end"><?= $fmt($it->unit_price) ?></td>
                                <?php for ($i = 1; $i <= 3; $i++): ?>
                                    <td class="text-end"><?= $it->qty($i) ? $qfmt($it->qty($i)) : '' ?></td>
                                    <td class="text-end"><?= $it->qty($i) ? $fmt($it->amount($i)) : '' ?></td>
                                <?php endfor; ?>
                                <td class="text-end"><?= $qfmt($it->totalQty()) ?></td>
                                <td class="text-end fw-semibold"><?= $fmt($it->totalAmount()) ?></td>
                            <?php else: ?>
                                <td class="text-end"><?= $fmt($it->unit_price) ?></td>
                                <td class="text-end"><?= $qfmt($it->qty_y1) ?> <?= Html::encode((string) $it->unit) ?></td>
                                <td class="text-end fw-semibold"><?= $fmt($it->amount(1)) ?></td>
                            <?php endif; ?>
                            <td class="text-center"><?= Html::encode($sources[$it->source] ?? $it->source) ?></td>
                            <?php if (!$is3): ?><td class="text-center"><?= Html::encode($types[$it->budget_type] ?? '') ?></td><?php endif; ?>
                            <td class="small"><?= Html::encode($policies[$it->policy] ?? '') ?></td>
                            <td class="text-center text-nowrap d-print-none">
                                <button type="button" class="btn btn-sm btn-link p-0 me-2" title="แก้ไข" data-bs-toggle="modal" data-bs-target="#invModal" data-item='<?= $data ?>'><i class="bi bi-pencil-square"></i></button>
                                <?= Html::beginForm(['delete', 'id' => $it->id], 'post', ['class' => 'd-inline']) ?>
                                <?= Html::hiddenInput('tab', $tab) ?>
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="ลบ" data-confirm="ลบรายการ &quot;<?= Html::encode($it->name) ?>&quot; ?<?= $is3 ? '' : ' (ลบทั้งแผน 3 ปีของรายการนี้)' ?>"><i class="bi bi-trash"></i></button>
                                <?= Html::endForm() ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php for ($k = 0; $k < 4; $k++) {
                        $grand['q'][$k] += $sub['q'][$k];
                        $grand['a'][$k] += $sub['a'][$k];
                    } ?>
                    <tr class="fw-semibold">
                        <td></td><td class="text-end">รวม<?= Html::encode($types[$type]) ?></td>
                        <?php if ($is3): ?>
                            <td></td><td></td>
                            <?php for ($k = 0; $k < 4; $k++): ?><td class="text-end"><?= $qfmt($sub['q'][$k]) ?></td><td class="text-end"><?= $fmt($sub['a'][$k]) ?></td><?php endfor; ?>
                            <td colspan="3" class="d-print-none"></td>
                        <?php else: ?>
                            <td></td><td></td><td class="text-end"><?= $fmt($sub['a'][0]) ?></td><td colspan="4"></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-primary fw-bold">
                        <td></td><td class="text-end">รวม</td>
                        <?php if ($is3): ?>
                            <td></td><td></td>
                            <?php for ($k = 0; $k < 4; $k++): ?><td class="text-end"><?= $qfmt($grand['q'][$k]) ?></td><td class="text-end"><?= $fmt($grand['a'][$k]) ?></td><?php endfor; ?>
                            <td colspan="3"></td>
                        <?php else: ?>
                            <td></td><td></td><td class="text-end"><?= $fmt($grand['a'][0]) ?></td><td colspan="4"></td>
                        <?php endif; ?>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="row mt-4 small">
            <div class="col-md-7">
                <div class="fw-semibold mb-1">** ให้เลือกระบุนโยบายดังนี้:</div>
                <?php foreach ($policies as $pl): ?><div><?= Html::encode($pl) ?></div><?php endforeach; ?>
            </div>
            <div class="col-md-5 text-center mt-3 mt-md-0">
                <div class="mt-2">ผู้จัดทำ.......................................................</div>
                <div class="mt-4">ผู้อำนวยการ.......................................................</div>
                <div class="mt-4">นายแพทย์สาธารณสุขจังหวัด.......................................................</div>
            </div>
        </div>
    </div>
</div>

<!-- ฟอร์มเพิ่ม/แก้ไขรายการลงทุน -->
<div class="modal fade" id="invModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <?= Html::beginForm(['save'], 'post', ['id' => 'invForm']) ?>
            <?= Html::hiddenInput('tab', $tab) ?>
            <?= Html::hiddenInput('id', '', ['id' => 'inv-id']) ?>
            <?= Html::hiddenInput('PlanInvestment[plan_year]', $year, ['id' => 'inv-plan_year']) ?>
            <div class="modal-header">
                <h5 class="modal-title" id="invModalTitle">เพิ่มรายการแผนลงทุน<?= $is3 ? ' 3 ปี' : ' 1 ปี' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">ประเภทงบ</label>
                        <?= Html::dropDownList('PlanInvestment[budget_type]', PlanInvestment::TYPE_EQUIPMENT, $types, ['class' => 'form-select', 'id' => 'inv-budget_type']) ?>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">ชื่อรายการลงทุน <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="PlanInvestment[name]" id="inv-name" required maxlength="255" placeholder="เช่น ระบบ Solar Rooftop 100 kW, เครื่อง Ultrasound">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ราคาต่อหน่วย (บาท)</label>
                        <input type="text" inputmode="decimal" class="form-control text-end inv-calc" name="PlanInvestment[unit_price]" id="inv-unit_price" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">หน่วยนับ</label>
                        <input type="text" class="form-control" name="PlanInvestment[unit]" id="inv-unit" maxlength="50" placeholder="เครื่อง / งาน / ชุด">
                    </div>
                    <?php if ($is3): ?>
                        <div class="col-12"><div class="row g-2">
                            <?php foreach ($years as $i => $y): ?>
                                <div class="col-4">
                                    <label class="form-label small mb-1">จำนวนหน่วย ปี <?= $y ?></label>
                                    <input type="text" inputmode="decimal" class="form-control text-end inv-calc" name="PlanInvestment[qty_y<?= $i + 1 ?>]" id="inv-qty_y<?= $i + 1 ?>" value="0">
                                </div>
                            <?php endforeach; ?>
                        </div></div>
                    <?php else: ?>
                        <div class="col-md-5">
                            <label class="form-label">จำนวนหน่วย (ปี <?= $year ?>)</label>
                            <input type="text" inputmode="decimal" class="form-control text-end inv-calc" name="PlanInvestment[qty_y1]" id="inv-qty_y1" value="1">
                        </div>
                    <?php endif; ?>
                    <div class="col-md-5">
                        <label class="form-label">แหล่งเงิน</label>
                        <?= Html::dropDownList('PlanInvestment[source]', PlanInvestment::SOURCE_MAINTENANCE, $sources, ['class' => 'form-select', 'id' => 'inv-source']) ?>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">สอดคล้องนโยบายด้านใด</label>
                        <?= Html::dropDownList('PlanInvestment[policy]', 8, $policies, ['class' => 'form-select', 'id' => 'inv-policy']) ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label">หมายเหตุ / เหตุผลความจำเป็น</label>
                        <input type="text" class="form-control" name="PlanInvestment[note]" id="inv-note" maxlength="500">
                    </div>
                    <div class="col-12 text-end small">รวมเป็นเงิน: <span class="fw-bold fs-6" id="inv-total">0.00</span> บาท</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">บันทึกรายการ</button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<?php
$titleAdd = 'เพิ่มรายการแผนลงทุน' . ($is3 ? ' 3 ปี' : ' 1 ปี');
$js = <<<JS
(function(){
  const modal = document.getElementById('invModal');
  const parse = v => parseFloat(String(v).replace(/[,\\s]/g,'')) || 0;
  const set = (k, v) => { const el = document.getElementById('inv-'+k); if (el) el.value = v; };
  function recalc(){
    const price = parse(document.getElementById('inv-unit_price').value);
    let q = 0;
    ['qty_y1','qty_y2','qty_y3'].forEach(k => { const el = document.getElementById('inv-'+k); if (el) q += parse(el.value); });
    document.getElementById('inv-total').textContent = (price*q).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
  }
  modal.addEventListener('show.bs.modal', function(e){
    const raw = e.relatedTarget ? e.relatedTarget.getAttribute('data-item') : '';
    const d = raw ? JSON.parse(raw) : null;
    document.getElementById('invModalTitle').textContent = d ? 'แก้ไขรายการแผนลงทุน' : '{$titleAdd}';
    set('id', d ? d.id : '');
    set('plan_year', d ? d.plan_year : '{$year}');
    set('budget_type', d ? d.budget_type : 'equipment');
    set('name', d ? d.name : '');
    set('unit', d && d.unit ? d.unit : '');
    set('unit_price', d ? d.unit_price : '0');
    set('qty_y1', d ? d.qty_y1 : '1');
    set('qty_y2', d ? d.qty_y2 : '0');
    set('qty_y3', d ? d.qty_y3 : '0');
    set('source', d ? d.source : 'maintenance');
    set('policy', d ? d.policy : '8');
    set('note', d && d.note ? d.note : '');
    recalc();
  });
  document.addEventListener('input', e => { if (e.target.classList.contains('inv-calc')) recalc(); });
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
