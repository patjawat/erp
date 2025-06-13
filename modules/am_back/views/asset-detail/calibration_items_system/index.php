<?php

use yii\helpers\Url;
use yii\widgets\Pjax;


?>
<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>
<a class="btn btn-primary" data-bs-toggle="pill" href="" aria-selected="true" role="tab" id="label-push">
      <i class="bi bi-plus-circle-fill"></i> เพิ่มรายการแผนบำรุงรักษา
</a>
<div id="input-push" style="display: none;"  >
    <label  class="form-label">เพิ่มรายการแผนบำรุงรักษา</label>
    <input type="text" class="form-control" id="input-push-input">
</div>

<div
    class="table-responsive mt-3"
>
    <table
        class="table table-primary">
        <thead class="table-secondary">
            <tr>
                <th scope="col" class="col">แผนบำรุงรักษา</th>
                <th scope="col" class="col-3">การดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if(isset($model->data_json["ma_items"])){
            ?>
            <?php
                foreach ($model->data_json["ma_items"] as $index => $item) {
            ?>
            <tr class="" id=<?= $index ?>>
                <td scope="row" id=<?= "label-" . $index ?>><?php echo $item  ?></td>
                <td id=<?="input-" . $index ?> style="display: none;"><input id=<?= $index ?> type="text" class="form-control" value="<?= $item ?>"></td>
                <td>
                  <button type="button" id=<?= $index ?> class="btn btn-warning">แก้ไข</button>
                  <button type="button" id=<?= $index ?> class="btn btn-danger">ลบ</button>
                </td>
            </tr>
                <?php
                    }
                 }
                ?>
        </tbody>
    </table>
</div>
<?php Pjax::end();?>
<?php
$url = Url::to(['/am/asset-detail']);

$js = <<< JS

$(document).ready(function() {
    $('.btn-primary').click(function() {
        $('#' + "label-push").hide()
        $('#' + "input-push").show();
    });
    $('input').keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault(); 
            var value = $(this).val(); 
            var rowId = $(this).attr('id'); 
            if (rowId == "input-push-input"){
                $.ajax({
                    type: "get",
                    url: "$url" + "/push-ma-item",
                    data:{
                        id:"$model->id",
                        id_row:rowId,
                        value: value
                    },
                    dataType: "json",
                    success: function (res) {
                            console.log(res);
                            if(res.status == 'success') {
                                // alert(data.status)
                                console.log(res.container);
                                // $('#main-modal').modal('toggle');
                                addtr(res.value,res.index)
                                success()
                                $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});                                                        
                                $('#' + "label-push").show();
                                $('#' + "input-push").hide()
                            }
                        }
                    });
            }
        }
    });

});

$(document).on('click', '.btn-warning', function() {
        var rowId = $(this).attr('id'); 
        $('#' + "label-" + rowId).hide()
        $('#' + "input-" + rowId).show();
});

$(document).on('click', '.btn-danger', function() {
    var rowId = $(this).attr('id'); 
        $.ajax({
        type: "get",
        url: "$url" + "/delete-ma-item",
        data:{
            id:"$model->id",
            id_row:rowId
        },
        dataType: "json",
        success: function (res) {
                console.log(res);
                if(res.status == 'success') {
                    // alert(data.status)
                    console.log(res.container);
                    // $('#main-modal').modal('toggle');
                    success()
                    $('#' + rowId).remove();
                    $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});                                                        
                }
            }
        });
})

$(document).on('keypress', 'input', function(event) {
        if (event.which == 13) {
            event.preventDefault();
            var value = $(this).val(); 
            var rowId = $(this).attr('id'); 
            if (rowId == "input-push-input"){
                return
            }
            $.ajax({
            type: "get",
            url: "$url" + "/update-ma-item",
            data:{
                id:"$model->id",
                id_row:rowId,
                value: value
            },
            dataType: "json",
            success: function (res) {
                    console.log(res);
                    if(res.status == 'success') {
                        // alert(data.status)
                        console.log(res.container);
                        // $('#main-modal').modal('toggle');
                        success()
                        $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});                                                        
                        $('#' + "label-" + rowId).show()
                        $('#' + "input-" + rowId).hide()
                        $('#' + "label-" + rowId).text(res.value)
                    }
                }
            });


        }
    });

function addtr(value, id){
    var newRow = document.createElement("tr");
    newRow.setAttribute("id", id);

    // สร้าง <td> และเพิ่มข้อความลงในแต่ละ <td>
    var cell1 = document.createElement("td");
    var cell2 = document.createElement("td");
    var cell3 = document.createElement("td");
    var input = document.createElement("input");
    var btn1 = document.createElement("button");
    var btn2 = document.createElement("button");
    cell1.textContent = value;
    cell1.setAttribute("id", "label-"+id)
    cell2.setAttribute("id", "input-"+id)
    input.setAttribute("id", id);
    btn1.setAttribute("id", id);
    btn2.setAttribute("id", id);
    input.classList.add("form-control");
    btn1.classList.add("btn", "btn-warning");
    btn2.classList.add("btn", "btn-danger");
    btn1.textContent = "แก้ไข"
    btn2.textContent = "ลบ"
    btn2.style.marginLeft = "7px";
    input.value = value;
    cell2.style.display = "none";
    cell2.appendChild(input);
    cell3.appendChild(btn1);
    cell3.appendChild(btn2);
    // เพิ่ม <td> ลงใน <tr>
    newRow.appendChild(cell1);
    newRow.appendChild(cell2);
    newRow.appendChild(cell3);

    // เพิ่ม <tr> ที่สร้างเข้าไปใน <tbody>
    var tbody = document.querySelector("tbody");
    tbody.appendChild(newRow);
}

JS;
$this->registerJs($js);
?>