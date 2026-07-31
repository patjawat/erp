<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\HelpdeskDetail $model */
/** @var app\modules\helpdesk2\models\HelpdeskDetail[] $partRows */
/** @var app\modules\inventoryV2\models\Warehouse[] $subWarehouses */

$initialRows = [];
foreach (($partRows ?? []) as $row) {
    $dj = is_array($row->data_json ?? null) ? $row->data_json : [];
    $initialRows[] = [
        'item_code' => (string) ($dj['item_code'] ?? $row->code ?? ''),
        'item_name' => (string) ($dj['item_name'] ?? $row->title ?? ''),
        'qty' => (float) ($dj['qty'] ?? 0),
        'unit' => (string) ($dj['unit'] ?? ''),
        'balance_qty' => (float) ($dj['balance_qty'] ?? 0),
        // สำคัญ: ต้องส่ง `warehouse_id` กลับไป backend ไม่งั้น backend จะมองเป็น 0
        // แล้วตัดสต๊อก/ประวัติใน inventoryV2 จะผิดคลัง
        'warehouse_id' => (int) ($dj['warehouse_id'] ?? 0),
    ];
}

$initialRowsJson = json_encode($initialRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$warehouseOptions = [];
foreach (($subWarehouses ?? []) as $w) {
    $warehouseOptions[(int) $w->id] = (string) ($w->warehouse_name ?? ('คลัง #' . $w->id));
}
?>

<div class="helpdesk-part-form p-3">
    <?php $form = ActiveForm::begin(['id' => 'part-pos-form']); ?>
    <input type="hidden" name="part_rows_json" id="part-rows-json" value="">

    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="erp-icon-box bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-3 p-2">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <h6 class="mb-0">เบิกอะไหล่จากคลัง</h6>
                    <div class="small text-muted">ค้นหารายการจาก inventoryV2, เพิ่มหลายรายการ แล้วคอนเฟิร์มครั้งเดียว</div>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-12 col-lg-3">
                    <?= Html::label('คลังย่อยที่ต้องการเบิก', 'part-sub-warehouse-id', ['class' => 'form-label']) ?>
                    <select id="part-sub-warehouse-id" class="form-select">
                        <option value="">เลือกคลังย่อย...</option>
                        <?php foreach ($warehouseOptions as $wid => $wname): ?>
                            <option value="<?= (int) $wid ?>"><?= Html::encode($wname) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-lg-5">
                    <?= Html::label('ค้นหาอะไหล่ (inventoryV2)', 'part-search', ['class' => 'form-label']) ?>
                    <input type="text" id="part-search" class="form-control" placeholder="พิมพ์ชื่อหรือรหัสอะไหล่...">
                    <div id="part-search-result" class="list-group mt-2"></div>
                </div>
                <div class="col-6 col-lg-2">
                    <?= Html::label('จำนวนเบิก', 'part-qty', ['class' => 'form-label']) ?>
                    <input type="number" id="part-qty" class="form-control" min="0" step="0.01" value="1">
                </div>
                <div class="col-6 col-lg-2 d-grid align-content-end">
                    <button type="button" class="btn btn-outline-primary" id="part-add-btn">
                        <i class="fa-solid fa-plus me-1"></i> เพิ่ม
                    </button>
                </div>
            </div>

            <div class="border rounded-3 p-2 mt-3">
                <div class="small text-muted mb-1">รายการเบิก (แก้ไข/ลบได้)</div>
                <div id="part-cart-body" class="d-flex flex-column gap-1">
                    <div id="part-empty-row" class="text-center text-muted py-2 border rounded-3 small">ยังไม่มีรายการ</div>
                </div>
            </div>

            <div class="border border-primary-subtle rounded-3 p-3 bg-primary bg-opacity-10 mt-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-medium">รวมจำนวนรายการเบิก</span>
                    <span id="part-grand-total" class="fw-bold text-primary fs-5">0 รายการ</span>
                </div>
            </div>

            <div class="d-grid gap-2 mt-3">
                <?= Html::submitButton('<i class="fa-solid fa-circle-check me-1"></i> ยืนยันการเบิกอะไหล่', ['class' => 'btn btn-primary']) ?>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">ปิดหน้าบันทึก</button>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$lookupUrl = \yii\helpers\Url::to(['/helpdesk/repair-parts/inventory-lookup']);
$js = <<<JS
var partInitialRows = $initialRowsJson;
var partCartRows = [];
var selectedInventoryItem = null;

function partParseNumber(v) {
  var n = parseFloat(String(v || '').replace(/,/g, '').trim());
  return isNaN(n) ? 0 : n;
}

function partFormatNumber(v) {
  return Number(v || 0).toLocaleString('th-TH', {minimumFractionDigits: 0, maximumFractionDigits: 2});
}

function partRenderCart() {
  var \$body = $('#part-cart-body');
  \$body.empty();
  if (!partCartRows.length) {
    \$body.append('<div id="part-empty-row" class="text-center text-muted py-2 border rounded-3 small">ยังไม่มีรายการ</div>');
    $('#part-grand-total').text('0 รายการ');
    return;
  }
  var totalQty = 0;
  partCartRows.forEach(function (row, idx) {
    totalQty += partParseNumber(row.qty);
    var html = '<div class="card border-0 shadow-sm"><div class="card-body p-2">' +
      '<div class="d-flex justify-content-between gap-2">' +
      '<div><div class="fw-medium small">' + $('<div>').text(row.item_name).html() + '</div>' +
      '<div class="small text-muted">' + $('<div>').text(row.item_code + ' / คงเหลือ ' + partFormatNumber(row.balance_qty) + ' ' + (row.unit || '')).html() + '</div></div>' +
      '<div class="text-end"><div class="fw-bold text-primary small">' + partFormatNumber(row.qty) + ' ' + (row.unit || '') + '</div></div></div>' +
      '<div class="d-flex justify-content-end gap-1 mt-2">' +
      '<button type="button" class="btn btn-sm btn-outline-secondary btn-part-edit" data-index="' + idx + '">แก้ไข</button>' +
      '<button type="button" class="btn btn-sm btn-outline-danger btn-part-delete" data-index="' + idx + '">ลบ</button></div></div></div>';
    \$body.append(html);
  });
  $('#part-grand-total').text(partFormatNumber(totalQty) + ' ชิ้น');
}

// Refresh balance_qty in cart from inventoryV2 before submitting.
// This is important when user is editing an existing repair ticket:
// the cart may contain stale balances loaded from helpdesk_detail.
function partRefreshBalancesBeforeSubmit() {
  return new Promise(function(resolve, reject) {
    var warehouseId = $('#part-sub-warehouse-id').val();
    if (!warehouseId) return resolve(false);

    var uniqueCodes = [];
    partCartRows.forEach(function(r) {
      var code = r.item_code ? String(r.item_code) : '';
      if (code && uniqueCodes.indexOf(code) === -1) uniqueCodes.push(code);
    });

    if (uniqueCodes.length === 0) return resolve(true);

    var jobs = uniqueCodes.map(function(code) {
      return new Promise(function(res2) {
        $.getJSON('$lookupUrl', { q: code, warehouse_id: warehouseId })
          .done(function(resp) {
            var list = Array.isArray(resp && resp.results) ? resp.results : [];
            var found = list.find(function(it) { return String(it.item_code) === String(code); });
            var bal = 0;
            if (found && found.balance_qty !== undefined) bal = partParseNumber(found.balance_qty);
            res2({ code: code, balance_qty: bal });
          })
          .fail(function() {
            // If lookup fails, keep existing balances to not block the flow unnecessarily.
            res2({ code: code, balance_qty: null });
          });
      });
    });

    Promise.all(jobs).then(function(results) {
      results.forEach(function(r) {
        if (r.balance_qty === null) return;
        partCartRows.forEach(function(row) {
          if (String(row.item_code) === String(r.code)) {
            row.balance_qty = r.balance_qty;
          }
        });
      });
      partRenderCart();
      resolve(true);
    }).catch(function() {
      // Don't break submission due to refresh issues.
      resolve(false);
    });
  });
}

var searchTimer = null;
$(document).off('input.partSearch').on('input.partSearch', '#part-search', function () {
  var warehouseId = $('#part-sub-warehouse-id').val();
  var q = ($(this).val() || '').trim();
  clearTimeout(searchTimer);
  if (!warehouseId) {
    $('#part-search-result').html('<div class="small text-muted p-2">กรุณาเลือกคลังย่อยก่อนค้นหา</div>');
    selectedInventoryItem = null;
    return;
  }
  if (!q) {
    $('#part-search-result').empty();
    selectedInventoryItem = null;
    return;
  }
  searchTimer = setTimeout(function () {
    $.getJSON('$lookupUrl', { q: q, warehouse_id: warehouseId }, function (res) {
      var list = Array.isArray(res.results) ? res.results : [];
      var html = '';
      list.forEach(function (it) {
        html += '<button type="button" class="list-group-item list-group-item-action part-pick-item" ' +
          'data-item=\'' + JSON.stringify(it).replace(/'/g, "&apos;") + '\'>' +
          '<div class="d-flex justify-content-between"><div><div class="fw-medium">' + $('<div>').text(it.item_name).html() + '</div>' +
          '<div class="small text-muted">' + $('<div>').text(it.item_code).html() + '</div></div>' +
          '<div class="small text-primary">คงเหลือ ' + partFormatNumber(it.balance_qty) + ' ' + $('<div>').text(it.unit_name || '').html() + '</div></div></button>';
      });
      $('#part-search-result').html(html || '<div class="small text-muted p-2">ไม่พบรายการ</div>');
    });
  }, 250);
});

$(document).off('change.partWarehouse').on('change.partWarehouse', '#part-sub-warehouse-id', function () {
  selectedInventoryItem = null;
  $('#part-search').val('');
  $('#part-search-result').empty();
});

$(document).off('click.partPick').on('click.partPick', '.part-pick-item', function () {
  var raw = $(this).attr('data-item') || '{}';
  try {
    selectedInventoryItem = JSON.parse(raw.replace(/&apos;/g, "'"));
  } catch (e) {
    selectedInventoryItem = null;
  }
  if (selectedInventoryItem) {
    $('#part-search').val(selectedInventoryItem.item_name + ' (' + selectedInventoryItem.item_code + ')');
    $('#part-search-result').empty();
  }
});

$(document).off('click.partAdd').on('click.partAdd', '#part-add-btn', function () {
  if (!selectedInventoryItem) {
    Swal.fire({ icon: 'warning', title: 'กรุณาเลือกรายการอะไหล่จากผลค้นหา' });
    return;
  }
  var warehouseId = $('#part-sub-warehouse-id').val();
  if (!warehouseId) {
    Swal.fire({ icon: 'warning', title: 'กรุณาเลือกคลังย่อยก่อน' });
    return;
  }
  var qty = partParseNumber($('#part-qty').val());
  if (qty <= 0) {
    Swal.fire({ icon: 'warning', title: 'จำนวนเบิกต้องมากกว่า 0' });
    return;
  }
  var editIndex = $(this).data('edit-index');
  var row = {
    warehouse_id: warehouseId,
    item_code: selectedInventoryItem.item_code || '',
    item_name: selectedInventoryItem.item_name || '',
    balance_qty: partParseNumber(selectedInventoryItem.balance_qty),
    unit: selectedInventoryItem.unit_name || '',
    qty: qty
  };
  if (editIndex !== undefined && editIndex !== null && editIndex !== '') {
    partCartRows[parseInt(editIndex, 10)] = row;
    $(this).removeData('edit-index').html('<i class="fa-solid fa-plus me-1"></i> เพิ่ม');
  } else {
    partCartRows.push(row);
  }
  selectedInventoryItem = null;
  $('#part-search').val('');
  $('#part-qty').val('1');
  partRenderCart();
});

$(document).off('click.partEdit').on('click.partEdit', '.btn-part-edit', function () {
  var idx = parseInt($(this).data('index'), 10);
  var row = partCartRows[idx];
  if (!row) return;
  selectedInventoryItem = {
    item_code: row.item_code,
    item_name: row.item_name,
    unit_name: row.unit,
    balance_qty: row.balance_qty
  };
  $('#part-search').val(row.item_name + ' (' + row.item_code + ')');
  $('#part-qty').val(row.qty);
  $('#part-add-btn').data('edit-index', idx).html('<i class="fa-solid fa-check me-1"></i> บันทึกการแก้ไข');
});

$(document).off('click.partDelete').on('click.partDelete', '.btn-part-delete', function () {
  var idx = parseInt($(this).data('index'), 10);
  partCartRows.splice(idx, 1);
  partRenderCart();
});

$(document).off('beforeSubmit.partForm').on('beforeSubmit.partForm', '#part-pos-form', function (e) {
  e.preventDefault();
  var form = $(this);
  if (!partCartRows.length) {
    Swal.fire({ icon: 'warning', title: 'ยังไม่มีรายการเบิกอะไหล่' });
    return false;
  }
  partRefreshBalancesBeforeSubmit().then(function() {
    // Validate current balances (best-effort).
    // If balances are stale and refresh couldn't run, backend will still validate.
    var over = partCartRows.filter(function(r) {
      var b = partParseNumber(r.balance_qty);
      var q = partParseNumber(r.qty);
      return q > b + 0.00001;
    });
    if (over.length) {
      Swal.fire({ icon: 'warning', title: 'จำนวนเกินคงเหลือล่าสุด', text: 'กรุณากดค้นหาอีกครั้ง/ปรับจำนวน แล้วค่อยยืนยันใหม่' });
      return;
    }

    $('#part-rows-json').val(JSON.stringify(partCartRows));
    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize(),
      dataType: 'json',
      success: function (res) {
        if (res.status === 'success') {
          Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1000, showConfirmButton: false }).then(function () {
            try {
              var el = document.getElementById('part-pos-offcanvas');
              if (el) bootstrap.Offcanvas.getOrCreateInstance(el).hide();
            } catch (e) {}
            window.location.reload();
          });
        } else {
          Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: res.message || 'ไม่สามารถบันทึกได้' });
        }
      },
      error: function () {
        Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้' });
      }
    });
  });
  return false;
});

if (Array.isArray(partInitialRows) && partInitialRows.length) {
  partCartRows = partInitialRows;
  // fallback: ถ้าแถวใดไม่มี warehouse_id ให้ใช้ค่าจาก dropdown ที่เลือกไว้
  var currentWid = $('#part-sub-warehouse-id').val();
  partCartRows.forEach(function (r) {
    if (!r.warehouse_id && currentWid) r.warehouse_id = currentWid;
  });
}
partRenderCart();
JS;
$this->registerJs($js);
?>
