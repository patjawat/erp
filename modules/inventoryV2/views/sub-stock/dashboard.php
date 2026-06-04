<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = 'Dashboard คลังย่อย';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('https://cdn.jsdelivr.net/npm/apexcharts', ['position' => View::POS_HEAD]);

$pendingReceiveCount = (int) ($pendingReceiveCount ?? 0);
$criticalCount = (int) ($criticalCount ?? 0);
$monthlyValue = (float) ($monthlyValue ?? 0);
$expiringSoonCount = (int) ($expiringSoonCount ?? 0);
$incomingList = $incomingList ?? [];
$chartData = $chartData ?? ['categories' => [], 'series' => []];
$warehouses = $warehouses ?? [];
$currentWarehouseId = $currentWarehouseId ?? null;
$activeWid = (int) ($currentWarehouseId ?? 0);

$currentWarehouseName = 'ทั้งหมด';
if ($currentWarehouseId && $warehouses) {
    foreach ($warehouses as $w) {
        if ((int)$w->id === (int)$currentWarehouseId) {
            $currentWarehouseName = $w->warehouse_name;
            break;
        }
    }
}

$monthlyValueLabel = '฿' . number_format($monthlyValue / 1000, 1) . 'K';
$totalIncoming = is_array($incomingList) ? count($incomingList) : 0;

$reportBalanceUrl = ['/inventory-v2/report/balance-by-warehouse'];
if (!empty($subWarehouseIds) && count($subWarehouseIds) === 1) {
    $reportBalanceUrl['warehouse_id'] = $subWarehouseIds[0];
}
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted small mb-0"><?= Html::encode($currentWarehouseName) ?> — ภาพรวมคลังย่อย รอตรวจรับ ต่ำกว่าจุดวิกฤต และแนวโน้มการจ่ายมาคลังย่อย</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="container-fluid px-3 px-md-4">
    <div class="row g-3 align-items-center justify-content-end">
        <div class="col-12 col-lg-auto">
            <?= $this->render('@app/modules/inventoryV2/views/default/_menu_sub', ['active' => 'dashboard', 'subWarehouseIds' => $subWarehouseIds ?? []]) ?>
        </div>
    </div>
</div>
<?php $this->endBlock(); ?>

<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
.sw-scope{
  --primary:#255FAE;--primary-dark:#1a3d6b;--primary-light:#eef5fc;--primary-mid:#2d72cc;
  --gold:#f59e0b;--gold-light:#fef3c7;
  --surface:#f8fafc;--card:#fff;--border:#e2e8f0;--border-soft:#f1f5f9;
  --text:#0f172a;--text-2:#475569;--text-3:#94a3b8;
  --green:#16a34a;--green-light:#dcfce7;
  --red:#dc2626;--red-light:#ffe4e6;
  --amber:#d97706;--amber-light:#fef3c7;
  --purple:#7c3aed;--purple-light:#ede9fe;
  --teal:#0d9488;--teal-light:#ccfbf1;
  --r-sm:8px;--r-md:12px;--r-lg:16px;
  --sh-sm:0 1px 3px rgba(15,23,42,.06);--sh-md:0 4px 16px rgba(15,23,42,.08);--sh-lg:0 12px 40px rgba(15,23,42,.14);
  font-family:'Sarabun',sans-serif;color:var(--text);font-size:15px;
}
.sw-scope *{box-sizing:border-box;}
.sw-scope .page-wrap{max-width:1500px;margin:0 auto;padding:18px 8px 40px;}
.sw-scope .page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px;flex-wrap:wrap;}
.sw-scope .page-title{font-size:22px;font-weight:700;margin-bottom:4px;}
.sw-scope .page-subtitle{font-size:13px;color:var(--text-3);}

.sw-scope .dept-picker{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);padding:12px 16px;margin-bottom:18px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;box-shadow:var(--sh-sm);}
.sw-scope .dept-label{font-size:12.5px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.4px;}
.sw-scope .dept-pills{display:flex;gap:5px;flex-wrap:wrap;}
.sw-scope .dept-pill{padding:6px 14px;border:1px solid var(--border);background:var(--surface);border-radius:20px;font-family:'Sarabun',sans-serif;font-size:13px;color:var(--text-2);cursor:pointer;transition:all .12s;}
.sw-scope .dept-pill:hover{border-color:var(--primary);color:var(--primary);}
.sw-scope .dept-pill.active{background:var(--primary);border-color:var(--primary);color:#fff;font-weight:600;}

.sw-scope .sw-btn-primary{display:inline-flex;align-items:center;gap:7px;padding:8px 18px;background:var(--primary);color:#fff;border:none;border-radius:var(--r-sm);font-family:'Sarabun',sans-serif;font-size:13.5px;font-weight:600;cursor:pointer;text-decoration:none;white-space:nowrap;transition:background .15s;}
.sw-scope .sw-btn-primary:hover{background:var(--primary-dark);color:#fff;}
.sw-scope .sw-btn-outline{display:inline-flex;align-items:center;gap:7px;padding:8px 14px;background:transparent;color:var(--text-2);border:1px solid var(--border);border-radius:var(--r-sm);font-family:'Sarabun',sans-serif;font-size:13.5px;font-weight:500;cursor:pointer;text-decoration:none;}
.sw-scope .sw-btn-outline:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}

.sw-scope .kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;}
.sw-scope .kpi-card{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);padding:14px 16px;box-shadow:var(--sh-sm);position:relative;overflow:hidden;transition:transform .15s,box-shadow .15s;}
.sw-scope a.kpi-card{text-decoration:none;color:inherit;display:block;}
.sw-scope a.kpi-card:hover{transform:translateY(-2px);box-shadow:var(--sh-md);}
.sw-scope .kpi-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;}
.sw-scope .kpi-card.blue::before{background:var(--primary);}
.sw-scope .kpi-card.green::before{background:var(--green);}
.sw-scope .kpi-card.amber::before{background:var(--amber);}
.sw-scope .kpi-card.red::before{background:var(--red);}
.sw-scope .kpi-card.purple::before{background:var(--purple);}
.sw-scope .kpi-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;}
.sw-scope .kpi-icon{width:34px;height:34px;border-radius:var(--r-sm);display:flex;align-items:center;justify-content:center;font-size:15px;}
.sw-scope .kpi-icon.blue{background:var(--primary-light);color:var(--primary);}
.sw-scope .kpi-icon.green{background:var(--green-light);color:var(--green);}
.sw-scope .kpi-icon.amber{background:var(--amber-light);color:var(--amber);}
.sw-scope .kpi-icon.red{background:var(--red-light);color:var(--red);}
.sw-scope .kpi-icon.purple{background:var(--purple-light);color:var(--purple);}
.sw-scope .kpi-val{font-size:22px;font-weight:700;line-height:1;font-variant-numeric:tabular-nums;}
.sw-scope .kpi-label{font-size:11.5px;color:var(--text-3);margin-top:4px;}
.sw-scope .kpi-foot{margin-top:8px;font-size:11px;color:var(--text-3);}
.sw-scope .kpi-foot.danger{color:var(--red);font-weight:600;}

.sw-scope .grid-2{display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:18px;}
.sw-scope .panel{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);box-shadow:var(--sh-sm);overflow:hidden;}
.sw-scope .panel-head{display:flex;align-items:center;justify-content:space-between;padding:14px 18px 12px;border-bottom:1px solid var(--border-soft);}
.sw-scope .panel-title{font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px;}
.sw-scope .panel-title i{color:var(--primary);font-size:16px;}
.sw-scope .panel-body{padding:14px 18px;}
.sw-scope .panel-footer{padding:10px 18px;border-top:1px solid var(--border-soft);text-align:center;font-size:12px;}
.sw-scope .panel-footer a{color:var(--text-3);text-decoration:none;}
.sw-scope .panel-footer a:hover{color:var(--primary);}

.sw-scope .act-item{display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--border-soft);align-items:center;}
.sw-scope .act-item:last-child{border-bottom:none;}
.sw-scope .act-icon{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;background:var(--primary-light);color:var(--primary);}
.sw-scope .act-body{flex:1;min-width:0;}
.sw-scope .act-title{font-size:13px;font-weight:600;color:var(--text);}
.sw-scope .act-meta{font-size:11.5px;color:var(--text-3);}
.sw-scope .act-action{flex-shrink:0;}

.sw-scope .empty-state{text-align:center;padding:30px 16px;color:var(--text-3);font-size:13px;}

@media(max-width:1100px){.sw-scope .kpi-row{grid-template-columns:repeat(2,1fr);}.sw-scope .grid-2{grid-template-columns:1fr;}}
@media(max-width:768px){.sw-scope .page-wrap{padding:12px 4px 32px;}}
</style>

<div class="sw-scope">
<main class="page-wrap">
  <div class="dept-picker">
    <span class="dept-label"><i class="bi bi-building"></i> คลังย่อย:</span>
    <div class="dept-pills">
      <?= Html::a(
          '<i class="bi bi-grid-fill"></i> ทุกคลัง',
          ['/inventory-v2/sub-stock/dashboard', 'warehouse_id' => 'all'],
          [
              'class' => 'dept-pill' . ($activeWid === 0 ? ' active' : ''),
              'style' => 'text-decoration:none;',
          ]
      ) ?>
      <?php foreach ($warehouses as $w): ?>
        <?= Html::a(
            '<i class="bi bi-building"></i> ' . Html::encode($w->warehouse_name),
            ['/inventory-v2/sub-stock/dashboard', 'warehouse_id' => $w->id],
            [
                'class' => 'dept-pill' . ((int) $w->id === $activeWid ? ' active' : ''),
                'style' => 'text-decoration:none;',
            ]
        ) ?>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="kpi-row">
    <div class="kpi-card blue">
      <div class="kpi-top">
        <div class="kpi-icon blue"><i class="bi bi-truck"></i></div>
      </div>
      <div class="kpi-val"><?= number_format($pendingReceiveCount) ?></div>
      <div class="kpi-label">รอตรวจรับเข้า (ใบ)</div>
      <div class="kpi-foot">จากคลังหลัก</div>
    </div>

    <a class="kpi-card red" href="<?= Url::to($reportBalanceUrl) ?>">
      <div class="kpi-top">
        <div class="kpi-icon red"><i class="bi bi-exclamation-triangle-fill"></i></div>
      </div>
      <div class="kpi-val" style="color:var(--red);"><?= number_format($criticalCount) ?></div>
      <div class="kpi-label">ต่ำกว่าจุดวิกฤต (รายการ)</div>
      <div class="kpi-foot danger">รีบกดเบิกทันที <i class="bi bi-arrow-right"></i></div>
    </a>

    <div class="kpi-card green">
      <div class="kpi-top">
        <div class="kpi-icon green"><i class="bi bi-cash-stack"></i></div>
      </div>
      <div class="kpi-val"><?= Html::encode($monthlyValueLabel) ?></div>
      <div class="kpi-label">มูลค่าเบิกใช้เดือนนี้</div>
      <div class="kpi-foot"><i class="bi bi-graph-up"></i> เทียบเดือนก่อน</div>
    </div>

    <div class="kpi-card amber">
      <div class="kpi-top">
        <div class="kpi-icon amber"><i class="bi bi-hourglass-split"></i></div>
      </div>
      <div class="kpi-val" style="color:var(--amber);"><?= number_format($expiringSoonCount) ?></div>
      <div class="kpi-label">หมดอายุภายใน 30 วัน (Lot)</div>
      <div class="kpi-foot">ควรนำออกมาใช้งานก่อน</div>
    </div>
  </div>

  <div class="grid-2">
    <div class="panel">
      <div class="panel-head">
        <div class="panel-title"><i class="bi bi-graph-up"></i> แนวโน้มมูลค่าที่จ่ายมาคลังย่อย (7 วันล่าสุด)</div>
      </div>
      <div class="panel-body">
        <div id="usageApexChart"></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <div class="panel-title"><i class="bi bi-box-seam"></i> รายการส่งของจากคลังหลัก</div>
        <span class="dept-pill" style="cursor:default;font-size:11.5px;padding:2px 10px;"><?= number_format($totalIncoming) ?></span>
      </div>
      <div class="panel-body" style="padding:6px 18px;">
        <?php if (!empty($incomingList)): ?>
          <?php foreach ($incomingList as $item): ?>
            <div class="act-item">
              <div class="act-icon"><i class="bi bi-file-earmark-text"></i></div>
              <div class="act-body">
                <div class="act-title"><?= Html::encode($item['doc_no'] ?? '-') ?></div>
                <div class="act-meta">
                  <?= (int)($item['detail_count'] ?? 0) ?> รายการ
                  <?php if (!empty($item['main_warehouse_name'])): ?>
                    · <?= Html::encode($item['main_warehouse_name']) ?>
                  <?php endif; ?>
                </div>
              </div>
              <div class="act-action">
                <?= Html::a('ดูใบส่ง', ['/inventory-v2/requisition/view', 'id' => $item['id'] ?? null], ['class' => 'sw-btn-outline', 'style' => 'padding:5px 10px;font-size:12px;']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">ไม่มีรายการส่งของจากคลังหลักในขณะนี้</div>
        <?php endif; ?>
      </div>
      <div class="panel-footer">
        <a href="<?= Url::to(['/inventory-v2/requisition/index']) ?>">ดูทะเบียนใบขอเบิก <i class="bi bi-chevron-right"></i></a>
      </div>
    </div>
  </div>

  <div class="panel">
    <?= $this->render('use_history', ['usageHistory' => $usageHistory, 'currentWarehouseId' => $currentWarehouseId]) ?>
  </div>
</main>
</div>

<!-- Modal ตรวจรับพัสดุ -->
<div class="modal fade border-0" id="receiveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-box-seam me-2"></i>ตรวจรับพัสดุเข้าคลังย่อย</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
                    <div><span class="text-muted">เลขที่ใบส่ง:</span> <strong id="m-doc-no">—</strong></div>
                    <div><span class="text-muted">คลังต้นทาง:</span> <strong id="m-from">คลังหลัก</strong></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-muted text-uppercase">
                                <th>รายการ</th>
                                <th class="text-center">Lot</th>
                                <th class="text-center">ส่งมา</th>
                                <th class="text-center" style="width: 140px;">รับจริง</th>
                                <th>หมายเหตุ</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider" id="receive-list-body">
                            <tr>
                                <td><strong>SSD 500GB Samsung</strong></td>
                                <td class="text-center font-monospace small">67-01</td>
                                <td class="text-center">5</td>
                                <td><input type="number" class="form-control text-center" value="5" min="0"></td>
                                <td><input type="text" class="form-control" placeholder="หมายเหตุ"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-primary rounded-pill px-5 me-2" id="btnConfirmModal">ยืนยันรับเข้าสต็อก</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<?php
$chartCategoriesJson = json_encode($chartData['categories']);
$chartSeriesJson = json_encode($chartData['series']);
$js = <<<JS
$(document).ready(function() {
    var categories = {$chartCategoriesJson};
    var seriesData = {$chartSeriesJson};
    if (categories.length === 0) categories = ['-'];
    if (seriesData.length === 0) seriesData = [0];
    var options = {
        series: [{ name: 'มูลค่าที่จ่ายมา (บาท)', data: seriesData }],
        chart: { height: 300, type: 'area', toolbar: { show: false }, fontFamily: 'Sarabun, sans-serif' },
        colors: ['#255FAE'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
        stroke: { curve: 'smooth', width: 3 },
        xaxis: { categories: categories, axisBorder: { show: false }, axisTicks: { show: false } },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        tooltip: { theme: 'light', y: { formatter: function(v) { return v != null ? Number(v).toLocaleString('th-TH') + ' บาท' : ''; } } }
    };
    var chart = new ApexCharts(document.querySelector("#usageApexChart"), options);
    chart.render();
});

$('#receiveModal').on('show.bs.modal', function(e) {
    var docNo = $(e.relatedTarget).data('doc-no') || '—';
    $('#m-doc-no').text(docNo);
});

$('#btnConfirmModal').on('click', function() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({ title: 'ยืนยันการรับ?', text: 'บันทึกการตรวจรับพัสดุ', icon: 'question', showCancelButton: true, confirmButtonText: 'ยืนยัน', confirmButtonColor: '#255FAE' })
            .then(function(result) {
                if (result.isConfirmed) $('#receiveModal').modal('hide');
            });
    } else {
        $('#receiveModal').modal('hide');
    }
});
JS;
$this->registerJs($js, View::POS_READY);
?>
