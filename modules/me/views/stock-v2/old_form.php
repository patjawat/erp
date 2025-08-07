<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use yii\web\View;

?>


<div class="card">
    <div class="card-body">
        <?php $form = ActiveForm::begin([
            'id' => 'form',
            // 'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
            // 'validationUrl' => ['/inventory/stock-in/create-validator']
        ]); ?>
        <h5 class="border-bottom pb-2 mb-3">
            <i class="fas fa-info-circle me-2"></i>ข้อมูลหลัก
        </h5>

        <!-- ส่วนข้อมูลหลัก -->
        <div class="row mb-4">
            <div class="col-md-3">
                <?= $form->field($model, 'document_no')->textInput(['readonly' => true, 'value' => 'WH-2024-002']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'request_date')->input('date', ['value' => '2024-12-15']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'created_by')->textInput(['placeholder' => 'ชื่อผู้ขอเบิก']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'data_json[department]')->widget(Select2::class, [
                    'data' => [
                        'บัญชี' => 'แผนกบัญชี',
                        'บุคคล' => 'แผนกบุคคล',
                        'ไอที' => 'แผนกไอที',
                        'การตลาด' => 'แผนกการตลาด',
                    ],
                    'options' => ['placeholder' => 'เลือกแผนก...'],
                    'pluginOptions' => ['allowClear' => true],
                ]) ?>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <?= $form->field($model, 'from_warehouse_id')->widget(Select2::class, [
                    'data' => [
                        'main' => 'คลังหลัก - อาคาร A',
                        'sub' => 'คลังสำรอง - อาคาร B',
                        'consumable' => 'คลังวัสดุสิ้นเปลือง',
                    ],
                    'options' => ['placeholder' => 'เลือกคลังต้นทาง...'],
                    'pluginOptions' => ['allowClear' => true],
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'data_json[issue_type]')->widget(Select2::class, [
                    'data' => [
                        'ทั่วไป' => 'เบิกทั่วไป',
                        'PO' => 'เบิกตามใบสั่งซื้อ (PO)',
                        'free' => 'เบิกของแถม / ของบริจาค',
                    ],
                    'options' => ['placeholder' => 'เลือกประเภทการเบิก...'],
                    'pluginOptions' => ['allowClear' => true],
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'data_json[remark]')->textInput(['placeholder' => 'หมายเหตุเพิ่มเติม']) ?>
            </div>
        </div>

        <!-- รายการวัสดุ -->
        <div class="mb-4">
            <div class="d-flex justify-content-between mb-3">
                <h5><i class="fas fa-list me-2"></i>รายการวัสดุ</h5>
                <button type="button" class="btn btn-success" onclick="addMaterialRow()">
                    <i class="fas fa-plus me-1"></i> เพิ่มรายการ
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="materialTable">
                    <thead class="table-primary">
                        <tr>
                            <th width="15%">รหัสวัสดุ</th>
                            <th width="30%">ชื่อวัสดุ</th>
                            <th width="15%">จำนวน</th>
                            <th width="15%">หน่วย</th>
                            <th width="20%">หมายเหตุ</th>
                            <th width="5%">ลบ</th>
                        </tr>
                    </thead>
                    <tbody id="materialTableBody">
                        <tr>
                            <td>
                                <input type="text" class="form-control" name="materials[0][code]" data-field="code">
                            </td>
                           <td>
                                <?= Select2::widget([
                                    'name' => 'materials[0][material_id]',
                                    'options' => [
                                        'placeholder' => 'ค้นหาวัสดุ...',
                                        'class' => 'form-control select2-material',
                                        'data-index' => 0, // สำหรับใช้ใน JS
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 2,
                                        'ajax' => [
                                            'url' => \yii\helpers\Url::to(['/me/stock-v2/material-list']),
                                            'dataType' => 'json',
                                            'delay' => 250,
                                            'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }'),
                                            'processResults' => new \yii\web\JsExpression('function(data) {
                                                return {results: data.results};
                                            }'),
                                            'cache' => true
                                        ],
                                    ],
                                ]); ?>
                            </td>

                            <td>
                                <input type="number" class="form-control" name="materials[0][quantity]" data-field="quantity" min="1">
                            </td>
                            <td>
                                <input type="text" class="form-control" name="materials[0][unit]" data-field="unit" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="materials[0][note]" data-field="note">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeMaterialRow(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="form-group mt-3 text-center">
            <?= Html::submitButton('<i class="bi bi-check2-circle me-1"></i> บันทึก', ['class' => 'btn btn-primary px-4']) ?>
        </div>

        <?php ActiveForm::end(); ?>


    </div>
</div>

<?php
$ref = $model->ref;
$js = <<< JS

   handleFormSubmit('#form', null, async function(response) {
        // await location.reload();
    });
    
 // Page Navigation
        function showPage(pageId) {
            // Hide all pages
            document.querySelectorAll('.page-section').forEach(page => {
                page.classList.remove('active');
            });
            
            // Show selected page
            document.getElementById(pageId).classList.add('active');
            
            // Update sidebar active state
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');
        }


                let materialIndex = 1;

                function addMaterialRow() {
                    $.ajax({
                        url: '/me/stock-v2/render-material-row', // <-- ทำ endpoint นี้
                        method: 'GET',
                        data: { index: materialIndex },
                        success: function(html) {
                            $('#materialTableBody').append(html);
                            materialIndex++;

                            // Re-initialize kartik Select2 for new element
                            $('#materialTableBody tr:last-child .select2-material').each(function () {
                                $(this).select2({
                                    theme: 'bootstrap-5',
                                    allowClear: true,
                                    minimumInputLength: 2,
                                    ajax: {
                                        url: '/inventory/material-list',
                                        dataType: 'json',
                                        delay: 250,
                                        data: function(params) {
                                            return { q: params.term };
                                        },
                                        processResults: function(data) {
                                            return { results: data.results };
                                        },
                                        cache: true
                                    }
                                }).on('select2:select', function (e) {
                                    const data = e.params.data;
                                    const row = $(this).closest('tr');
                                    row.find('input[name$="[code]"]').val(data.code);
                                    row.find('input[name$="[unit]"]').val(data.unit);
                                });
                            });
                        }
                    });
                }


        function removeMaterialRow(button) {
            const row = button.closest('tr');
            const tableBody = document.getElementById('materialTableBody');
            
            if (tableBody.children.length > 1) {
                row.remove();
                updateItemCount();
            } else {
                alert('ต้องมีรายการวัสดุอย่างน้อย 1 รายการ');
            }
        }

        function updateItemCount() {
            const rowCount = document.getElementById('materialTableBody').children.length;
            document.getElementById('itemCount').textContent = rowCount;
        }

        function resetForm() {
            document.getElementById('issuanceForm').reset();
            const tableBody = document.getElementById('materialTableBody');
            
            // Keep only first row
            while (tableBody.children.length > 1) {
                tableBody.removeChild(tableBody.lastChild);
            }
            
            // Generate new order number
            const orderNumber = 'WH-2024-' + String(Math.floor(Math.random() * 1000) + 1).padStart(3, '0');
            document.querySelector('input[readonly]').value = orderNumber;
            
            updateItemCount();
        }

        function confirmIssuance() {
            // Simulate processing
            setTimeout(() => {
                document.getElementById('confirmModal').querySelector('.btn-close').click();
                document.getElementById('successMessage').textContent = 'การตัดจ่ายวัสดุเสร็จสิ้นเรียบร้อยแล้ว';
                new bootstrap.Modal(document.getElementById('successModal')).show();
                
                // Reset form after success
                setTimeout(() => {
                    resetForm();
                }, 2000);
            }, 1000);
        }

        function approveRequest() {
            setTimeout(() => {
                document.getElementById('approveModal').querySelector('.btn-close').click();
                document.getElementById('successMessage').textContent = 'อนุมัติการเบิกข้ามคลังเรียบร้อยแล้ว';
                new bootstrap.Modal(document.getElementById('successModal')).show();
            }, 1000);
        }

        function rejectRequest() {
            const reason = document.querySelector('#rejectModal textarea').value;
            if (!reason.trim()) {
                alert('กรุณาระบุเหตุผลที่ไม่อนุมัติ');
                return;
            }
            
            setTimeout(() => {
                document.getElementById('rejectModal').querySelector('.btn-close').click();
                document.getElementById('successMessage').textContent = 'ไม่อนุมัติการเบิกข้ามคลังเรียบร้อยแล้ว';
                new bootstrap.Modal(document.getElementById('successModal')).show();
            }, 1000);
        }

        // Initialize Chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('monthlyChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
                    datasets: [{
                        label: 'รับเข้า',
                        data: [120, 190, 300, 500, 200, 300, 450, 300, 250, 400, 350, 500],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'ตัดจ่าย',
                        data: [80, 150, 200, 300, 150, 250, 300, 200, 180, 300, 280, 350],
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Initialize item count
            updateItemCount();

            // Auto-update notifications (simulate real-time)
            setInterval(() => {
                const notifications = document.querySelectorAll('.notification-badge');
                notifications.forEach(badge => {
                    const currentCount = parseInt(badge.textContent);
                    if (Math.random() > 0.8) { // 20% chance to update
                        badge.textContent = Math.max(0, currentCount + (Math.random() > 0.5 ? 1 : -1));
                    }
                });
            }, 30000); // Update every 30 seconds
        });

        // Material selection auto-fill unit
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('form-select') && e.target.closest('#materialTableBody')) {
                const row = e.target.closest('tr');
                const unitInput = row.querySelector('input[placeholder="หน่วย"]');
                const materialSelect = e.target;
                
                const units = {
                    'กระดาษ A4': 'แผ่น',
                    'ปากกาลูกลื่น': 'ด้าม',
                    'ลวดเย็บกระดาษ': 'กล่อง',
                    'คลิป': 'กล่อง'
                };
                
                unitInput.value = units[materialSelect.value] || '';
            }
        });


JS;
$this->registerJS($js, View::POS_END);
?>