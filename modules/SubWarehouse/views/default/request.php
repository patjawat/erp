<?php
use yii\helpers\Url;
/** @var yii\web\View $this */
/** @var string $mode */
$mode = $mode ?? 'in';
$this->title = $mode === 'in' ? 'เบิกจากคลังกลาง' : 'ใบจ่ายออก';
$urlIn = Url::to(['/sub-warehouse/default/request', 'mode' => 'in']);
$urlOut = Url::to(['/sub-warehouse/default/request', 'mode' => 'out']);
$urlOverview = Url::to(['/sub-warehouse/default/index']);
?>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
.sw-scope{
  --primary:#255FAE;--primary-dark:#1a3d6b;--primary-light:#eef5fc;
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

.sw-scope .mode-tabs{background:var(--card);border-bottom:1px solid var(--border);box-shadow:var(--sh-sm);margin-bottom:0;}
.sw-scope .mode-inner{max-width:1500px;margin:0 auto;padding:0 24px;display:flex;align-items:center;gap:4px;height:52px;overflow-x:auto;}
.sw-scope .mode-tab{display:flex;align-items:center;gap:8px;padding:8px 18px;border:none;background:transparent;color:var(--text-2);font-family:'Sarabun',sans-serif;font-size:13.5px;font-weight:500;cursor:pointer;border-radius:var(--r-sm);text-decoration:none;white-space:nowrap;transition:all .12s;}
.sw-scope .mode-tab:hover{background:var(--primary-light);color:var(--primary);}
.sw-scope .mode-tab.active{background:var(--primary);color:#fff;font-weight:600;}
.sw-scope .mode-tab i{font-size:15px;}

.sw-scope .page-wrap{max-width:1500px;margin:0 auto;padding:24px 24px 60px;}
.sw-scope .page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px;flex-wrap:wrap;}
.sw-scope .page-title{font-size:22px;font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:10px;}
.sw-scope .page-title-icon{width:38px;height:38px;border-radius:var(--r-sm);display:flex;align-items:center;justify-content:center;font-size:18px;}
.sw-scope .page-title-icon.in{background:var(--green-light);color:var(--green);}
.sw-scope .page-title-icon.out{background:var(--amber-light);color:var(--amber);}
.sw-scope .page-subtitle{font-size:13px;color:var(--text-3);margin-left:48px;}

.sw-scope .sw-btn-primary{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:var(--primary);color:#fff;border:none;border-radius:var(--r-sm);font-family:'Sarabun',sans-serif;font-size:13.5px;font-weight:600;cursor:pointer;text-decoration:none;white-space:nowrap;}
.sw-scope .sw-btn-primary:hover{background:var(--primary-dark);color:#fff;}
.sw-scope .sw-btn-outline{display:inline-flex;align-items:center;gap:7px;padding:8px 14px;background:transparent;color:var(--text-2);border:1px solid var(--border);border-radius:var(--r-sm);font-family:'Sarabun',sans-serif;font-size:13.5px;font-weight:500;cursor:pointer;text-decoration:none;}
.sw-scope .sw-btn-outline:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
.sw-scope .sw-btn-success{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:var(--green);color:#fff;border:none;border-radius:var(--r-sm);font-family:'Sarabun',sans-serif;font-size:13.5px;font-weight:600;cursor:pointer;}
.sw-scope .sw-btn-success:hover{background:#15803d;}

.sw-scope .layout{display:grid;grid-template-columns:1fr 320px;gap:18px;align-items:start;}

.sw-scope .panel{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);box-shadow:var(--sh-sm);overflow:hidden;margin-bottom:16px;}
.sw-scope .panel-head{display:flex;align-items:center;justify-content:space-between;padding:14px 18px 12px;border-bottom:1px solid var(--border-soft);}
.sw-scope .panel-title{font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px;}
.sw-scope .panel-title i{color:var(--primary);font-size:16px;}
.sw-scope .panel-body{padding:16px 18px;}

.sw-scope .fg{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;}
.sw-scope .fg.thirds{grid-template-columns:1fr 1fr 1fr;}
.sw-scope .fg.full{grid-template-columns:1fr;}
.sw-scope .form-group{display:flex;flex-direction:column;gap:4px;}
.sw-scope .form-label{font-size:13px;font-weight:600;color:var(--text-2);}
.sw-scope .req{color:var(--red);margin-left:2px;}
.sw-scope .form-control{padding:8px 12px;border:1px solid var(--border);border-radius:var(--r-sm);font-family:'Sarabun',sans-serif;font-size:13.5px;background:var(--surface);outline:none;width:100%;transition:border-color .15s,box-shadow .15s;}
.sw-scope .form-control:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(37,95,174,.12);background:#fff;}
.sw-scope textarea.form-control{resize:vertical;min-height:60px;}

.sw-scope .fs-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-3);margin:14px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border-soft);display:flex;align-items:center;gap:6px;}
.sw-scope .fs-title:first-child{margin-top:0;}
.sw-scope .fs-title i{color:var(--primary);font-size:13px;}

.sw-scope .items-table{width:100%;border-collapse:collapse;margin-top:8px;}
.sw-scope .items-table th{background:var(--surface);font-size:11px;font-weight:700;color:var(--text-2);text-transform:uppercase;letter-spacing:.4px;padding:9px 8px;text-align:left;border-bottom:1px solid var(--border);}
.sw-scope .items-table th.right{text-align:right;}
.sw-scope .items-table th.center{text-align:center;}
.sw-scope .items-table td{padding:7px 6px;border-bottom:1px solid var(--border-soft);vertical-align:middle;}
.sw-scope .items-table select,.sw-scope .items-table input{padding:6px 10px;border:1px solid var(--border);border-radius:6px;font-family:'Sarabun',sans-serif;font-size:12.5px;background:var(--surface);outline:none;width:100%;}
.sw-scope .items-table select:focus,.sw-scope .items-table input:focus{border-color:var(--primary);background:#fff;}
.sw-scope .items-table td.right{text-align:right;font-variant-numeric:tabular-nums;font-weight:600;}
.sw-scope .items-table tr.total-row{background:var(--primary-light);font-weight:700;}
.sw-scope .items-table tr.total-row td{padding:11px 8px;font-size:13.5px;color:var(--primary-dark);}
.sw-scope .stock-hint{font-size:10.5px;color:var(--text-3);margin-top:2px;}
.sw-scope .stock-hint.low{color:var(--red);font-weight:600;}
.sw-scope .fifo-hint{font-size:10px;color:var(--purple);font-family:'Courier New',monospace;margin-top:2px;}

.sw-scope .btn-add{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--primary-light);color:var(--primary);border:1px dashed var(--primary);border-radius:var(--r-sm);font-family:'Sarabun',sans-serif;font-size:12.5px;font-weight:600;cursor:pointer;margin-top:8px;}
.sw-scope .btn-add:hover{background:var(--primary);color:#fff;border-style:solid;}
.sw-scope .btn-rm{width:28px;height:28px;border-radius:6px;border:none;background:transparent;cursor:pointer;color:var(--red);display:flex;align-items:center;justify-content:center;}
.sw-scope .btn-rm:hover{background:var(--red-light);}

.sw-scope .list-card{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);box-shadow:var(--sh-sm);overflow:hidden;}
.sw-scope .tbl-responsive{overflow-x:auto;}
.sw-scope .list-table{width:100%;border-collapse:collapse;min-width:800px;}
.sw-scope .list-table thead th{background:var(--surface);color:var(--text-2);font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;padding:11px 14px;border-bottom:1px solid var(--border);white-space:nowrap;text-align:left;}
.sw-scope .list-table thead th.right{text-align:right;}
.sw-scope .list-table thead th.center{text-align:center;}
.sw-scope .list-table tbody tr{border-bottom:1px solid var(--border-soft);cursor:pointer;}
.sw-scope .list-table tbody tr:last-child{border-bottom:none;}
.sw-scope .list-table tbody tr:hover{background:var(--primary-light);}
.sw-scope .list-table tbody td{padding:11px 14px;font-size:13px;}
.sw-scope .list-table tbody td.right{text-align:right;font-variant-numeric:tabular-nums;}
.sw-scope .list-table tbody td.center{text-align:center;}

.sw-scope .doc-no{font-weight:700;color:var(--primary);font-size:13px;}
.sw-scope .doc-no.amber{color:var(--amber);}
.sw-scope .doc-date{font-size:11px;color:var(--text-3);}

.sw-scope .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;}
.sw-scope .badge.pending{background:var(--amber-light);color:var(--amber);}
.sw-scope .badge.done{background:var(--green-light);color:var(--green);}
.sw-scope .badge.cancelled{background:#f1f5f9;color:#64748b;}

.sw-scope .toolbar{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);padding:12px 16px;margin-bottom:14px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;box-shadow:var(--sh-sm);}
.sw-scope .search-box{position:relative;flex:1;min-width:200px;}
.sw-scope .search-box input{width:100%;padding:8px 12px 8px 36px;border:1px solid var(--border);border-radius:var(--r-sm);font-family:'Sarabun',sans-serif;font-size:13.5px;background:var(--surface);outline:none;}
.sw-scope .search-box i{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-3);font-size:15px;}
.sw-scope .f-select{padding:8px 12px;border:1px solid var(--border);border-radius:var(--r-sm);font-family:'Sarabun',sans-serif;font-size:13.5px;background:var(--surface);cursor:pointer;outline:none;}

.sw-scope .info-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;font-size:13px;border-bottom:1px solid var(--border-soft);}
.sw-scope .info-row:last-child{border-bottom:none;}
.sw-scope .info-row-label{color:var(--text-3);font-size:12px;}
.sw-scope .info-row-val{font-weight:600;}

.sw-scope .summary-card{background:linear-gradient(135deg,var(--primary-light),#dbeafe);border-radius:var(--r-md);padding:14px 16px;margin-bottom:12px;}
.sw-scope .summary-label{font-size:11px;color:var(--primary-dark);text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;}
.sw-scope .summary-val{font-size:22px;font-weight:700;color:var(--primary-dark);}

.sw-scope .toast-wrap{position:fixed;top:68px;right:20px;z-index:5000;display:flex;flex-direction:column;gap:8px;}
.sw-scope .toast{background:var(--card);border:1px solid var(--border);border-radius:var(--r-md);padding:11px 16px;box-shadow:var(--sh-md);display:flex;align-items:center;gap:10px;font-size:13.5px;min-width:250px;animation:swtIn .22s ease;border-left:3px solid #22c55e;}
.sw-scope .toast.error{border-left-color:var(--red);}
.sw-scope .toast i{font-size:16px;color:#22c55e;}
.sw-scope .toast.error i{color:var(--red);}
@keyframes swtIn{from{opacity:0;transform:translateX(20px);}to{opacity:1;transform:translateX(0);}}

@media(max-width:1100px){.sw-scope .layout{grid-template-columns:1fr;}}
@media(max-width:768px){
  .sw-scope .page-wrap{padding:16px 14px 40px;}
  .sw-scope .fg,.sw-scope .fg.thirds{grid-template-columns:1fr;}
}
</style>

<div class="sw-scope">

<div class="mode-tabs">
  <div class="mode-inner">
    <a class="mode-tab" href="<?= $urlOverview ?>"><i class="bi bi-grid-1x2-fill"></i> ภาพรวม</a>
    <a class="mode-tab <?= $mode === 'in' ? 'active' : '' ?>" href="<?= $urlIn ?>" id="sw-tab-in"><i class="bi bi-arrow-down-circle-fill"></i> เบิกจากคลังกลาง (รับเข้า)</a>
    <a class="mode-tab <?= $mode === 'out' ? 'active' : '' ?>" href="<?= $urlOut ?>" id="sw-tab-out"><i class="bi bi-arrow-up-circle-fill"></i> จ่ายออกไปใช้</a>
    <div style="flex:1;"></div>
    <button type="button" class="mode-tab" id="sw-btn-new"><i class="bi bi-plus-lg"></i> สร้างใบใหม่</button>
  </div>
</div>

<main class="page-wrap">

  <div id="sw-listView">
    <div class="page-header">
      <div>
        <div class="page-title">
          <span class="page-title-icon in" id="sw-ptIcon"><i class="bi bi-arrow-down-circle-fill"></i></span>
          <span id="sw-ptTitle">ใบเบิกจากคลังกลาง</span>
        </div>
        <div class="page-subtitle" id="sw-ptSub">บันทึกการรับวัสดุจากคลังกลางเข้าคลังย่อยของหน่วยงาน</div>
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" class="sw-btn-outline" id="sw-btn-export"><i class="bi bi-download"></i> Export</button>
        <button type="button" class="sw-btn-primary" id="sw-btn-new2"><i class="bi bi-plus-lg"></i> สร้างใบใหม่</button>
      </div>
    </div>

    <div class="toolbar">
      <div class="search-box"><i class="bi bi-search"></i><input type="text" id="sw-searchInput" placeholder="ค้นหา เลขที่, ผู้เบิก, วัตถุประสงค์..."></div>
      <select class="f-select" id="sw-filterDept"><option value="">ทุกแผนก</option></select>
      <select class="f-select" id="sw-filterStatus">
        <option value="">ทุกสถานะ</option>
        <option value="pending">รอ</option>
        <option value="done">เสร็จ</option>
      </select>
      <input type="date" class="f-select" id="sw-filterDate">
    </div>

    <div class="list-card">
      <div class="tbl-responsive">
        <table class="list-table">
          <thead>
            <tr>
              <th>เลขที่ / วันที่</th>
              <th>ผู้เบิก / แผนก</th>
              <th>วัตถุประสงค์</th>
              <th class="center">รายการ</th>
              <th class="right">มูลค่ารวม</th>
              <th class="center">สถานะ</th>
              <th class="center">จัดการ</th>
            </tr>
          </thead>
          <tbody id="sw-listBody"></tbody>
        </table>
      </div>
    </div>
  </div>

  <div id="sw-formView" style="display:none;">
    <div class="page-header">
      <div>
        <div class="page-title">
          <span class="page-title-icon in" id="sw-fmIcon"><i class="bi bi-plus-circle-fill"></i></span>
          <span id="sw-fmTitle">สร้างใบเบิกใหม่</span>
        </div>
        <div class="page-subtitle" id="sw-fmSub">กรอกข้อมูลและเลือกรายการพัสดุ</div>
      </div>
      <button type="button" class="sw-btn-outline" id="sw-btn-back"><i class="bi bi-arrow-left"></i> กลับสู่รายการ</button>
    </div>

    <div class="layout">
      <div>
        <div class="panel">
          <div class="panel-head"><div class="panel-title"><i class="bi bi-info-circle-fill"></i> ข้อมูลใบเบิก</div></div>
          <div class="panel-body">
            <div class="fs-title"><i class="bi bi-person-fill"></i> ผู้เบิก / แผนก</div>
            <div class="fg thirds">
              <div class="form-group"><label class="form-label">เลขที่ใบ</label><input class="form-control" id="sw-f_no" readonly></div>
              <div class="form-group"><label class="form-label">วันที่ <span class="req">*</span></label><input type="date" class="form-control" id="sw-f_date"></div>
              <div class="form-group"><label class="form-label">แผนก (คลังย่อย) <span class="req">*</span></label>
                <select class="form-control" id="sw-f_dept"></select>
              </div>
            </div>
            <div class="fg">
              <div class="form-group"><label class="form-label">ผู้เบิก <span class="req">*</span></label><input class="form-control" id="sw-f_by" placeholder="ชื่อ-นามสกุลผู้เบิก"></div>
              <div class="form-group"><label class="form-label">ผู้อนุมัติ</label><input class="form-control" id="sw-f_approver" placeholder="หัวหน้าแผนก"></div>
            </div>

            <div class="fs-title"><i class="bi bi-bullseye"></i> วัตถุประสงค์</div>
            <div class="fg full">
              <div class="form-group">
                <label class="form-label">วัตถุประสงค์ในการเบิก <span class="req">*</span></label>
                <input class="form-control" id="sw-f_purpose" placeholder="เช่น เติมสต็อกประจำเดือน, ใช้บริการผู้ป่วย, ใช้งานซ่อม...">
              </div>
            </div>
            <div class="fg" id="sw-repairRefRow" style="display:none;">
              <div class="form-group">
                <label class="form-label">เชื่อมกับงานซ่อม</label>
                <select class="form-control" id="sw-f_repairRef"><option value="">-- ไม่เชื่อม --</option></select>
              </div>
              <div class="form-group">
                <label class="form-label">หมายเหตุ</label>
                <input class="form-control" id="sw-f_note">
              </div>
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-head">
            <div class="panel-title"><i class="bi bi-list-ul"></i> รายการพัสดุ (เพิ่มได้หลายรายการ)</div>
            <span id="sw-fifoHint" style="font-size:11px;color:var(--purple);font-weight:600;display:none;"><i class="bi bi-info-circle-fill"></i> ระบบจะตัดสต็อกตาม FIFO อัตโนมัติ</span>
          </div>
          <div class="panel-body">
            <div style="overflow-x:auto;">
              <table class="items-table">
                <thead>
                  <tr>
                    <th style="width:30px;">#</th>
                    <th>เลือกพัสดุ <span class="req">*</span></th>
                    <th class="right" style="width:100px;" id="sw-thAvail">คงคลัง</th>
                    <th class="right" style="width:90px;">จำนวน</th>
                    <th style="width:70px;">หน่วย</th>
                    <th class="right" style="width:100px;">ราคา</th>
                    <th class="right" style="width:110px;">รวม</th>
                    <th style="width:36px;"></th>
                  </tr>
                </thead>
                <tbody id="sw-itemsBody"></tbody>
                <tfoot>
                  <tr class="total-row">
                    <td colspan="6" class="right">รวมมูลค่าทั้งสิ้น</td>
                    <td class="right" id="sw-grandTotal">฿0.00</td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
            <button type="button" class="btn-add" id="sw-btn-addRow"><i class="bi bi-plus-lg"></i> เพิ่มรายการ</button>
          </div>
        </div>

        <div style="display:flex;justify-content:space-between;gap:10px;padding:14px 18px;background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);box-shadow:var(--sh-sm);flex-wrap:wrap;">
          <button type="button" class="sw-btn-outline" id="sw-btn-cancel">ยกเลิก</button>
          <div style="display:flex;gap:8px;">
            <button type="button" class="sw-btn-outline" id="sw-btn-draft"><i class="bi bi-floppy-fill"></i> บันทึกร่าง</button>
            <button type="button" class="sw-btn-success" id="sw-btn-submit"><i class="bi bi-check-lg"></i> ยืนยัน <span id="sw-submitLabel"></span></button>
          </div>
        </div>
      </div>

      <aside>
        <div class="panel">
          <div class="panel-head"><div class="panel-title"><i class="bi bi-bar-chart-fill"></i> สรุปใบเบิก</div></div>
          <div class="panel-body">
            <div class="summary-card">
              <div class="summary-label">มูลค่ารวม</div>
              <div class="summary-val" id="sw-sumTotal">฿0</div>
            </div>
            <div class="info-row"><span class="info-row-label">จำนวนรายการ</span><span class="info-row-val" id="sw-sumItems">0</span></div>
            <div class="info-row"><span class="info-row-label">ผู้เบิก</span><span class="info-row-val" id="sw-sumBy">—</span></div>
            <div class="info-row"><span class="info-row-label">แผนก</span><span class="info-row-val" id="sw-sumDept">—</span></div>
            <div class="info-row"><span class="info-row-label">ประเภท</span><span class="info-row-val" id="sw-sumMode">—</span></div>
          </div>
        </div>

        <div class="panel" id="sw-fifoPanel" style="display:none;">
          <div class="panel-head"><div class="panel-title"><i class="bi bi-stack"></i> Lot ที่จะถูกตัด (FIFO)</div></div>
          <div class="panel-body" id="sw-fifoPreview"></div>
        </div>
      </aside>
    </div>
  </div>
</main>

<div class="toast-wrap" id="sw-toastWrap"></div>
</div>

<script>
(function(){
const MODE = <?= json_encode($mode) ?>;

const DEPTS = [
  {id:'er',name:'เวชกิจฉุกเฉิน'},
  {id:'opd',name:'OPD'},
  {id:'dental',name:'ทันตกรรม'},
  {id:'admin',name:'ธุรการ'},
  {id:'maint',name:'งานซ่อมบำรุง'},
  {id:'phc',name:'สาธารณสุข'},
];

const CAT_LABEL = {medical:'เวชภัณฑ์/ยา',office:'สำนักงาน',cleaning:'ทำความสะอาด',electric:'ไฟฟ้า',lab:'แล็บ',food:'อาหาร',spare:'อะไหล่'};

const mainStock = JSON.parse(localStorage.getItem('erp_inventory')||'null') || [
  {id:1,code:'MED-001',name:'พาราเซตามอล 500mg',unit:'แผง',price:25,stock:240,cat:'medical'},
  {id:2,code:'MED-002',name:'สำลีก้อน 500g',unit:'ห่อ',price:120,stock:18,cat:'medical'},
  {id:3,code:'MED-003',name:'เข็มฉีดยา 21G',unit:'กล่อง',price:180,stock:0,cat:'medical'},
  {id:4,code:'OFF-001',name:'กระดาษ A4 80gsm',unit:'รีม',price:135,stock:85,cat:'office'},
  {id:5,code:'OFF-002',name:'ปากกาลูกลื่น',unit:'ด้าม',price:8,stock:320,cat:'office'},
  {id:6,code:'OFF-003',name:'หมึก HP 26A',unit:'ตลับ',price:2800,stock:3,cat:'office'},
  {id:7,code:'CLN-001',name:'น้ำยาฆ่าเชื้อ 1L',unit:'ขวด',price:185,stock:42,cat:'cleaning'},
  {id:8,code:'SPR-001',name:'น้ำยาแอร์ R32',unit:'กก.',price:850,stock:8,cat:'spare'},
  {id:9,code:'SPR-002',name:'หลอด LED 9W',unit:'หลอด',price:75,stock:55,cat:'electric'},
];

let subStock = JSON.parse(localStorage.getItem('erp_substock')||'[]');
let subMoves = JSON.parse(localStorage.getItem('erp_submoves')||'[]');
const REPAIRS = JSON.parse(localStorage.getItem('erp_repairs')||'[]');

let formDeptId = '';
let formItems = [];
let editingId = null;

const $ = id => document.getElementById(id);

function saveAll(){
  localStorage.setItem('erp_substock', JSON.stringify(subStock));
  localStorage.setItem('erp_submoves', JSON.stringify(subMoves));
}

function setMode(){
  if(MODE === 'in'){
    $('sw-ptIcon').className = 'page-title-icon in';
    $('sw-ptIcon').innerHTML = '<i class="bi bi-arrow-down-circle-fill"></i>';
    $('sw-ptTitle').textContent = 'ใบเบิกจากคลังกลาง (รับเข้าคลังย่อย)';
    $('sw-ptSub').textContent = 'บันทึกการรับวัสดุจากคลังกลางเข้าคลังย่อยของหน่วยงาน';
    $('sw-thAvail').textContent = 'คลังกลาง';
    $('sw-fifoHint').style.display = 'none';
    $('sw-submitLabel').textContent = 'รับเข้า';
    $('sw-repairRefRow').style.display = 'none';
  } else {
    $('sw-ptIcon').className = 'page-title-icon out';
    $('sw-ptIcon').innerHTML = '<i class="bi bi-arrow-up-circle-fill"></i>';
    $('sw-ptTitle').textContent = 'ใบจ่ายออก (เบิกใช้งาน)';
    $('sw-ptSub').textContent = 'เบิกพัสดุจากคลังย่อยไปใช้ในงาน · ตัด FIFO อัตโนมัติ · เชื่อมกับงานซ่อมได้';
    $('sw-thAvail').textContent = 'คงคลังย่อย';
    $('sw-fifoHint').style.display = '';
    $('sw-submitLabel').textContent = 'จ่ายออก';
    $('sw-repairRefRow').style.display = '';
  }
  const deptSel = $('sw-f_dept');
  deptSel.innerHTML = '<option value="">-- เลือกแผนก --</option>' + DEPTS.map(d=>`<option value="${d.id}">${d.name}</option>`).join('');
  const filterDept = $('sw-filterDept');
  filterDept.innerHTML = '<option value="">ทุกแผนก</option>' + DEPTS.map(d=>`<option value="${d.id}">${d.name}</option>`).join('');
  const repairSel = $('sw-f_repairRef');
  const repairs = REPAIRS.filter(r => r.status === 'pending' || r.status === 'progress');
  repairSel.innerHTML = '<option value="">-- ไม่เชื่อม --</option>' + repairs.map(r=>`<option value="${r.no}">${r.no} · ${r.title}</option>`).join('');
}

function renderList(){
  const q = $('sw-searchInput').value.toLowerCase();
  const dept = $('sw-filterDept').value;
  const status = $('sw-filterStatus').value;
  const date = $('sw-filterDate').value;
  const list = subMoves.filter(m => {
    if(m.type !== MODE) return false;
    if(q && !(`${m.no} ${m.by} ${m.purpose}`.toLowerCase().includes(q))) return false;
    if(dept && m.deptId !== dept) return false;
    if(status && m.status !== status) return false;
    if(date && m.date !== date) return false;
    return true;
  }).sort((a,b)=>new Date(b.date)-new Date(a.date));
  const tbody = $('sw-listBody');
  if(!list.length){
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-3);">ไม่พบใบเบิก</td></tr>`;
    return;
  }
  tbody.innerHTML = list.map(m => {
    const d = DEPTS.find(x=>x.id===m.deptId);
    const noClass = m.type === 'out' ? 'amber' : '';
    return `<tr data-edit-id="${m.id}">
      <td><div class="doc-no ${noClass}">${m.no}</div><div class="doc-date">${new Date(m.date).toLocaleDateString('th-TH',{day:'numeric',month:'short',year:'2-digit'})}</div></td>
      <td><div style="font-weight:600;">${m.by}</div><div style="font-size:11px;color:var(--text-3);">${d?d.name:''}</div></td>
      <td>${m.purpose}${m.repairRef?`<div style="font-size:11px;color:var(--red);margin-top:2px;"><i class="bi bi-tools"></i> ${m.repairRef}</div>`:''}</td>
      <td class="center">${m.items}</td>
      <td class="right"><strong>฿${m.total.toLocaleString()}</strong></td>
      <td class="center"><span class="badge ${m.status}">${m.status==='pending'?'รอ':m.status==='done'?'เสร็จ':'ยกเลิก'}</span></td>
      <td class="center"><button type="button" data-view-id="${m.id}" style="background:transparent;border:none;color:var(--primary);cursor:pointer;padding:6px;"><i class="bi bi-eye-fill"></i></button></td>
    </tr>`;
  }).join('');
  tbody.querySelectorAll('tr[data-edit-id]').forEach(tr => {
    tr.addEventListener('click', () => openEdit(+tr.dataset.editId));
  });
  tbody.querySelectorAll('button[data-view-id]').forEach(b => {
    b.addEventListener('click', (e) => { e.stopPropagation(); openEdit(+b.dataset.viewId); });
  });
}

['sw-searchInput','sw-filterDept','sw-filterStatus','sw-filterDate'].forEach(id => {
  $(id).addEventListener('input', renderList);
  $(id).addEventListener('change', renderList);
});

function openForm(){
  editingId = null;
  formItems = [{itemId:'', qty:1, price:0, unit:'', name:''}];
  formDeptId = '';
  const maxN = subMoves.filter(m=>m.type===MODE).length+1;
  const prefix = MODE === 'in' ? 'WI' : 'WO';
  $('sw-f_no').value = `${prefix}-2569-${String(maxN).padStart(4,'0')}`;
  $('sw-f_date').value = new Date().toISOString().slice(0,10);
  $('sw-f_dept').value = '';
  $('sw-f_by').value = '';
  $('sw-f_approver').value = '';
  $('sw-f_purpose').value = '';
  $('sw-f_repairRef').value = '';
  $('sw-f_note').value = '';
  $('sw-listView').style.display = 'none';
  $('sw-formView').style.display = '';
  $('sw-fmIcon').className = 'page-title-icon ' + MODE;
  $('sw-fmIcon').innerHTML = `<i class="bi bi-${MODE==='in'?'arrow-down-circle-fill':'arrow-up-circle-fill'}"></i>`;
  $('sw-fmTitle').textContent = MODE === 'in' ? 'รับเข้าคลังย่อย' : 'จ่ายออกไปใช้';
  $('sw-fmSub').textContent = MODE === 'in' ? 'เบิกวัสดุจากคลังกลางเข้าคลังย่อย' : 'จ่ายวัสดุจากคลังย่อยเพื่อใช้งาน — ตัดตาม FIFO';
  $('sw-sumMode').textContent = MODE === 'in' ? 'รับเข้า (จากคลังกลาง)' : 'จ่ายออก (ตัด FIFO)';
  renderRows();
}

function openEdit(id){
  const m = subMoves.find(x=>x.id===id); if(!m) return;
  editingId = id;
  formItems = (m.itemsList || []).map(it=>({...it}));
  if(!formItems.length) formItems = [{itemId:'',qty:1,price:0,unit:'',name:''}];
  formDeptId = m.deptId;
  $('sw-f_no').value = m.no;
  $('sw-f_date').value = m.date;
  $('sw-f_dept').value = m.deptId;
  $('sw-f_by').value = m.by;
  $('sw-f_approver').value = m.approver || '';
  $('sw-f_purpose').value = m.purpose;
  $('sw-f_repairRef').value = m.repairRef || '';
  $('sw-f_note').value = m.note || '';
  $('sw-listView').style.display = 'none';
  $('sw-formView').style.display = '';
  $('sw-fmTitle').textContent = (MODE === 'in' ? 'แก้ไขใบรับเข้า' : 'แก้ไขใบจ่ายออก');
  $('sw-sumMode').textContent = MODE === 'in' ? 'รับเข้า' : 'จ่ายออก';
  renderRows();
}

function cancelForm(){
  $('sw-listView').style.display = '';
  $('sw-formView').style.display = 'none';
  renderList();
}

$('sw-f_dept').addEventListener('change', function(){
  formDeptId = this.value;
  renderRows();
});

function renderRows(){
  const tbody = $('sw-itemsBody');
  tbody.innerHTML = formItems.map((it,i) => {
    let availStock = 0, fifoHint = '', warn = false;
    if(MODE === 'in'){
      const main = mainStock.find(x=>x.id===+it.itemId);
      if(main) availStock = main.stock;
      if(+it.qty > availStock) warn = true;
    } else {
      if(formDeptId){
        const sub = subStock.find(x=>x.id===+it.itemId && x.deptId===formDeptId);
        if(sub){
          availStock = (sub.lots||[]).reduce((s,l)=>s+(+l.qty||0), 0);
          const lots = (sub.lots||[]).filter(l=>+l.qty>0).sort((a,b)=>new Date(a.received)-new Date(b.received));
          if(lots.length) fifoHint = `FIFO: ${lots[0].lot}`;
        }
        if(+it.qty > availStock) warn = true;
      }
    }
    const options = MODE === 'in'
      ? mainStock.map(s=>`<option value="${s.id}" ${+it.itemId===s.id?'selected':''}>${s.code} · ${s.name}</option>`).join('')
      : (formDeptId ? subStock.filter(s=>s.deptId===formDeptId).map(s=>`<option value="${s.id}" ${+it.itemId===s.id?'selected':''}>${s.code} · ${s.name}</option>`).join('') : '');
    return `<tr>
      <td style="text-align:center;color:var(--text-3);font-weight:600;">${i+1}</td>
      <td>
        <select data-act="select" data-i="${i}">
          <option value="">${MODE==='out' && !formDeptId ? '-- เลือกแผนกก่อน --' : '-- เลือกพัสดุ --'}</option>
          ${options}
        </select>
        ${fifoHint ? `<div class="fifo-hint">${fifoHint}</div>` : ''}
      </td>
      <td class="right"><span class="stock-hint ${warn?'low':''}">${availStock} ${it.unit||''}</span></td>
      <td class="right"><input type="number" min="1" max="${availStock}" value="${it.qty||''}" data-act="qty" data-i="${i}"></td>
      <td>${it.unit||'—'}</td>
      <td class="right">฿${it.price||0}</td>
      <td class="right">฿${((+it.qty||0)*it.price).toLocaleString('th-TH',{minimumFractionDigits:2})}</td>
      <td>${formItems.length>1 ? `<button type="button" class="btn-rm" data-act="rm" data-i="${i}"><i class="bi bi-trash3"></i></button>` : ''}</td>
    </tr>`;
  }).join('');
  tbody.querySelectorAll('[data-act="select"]').forEach(el => {
    el.addEventListener('change', function(){
      const i = +this.dataset.i;
      formItems[i].itemId = this.value;
      onSelect(i);
    });
  });
  tbody.querySelectorAll('[data-act="qty"]').forEach(el => {
    el.addEventListener('input', function(){
      const i = +this.dataset.i;
      formItems[i].qty = +this.value;
      updateTotal();
      renderRows();
    });
  });
  tbody.querySelectorAll('[data-act="rm"]').forEach(el => {
    el.addEventListener('click', function(){
      formItems.splice(+this.dataset.i, 1);
      renderRows();
    });
  });
  updateTotal();
  renderFifoPreview();
}

function onSelect(i){
  const id = +formItems[i].itemId;
  let src;
  if(MODE === 'in'){
    src = mainStock.find(x=>x.id===id);
  } else {
    src = subStock.find(x=>x.id===id && x.deptId===formDeptId);
  }
  if(src){
    formItems[i].price = src.price;
    formItems[i].unit = src.unit;
    formItems[i].name = src.name;
    formItems[i].code = src.code;
  }
  renderRows();
}

function addRow(){ formItems.push({itemId:'', qty:1, price:0, unit:'', name:''}); renderRows(); }

function updateTotal(){
  const total = formItems.reduce((s,it)=>s+((+it.qty||0)*it.price), 0);
  $('sw-grandTotal').textContent = '฿' + total.toLocaleString('th-TH',{minimumFractionDigits:2});
  $('sw-sumTotal').textContent = '฿' + total.toLocaleString();
  $('sw-sumItems').textContent = formItems.filter(it=>it.itemId && it.qty>0).length;
  $('sw-sumBy').textContent = $('sw-f_by').value || '—';
  const d = DEPTS.find(x=>x.id===$('sw-f_dept').value);
  $('sw-sumDept').textContent = d?d.name:'—';
}

function renderFifoPreview(){
  if(MODE !== 'out' || !formDeptId){ $('sw-fifoPanel').style.display='none'; return; }
  const validItems = formItems.filter(it=>it.itemId && it.qty>0);
  if(!validItems.length){ $('sw-fifoPanel').style.display='none'; return; }
  let html = '';
  validItems.forEach(it => {
    const sub = subStock.find(x=>x.id===+it.itemId && x.deptId===formDeptId);
    if(!sub) return;
    const lots = (sub.lots||[]).filter(l=>+l.qty>0).sort((a,b)=>new Date(a.received)-new Date(b.received));
    let need = +it.qty;
    const cuts = [];
    for(const l of lots){
      if(need <= 0) break;
      const cut = Math.min(l.qty, need);
      cuts.push({lot:l.lot, qty:cut});
      need -= cut;
    }
    html += `<div style="margin-bottom:12px;">
      <div style="font-size:12.5px;font-weight:600;">${it.name||it.code||'—'} · ขอ ${it.qty} ${it.unit||''}</div>
      ${cuts.map(c=>`<div style="font-size:11.5px;color:var(--purple);font-family:'Courier New',monospace;margin-left:10px;">→ ${c.lot} : -${c.qty} ${it.unit||''}</div>`).join('') || '<div style="font-size:11px;color:var(--red);margin-left:10px;">สต็อกไม่พอ</div>'}
    </div>`;
  });
  $('sw-fifoPreview').innerHTML = html;
  $('sw-fifoPanel').style.display = '';
}

$('sw-f_by').addEventListener('input', updateTotal);

function collectForm(){
  const validItems = formItems.filter(it=>it.itemId && it.qty>0);
  return {
    no: $('sw-f_no').value,
    type: MODE,
    deptId: $('sw-f_dept').value,
    date: $('sw-f_date').value,
    by: $('sw-f_by').value.trim(),
    approver: $('sw-f_approver').value.trim(),
    purpose: $('sw-f_purpose').value.trim(),
    repairRef: $('sw-f_repairRef').value || null,
    note: $('sw-f_note').value.trim(),
    items: validItems.length,
    total: validItems.reduce((s,it)=>s+(+it.qty*it.price), 0),
    itemsList: validItems.map(it=>({itemId:+it.itemId,qty:+it.qty,price:it.price,unit:it.unit,name:it.name,code:it.code})),
  };
}

function validate(d){
  if(!d.deptId){ toast('กรุณาเลือกแผนก','error'); return false; }
  if(!d.by){ toast('กรุณาระบุผู้เบิก','error'); return false; }
  if(!d.purpose){ toast('กรุณาระบุวัตถุประสงค์','error'); return false; }
  if(!d.itemsList.length){ toast('กรุณาเพิ่มรายการ','error'); return false; }
  return true;
}

function saveDraft(){
  const d = collectForm();
  if(!d.itemsList.length){ toast('กรุณาเพิ่มรายการ','error'); return; }
  upsert(d, 'pending');
  toast('บันทึกร่างเรียบร้อย');
  cancelForm();
}

function submitRequest(){
  const d = collectForm();
  if(!validate(d)) return;
  if(MODE === 'in'){
    d.itemsList.forEach(it => {
      let sub = subStock.find(s=>s.id===it.itemId && s.deptId===d.deptId);
      if(!sub){
        const src = mainStock.find(m=>m.id===it.itemId);
        sub = { id:it.itemId, code:src.code, name:src.name, cat:src.cat, unit:src.unit, price:src.price, deptId:d.deptId, min:Math.max(5,Math.ceil(it.qty/3)), lots:[] };
        subStock.push(sub);
      }
      const lotNo = `L${d.date.replace(/-/g,'').slice(2)}-${String(sub.lots.length+1).padStart(2,'0')}`;
      sub.lots.push({lot:lotNo, qty:it.qty, expire:null, received:d.date});
      const main = mainStock.find(m=>m.id===it.itemId);
      if(main) main.stock = Math.max(0, main.stock - it.qty);
    });
    localStorage.setItem('erp_inventory', JSON.stringify(mainStock));
  } else {
    d.itemsList.forEach(it => {
      const sub = subStock.find(s=>s.id===it.itemId && s.deptId===d.deptId);
      if(!sub) return;
      const lots = (sub.lots||[]).filter(l=>+l.qty>0).sort((a,b)=>new Date(a.received)-new Date(b.received));
      let need = it.qty;
      for(const l of lots){
        if(need <= 0) break;
        const cut = Math.min(l.qty, need);
        l.qty -= cut;
        need -= cut;
      }
    });
  }
  upsert(d, 'done');
  toast(MODE === 'in' ? 'รับเข้าคลังย่อยเรียบร้อย' : 'จ่ายออกเรียบร้อย — ตัด FIFO แล้ว');
  cancelForm();
}

function upsert(data, status){
  data.status = status;
  if(editingId){
    const idx = subMoves.findIndex(m=>m.id===editingId);
    if(idx !== -1) subMoves[idx] = {...subMoves[idx], ...data};
  } else {
    const newId = Math.max(0,...subMoves.map(m=>m.id))+1;
    subMoves.push({id:newId, ...data});
  }
  saveAll();
}

function exportCSV(){
  const list = subMoves.filter(m=>m.type===MODE);
  const rows = [['เลขที่','วันที่','ผู้เบิก','แผนก','วัตถุประสงค์','รายการ','มูลค่า','สถานะ']];
  list.forEach(m => {
    const d = DEPTS.find(x=>x.id===m.deptId);
    rows.push([m.no, m.date, m.by, d?d.name:'', m.purpose, m.items, m.total, m.status]);
  });
  const csv = '﻿' + rows.map(r=>r.map(v=>`"${v}"`).join(',')).join('\n');
  const a = document.createElement('a');
  a.href = URL.createObjectURL(new Blob([csv],{type:'text/csv;charset=utf-8'}));
  a.download = `subwarehouse_${MODE}_${new Date().toISOString().slice(0,10)}.csv`;
  a.click();
  toast('ส่งออก CSV เรียบร้อย');
}

function toast(msg, type){
  type = type || 'success';
  const t = document.createElement('div');
  t.className = 'toast ' + (type==='error'?'error':'');
  t.innerHTML = `<i class="bi bi-${type==='error'?'exclamation':'check'}-circle-fill"></i><span>${msg}</span>`;
  $('sw-toastWrap').appendChild(t);
  setTimeout(()=>t.remove(), 3000);
}

$('sw-btn-new').addEventListener('click', openForm);
$('sw-btn-new2').addEventListener('click', openForm);
$('sw-btn-back').addEventListener('click', cancelForm);
$('sw-btn-cancel').addEventListener('click', cancelForm);
$('sw-btn-draft').addEventListener('click', saveDraft);
$('sw-btn-submit').addEventListener('click', submitRequest);
$('sw-btn-export').addEventListener('click', exportCSV);
$('sw-btn-addRow').addEventListener('click', addRow);

setMode();
renderList();
})();
</script>
