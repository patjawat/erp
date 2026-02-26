<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted fw-bold uppercase">มูลค่าวัสดุคงคลังรวม</h6>
                    <h3 class="mb-0">฿1,245,000</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted fw-bold uppercase">ใบเบิกค้างอนุมัติ</h6>
                    <h3 class="mb-0">12 รายการ</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-danger border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted fw-bold uppercase">พัสดุใกล้หมดอายุ (90 วัน)</h6>
                    <h3 class="mb-0 text-danger">8 รายการ</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted fw-bold uppercase">รับเข้าเดือนนี้</h6>
                    <h3 class="mb-0 text-success">45 ครั้ง</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-grid-3x3-gap"></i> สรุปยอดแยกตามกลุ่มวัสดุ</h5>
                    <button class="btn btn-sm btn-outline-secondary">ดูทั้งหมด</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>หมวด</th>
                                    <th>ชื่อหมวดวัสดุ</th>
                                    <th class="text-center">คงเหลือ</th>
                                    <th class="text-end">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge text-bg-success">M7</span></td>
                                    <td>วัสดุวิทยาศาสตร์หรือการแพทย์</td>
                                    <td class="text-center fw-bold">14,200</td>
                                    <td class="text-end"><span class="text-success"><i class="bi bi-check-circle-fill"></i> ปกติ</span></td>
                                </tr>
                                <tr>
                                    <td><span class="badge text-bg-primary">M22</span></td>
                                    <td>วัสดุการแพทย์ทั่วไป</td>
                                    <td class="text-center fw-bold">8,500</td>
                                    <td class="text-end"><span class="text-success"><i class="bi bi-check-circle-fill"></i> ปกติ</span></td>
                                </tr>
                                <tr>
                                    <td><span class="badge text-bg-warning text-dark">M1</span></td>
                                    <td>วัสดุสำนักงาน</td>
                                    <td class="text-center fw-bold text-danger">120</td>
                                    <td class="text-end"><span class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> ต่ำกว่าเกณฑ์</span></td>
                                </tr>
                                <tr>
                                    <td><span class="badge text-bg-info text-dark">M26</span></td>
                                    <td>วัสดุการแพทย์ ออกซิเจน</td>
                                    <td class="text-center fw-bold">450</td>
                                    <td class="text-end"><span class="text-success"><i class="bi bi-check-circle-fill"></i> ปกติ</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-alarm"></i> รายการด่วนต้องจัดการ</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 text-danger fw-bold">EXP Soon: น้ำเกลือ NSS 0.9%</h6>
                            <small>Lot: 6601</small>
                        </div>
                        <p class="mb-1 text-muted small">จะหมดอายุในอีก 15 วัน (จำนวน 40 ขวด)</p>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 fw-bold text-warning">Low Stock: หน้ากากอนามัย</h6>
                            <small>M7</small>
                        </div>
                        <p class="mb-1 text-muted small">คงเหลือ 2 กล่อง (จุดสั่งเติม 10 กล่อง)</p>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 fw-bold text-primary">ใบเบิกใหม่: รพ.สต. บ้านโพธิ์</h6>
                            <small>2 ชม. ที่แล้ว</small>
                        </div>
                        <p class="mb-1 text-muted small">รออนุมัติจ่ายวัสดุการแพทย์ 5 รายการ</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>