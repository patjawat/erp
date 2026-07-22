<?php
use yii\helpers\Html;
use yii\helpers\Url;
/** @var yii\web\View $this */
/** @var \app\modules\inventory\models\Warehouse|null $warehouse */
/** @var \app\modules\inventoryV2\models\Warehouse[] $allowedWarehouses */
/** @var \app\modules\inventory\models\Stock[] $stockItems */
/** @var int $totalItemCount */
/** @var float $totalValue */
$this->title = 'คลังหน่วยงาน (คลังย่อย)';
$warehouse = $warehouse ?? null;
$allowedWarehouses = $allowedWarehouses ?? [];
$stockItems = $stockItems ?? [];
$totalItemCount = (int) ($totalItemCount ?? 0);
$totalValue = (float) ($totalValue ?? 0);
$valueLabel = '฿' . number_format($totalValue / 1000, 1) . 'K';
$activeWid = $warehouse ? (int) $warehouse->id : 0;
?>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<?php // bootstrap-icons โหลดจาก AppAsset (self-hosted) แล้ว ?>

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

.sw-scope .page-wrap{max-width:1500px;margin:0 auto;padding:24px 24px 60px;}
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

.sw-scope .kpi-row{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:18px;}
.sw-scope .kpi-card{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);padding:14px 16px;box-shadow:var(--sh-sm);position:relative;overflow:hidden;}
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

.sw-scope .grid-2{display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:18px;}
.sw-scope .grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px;}
.sw-scope .panel{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);box-shadow:var(--sh-sm);overflow:hidden;}
.sw-scope .panel-head{display:flex;align-items:center;justify-content:space-between;padding:14px 18px 12px;border-bottom:1px solid var(--border-soft);}
.sw-scope .panel-title{font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px;}
.sw-scope .panel-title i{color:var(--primary);font-size:16px;}
.sw-scope .panel-body{padding:14px 18px;}

.sw-scope .tbl-responsive{overflow-x:auto;}
.sw-scope table{width:100%;border-collapse:collapse;}
.sw-scope thead th{background:var(--surface);color:var(--text-2);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;padding:11px 14px;border-bottom:1px solid var(--border);white-space:nowrap;text-align:left;}
.sw-scope thead th.right{text-align:right;}
.sw-scope thead th.center{text-align:center;}
.sw-scope tbody tr{border-bottom:1px solid var(--border-soft);}
.sw-scope tbody tr:last-child{border-bottom:none;}
.sw-scope tbody tr:hover{background:var(--primary-light);}
.sw-scope tbody td{padding:11px 14px;font-size:13px;}
.sw-scope tbody td.right{text-align:right;font-variant-numeric:tabular-nums;}
.sw-scope tbody td.center{text-align:center;}

.sw-scope .item-name{font-weight:600;font-size:13px;}
.sw-scope .item-code{font-family:'Courier New',monospace;font-size:11px;color:var(--text-3);font-weight:700;}

.sw-scope .badge{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:10px;font-size:11px;font-weight:600;}
.sw-scope .badge::before{content:'';width:6px;height:6px;border-radius:50%;}
.sw-scope .badge.ok{background:var(--green-light);color:var(--green);}
.sw-scope .badge.ok::before{background:#22c55e;}
.sw-scope .badge.low{background:var(--amber-light);color:var(--amber);}
.sw-scope .badge.low::before{background:var(--gold);}
.sw-scope .badge.out{background:var(--red-light);color:var(--red);}
.sw-scope .badge.out::before{background:var(--red);}

.sw-scope .cat-badge{display:inline-flex;padding:2px 8px;border-radius:10px;font-size:10.5px;font-weight:600;}
.sw-scope .cat-badge.medical{background:var(--red-light);color:var(--red);}
.sw-scope .cat-badge.office{background:var(--primary-light);color:var(--primary);}
.sw-scope .cat-badge.cleaning{background:var(--teal-light);color:var(--teal);}
.sw-scope .cat-badge.electric{background:var(--amber-light);color:var(--amber);}
.sw-scope .cat-badge.lab{background:var(--purple-light);color:var(--purple);}
.sw-scope .cat-badge.spare{background:var(--gold-light);color:#b45309;}

.sw-scope .lot-bar{display:flex;align-items:center;gap:6px;}
.sw-scope .lot-progress{width:80px;height:6px;background:var(--border-soft);border-radius:3px;overflow:hidden;display:inline-block;}
.sw-scope .lot-fill{height:100%;border-radius:3px;}

.sw-scope .act-item{display:flex;gap:10px;padding:9px 0;border-bottom:1px solid var(--border-soft);}
.sw-scope .act-item:last-child{border-bottom:none;}
.sw-scope .act-icon{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;}
.sw-scope .act-icon.in{background:var(--green-light);color:var(--green);}
.sw-scope .act-icon.out{background:var(--amber-light);color:var(--amber);}
.sw-scope .act-icon.repair{background:var(--red-light);color:var(--red);}
.sw-scope .act-body{flex:1;min-width:0;}
.sw-scope .act-title{font-size:12.5px;font-weight:600;}
.sw-scope .act-meta{font-size:11px;color:var(--text-3);}
.sw-scope .act-qty{font-size:12px;font-weight:700;align-self:center;}
.sw-scope .act-qty.in{color:var(--green);}
.sw-scope .act-qty.out{color:var(--amber);}

.sw-scope .consumption{display:flex;flex-direction:column;gap:12px;}
.sw-scope .cons-row{display:grid;grid-template-columns:1fr auto;gap:6px;font-size:12.5px;}
.sw-scope .cons-label{font-weight:500;color:var(--text-2);}
.sw-scope .cons-val{font-weight:700;font-variant-numeric:tabular-nums;}
.sw-scope .cons-bar-wrap{grid-column:1/-1;height:7px;background:var(--border-soft);border-radius:4px;overflow:hidden;}
.sw-scope .cons-bar{height:100%;background:linear-gradient(90deg,var(--primary),var(--primary-mid));border-radius:4px;}

@media(max-width:1100px){.sw-scope .kpi-row{grid-template-columns:repeat(3,1fr);}.sw-scope .grid-2,.sw-scope .grid-3{grid-template-columns:1fr;}}
@media(max-width:768px){.sw-scope .kpi-row{grid-template-columns:repeat(2,1fr);}.sw-scope .page-wrap{padding:16px 14px 40px;}}
</style>

<div class="sw-scope">
<main class="page-wrap">
  <div class="page-header">
    <div>
      <div class="page-title">คลังหน่วยงาน (คลังย่อย)</div>
      <div class="page-subtitle">บริหารคลังย่อยของแต่ละแผนก เบิกจากคลังกลาง → จ่ายออกใช้งาน · ตัดตาม FIFO</div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <a class="sw-btn-outline" href="<?= \yii\helpers\Url::to(['/sub-warehouse/default/request', 'mode' => 'out']) ?>"><i class="bi bi-arrow-up-circle"></i> ใบจ่ายออก</a>
      <a class="sw-btn-primary" href="<?= \yii\helpers\Url::to(['/sub-warehouse/default/request', 'mode' => 'in']) ?>"><i class="bi bi-plus-lg"></i> เบิกจากคลังกลาง</a>
    </div>
  </div>

  <div class="dept-picker">
    <span class="dept-label"><i class="bi bi-building"></i> คลังของแผนก:</span>
    <div class="dept-pills" id="sw-deptPills">
      <?php if (empty($allowedWarehouses)): ?>
        <span style="font-size:12.5px;color:var(--text-3);">ไม่พบคลังย่อยที่คุณมีสิทธิ์เข้าถึง</span>
      <?php else: ?>
        <?php foreach ($allowedWarehouses as $w): ?>
          <?= Html::a(
              '<i class="bi bi-building"></i> ' . Html::encode($w->warehouse_name),
              ['index', 'warehouse_id' => $w->id],
              [
                  'class' => 'dept-pill' . ((int) $w->id === $activeWid ? ' active' : ''),
                  'style' => 'text-decoration:none;',
              ]
          ) ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="kpi-row">
    <div class="kpi-card blue">
      <div class="kpi-top"><div class="kpi-icon blue"><i class="bi bi-boxes"></i></div></div>
      <div class="kpi-val" id="sw-k-items"><?= number_format($totalItemCount) ?></div>
      <div class="kpi-label">รายการพัสดุในคลังย่อย<?= $warehouse ? ' · ' . Html::encode($warehouse->warehouse_name) : '' ?></div>
    </div>
    <div class="kpi-card green">
      <div class="kpi-top"><div class="kpi-icon green"><i class="bi bi-cash-stack"></i></div></div>
      <div class="kpi-val" id="sw-k-value"><?= Html::encode($valueLabel) ?></div>
      <div class="kpi-label">มูลค่าคงคลัง</div>
    </div>
    <div class="kpi-card amber">
      <div class="kpi-top"><div class="kpi-icon amber"><i class="bi bi-exclamation-triangle-fill"></i></div></div>
      <div class="kpi-val" id="sw-k-low">0</div>
      <div class="kpi-label">ใกล้หมด</div>
    </div>
    <div class="kpi-card red">
      <div class="kpi-top"><div class="kpi-icon red"><i class="bi bi-x-circle-fill"></i></div></div>
      <div class="kpi-val" id="sw-k-out">0</div>
      <div class="kpi-label">หมดสต็อก</div>
    </div>
    <div class="kpi-card purple">
      <div class="kpi-top"><div class="kpi-icon purple"><i class="bi bi-arrow-down-up"></i></div></div>
      <div class="kpi-val" id="sw-k-moves">0</div>
      <div class="kpi-label">เคลื่อนไหวเดือนนี้</div>
    </div>
  </div>

  <div class="grid-2">
    <div class="panel">
      <div class="panel-head">
        <div class="panel-title"><i class="bi bi-list-ul"></i> สต็อกคลังย่อย (รายการล่าสุด)</div>
        <a href="#" class="sw-btn-outline" style="font-size:11.5px;padding:4px 10px;">ดูทั้งหมด →</a>
      </div>
      <div class="panel-body" style="padding:0;">
        <div class="tbl-responsive">
          <table>
            <thead>
              <tr>
                <th>รหัส / ชื่อพัสดุ</th>
                <th>หมวด</th>
                <th class="right">คงเหลือ</th>
                <th>สถานะ Lot (FIFO)</th>
                <th>วันหมดอายุ</th>
                <th class="center">สถานะ</th>
              </tr>
            </thead>
            <tbody id="sw-stockBody">
              <?php if (!$warehouse): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-3);">
                  ยังไม่ได้ตั้งค่าคลังย่อย —
                  <?= Html::a('กำหนดคลังย่อย', ['/me/store-v2/set-warehouse'], ['style' => 'color:var(--primary);text-decoration:underline;']) ?>
                </td></tr>
              <?php elseif (empty($stockItems)): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-3);">ไม่พบรายการในคลังย่อยนี้</td></tr>
              <?php else: ?>
                <?php foreach ($stockItems as $it): ?>
                  <?php
                  $product = $it->product ?? null;
                  $itemCode = (string) ($it->asset_item ?? ($product->code ?? '-'));
                  $itemName = (string) ($product->title ?? $itemCode);
                  $unit = (string) ($product->data_json['unit'] ?? '');
                  $unitPrice = (float) ($it->unit_price ?? 0);
                  $qty = (float) (method_exists($it, 'SumQty') ? $it->SumQty() : ($it->qty ?? 0));

                  // นับจำนวน lot ที่ยังเหลือ
                  $lotCount = (int) \app\modules\inventory\models\Stock::find()
                      ->where(['asset_item' => $itemCode, 'warehouse_id' => $warehouse->id])
                      ->andWhere(['>', 'qty', 0])
                      ->count();
                  $firstLot = \app\modules\inventory\models\Stock::find()
                      ->where(['asset_item' => $itemCode, 'warehouse_id' => $warehouse->id])
                      ->andWhere(['>', 'qty', 0])
                      ->orderBy(['id' => SORT_ASC])
                      ->one();
                  $firstLotNo = $firstLot ? (string) ($firstLot->lot_number ?? '') : '';

                  $status = $qty <= 0 ? 'out' : 'ok';
                  $statusLabel = $status === 'out' ? 'หมดสต็อก' : 'ปกติ';
                  $barCls = $status === 'out' ? 'red' : 'green';
                  $pct = $qty > 0 ? min(100, ($qty / max(1, $qty)) * 100) : 0;

                  $expireLabel = '—';
                  if (!empty($it->data_json['expire_date'])) {
                      try {
                          $expireLabel = Yii::$app->formatter->asDate($it->data_json['expire_date'], 'medium');
                      } catch (\Throwable $e) {
                          $expireLabel = (string) $it->data_json['expire_date'];
                      }
                  }

                  $catType = (string) ($product->productType->title ?? ($product->category_id ?? '-'));
                  ?>
                  <tr>
                    <td>
                      <span class="item-code"><?= Html::encode($itemCode) ?></span>
                      <div class="item-name"><?= Html::encode($itemName) ?></div>
                      <span style="font-size:11px;color:var(--text-3);"><?= Html::encode($warehouse->warehouse_name) ?> · ฿<?= number_format($unitPrice, 2) ?>/หน่วย</span>
                    </td>
                    <td><span class="cat-badge office"><?= Html::encode($catType) ?></span></td>
                    <td class="right">
                      <strong style="font-size:14px;"><?= number_format($qty) ?></strong>
                      <span style="color:var(--text-3);font-size:11px;"><?= Html::encode($unit) ?></span>
                    </td>
                    <td>
                      <div class="lot-bar">
                        <span style="font-size:11px;color:var(--text-3);"><?= number_format($lotCount) ?> lot</span>
                        <span class="lot-progress"><span class="lot-fill" style="width:<?= $pct ?>%;background:<?= $barCls === 'red' ? 'var(--red)' : 'var(--green)' ?>;"></span></span>
                      </div>
                      <?php if ($firstLotNo !== ''): ?>
                        <div style="font-size:10.5px;color:var(--text-3);margin-top:2px;font-family:'Courier New',monospace;">FIFO: <?= Html::encode($firstLotNo) ?></div>
                      <?php endif; ?>
                    </td>
                    <td><?= Html::encode($expireLabel) ?></td>
                    <td class="center"><span class="badge <?= $status ?>"><?= $statusLabel ?></span></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <div class="panel-title"><i class="bi bi-clock-history"></i> กิจกรรมล่าสุด</div>
      </div>
      <div class="panel-body" id="sw-activityFeed"></div>
    </div>
  </div>

  <div class="grid-3">
    <div class="panel">
      <div class="panel-head"><div class="panel-title"><i class="bi bi-bar-chart-fill"></i> Top การใช้พัสดุ</div></div>
      <div class="panel-body">
        <div class="consumption" id="sw-consumption"></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <div class="panel-title"><i class="bi bi-tools"></i> เบิกใช้งานซ่อม</div>
        <a href="#" style="font-size:11px;color:var(--primary);text-decoration:none;">ไปงานซ่อม →</a>
      </div>
      <div class="panel-body" id="sw-repairLink"></div>
    </div>

    <div class="panel">
      <div class="panel-head"><div class="panel-title"><i class="bi bi-calendar-week-fill"></i> ใบเบิกรออนุมัติ</div></div>
      <div class="panel-body" id="sw-pendingReqs"></div>
    </div>
  </div>
</main>
</div>

<script>
(function(){
const CAT_LABEL = {medical:'เวชภัณฑ์/ยา',office:'สำนักงาน',cleaning:'ทำความสะอาด',electric:'ไฟฟ้า',lab:'แล็บ',food:'อาหาร',spare:'อะไหล่'};

const DEPTS = [
  {id:'all',name:'ทุกแผนก',icon:'bi-grid-fill'},
  {id:'er',name:'เวชกิจฉุกเฉิน',icon:'bi-heart-pulse-fill'},
  {id:'opd',name:'OPD',icon:'bi-clipboard-pulse-fill'},
  {id:'dental',name:'ทันตกรรม',icon:'bi-emoji-smile-fill'},
  {id:'admin',name:'ธุรการ',icon:'bi-briefcase-fill'},
  {id:'maint',name:'งานซ่อมบำรุง',icon:'bi-tools'},
  {id:'phc',name:'สาธารณสุข',icon:'bi-people-fill'},
];

let subStock = JSON.parse(localStorage.getItem('erp_substock')||'null') || [
  {id:1,code:'MED-001',name:'พาราเซตามอล 500mg',cat:'medical',unit:'แผง',price:25,deptId:'er',min:20,lots:[
    {lot:'L2025-04-15',qty:35,expire:'2027-04-15',received:'2025-05-08'},
    {lot:'L2025-05-10',qty:40,expire:'2027-05-10',received:'2025-05-15'},
  ]},
  {id:2,code:'MED-002',name:'สำลีก้อน 500g',cat:'medical',unit:'ห่อ',price:120,deptId:'er',min:10,lots:[
    {lot:'L2025-03-20',qty:5,expire:null,received:'2025-04-02'},
  ]},
  {id:3,code:'MED-003',name:'เข็มฉีดยา 21G',cat:'medical',unit:'กล่อง',price:180,deptId:'opd',min:8,lots:[
    {lot:'L2025-02-10',qty:0,expire:'2027-02-10',received:'2025-03-01'},
  ]},
  {id:4,code:'OFF-001',name:'กระดาษ A4 80gsm',cat:'office',unit:'รีม',price:135,deptId:'admin',min:10,lots:[
    {lot:'L2025-05-01',qty:18,expire:null,received:'2025-05-05'},
    {lot:'L2025-05-15',qty:8,expire:null,received:'2025-05-18'},
  ]},
  {id:5,code:'OFF-002',name:'ปากกาลูกลื่น',cat:'office',unit:'ด้าม',price:8,deptId:'admin',min:30,lots:[
    {lot:'L2025-04-10',qty:85,expire:null,received:'2025-04-12'},
  ]},
  {id:6,code:'CLN-001',name:'น้ำยาฆ่าเชื้อ 1L',cat:'cleaning',unit:'ขวด',price:185,deptId:'er',min:5,lots:[
    {lot:'L2025-04-20',qty:12,expire:'2026-10-20',received:'2025-04-25'},
  ]},
  {id:7,code:'CLN-002',name:'ถุงมือยาง L',cat:'cleaning',unit:'กล่อง',price:160,deptId:'opd',min:8,lots:[
    {lot:'L2025-05-05',qty:3,expire:'2027-05-05',received:'2025-05-10'},
  ]},
  {id:8,code:'SPR-001',name:'น้ำยาแอร์ R32',cat:'spare',unit:'กก.',price:850,deptId:'maint',min:2,lots:[
    {lot:'L2025-03-15',qty:4,expire:null,received:'2025-03-20'},
  ]},
  {id:9,code:'SPR-002',name:'หลอด LED 9W',cat:'electric',unit:'หลอด',price:75,deptId:'maint',min:15,lots:[
    {lot:'L2025-04-01',qty:8,expire:null,received:'2025-04-05'},
    {lot:'L2025-05-01',qty:12,expire:null,received:'2025-05-08'},
  ]},
  {id:10,code:'OFF-003',name:'หมึก HP 26A',cat:'office',unit:'ตลับ',price:2800,deptId:'admin',min:2,lots:[
    {lot:'L2025-04-25',qty:1,expire:null,received:'2025-04-28'},
  ]},
];
if(!localStorage.getItem('erp_substock')) localStorage.setItem('erp_substock', JSON.stringify(subStock));

let subMoves = JSON.parse(localStorage.getItem('erp_submoves')||'null') || [
  {id:1,no:'WI-2569-0001',type:'in',deptId:'er',date:'2026-05-15',by:'นางวิภา กรองดี',items:3,total:5200,purpose:'เติมสต็อกประจำเดือน',status:'done'},
  {id:2,no:'WO-2569-0001',type:'out',deptId:'er',date:'2026-05-17',by:'พยาบาลสุพรรณี',items:2,total:340,purpose:'ใช้งานในแผนก ER',status:'done'},
  {id:3,no:'WI-2569-0002',type:'in',deptId:'admin',date:'2026-05-16',by:'นายวิชัย นาคสุวรรณ',items:4,total:4200,purpose:'รับวัสดุสำนักงานประจำเดือน',status:'done'},
  {id:4,no:'WO-2569-0002',type:'out',deptId:'maint',date:'2026-05-18',by:'นายช่างมาโนช',items:1,total:850,purpose:'ซ่อมแอร์ OPD (RP-2569-0004)',status:'done',repairRef:'RP-2569-0004'},
  {id:5,no:'WI-2569-0003',type:'in',deptId:'maint',date:'2026-05-18',by:'นายช่างประพันธ์',items:2,total:920,purpose:'เติมสต็อกอะไหล่',status:'pending'},
  {id:6,no:'WO-2569-0003',type:'out',deptId:'admin',date:'2026-05-19',by:'นางสุนีย์',items:3,total:285,purpose:'ใช้งานในสำนักงาน',status:'pending'},
  {id:7,no:'WO-2569-0004',type:'out',deptId:'opd',date:'2026-05-19',by:'พยาบาลปริม',items:5,total:680,purpose:'ใช้บริการผู้ป่วยนอก',status:'done'},
];
if(!localStorage.getItem('erp_submoves')) localStorage.setItem('erp_submoves', JSON.stringify(subMoves));

let activeDept = 'all';

function renderDeptPills(){
  // dept-pills มาจาก PHP (คลังย่อยที่ user มีสิทธิ์) — ไม่ overwrite ในนี้
  return;
  /* eslint-disable */
  document.getElementById('sw-deptPills').innerHTML = DEPTS.map(d => `
    <button class="dept-pill ${activeDept===d.id?'active':''}" data-dept="${d.id}">
      <i class="bi ${d.icon}"></i> ${d.name}
    </button>
  `).join('');
  document.querySelectorAll('#sw-deptPills .dept-pill').forEach(el => {
    el.addEventListener('click', function(){ activeDept = this.dataset.dept; renderDeptPills(); renderAll(); });
  });
}

function getStock(it){ return (it.lots||[]).reduce((s,l)=>s+(+l.qty||0), 0); }
function getStatus(it){
  const s = getStock(it);
  if(s === 0) return 'out';
  if(s <= it.min) return 'low';
  return 'ok';
}
function filteredStock(){ return subStock.filter(it => activeDept === 'all' || it.deptId === activeDept); }
function filteredMoves(){ return subMoves.filter(m => activeDept === 'all' || m.deptId === activeDept); }

function renderKPI(){
  const items = filteredStock();
  const moves = filteredMoves();
  const value = items.reduce((s,it) => s + getStock(it)*it.price, 0);
  const low = items.filter(it => getStatus(it) === 'low').length;
  const out = items.filter(it => getStatus(it) === 'out').length;
  // k-items และ k-value มาจาก PHP (ข้อมูลจริงในคลังย่อย) — ไม่ overwrite ในนี้
  // document.getElementById('sw-k-items').textContent = items.length;
  // document.getElementById('sw-k-value').textContent = '฿' + (value/1000).toFixed(1) + 'K';
  document.getElementById('sw-k-low').textContent = low;
  document.getElementById('sw-k-out').textContent = out;
  document.getElementById('sw-k-moves').textContent = moves.length;
}

function renderStockTable(){
  // ตารางสต็อกใช้ข้อมูลจริงจาก PHP — ข้ามการ render ฝั่ง JS
  return;
  /* eslint-disable */
  const items = filteredStock().slice(0,8);
  const tbody = document.getElementById('sw-stockBody');
  if(!items.length){
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-3);">ไม่มีรายการในคลังย่อยของแผนกนี้</td></tr>`;
    return;
  }
  tbody.innerHTML = items.map(it => {
    const stock = getStock(it);
    const status = getStatus(it);
    const lots = (it.lots||[]).filter(l => +l.qty > 0).sort((a,b)=>new Date(a.received)-new Date(b.received));
    const firstLot = lots[0];
    const totalLots = (it.lots||[]).length;
    const pct = Math.min(100, stock/(it.min*3)*100);
    const barCls = status === 'out' ? 'red' : status === 'low' ? 'amber' : 'green';
    const dept = DEPTS.find(d=>d.id===it.deptId);
    const nearestExpire = lots.filter(l=>l.expire).sort((a,b)=>new Date(a.expire)-new Date(b.expire))[0];
    let expireLabel = '—';
    if(nearestExpire){
      const days = Math.ceil((new Date(nearestExpire.expire) - new Date())/(1000*60*60*24));
      const color = days < 60 ? 'var(--red)' : days < 180 ? 'var(--amber)' : 'var(--text-2)';
      expireLabel = `<span style="color:${color};font-size:12px;">${new Date(nearestExpire.expire).toLocaleDateString('th-TH',{day:'numeric',month:'short',year:'2-digit'})}</span>`;
    }
    return `<tr>
      <td><span class="item-code">${it.code}</span><div class="item-name">${it.name}</div><span style="font-size:11px;color:var(--text-3);">${dept?dept.name:''} · ฿${it.price}/หน่วย</span></td>
      <td><span class="cat-badge ${it.cat}">${CAT_LABEL[it.cat]||it.cat}</span></td>
      <td class="right"><strong style="font-size:14px;">${stock.toLocaleString()}</strong> <span style="color:var(--text-3);font-size:11px;">${it.unit}</span></td>
      <td>
        <div class="lot-bar">
          <span style="font-size:11px;color:var(--text-3);">${totalLots} lot</span>
          <span class="lot-progress"><span class="lot-fill" style="width:${pct}%;background:${barCls==='red'?'var(--red)':barCls==='amber'?'var(--amber)':'var(--green)'};"></span></span>
        </div>
        ${firstLot ? `<div style="font-size:10.5px;color:var(--text-3);margin-top:2px;font-family:'Courier New',monospace;">FIFO: ${firstLot.lot}</div>` : ''}
      </td>
      <td>${expireLabel}</td>
      <td class="center"><span class="badge ${status}">${status==='ok'?'ปกติ':status==='low'?'ใกล้หมด':'หมดสต็อก'}</span></td>
    </tr>`;
  }).join('');
}

function renderActivity(){
  const moves = filteredMoves().slice().sort((a,b)=>new Date(b.date)-new Date(a.date)).slice(0,6);
  const el = document.getElementById('sw-activityFeed');
  if(!moves.length){ el.innerHTML = `<div style="text-align:center;padding:24px;color:var(--text-3);font-size:12.5px;">ไม่มีกิจกรรม</div>`; return; }
  el.innerHTML = moves.map(m => {
    const type = m.repairRef ? 'repair' : m.type;
    const iconMap = {in:'bi-arrow-down-circle-fill',out:'bi-arrow-up-circle-fill',repair:'bi-tools'};
    const dept = DEPTS.find(d=>d.id===m.deptId);
    return `<div class="act-item">
      <div class="act-icon ${type}"><i class="bi ${iconMap[type]}"></i></div>
      <div class="act-body">
        <div class="act-title">${m.purpose}</div>
        <div class="act-meta">${m.no} · ${m.by} · ${dept?dept.name:''} · ${new Date(m.date).toLocaleDateString('th-TH',{day:'numeric',month:'short'})}</div>
      </div>
      <div class="act-qty ${m.type}">${m.type==='out'?'-':'+'}฿${(m.total/1000).toFixed(1)}K</div>
    </div>`;
  }).join('');
}

function renderConsumption(){
  const items = filteredStock();
  const data = items.map(it => ({name:it.name, val:Math.max(0, it.min*2-getStock(it)) + 5})).sort((a,b)=>b.val-a.val).slice(0,5);
  const max = data[0]?.val || 1;
  document.getElementById('sw-consumption').innerHTML = data.map(d => `
    <div class="cons-row">
      <div class="cons-label">${d.name}</div>
      <div class="cons-val">${d.val}</div>
      <div class="cons-bar-wrap"><div class="cons-bar" style="width:${d.val/max*100}%"></div></div>
    </div>
  `).join('') || '<div style="color:var(--text-3);font-size:12px;text-align:center;padding:14px;">ยังไม่มีข้อมูล</div>';
}

function renderRepairLink(){
  const moves = filteredMoves().filter(m => m.repairRef);
  const el = document.getElementById('sw-repairLink');
  if(!moves.length){
    el.innerHTML = `<div style="text-align:center;padding:14px;color:var(--text-3);font-size:12.5px;">ยังไม่มีการเบิกใช้งานซ่อม</div>`;
    return;
  }
  el.innerHTML = moves.slice(0,3).map(m => `
    <div style="padding:9px 0;border-bottom:1px solid var(--border-soft);">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:12.5px;font-weight:600;color:var(--red);"><i class="bi bi-tools"></i> ${m.repairRef}</div>
        <div style="font-size:11.5px;font-weight:700;">฿${m.total.toLocaleString()}</div>
      </div>
      <div style="font-size:11px;color:var(--text-3);margin-top:2px;">${m.purpose}</div>
    </div>
  `).join('');
}

function renderPending(){
  const pending = filteredMoves().filter(m => m.status === 'pending');
  const el = document.getElementById('sw-pendingReqs');
  if(!pending.length){
    el.innerHTML = `<div style="text-align:center;padding:14px;color:var(--text-3);font-size:12.5px;">ไม่มีใบเบิกค้าง</div>`;
    return;
  }
  el.innerHTML = pending.slice(0,4).map(m => `
    <div style="padding:9px 0;border-bottom:1px solid var(--border-soft);">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
        <div style="font-size:12.5px;font-weight:600;">${m.no}</div>
        <span class="badge low">รอ${m.type==='in'?'อนุมัติ':'จ่าย'}</span>
      </div>
      <div style="font-size:11px;color:var(--text-3);margin-top:2px;">${m.by} · ${m.items} รายการ · ฿${m.total.toLocaleString()}</div>
    </div>
  `).join('');
}

function renderAll(){
  renderKPI();
  renderStockTable();
  renderActivity();
  renderConsumption();
  renderRepairLink();
  renderPending();
}

renderDeptPills();
renderAll();
})();
</script>
