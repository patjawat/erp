<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-download"></i> บันทึกรับวัสดุเข้าคลัง (Mockup Mode)</h5>
            <span class="badge text-bg-info text-dark">ประเภท: <span id="displayType">รับจากการจัดซื้อ</span></span>
        </div>
        <div class="card-body">
            
            <div class="row g-3 mb-4 p-3 bg-light rounded border">
                <div class="col-md-3">
                    <label class="form-label fw-bold">ประเภทการรับเข้า</label>
                    <select class="form-select border-primary" id="receiptType">
                        <option value="รับจากการจัดซื้อ">รับจากการจัดซื้อ (PO)</option>
                        <option value="รับจากใบรับสินค้า/บริจาค">รับจากใบรับสินค้า/บริจาค</option>
                        <option value="รับจากยอดยกมา">รับจากยอดยกมา (Initial Stock)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">เลขที่เอกสาร</label>
                    <input type="text" class="form-control" id="docNo" value="PO670001">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">วันที่รับเข้า</label>
                    <input type="date" class="form-control" id="docDate">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">คลังที่รับเข้า</label>
                    <select class="form-select">
                        <option>คลังหลัก (Main Stock)</option>
                    </select>
                </div>
            </div>

            <div class="row g-2 mb-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted">เลือกวัสดุเพื่อเพิ่มรายการ</label>
                    <select class="form-select select2" id="itemSelector">
                        <option value="">-- เลือกวัสดุ --</option>
                        <option value="1" data-name="ถุงมือตรวจโรค (Size M)" data-unit="กล่อง" data-cat="M7">ถุงมือตรวจโรค (Size M) [M7]</option>
                        <option value="2" data-name="ผ้าก๊อซ 2x2" data-unit="ห่อ" data-cat="M22">ผ้าก๊อซ 2x2 [M22]</option>
                        <option value="3" data-name="กระดาษ A4 80 แกรม" data-unit="รีม" data-cat="M1">กระดาษ A4 80 แกรม [M1]</option>
                        <option value="4" data-name="ออกซิเจนถัง 6คิว" data-unit="ถัง" data-cat="M26">ออกซิเจนถัง 6คิว [M26]</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" id="btnAddItem"><i class="bi bi-plus-circle"></i> เพิ่มรายการ</button>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-bordered align-middle" id="inboundTable">
                    <thead class="table-primary text-center">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 25%;">รายการวัสดุ</th>
                            <th style="width: 15%;">Lot Number</th>
                            <th style="width: 15%;">วันหมดอายุ</th>
                            <th style="width: 10%;">จำนวน</th>
                            <th style="width: 10%;">ราคา/หน่วย</th>
                            <th style="width: 15%;">รวม</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="emptyRow">
                            <td colspan="8" class="text-center py-4 text-muted">ยังไม่มีรายการที่ถูกเพิ่ม</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-bold text-end">
                        <tr>
                            <td colspan="6">ยอดรวมสุทธิ</td>
                            <td id="grandTotal">0.00</td>
                            <td>บาท</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-end mt-4">
                <button class="btn btn-success btn-lg px-5 shadow" id="btnSave"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<?php // jQuery + SweetAlert2 โหลดจาก AppAsset/YiiAsset (self-hosted) แล้ว ?>
<script>
$(document).ready(function() {
    // ตั้งค่าวันที่ปัจจุบัน
    let today = new Date().toISOString().split('T')[0];
    $('#docDate').val(today);

    // เปลี่ยนชื่อประเภทตามการเลือก
$('#receiptType').change(function() {
    let type = $(this).val();
    let today = new Date();
    let year = today.getFullYear() + 543; // พ.ศ.
    let shortYear = year.toString().substring(2);

    if (type === "รับจากยอดยกมา") {
        // สร้างเลขที่อัตโนมัติ เช่น INIT-67-001
        $('#docNo').val('INIT-' + shortYear + '-001');
        $('#docNo').addClass('bg-warning-subtle'); // ไฮไลท์สีให้รู้ว่าเป็นเลขที่ระบบสร้าง
    } else if (type === "รับจากการจัดซื้อ") {
        $('#docNo').val('PO' + shortYear + '-');
        $('#docNo').removeClass('bg-warning-subtle');
    } else {
        $('#docNo').val('');
        $('#docNo').removeClass('bg-warning-subtle');
    }
});

    // ฟังก์ชันเพิ่มรายการสินค้า
    $('#btnAddItem').click(function() {
        let selected = $('#itemSelector option:selected');
        let id = selected.val();
        if(!id) {
            Swal.fire('กรุณาเลือกวัสดุ', '', 'warning');
            return;
        }

        let name = selected.data('name');
        let unit = selected.data('unit');
        let cat = selected.data('cat');

        $('#emptyRow').hide();

        let rowCount = $('#inboundTable tbody tr:visible').length + 1;
        let row = `
            <tr class="item-row">
                <td class="text-center">${rowCount}</td>
                <td>
                    <span class="badge text-bg-info text-dark">${cat}</span> <strong>${name}</strong><br>
                    <small class="text-muted">หน่วยนับ: ${unit}</small>
                </td>
                <td><input type="text" class="form-control form-control-sm lot-no" placeholder="Lot No."></td>
                <td><input type="date" class="form-control form-control-sm exp-date"></td>
                <td><input type="number" class="form-control form-control-sm qty text-center" value="1"></td>
                <td><input type="number" class="form-control form-control-sm price text-end" placeholder="0.00"></td>
                <td class="text-end fw-bold row-total">0.00</td>
                <td><button class="btn btn-outline-danger btn-sm btn-del"><i class="bi bi-trash"></i></button></td>
            </tr>
        `;
        $('#inboundTable tbody').append(row);
        calculateTotal();
    });

    // ลบแถว
    $(document).on('click', '.btn-del', function() {
        $(this).closest('tr').remove();
        if($('#inboundTable tbody tr.item-row').length === 0) {
            $('#emptyRow').show();
        }
        calculateTotal();
    });

    // คำนวณราคาทันทีเมื่อเปลี่ยนจำนวนหรือราคา
    $(document).on('input', '.qty, .price', function() {
        let row = $(this).closest('tr');
        let qty = parseFloat(row.find('.qty').val()) || 0;
        let price = parseFloat(row.find('.price').val()) || 0;
        let total = qty * price;
        row.find('.row-total').text(total.toLocaleString(undefined, {minimumFractionDigits: 2}));
        calculateTotal();
    });

    function calculateTotal() {
        let grandTotal = 0;
        $('.row-total').each(function() {
            grandTotal += parseFloat($(this).text().replace(/,/g, '')) || 0;
        });
        $('#grandTotal').text(grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2}));
    }

    // จำลองการบันทึกข้อมูล
    $('#btnSave').click(function() {
        if($('#inboundTable tbody tr.item-row').length === 0) {
            Swal.fire('ไม่พบรายการรับเข้า', 'กรุณาเพิ่มอย่างน้อย 1 รายการ', 'error');
            return;
        }

        Swal.fire({
            title: 'ยืนยันการรับเข้า?',
            text: "ระบบจะทำการอัปเดตสต็อกใน Lot ที่ระบุ",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'ยืนยันการบันทึก'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('สำเร็จ!', 'บันทึกข้อมูลและอัปเดตสต็อกเรียบร้อย', 'success')
                .then(() => location.reload()); // ล้างหน้าจอหลังบันทึก
            }
        });
    });
});
</script>