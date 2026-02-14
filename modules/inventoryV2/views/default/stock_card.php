<?php
use yii\web\View;
$this->title = 'Stock Card | บัตรควบคุมพัสดุ';
?>


<div class="card">
    <div class="card-body">
<div class="row">
        <div class="col-md-7">
            <h3 class="fw-bold text-dark mb-0">Stock Card (บัตรควบคุมพัสดุ)</h3>
            <p class="text-muted">ตรวจสอบการเคลื่อนไหวและยอดคงเหลือสะสมรายรายการ</p>
        </div>
        <div class="col-md-5 text-md-end">
            <button class="btn btn-outline-primary rounded-pill px-4 me-2">
                <i class="bi bi-printer me-2"></i>พิมพ์รายงาน
            </button>
            <button class="btn btn-success rounded-pill px-4">
                <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
            </button>
        </div>
    </div>
    </div>
</div>

    

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-primary">1. เลือกรายการพัสดุ</label>
                    <select class="form-select border-0 bg-light rounded-3 shadow-none select2">
                        <option>SSD 500GB Samsung (IT-0023)</option>
                        <option>สาย LAN CAT6 (300m)</option>
                        <option>หลอดไฟ LED 18W</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-primary">2. คลังสินค้า</label>
                    <select class="form-select border-0 bg-light rounded-3">
                        <option value="all">ทุกคลัง (รวม)</option>
                        <option value="1">คลังพัสดุกลาง</option>
                        <option value="2">คลังย่อยแผนกไอที</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-primary">3. ช่วงเวลา</label>
                    <div class="input-group">
                        <input type="date" class="form-control border-0 bg-light rounded-start-3" value="2026-02-01">
                        <span class="input-group-text border-0 bg-light">-</span>
                        <input type="date" class="form-control border-0 bg-light rounded-end-3" value="2026-02-14">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100 rounded-3 py-2 fw-bold shadow-sm">
                        <i class="bi bi-search me-2"></i>แสดงข้อมูล
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-3">
                <small class="text-white-50">ยอดยกมา (Balance Brought Forward)</small>
                <h4 class="fw-bold mb-0">120 <small class="fs-6 fw-normal">ชิ้น</small></h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <small class="text-muted">ความเคลื่อนไหว (In / Out)</small>
                <h4 class="fw-bold mb-0">
                    <span class="text-success">+20</span> / <span class="text-danger">-15</span>
                </h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white p-3">
                <small class="text-white-50">คงเหลือ ณ ปัจจุบัน (Current Balance)</small>
                <h4 class="fw-bold mb-0 text-warning">125 <small class="fs-6 fw-normal">ชิ้น</small></h4>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="stockCardTable">
                <thead class="bg-secondary text-white text-center">
                    <tr>
                        <th rowspan="2" class="align-middle" width="12%">วัน/เวลา</th>
                        <th rowspan="2" class="align-middle" width="15%">เลขที่เอกสาร</th>
                        <th rowspan="2" class="align-middle" width="20%">รายการธุรกรรม/คลัง</th>
                        <th colspan="3" class="border-bottom">จำนวน (Units)</th>
                        <th rowspan="2" class="align-middle" width="10%">Lot</th>
                    </tr>
                    <tr>
                        <th class="bg-success bg-opacity-75 text-white" width="10%">รับเข้า (+)</th>
                        <th class="bg-danger bg-opacity-75 text-white" width="10%">จ่ายออก (-)</th>
                        <th class="bg-dark bg-opacity-75 text-white" width="10%">คงเหลือ</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <tr class="table-light italic">
                        <td>01/02/2026</td>
                        <td class="text-muted">-</td>
                        <td class="text-start ps-4">ยอดยกมาสะสม</td>
                        <td>-</td>
                        <td>-</td>
                        <td class="fw-bold">120</td>
                        <td class="text-muted">-</td>
                    </tr>
                    <tr>
                        <td>05/02/2026 10:30</td>
                        <td><span class="badge bg-light text-dark border">RCV-67-004</span></td>
                        <td class="text-start ps-4">รับเข้าจากซัพพลายเออร์ (คลังหลัก)</td>
                        <td class="text-success fw-bold">+20</td>
                        <td>-</td>
                        <td class="fw-bold">140</td>
                        <td>LOT67-01</td>
                    </tr>
                    <tr>
                        <td>11/02/2026 14:15</td>
                        <td><span class="badge bg-light text-dark border">ISS-67-045</span></td>
                        <td class="text-start ps-4">จ่ายของให้: แผนกไอที (IT Dept)</td>
                        <td>-</td>
                        <td class="text-danger fw-bold">-10</td>
                        <td class="fw-bold">130</td>
                        <td>LOT67-01</td>
                    </tr>
                    <tr>
                        <td>12/02/2026 09:00</td>
                        <td><span class="badge bg-light text-dark border">ISS-67-048</span></td>
                        <td class="text-start ps-4">จ่ายของให้: แผนกซ่อมบำรุง</td>
                        <td>-</td>
                        <td class="text-danger fw-bold">-5</td>
                        <td class="fw-bold">125</td>
                        <td>LOT67-05</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<style>
    #stockCardTable thead th { font-size: 0.85rem; padding: 12px; }
    #stockCardTable tbody td { font-size: 0.9rem; padding: 15px; }
    .table-hover tbody tr:hover { background-color: #f8faff; }
    .italic { font-style: italic; }
</style>