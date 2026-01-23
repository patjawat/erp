<!-- https://www.canva.com/ai/code/thread/e431715f-f484-472f-986d-fd94504c9f98 -->

<div id="receiveFormView" data-select2-id="select2-data-receiveFormView">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">ใบรับเข้าสินค้าคงคลัง</h2>
                <div>
                    <button class="btn btn-outline-primary me-2" id="btnPrint">
                        <i class="bi bi-printer"></i> พิมพ์
                    </button>
                    <button class="btn btn-outline-secondary" id="btnBack">
                        <i class="bi bi-arrow-left"></i> กลับ
                    </button>
                </div>
            </div>

            <div class="form-container" data-select2-id="select2-data-87-16pc">
                <form id="inventoryForm" data-select2-id="select2-data-inventoryForm">
                    <div class="row mb-4" data-select2-id="select2-data-86-6kin">
                        <div class="col-md-6">
                            <h4 class="mb-3">ข้อมูลใบนำเข้า</h4>
                            <div class="mb-3">
                                <label for="documentNumber" class="form-label required-field">เลขที่เอกสาร</label>
                                <div class="input-group">
                                    <span class="input-group-text">DOC-</span>
                                    <input type="text" class="form-control" id="documentNumber" required="">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="documentDate" class="form-label required-field">วันที่เอกสาร</label>
                                <input type="date" class="form-control" id="documentDate" required="">
                            </div>
                            <div class="mb-3">
                                <label for="referenceNumber" class="form-label">เลขที่อ้างอิง</label>
                                <input type="text" class="form-control" id="referenceNumber">
                            </div>
                        </div>
                        <div class="col-md-6" data-select2-id="select2-data-85-hfo4">
                            <h4 class="mb-3">ข้อมูลผู้ส่งมอบ</h4>
                            <div class="mb-3" data-select2-id="select2-data-84-55xb">
                                <label for="supplierName" class="form-label required-field">ชื่อผู้ส่งมอบ</label>
                                <select class="form-select supplier-select select2-hidden-accessible" id="supplierName" required="" data-select2-id="select2-data-supplierName" tabindex="-1" aria-hidden="true">
                                    <option value="" selected="" disabled="" data-select2-id="select2-data-2-p770">เลือกผู้ส่งมอบ</option>
                                <option value="SUP001" data-select2-id="select2-data-3-81tp">บริษัท อินโนเวชั่น จำกัด</option><option value="SUP002" data-select2-id="select2-data-4-qvwb">บริษัท เทคโนโลยี จำกัด</option><option value="SUP003" data-select2-id="select2-data-5-b7h7">ห้างหุ้นส่วนจำกัด วัสดุภัณฑ์</option><option value="SUP004" data-select2-id="select2-data-6-kbeo">บริษัท ออฟฟิศ ซัพพลาย จำกัด</option><option value="SUP005" data-select2-id="select2-data-7-bzgf">บริษัท คอมพิวเตอร์ จำกัด</option></select><span class="select2 select2-container select2-container--bootstrap-5 select2-container--below" dir="ltr" data-select2-id="select2-data-1-0dms" style="width: auto;"><span class="selection"><span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-supplierName-container" aria-controls="select2-supplierName-container"><span class="select2-selection__rendered" id="select2-supplierName-container" role="textbox" aria-readonly="true" title="เลือกผู้ส่งมอบ"><span class="select2-selection__placeholder">เลือกผู้ส่งมอบ</span></span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>
                            </div>
                            <div class="mb-3">
                                <label for="contactInfo" class="form-label">ข้อมูลติดต่อ</label>
                                <input type="text" class="form-control" id="contactInfo">
                            </div>
                            <div class="mb-3">
                                <label for="department" class="form-label required-field">หน่วยงาน</label>
                                <select class="form-select department-select select2-hidden-accessible" id="department" required="" data-select2-id="select2-data-department" tabindex="-1" aria-hidden="true">
                                    <option value="" selected="" disabled="" data-select2-id="select2-data-9-5pxp">เลือกหน่วยงาน</option>
                                    <option value="สำนักงานปลัด" data-select2-id="select2-data-65-xeql">สำนักงานปลัด</option>
                                    <option value="กองคลัง" data-select2-id="select2-data-63-v3ti">กองคลัง</option>
                                    <option value="กองช่าง">กองช่าง</option>
                                    <option value="กองการศึกษา">กองการศึกษา</option>
                                    <option value="กองสาธารณสุข">กองสาธารณสุข</option>
                                    <option value="กองสวัสดิการสังคม">กองสวัสดิการสังคม</option>
                                    <option value="กองวิชาการและแผนงาน">กองวิชาการและแผนงาน</option>
                                </select><span class="select2 select2-container select2-container--bootstrap-5" dir="ltr" data-select2-id="select2-data-8-bmkf" style="width: auto;"><span class="selection"><span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-department-container" aria-controls="select2-department-container"><span class="select2-selection__rendered" id="select2-department-container" role="textbox" aria-readonly="true" title="เลือกหน่วยงาน"><span class="select2-selection__placeholder">เลือกหน่วยงาน</span></span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>
                            </div>
                        </div>
                    </div>

                    <h4 class="mb-3">รายการสินค้า</h4>
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="itemTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th style="width: 15%">รหัสสินค้า</th>
                                        <th style="width: 30%">รายการ</th>
                                        <th style="width: 10%">จำนวน</th>
                                        <th style="width: 10%">หน่วย</th>
                                        <th style="width: 15%">ราคาต่อหน่วย</th>
                                        <th style="width: 15%">จำนวนเงิน</th>
                                        <th style="width: 10%" class="no-print">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody><tr><td colspan="8" class="text-center">ไม่มีรายการสินค้า</td></tr></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">รวมทั้งสิ้น</td>
                                        <td class="fw-bold" id="totalAmount">0.00</td>
                                        <td class="no-print"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <button type="button" class="btn btn-success btn-add-item no-print" id="addItemBtn">
                            <i class="bi bi-plus-circle"></i> เพิ่มรายการสินค้า
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="notes" class="form-label">หมายเหตุ</label>
                                <textarea class="form-control" id="notes" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="receivedBy" class="form-label required-field">ผู้รับสินค้า</label>
                                <select class="form-select employee-select select2-hidden-accessible" id="receivedBy" required="" data-select2-id="select2-data-receivedBy" tabindex="-1" aria-hidden="true">
                                    <option value="" selected="" disabled="" data-select2-id="select2-data-11-7iho">เลือกผู้รับสินค้า</option>
                                <option value="EMP001" data-select2-id="select2-data-12-wftv">นายสมชาย ใจดี</option><option value="EMP002" data-select2-id="select2-data-13-xeph">นางสาวสมหญิง รักงาน</option><option value="EMP003" data-select2-id="select2-data-14-91in">นายวิชัย สุขสันต์</option><option value="EMP004" data-select2-id="select2-data-15-ct7d">นางสาวรัตนา มีสุข</option><option value="EMP005" data-select2-id="select2-data-16-etfu">นายธนา รุ่งเรือง</option><option value="EMP006" data-select2-id="select2-data-17-fipu">นางสาวพิมพ์ใจ ดีงาม</option></select><span class="select2 select2-container select2-container--bootstrap-5" dir="ltr" data-select2-id="select2-data-10-dmae" style="width: auto;"><span class="selection"><span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-receivedBy-container" aria-controls="select2-receivedBy-container"><span class="select2-selection__rendered" id="select2-receivedBy-container" role="textbox" aria-readonly="true" title="เลือกพนักงาน"><span class="select2-selection__placeholder">เลือกพนักงาน</span></span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>
                            </div>
                            <div class="mb-3">
                                <label for="approvedBy" class="form-label required-field">ผู้อนุมัติ</label>
                                <select class="form-select employee-select select2-hidden-accessible" id="approvedBy" required="" data-select2-id="select2-data-approvedBy" tabindex="-1" aria-hidden="true">
                                    <option value="" selected="" disabled="" data-select2-id="select2-data-19-p7oc">เลือกผู้อนุมัติ</option>
                                <option value="EMP001" data-select2-id="select2-data-20-6cqg">นายสมชาย ใจดี</option><option value="EMP002" data-select2-id="select2-data-21-p385">นางสาวสมหญิง รักงาน</option><option value="EMP003" data-select2-id="select2-data-22-xxkk">นายวิชัย สุขสันต์</option><option value="EMP004" data-select2-id="select2-data-23-50pa">นางสาวรัตนา มีสุข</option><option value="EMP005" data-select2-id="select2-data-24-enle">นายธนา รุ่งเรือง</option><option value="EMP006" data-select2-id="select2-data-25-veak">นางสาวพิมพ์ใจ ดีงาม</option></select><span class="select2 select2-container select2-container--bootstrap-5" dir="ltr" data-select2-id="select2-data-18-tdkl" style="width: auto;"><span class="selection"><span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-approvedBy-container" aria-controls="select2-approvedBy-container"><span class="select2-selection__rendered" id="select2-approvedBy-container" role="textbox" aria-readonly="true" title="เลือกพนักงาน"><span class="select2-selection__placeholder">เลือกพนักงาน</span></span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 no-print">
                        <button type="button" class="btn btn-secondary me-2" id="cancelBtn">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">บันทึกข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>



    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">เพิ่มรายการสินค้า</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="itemForm">
                        <input type="hidden" id="itemIndex" value="-1">
                        <div class="mb-3">
                            <label for="itemCode" class="form-label required-field">รหัสสินค้า</label>
                            <select class="form-select item-code-select" id="itemCode" required>
                                <option value="" selected disabled>เลือกรหัสสินค้า</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="itemName" class="form-label required-field">รายการ</label>
                            <select class="form-select item-name-select" id="itemName" required>
                                <option value="" selected disabled>เลือกรายการสินค้า</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="itemQuantity" class="form-label required-field">จำนวน</label>
                                    <input type="number" class="form-control" id="itemQuantity" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="itemUnit" class="form-label required-field">หน่วย</label>
                                    <select class="form-select unit-select" id="itemUnit" required>
                                        <option value="" selected disabled>เลือกหน่วย</option>
                                        <option value="ชิ้น">ชิ้น</option>
                                        <option value="อัน">อัน</option>
                                        <option value="ชุด">ชุด</option>
                                        <option value="กล่อง">กล่อง</option>
                                        <option value="แพ็ค">แพ็ค</option>
                                        <option value="รีม">รีม</option>
                                        <option value="ขวด">ขวด</option>
                                        <option value="ลัง">ลัง</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="itemPrice" class="form-label required-field">ราคาต่อหน่วย</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="itemPrice" min="0" step="0.01" required>
                                <span class="input-group-text">บาท</span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" id="saveItemBtn">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container-fluid">
            <div class="text-center">
                <small class="text-muted">© 2023 ระบบรับเข้าสินค้าคงคลังหน่วยงานราชการ</small>
            </div>
        </div>
    </footer>


<?php
use yii\web\View;
$js = <<< JS

         // Add Item Modal
            const addItemModal = new bootstrap.Modal(document.getElementById('addItemModal'));
            

       // Add Item Button Click
            $('#addItemBtn').click(function() {
                resetItemForm();
                editingIndex = -1;
                $('#addItemModalLabel').text('เพิ่มรายการสินค้า');
                addItemModal.show();
            });
                 function resetItemForm() {
                $('#itemForm')[0].reset();
                $('.item-code-select').val(null).trigger('change');
                $('.item-name-select').val(null).trigger('change');
                $('.unit-select').val(null).trigger('change');
            }
            
            

JS;
$this->registerJS($js,View::POS_END);
?>