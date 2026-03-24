<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\HelpdeskDetail $model */
/** @var app\modules\helpdesk2\models\HelpdeskDetail[] $expenseRows */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="helpdesk-detail-form p-3">
  <?php
  $initialRows = [];
  foreach (($expenseRows ?? []) as $row) {
    $dj = is_array($row->data_json ?? null) ? $row->data_json : [];
    $qty = (float) ($dj['qty'] ?? 1);
    if ($qty <= 0) {
      $qty = 1;
    }
    $unitPrice = (float) ($dj['unit_price'] ?? 0);
    $total = (float) ($dj['total'] ?? ($qty * $unitPrice));
    $initialRows[] = [
      'status' => (string) ($row->status ?? 'ค่าใช้จ่าย'),
      'title' => (string) ($row->title ?? ''),
      'qty' => $qty,
      'unit_price' => $unitPrice,
      'total' => $total,
      'expense_type' => (string) ($dj['expense_type'] ?? ''),
      'note' => (string) ($dj['note'] ?? ''),
    ];
  }
  ?>

  <?php $form = ActiveForm::begin(['id' => 'expense-pos-form']); ?>
  <input type="hidden" name="helpdesk_id" value="<?= (int) $model->helpdesk_id ?>">
  <input type="hidden" id="expense-rows-json" name="expense_rows_json" value="">

<div class="row g-3">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div class="d-flex align-items-center gap-2">
            <div class="erp-icon-box bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-3 p-2">
              <i class="fa-solid fa-cash-register"></i>
            </div>
            <div>
              <h6 class="mb-0">บันทึกค่าใช้จ่ายงานซ่อม</h6>
              <div class="small text-muted">เพิ่มรายการได้เรื่อยๆ แล้วกดคอนเฟิร์มบันทึกครั้งเดียว</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4">
    <?= Html::label('หมวดค่าใช้จ่าย', 'expense-status', ['class' => 'form-label']) ?>
    <select id="expense-status" class="form-select">
      <option value="ค่าแรง">ค่าแรง</option>
      <option value="ค่าอะไหล่">ค่าอะไหล่</option>
      <option value="ค่าบริการ">ค่าบริการ</option>
      <option value="ค่าส่งซ่อมภายนอก">ค่าส่งซ่อมภายนอก</option>
      <option value="ค่าใช้จ่ายอื่นๆ">ค่าใช้จ่ายอื่นๆ</option>
    </select>
  </div>
  <div class="col-12 col-md-8">
    <?= Html::label('รายการ', 'expense-title', ['class' => 'form-label']) ?>
    <input type="text" id="expense-title" class="form-control" placeholder="เช่น ค่าอะไหล่พัดลมคอยล์เย็น">
  </div>
  <div class="col-4">
    <?= Html::label('จำนวน', 'expense-qty', ['class' => 'form-label']) ?>
    <input type="number" min="0" step="0.01" id="expense-qty" class="form-control" value="1">
  </div>
  <div class="col-4">
    <?= Html::label('ราคาต่อหน่วย', 'expense-unit-price', ['class' => 'form-label']) ?>
    <input type="number" min="0" step="0.01" id="expense-unit-price" class="form-control" value="0">
  </div>
  <div class="col-4">
    <?= Html::label('รวมรายการ', 'expense-line-total-display', ['class' => 'form-label']) ?>
    <input type="text" id="expense-line-total-display" class="form-control fw-bold text-danger" value="0.00" readonly>
  </div>
  <div class="col-12">
    <?= Html::label('หมายเหตุ', 'expense-note', ['class' => 'form-label']) ?>
    <input type="text" id="expense-note" class="form-control" placeholder="เลขบิล/ชื่อร้าน/หมายเหตุ">
  </div>
  <div class="col-12 d-grid">
    <button type="button" class="btn btn-outline-primary" id="expense-add-row-btn">
      <i class="fa-solid fa-plus me-1"></i> เพิ่มรายการเข้าบิล
    </button>
  </div>

  <div class="col-12">
                    <div class="border rounded-3 p-2">
                        <div class="small text-muted mb-1">รายการในบิล (แก้ไข/ลบได้)</div>
                        <div id="expense-cart-body" class="d-flex flex-column gap-1">
                            <div id="expense-empty-row" class="text-center text-muted py-2 border rounded-3 small">ยังไม่มีรายการ</div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="border border-success-subtle rounded-3 p-3 bg-success bg-opacity-10">
      <div class="d-flex justify-content-between align-items-center">
        <span class="fw-medium">ยอดรวมบิลค่าใช้จ่าย</span>
        <span id="expense-grand-total" class="fw-bold text-success fs-5">0.00 บาท</span>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="d-grid gap-2">
      <?= Html::submitButton('<i class="fa-solid fa-circle-check me-1"></i> ยืนยันบันทึกค่าใช้จ่ายทั้งหมด', ['class' => 'btn btn-primary']) ?>
      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">ปิดหน้าบันทึก</button>
    </div>
  </div>
</div>

<?php ActiveForm::end(); ?>

</div>

<?php
$initialRowsJson = json_encode($initialRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$js = <<< JS
var expenseCartRows = [];
var expenseInitialRows = $initialRowsJson;

function expenseParseNumber(v) {
  if (v === null || v === undefined) return 0;
  var n = parseFloat(String(v).replace(/,/g, '').trim());
  return isNaN(n) ? 0 : n;
}

function expenseFormatNumber(v) {
  return Number(v || 0).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function expenseRecalc() {
  var qty = expenseParseNumber($('#expense-qty').val());
  var price = expenseParseNumber($('#expense-unit-price').val());
  var total = qty * price;
  $('#expense-line-total-display').val(expenseFormatNumber(total));
}

$(document).off('input.expenseRecalc').on('input.expenseRecalc', '#expense-qty, #expense-unit-price', expenseRecalc);
expenseRecalc();

function expenseRenderTable() {
  var \$body = $('#expense-cart-body');
  \$body.empty();
  if (!expenseCartRows.length) {
    \$body.append('<div id="expense-empty-row" class="text-center text-muted py-2 border rounded-3 small">ยังไม่มีรายการ</div>');
    $('#expense-grand-total').text('0.00 บาท');
    return;
  }
  var grand = 0;
  expenseCartRows.forEach(function (row, idx) {
    var total = expenseParseNumber(row.total);
    grand += total;
    var card = '<div class="card border-0 shadow-sm" data-index="' + idx + '">' +
      '<div class="card-body p-2">' +
      '<div class="d-flex flex-wrap justify-content-between align-items-start gap-2">' +
      '<div>' +
      '<div class="small text-muted mb-1">' + $('<div>').text(row.status || '').html() + '</div>' +
      '<div class="fw-medium small">' + $('<div>').text(row.title || '').html() + '</div>' +
      '<div class="small text-muted mt-1">' + $('<div>').text(row.note || '').html() + '</div>' +
      '</div>' +
      '<div class="text-end">' +
      '<div class="small text-muted">จำนวน ' + expenseFormatNumber(row.qty) + ' x ' + expenseFormatNumber(row.unit_price) + '</div>' +
      '<div class="fw-bold text-danger small">' + expenseFormatNumber(total) + ' บาท</div>' +
      '</div>' +
      '</div>' +
      '<div class="d-flex justify-content-end gap-1 mt-2">' +
      '<button type="button" class="btn btn-sm btn-outline-secondary btn-edit-row" data-index="' + idx + '">แก้ไข</button>' +
      '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-row" data-index="' + idx + '">ลบ</button>' +
      '</div>' +
      '</div>' +
      '</div>';
    \$body.append(card);
  });
  $('#expense-grand-total').text(expenseFormatNumber(grand) + ' บาท');
}

function expenseLoadRowToEditor(row) {
  $('#expense-status').val(row.status || 'ค่าใช้จ่าย');
  $('#expense-title').val(row.title || '');
  $('#expense-qty').val(row.qty || 1);
  $('#expense-unit-price').val(row.unit_price || 0);
  $('#expense-note').val(row.note || '');
  $('#expense-add-row-btn').data('edit-index', row.__index);
  expenseRecalc();
}

$(document).off('click.expenseAddRow').on('click.expenseAddRow', '#expense-add-row-btn', function () {
  var row = {
    status: $('#expense-status').val() || 'ค่าใช้จ่าย',
    title: ($('#expense-title').val() || '').trim(),
    qty: expenseParseNumber($('#expense-qty').val()),
    unit_price: expenseParseNumber($('#expense-unit-price').val()),
    note: ($('#expense-note').val() || '').trim()
  };
  row.total = row.qty * row.unit_price;
  if (!row.title) {
    Swal.fire({ icon: 'warning', title: 'กรุณาระบุรายการค่าใช้จ่าย' });
    return;
  }
  if (row.qty <= 0) {
    Swal.fire({ icon: 'warning', title: 'จำนวนต้องมากกว่า 0' });
    return;
  }
  var editIndex = $(this).data('edit-index');
  if (editIndex !== undefined && editIndex !== null && editIndex !== '') {
    expenseCartRows[parseInt(editIndex, 10)] = row;
    $(this).removeData('edit-index').html('<i class="fa-solid fa-plus me-1"></i> เพิ่มรายการเข้าบิล');
  } else {
    expenseCartRows.push(row);
  }
  $('#expense-title').val('');
  $('#expense-qty').val('1');
  $('#expense-unit-price').val('0');
  $('#expense-note').val('');
  expenseRecalc();
  expenseRenderTable();
});

$(document).off('click.expenseEditRow').on('click.expenseEditRow', '.btn-edit-row', function () {
  var idx = parseInt($(this).data('index'), 10);
  var row = expenseCartRows[idx];
  if (!row) return;
  row.__index = idx;
  expenseLoadRowToEditor(row);
  $('#expense-add-row-btn').html('<i class="fa-solid fa-check me-1"></i> บันทึกการแก้ไขรายการ');
});

$(document).off('click.expenseDeleteRow').on('click.expenseDeleteRow', '.btn-delete-row', function () {
  var idx = parseInt($(this).data('index'), 10);
  expenseCartRows.splice(idx, 1);
  expenseRenderTable();
});

$(document).off('beforeSubmit.expenseForm').on('beforeSubmit.expenseForm', '#expense-pos-form', function (e) {
    e.preventDefault();
    const form = $(this);
    if (!expenseCartRows.length) {
      Swal.fire({ icon: 'warning', title: 'ยังไม่มีรายการค่าใช้จ่าย' });
      return false;
    }
    $('#expense-rows-json').val(JSON.stringify(expenseCartRows));
    Swal.fire({
      title: "ยืนยัน?",
      text: "บันทึกค่าใช้จ่ายทั้งหมด " + expenseCartRows.length + " รายการ",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      cancelButtonText: "ยกเลิก!",
      confirmButtonText: "ใช่, ยืนยัน!"
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'กำลังบันทึก...',
          text: 'กรุณารอสักครู่',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        $.ajax({
          url:  form.attr('action'),
          type: 'POST',
          data: form.serialize(),
          dataType: 'json',
          success: function (response) {
            Swal.close();
            if (response.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: 'บันทึกข้อมูลเรียบร้อยแล้ว',
                timer: 1000,
                showConfirmButton: false
              }).then(() => {
                try {
                  var offcanvasEl = document.getElementById('expense-pos-offcanvas');
                  if (offcanvasEl) {
                    var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                    offcanvas.hide();
                  }
                } catch (e) {}
                window.location.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: response.message || 'ไม่สามารถบันทึกข้อมูลได้'
              });
            }
          },
          error: function () {
            Swal.close();
            Swal.fire({
              icon: 'error',
              title: 'เกิดข้อผิดพลาด',
              text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
            });
          }
        });
      }
    });
    return false;
  });

if (Array.isArray(expenseInitialRows) && expenseInitialRows.length) {
  expenseCartRows = expenseInitialRows.map(function (r) {
    return {
      status: r.status || 'ค่าใช้จ่าย',
      title: r.title || '',
      qty: expenseParseNumber(r.qty),
      unit_price: expenseParseNumber(r.unit_price),
      total: expenseParseNumber(r.total),
      expense_type: r.expense_type || '',
      note: r.note || ''
    };
  });
}
expenseRenderTable();
JS;
$this->registerJs($js);
?>