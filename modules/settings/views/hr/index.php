<?php

$items = [
  ['name' => 'prefix','title' => 'คำนำหน้า'],
  ['name' => 'Nationality','title' => 'สัญชาติ/เชื้อชาติ'],
  ['name' => 'marry','title' => 'สถานภาพสมรส'],
  ['name' => 'blood','title' => 'หมู่โลหิต'],
  ['name' => 'religion','title' => 'ศาสนา'],
  ['name' => 'position_type','title' => 'ประเภทตำแหน่ง'],
  ['name' => 'position_group','title' => 'กลุ่มงาน'],
  ['name' => 'position_name','title' => 'กำหนดตำแหน่ง'],
  ['name' => 'position_level','title' => 'ระดับตำแหน่ง'],
  // ['name' => 'position_manage','title' => 'ตำแหน่งบริหาร'],
  ['name' => 'expertise','title' => 'ความเชี่ยวชาญ'],
];

// $items = [
//     [
//         'title' => 'คำนาหน้า',
//         'description' => 'คั้งค่าการกำหนดคำนำหน้าชื่อ',
//         'icon' => '<i class="fa-solid fa-user-gear"></i>',
//         'link' => ''
//     ],
//     [
//         'title' => 'สัญชาติ/เชื้อชาติ',
//         'description' => 'คั้งค่าการกำหนดสัญชาติ/เชื้อชาติ',
//         'icon' => '<i class="fa-solid fa-user-gear"></i>',
//         'link' => ''
//     ],
//     [
//         'title' => 'สถานภาพสมรส',
//         'description' => 'คั้งค่าการกำหนดสถานภาพสมรส',
//         'icon' => '<i class="fa-solid fa-user-gear"></i>',
//         'link' => ''
//     ],
//     [
//         'title' => 'หมู่โลหิต',
//         'description' => 'คั้งค่าการกำหนดหมู่โลหิต',
//         'icon' => '<i class="fa-solid fa-user-gear"></i>',
//         'link' => ''
//     ],
//     [
//         'title' => 'ศาสนา',
//         'description' => 'คั้งค่าการกำหนดศาสนา',
//         'icon' => '<i class="fa-solid fa-user-gear"></i>',
//         'link' => ''
//     ],
// ];

?>
<style>
.card .icon {
  font-size: 3rem;
  color:  var(--bs-primary);
}

.card-text {
  color: gray;
}

.card {
  transition: all 0.5s;
}

.card:hover {
  background-color: var(--bs-primary);
  color: #fff;
}

.card:hover .icon,
.card:hover .card-text,
.card:hover .card-title {
  color: #fff !important;
}

</style>
<div class="row d-flex justify-content-center3">
<?php foreach ($items as $item):?>
  <div class="col-2">
<a href="http://">

  <div class="card text-center  shadow p-4" style="max-width: 22rem;">
    <div class="icon">
      <i class="fa-solid fa-user-gear"></i>
    </div>
    <div class="card-body">
      <h4 class="card-title fw-bold"><?=$item['title']?></h4>
      <p class="card-text">xx</p>
    </div>
  </div>
</a>
</div>
<?php endforeach;?>    
    
</div>
