<?php
 use softark\duallistbox\DualListbox;
  use yii\widgets\ActiveForm;
?>
<?php
#<span> https://github.com/softark/yii2-dual-listbox</span>
?>

<div class="card border">
  <div class="card-body d-flex">
    <div class="card border flex-fill">
      <div class="card-header d-flex justify-content-center" >
        items
      </div>
      <div class="card-body">
            <div class="card-subtitle">
                  <input type="text" name="" id="search-select" class="w-100 form-control" placeholder="search">
            </div>
            <div id="list-select" class="mt-3">
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
        selected items
      </div>
      <div class="card-body">
            <div class="card-subtitle">
                 <input type="text" name="" id="search-selected" class="w-100 form-control" placeholder="search" >
            </div>
            <div id="list-selected"  class="mt-2">
            </div>
      </div>
    </div>
  </div>
</div>
<div class="d-flex align-items-center justify-content-center" style="
    width: 54px;
    height: 28px;
    line-height: 28px;
    color: rgb(255, 255, 255);
    background: rgb(56, 126, 232);" id="btn-submit">
            <span>Submit</span>
</div>


<?php
    $options = [
        'multiple' => true,
        'size' => 20,
        "style" => "background: black;"
    ];
    // echo Html::activeListBox($model, $attribute, $items, $options);
    echo DualListbox::widget([
        'model' => $model,
        'attribute' => "name",
        'items' => [
          1 => "A",
          2 => "B",
          3 => "C",
        ],
        'options' => $options,
        'clientOptions' => [
            'moveOnSelect' => false,
            'selectedListLabel' => 'Selected Items',
            'nonSelectedListLabel' => 'Available Items',
        ],
    ]);
?>

<?php
$js = <<< JS

let data = {
  0 : {
    id : 0,
    name : "Apple"
  },
  1 : {
    id : 1,
    name : "Book"
  },
  2 : {
    id : 2,
    name : "Cat"
  },
  3 : {
    id : 3,
    name : "Dog"
  },
  4 : {
    id : 4,
    name : "Egg"
  },
}

let current_data = []
current_data = data
selected_data = []
selected_data_2 = []

show_data_1 = []
show_data_2 = []
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
    if(!show_data_1.includes(String(list[key]["id"])) && show_data_2.length != 0 && show_data_2.includes(String(list[key]["id"]))){
      addlist("list-selected",list[key]["id"], list[key]["name"])
    }else{
      addlist("list-select",list[key]["id"], list[key]["name"])
    }
  }
}
function AddAllListSub(list,index){
  if (index == 1) {
    removeAll("#list-selected")
    for (let key in list) {
      if(!show_data_1.includes(String(list[key]["id"])) && show_data_2.length != 0 && show_data_2.includes(String(list[key]["id"]))){
      addlist("list-selected",list[key]["id"], list[key]["name"])
    }
    }
  }else{
    removeAll("#list-select")
    for (let key in list) {
      if(show_data_1.includes(String(list[key]["id"])) && show_data_1.length != 0 && show_data_1.includes(String(list[key]["id"]))){
      addlist("list-select",list[key]["id"], list[key]["name"])
    }
    }
  }

}
AddAllList(data)
if (show_data_2.length == 0) {
  for (let key in data) {
    show_data_1.push(key)
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
    let newValue = $(this).val();
    current_data = search(newValue)
    removeAll("#list-select")
    removeAll("#list-selected")
    AddAllList(current_data)
    AddAllListSub(data, 1)
});

$('#search-selected').on('input', function() {

    let newValue = $(this).val();
    current_data = search(newValue)
    removeAll("#list-select")
    removeAll("#list-selected")
    AddAllList(current_data)
    AddAllListSub(data, 2)
});


$('#btn-submit').click(function() {
  console.log(current_data)
  console.log(show_data_1)
  console.log(show_data_2)
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
});

$('#btn-unaccept').click(function() {
  let list_remove = []
  selected_data_2.forEach(function(key) {
    //addlist("list-select",key, data[key]["name"])
    show_data_1.push(key)
    list_remove.push(key)
  });
  list_remove.forEach(function(key) {
    selected_data_2.splice(selected_data_2.indexOf(key) ,1)
    show_data_2.splice(show_data_2.indexOf(key) ,1)
  });
  removeAll("#list-selected")
  removeAll("#list-select")
  AddAllList(current_data)
  AddAllListSub(data, 2)
});




JS;
$this->registerJS($js, yii\web\View::POS_END);
?>