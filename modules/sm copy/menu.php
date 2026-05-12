<?php
use yii\helpers\Url;
$menuItems = [
    [
        'title' => '<i class="fa-solid fa-user-tag"></i> บริหารงานพัสดุ',
        'url' => '/sm'
    ],
    // [
    //     'title' => 'ทะเบียนประวัติ',
    //     'url' => '/hr/employees'
    // ],
    // [
    //     'title' => 'ปฎิทินวันหยุด',
    //     'url' => '/hr/employees'
    // ]
];
?>
  <nav class="nav" aria-label="Secondary navigation">
    <a class="nav-link active" aria-current="page" href="<?=Url::to(['/sm'])?>"><i class="bi bi-bar-chart fs-5"></i> Dashboard</a>
    <!-- <a class="nav-link" href="<?=Url::to(['/sm'])?>">
    <i class="bi bi-person-check-fill fs-5"></i> บริหารงานพัสดุ
      <span class="badge text-bg-secondary text-light rounded-pill align-text-bottom">27</span>
    </a> -->
    <a class="nav-link" href="<?=Url::to(['/sm/order'])?>"><i class="bi bi-motherboard fs-5"></i> ทะเบียนคุม</a>
    <a class="nav-link" href="<?=Url::to(['/sm/sell'])?>"><i class="bi bi-cash-coin fs-5"></i> จำหน่ายขาดทอดตลาด</a>
  </nav>