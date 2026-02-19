<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-journal-text"></i> ทะเบียนรับเข้าวัสดุ</h5>
            <button class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> เพิ่มการรับเข้าใหม่</button>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-4">
                <div class="col-md-3">
                    <input type="text" id="searchRegistry" class="form-control" placeholder="ค้นหาเลขที่เอกสาร/วัสดุ...">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" id="filterDate">
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="filterType">
                        <option value="">ทุกหมวด M</option>
                        <option value="M7">M7 - วิทยาศาสตร์/การแพทย์</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-secondary w-100"><i class="bi bi-funnel"></i></button>
                </div>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>เลขที่เอกสาร</th>
                            <th>รายการหลัก</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="registryBody">
                        <tr id="row-1">
                            <td>INIT-67-001</td>
                            <td>ถุงมือตรวจโรค (100 กล่อง), ผ้าก๊อซ (50 ห่อ)</td>
                            <td><span class="badge bg-warning text-dark" id="status-1">รอตรวจรับ</span></td>
                            <td>
                                <button class="btn btn-sm btn-success btn-verify" data-id="1">
                                    <i class="bi bi-check-lg"></i> ตรวจรับเข้าสต็อก
                                </button>
                                <button class="btn btn-sm btn-outline-secondary">ดูรายละเอียด</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> แก้ไขรายการรับเข้า: <span id="editDocNo"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="editContent">
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-info-circle"></i> <strong>หมายเหตุ:</strong> การแก้ไขข้อมูลที่มีการเบิกจ่ายไปแล้ว อาจส่งผลกระทบต่อยอดสต็อกการ์ด
                    </div>
                    <table class="table table-sm table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>รายการ</th>
                                <th width="150">Lot No.</th>
                                <th width="150">วันหมดอายุ</th>
                                <th width="100">จำนวน</th>
                                <th>หน่วย</th>
                            </tr>
                        </thead>
                        <tbody id="editItemBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary" id="btnUpdateSave">บันทึกการแก้ไข</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // เมื่อกดปุ่มแก้ไข
        $(document).on('click', '.btn-edit', function() {
            let docNo = $(this).closest('tr').data('doc');
            $('#editDocNo').text(docNo);

            // จำลองการดึงข้อมูลมาแสดงใน Modal
            let mockupItems = `
            <tr>
                <td>ถุงมือตรวจโรค (Size M)</td>
                <td><input type="text" class="form-control form-control-sm" value="LOT67-001"></td>
                <td><input type="date" class="form-control form-control-sm" value="2026-12-31"></td>
                <td><input type="number" class="form-control form-control-sm text-center" value="100"></td>
                <td>กล่อง</td>
            </tr>
        `;
            $('#editItemBody').html(mockupItems);
            $('#editModal').modal('show');
        });

        // เมื่อกดยืนยันการแก้ไข
        $('#btnUpdateSave').click(function() {
            Swal.fire({
                title: 'ยืนยันการแก้ไขข้อมูล?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('แก้ไขสำเร็จ!', 'ข้อมูลถูกอัปเดตเรียบร้อย', 'success');
                    $('#editModal').modal('hide');
                }
            });
        });

        $(document).on('click', '.btn-verify', function() {
            let rowId = $(this).data('id');

            Swal.fire({
                title: 'ยืนยันการตรวจรับ?',
                text: "เมื่อยืนยันแล้ว ยอดพัสดุจะถูกเพิ่มเข้าสต็อกแยกตาม Lot ทันที",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: 'ยืนยันการตรวจรับ'
            }).then((result) => {
                if (result.isConfirmed) {
                    // จำลองการเปลี่ยนสถานะใน UI
                    $(`#status-${rowId}`).removeClass('bg-warning text-dark').addClass('bg-success').text('รับเข้าสต็อกแล้ว');
                    $(this).remove(); // เอาปุ่ม Verify ออก

                    Swal.fire('สำเร็จ!', 'เพิ่มยอดเข้าสต็อกแยกตาม Lot เรียบร้อยแล้ว', 'success');

                    // LOGIC เบื้องหลัง (Backend Concept):
                    // 1. ไปดึงรายการจาก stock_details ของใบรับนี้
                    // 2. Loop แต่ละรายการไป UPDATE quantity ในตาราง stocks 
                    //    WHERE item_id = x AND lot_number = y
                    // 3. ถ้าไม่มี lot นั้นให้ INSERT ใหม่
                }
            });
        });

    });
</script>


<button class="btn btn-sm btn-info text-white" onclick="viewDetail('INIT-67-001')">
    <i class="bi bi-search"></i> ดูรายละเอียด/ตรวจรับ
</button>

<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-check"></i> รายละเอียดการรับเข้าวัสดุ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 border-end">
                                <small class="text-muted d-block">เลขที่เอกสาร</small>
                                <strong id="detDocNo" class="text-primary">-</strong>
                            </div>
                            <div class="col-md-3 border-end">
                                <small class="text-muted d-block">ประเภท</small>
                                <strong id="detType">-</strong>
                            </div>
                            <div class="col-md-3 border-end">
                                <small class="text-muted d-block">วันที่บันทึก</small>
                                <strong id="detDate">-</strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">สถานะปัจจุบัน</small>
                                <span id="detStatusBadge" class="badge rounded-pill">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="5%">#</th>
                                    <th width="35%">รายการวัสดุ</th>
                                    <th width="15%">Lot Number</th>
                                    <th width="15%">วันหมดอายุ</th>
                                    <th class="text-center" width="10%">จำนวน</th>
                                    <th class="text-end" width="10%">ราคาทุน</th>
                                    <th class="text-end" width="10%">รวม</th>
                                </tr>
                            </thead>
                            <tbody id="detailItemBody">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                <button type="button" class="btn btn-dark"><i class="bi bi-printer"></i> พิมพ์ใบรับ</button>
                <button type="button" id="btnConfirmVerify" class="btn btn-success px-4">
                    <i class="bi bi-check-circle-fill"></i> ยืนยันตรวจรับเข้าสต็อก
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ฟังก์ชันจำลองการโหลดข้อมูล
function viewDetail(docNo) {
    // Mockup Data (ในงานจริงต้องดึงจาก Database ด้วย Ajax)
    const data = {
        docNo: docNo,
        type: 'ยอดยกมา (Initial)',
        date: '20/05/2024',
        status: 'pending', // pending, verified
        items: [
            { name: 'ถุงมือตรวจโรค (Size M)', cat: 'M7', lot: 'LOT67-001', exp: '2026-12-31', qty: 100, price: 120, unit: 'กล่อง' },
            { name: 'ผ้าก๊อซ 2x2', cat: 'M22', lot: 'LOT67-005', exp: '2025-05-20', qty: 50, price: 45, unit: 'ห่อ' }
        ]
    };

    // แสดงข้อมูลใน Modal
    $('#detDocNo').text(data.docNo);
    $('#detType').text(data.type);
    $('#detDate').text(data.date);
    
    if(data.status === 'pending') {
        $('#detStatusBadge').attr('class', 'badge rounded-pill bg-warning text-dark').text('รอตรวจรับ');
        $('#btnConfirmVerify').show();
    } else {
        $('#detStatusBadge').attr('class', 'badge rounded-pill bg-success').text('ตรวจรับแล้ว');
        $('#btnConfirmVerify').hide();
    }

    // สร้างตารางรายการ
    let html = '';
    data.items.forEach((item, index) => {
        let total = item.qty * item.price;
        html += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td>
                    <span class="badge bg-secondary-subtle text-secondary small">${item.cat}</span><br>
                    <strong>${item.name}</strong>
                </td>
                <td class="fw-bold text-primary">${item.lot}</td>
                <td>${item.exp}</td>
                <td class="text-center">${item.qty} ${item.unit}</td>
                <td class="text-end">${item.price.toLocaleString()}</td>
                <td class="text-end fw-bold">${total.toLocaleString()}</td>
            </tr>
        `;
    });
    $('#detailItemBody').html(html);
    $('#detailModal').modal('show');
}

// คลิกยืนยันตรวจรับ
$('#btnConfirmVerify').click(function() {
    Swal.fire({
        title: 'ยืนยันการตรวจรับเข้าสต็อก?',
        text: "เมื่อกดยืนยัน ยอดพัสดุจะถูกเพิ่มเข้าไปในสต็อกแยกตาม Lot ทันที",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        confirmButtonText: 'ใช่, ตรวจรับถูกต้อง'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('สำเร็จ!', 'วัสดุถูกบันทึกเข้าสต็อกเรียบร้อยแล้ว', 'success');
            $('#detailModal').modal('hide');
            // ตรงนี้ให้เขียน Logic อัปเดตสถานะในหน้าทะเบียนต่อ
        }
    });
});
</script>
 <div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> ยืนยันการยกเลิกรายการรับเข้า</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-danger fw-bold">คำเตือน: การยกเลิกจะทำให้ยอดในสต็อกของ Lot นี้ถูกหักออกทันที</p>
                <div class="mb-3">
                    <label class="form-label">สาเหตุที่ยกเลิก/แก้ไขผิด</label>
                    <select class="form-select" id="voidReason">
                        <option value="">-- กรุณาเลือกสาเหตุ --</option>
                        <option value="1">กรอกจำนวนผิด</option>
                        <option value="2">เลือกวัสดุผิดรายการ</option>
                        <option value="3">ระบุ Lot Number หรือวันหมดอายุผิด</option>
                        <option value="4">คีย์ข้อมูลซ้ำ (Duplicate)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">หมายเหตุเพิ่มเติม</label>
                    <textarea class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-danger" id="confirmVoid">ยืนยันการยกเลิกรายการ</button>
            </div>
        </div>
    </div>
</div>