<?php
  use softark\duallistbox\DualListbox;
  use yii\widgets\ActiveForm;
  use app\modules\am\models\Asset;
  use yii\helpers\ArrayHelper;
  use yii\helpers\Url;
use app\modules\am\models\AssetDetail;
?>

<?php

#<span> https://github.com/softark/yii2-dual-listbox</span>->select('code')->asArray()
// ดึงข้อมูลทั้งหมดของ Asset



$model_asset_detail = AssetDetail::find()->where(['code' => $model->code])->one();
if (null !== $model_asset_detail) {
  if (!isset($model_asset_detail->data_json["items"])){
    $have_list = json_encode([]);
  }else{
    $have_list = json_encode($model_asset_detail->data_json["items"]);
  }
} else {
  // กรณีที่ไม่พบข้อมูล
  $have_list = json_encode([]);
  $model_asset_detail = new AssetDetail([
    'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
  ]);
}


$assets = Asset::find()->all();

$filteredAssets = array_filter($assets, function($asset) {
    return isset($asset->data_json['item']) && $asset->asset_group == 3 && $asset->asset_status == 1;  //$asset->data_json['item']["name"] == "asset_item";
});

$selectedData = ArrayHelper::getColumn($filteredAssets, function($asset) {
    return [
        'code' => $asset->code,
        'name' => $asset->data_json["item"]["title"],
    ];
});

// แสดงผลลัพธ์
$random_items = [];
foreach (array_rand($selectedData, 10) as $key) {
    $random_items[] = $selectedData[$key];
}
$list =json_encode($selectedData);
?>
    <?php 
    $form = ActiveForm::begin([
        'id' => 'form-asset-detail',
    ]); 
?>
<?= $form->field($model_asset_detail, 'ma')->hiddenInput(['id' => "data_json-input"])->label(false) ?>
<?= $form->field($model_asset_detail, 'name')->hiddenInput(['value' => "asset_item"])->label(false) ?>
<?= $form->field($model_asset_detail, 'code')->hiddenInput(['value' => $model->code])->label(false) ?>

<div class="card border">
  <div class="card-body d-flex">
    <div class="card border flex-fill">
      <div class="card-header d-flex justify-content-center" >
        เลือกครุภัณฑ์ภายใน
      </div>
      <div class="card-body">
            <div class="card-subtitle">
                  <input type="text" name="" id="search-select" class="w-100 form-control" placeholder="search">
            </div>
            <div id="list-select" class="mt-3 overflow-auto" style="height: 250px;">
            </div>
      </div>
    </div>
    <div class="card flex-shrink-5 align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center" style="
    width: 34px;
    height: 28px;
    line-height: 28px;
    color: rgb(187, 187, 187);
    background: rgb(224, 224, 224);" id="btn-accept">
            <i class="bi bi-caret-right-fill"></i>
      </div>
      <div class="mt-1">
      </div>
      <div class="d-flex align-items-center justify-content-center" style="
    width: 34px;
    height: 28px;
    line-height: 28px;
    color: rgb(187, 187, 187);
    background: rgb(224, 224, 224);" id="btn-unaccept">
            <i class="bi bi-caret-left-fill"></i>
      </div>
    </div>
    <div class="card border flex-fill">
      <div class="card-header  d-flex justify-content-center">
        ครุภัณฑ์ภายใน
      </div>
      <div class="card-body">
            <div class="card-subtitle">
                 <input type="text" name="" id="search-selected" class="w-100 form-control" placeholder="search" >
            </div>
            <div id="list-selected"  class="mt-2 overflow-auto"  style="height: 250px;">
            </div>
      </div>
    </div>
  </div>
</div>
<?php $create =  $model_asset_detail->isNewRecord ?>
<div class="d-flex justify-content-center">
<?= app\components\AppHelper::BtnSave() ?>

</div>

<?php ActiveForm::end(); ?>

<?php
$url = Url::to(['/am/asset-detail']);
$js = <<< JS
let data = $list
let current_data = []
current_data = data
selected_data = []
selected_data_2 = []

show_data_1 = []
show_data_2 = $have_list
var btn1 = document.getElementById("btn-accept");
var btn2 = document.getElementById("btn-unaccept");
function removelist(id){
    var checkbox = document.getElementById(id);
    var div = document.querySelector('.form-check');

    checkbox.parentNode.parentNode.removeChild(checkbox.parentNode);
}

function removeAll(path) {
    var elements = document.querySelectorAll(path+' .form-check');
    elements.forEach(function(element) {
        element.parentNode.removeChild(element);
    });
}

function AddAllList(list){
  for (let key in list) { 
    if(!show_data_1.includes(String(list[key]["code"])) && show_data_2.length != 0 && show_data_2.includes(String(list[key]["code"]))){
      addlist("list-selected",list[key]["code"], list[key]["name"])
    }else{
      addlist("list-select",list[key]["code"], list[key]["name"])
    }
  }
}
function AddAllListSub(list,index){
  if (index == 1) {
    removeAll("#list-selected")
    for (let key in list) {
      if(!show_data_1.includes(String(list[key]["code"])) && show_data_2.length != 0 && show_data_2.includes(String(list[key]["code"]))){
      addlist("list-selected",list[key]["code"], list[key]["name"])
    }
    }
  }else{
    removeAll("#list-select")
    for (let key in list) {
      if(show_data_1.includes(String(list[key]["code"])) && show_data_1.length != 0 && show_data_1.includes(String(list[key]["code"]))){
      addlist("list-select",list[key]["code"], list[key]["name"])
    }
    }
  }

}
AddAllList(data)
  for (let key in data) {
    if(!(show_data_2.includes(data[key]["code"]))){
      show_data_1.push(data[key]["code"])
    }
  }
document.addEventListener('change', function(event) {
  if (event.target.matches('.form-check-input')) {
    let checkbox = event.target;
    let checkboxId = checkbox.id;
    console.log(checkbox.closest('#list-select') == null);
    if (checkbox.checked ) {
      if (checkbox.closest('#list-select') == null) {
        selected_data_2.push(checkbox.id) 
      }else{
        selected_data.push(checkbox.id)
      }
    } else {
      if (checkbox.closest('#list-select') == null) {
        selected_data_2.splice(selected_data_2.indexOf(checkbox.id) ,1)
      }else{
        selected_data.splice(selected_data.indexOf(checkbox.id) ,1)
      }
    }
    if (selected_data.length >= 1){
      btn1.style.width = "34px";
      btn1.style.height = "28px";
      btn1.style.lineHeight = "28px";
      btn1.style.color = "rgb(255, 255, 255)";
      btn1.style.backgroundColor = "rgb(56, 126, 232)";
    }else{
      btn1.style.width = "34px";
      btn1.style.height = "28px";
      btn1.style.lineHeight = "28px";
      btn1.style.color = "rgb(187, 187, 187)";
      btn1.style.backgroundColor = "rgb(224, 224, 224)";
    }
    if (selected_data_2.length >= 1){
      btn2.style.width = "34px";
      btn2.style.height = "28px";
      btn2.style.lineHeight = "28px";
      btn2.style.color = "rgb(255, 255, 255)";
      btn2.style.backgroundColor = "rgb(56, 126, 232)";
    }else{
      btn2.style.width = "34px";
      btn2.style.height = "28px";
      btn2.style.lineHeight = "28px";
      btn2.style.color = "rgb(187, 187, 187)";
      btn2.style.backgroundColor = "rgb(224, 224, 224)";
    }
  }
});




function addlist(path, id, name){
    // สร้าง element checkbox
    var checkbox = document.createElement('input');
    checkbox.setAttribute('type', 'checkbox');
    checkbox.setAttribute('value', '');
    checkbox.setAttribute('id', id);
    checkbox.classList.add('form-check-input');

    // สร้าง element label
    var label = document.createElement('label');
    label.setAttribute('for', id);
    label.classList.add('form-check-label');
    label.innerText = name;

    // สร้าง element div เพื่อห่อ checkbox และ label
    var div = document.createElement('div');
    div.classList.add('form-check');
    div.appendChild(checkbox);
    div.appendChild(label);

    // เพิ่ม element div ลงใน div ที่มี id เป็น 'list-accept'
    var listAccept = document.getElementById(path);
    listAccept.appendChild(div);
    
}



function search(keyword) {
    let filteredData = Object.values(data).filter((item) => {
        return item.name.toLowerCase().includes(keyword.toLowerCase());
    });

    return filteredData;
}


$('#search-select').on('input', function() {
    selected_data.splice(0,selected_data.length)
    selected_data_2.splice(0,selected_data_2.length)
    let newValue = $(this).val();
    current_data = search(newValue)
    removeAll("#list-select")
    removeAll("#list-selected")
    AddAllList(current_data)
    AddAllListSub(data, 1)
});

$('#search-selected').on('input', function() {
    selected_data.splice(0,selected_data.length)
    selected_data_2.splice(0,selected_data_2.length)
    let newValue = $(this).val();
    current_data = search(newValue)
    removeAll("#list-select")
    removeAll("#list-selected")
    AddAllList(current_data)
    AddAllListSub(data, 2)
});


$('#btn-submit').click(function() {
  console.log($('#data_json-input').val());
});

$('#form-asset-detail').on('beforeSubmit', function (e) {
    var form = $(this);
    if("$create" == 1){
      $.ajax({
          url: "$url" + "/create",
          type: 'post',
          data: form.serialize(),
          dataType: 'json',
          success: async function (response) {
              form.yiiActiveForm('updateMessages', response, true);
              console.log(response);
              if(response.status == 'success') {
                  success()
                  await  $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
              }
          }
      });
    }else{
      $.ajax({
          url: "$url" + "/update?id=$model_asset_detail->id",
          type: 'post',
          data: form.serialize(),
          dataType: 'json',
          success: async function (response) {
              form.yiiActiveForm('updateMessages', response, true);
              console.log(response);
              if(response.status == 'success') {
                  success()
                  await  $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
              }
          }
      });
    }
    return false;
});

$('#btn-accept').click(function() {
  let list_remove = []
  selected_data.forEach(function(key) {
    //addlist("list-selected",key, data[key]["name"])
    list_remove.push(key)
    show_data_2.push(key)
  });
  list_remove.forEach(function(key) {
    selected_data.splice(selected_data.indexOf(key) ,1)
    show_data_1.splice(show_data_1.indexOf(key) ,1)
  });
  removeAll("#list-selected")
  removeAll("#list-select")
  AddAllList(current_data)
  AddAllListSub(data, 1)
  $('#data_json-input').val(show_data_2)
});

$('#btn-unaccept').click(function() {
  let list_remove = []
  selected_data_2.forEach(function(key) {
    //addlist("list-select",key, data[key]["name"])
    list_remove.push(key)
    show_data_1.push(key)
  });
  list_remove.forEach(function(key) {
    selected_data_2.splice(selected_data_2.indexOf(key) ,1)
    show_data_2.splice(show_data_2.indexOf(key) ,1)
  });
  removeAll("#list-selected")
  removeAll("#list-select")
  AddAllList(current_data)
  AddAllListSub(data, 2)
  $('#data_json-input').val(show_data_2)
});




JS;
$this->registerJS($js, yii\web\View::POS_END);
?>

