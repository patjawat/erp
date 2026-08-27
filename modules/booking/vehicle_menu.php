<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var string $active */
$active = $active ?? '';
$officialActive = $active === 'official';
$ambulanceActive = $active === 'ambulance';
$moreActive = in_array($active, ['asset', 'setting'], true);
?>
<nav class="d-flex flex-wrap align-items-center gap-2" aria-label="เมนูระบบยานพาหนะ">
    <a href="<?= Url::to(['/booking/vehicle/dashboard']) ?>"
       class="btn text-nowrap <?= $active === 'dashboard' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i class="bi bi-grid" aria-hidden="true"></i>
        ภาพรวม
    </a>

    <a href="<?= Url::to(['/booking/vehicle/schedule']) ?>"
       class="btn text-nowrap <?= $active === 'schedule' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i class="bi bi-calendar2-week" aria-hidden="true"></i>
        ตารางการใช้รถ
    </a>

    <div class="dropdown">
        <button type="button"
                class="btn text-nowrap dropdown-toggle <?= $officialActive ? 'btn-primary' : 'btn-outline-primary' ?>"
                data-bs-toggle="dropdown"
                aria-expanded="false">
            <i class="bi bi-car-front" aria-hidden="true"></i>
            รถยนต์ทั่วไป
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <?= Html::a(
                    '<i class="bi bi-calendar3 me-2" aria-hidden="true"></i>ปฏิทินการใช้รถ',
                    ['/booking/vehicle/calendar'],
                    ['class' => 'dropdown-item']
                ) ?>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <?= Html::a(
                    '<i class="bi bi-journal-text me-2" aria-hidden="true"></i>ทะเบียนการจอง',
                    ['/booking/vehicle/index'],
                    ['class' => 'dropdown-item']
                ) ?>
            </li>
            <li>
                <?= Html::a(
                    '<i class="bi bi-person-check me-2" aria-hidden="true"></i>ทะเบียนการจัดสรร',
                    ['/booking/vehicle/work-official'],
                    ['class' => 'dropdown-item']
                ) ?>
            </li>
        </ul>
    </div>

    <div class="dropdown">
        <button type="button"
                class="btn text-nowrap dropdown-toggle <?= $ambulanceActive ? 'btn-primary' : 'btn-outline-primary' ?>"
                data-bs-toggle="dropdown"
                aria-expanded="false">
            <i class="bi bi-truck-front" aria-hidden="true"></i>
            รถฉุกเฉิน
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <?= Html::a(
                    '<i class="bi bi-calendar3 me-2" aria-hidden="true"></i>ปฏิทินการใช้รถ',
                    ['/booking/vehicle/calendar-ambulance'],
                    ['class' => 'dropdown-item']
                ) ?>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <?= Html::a(
                    '<i class="bi bi-journal-text me-2" aria-hidden="true"></i>ทะเบียนการจอง',
                    ['/booking/vehicle/ambulance'],
                    ['class' => 'dropdown-item']
                ) ?>
            </li>
            <li>
                <?= Html::a(
                    '<i class="bi bi-person-check me-2" aria-hidden="true"></i>ทะเบียนการจัดสรร',
                    ['/booking/vehicle/work-ambulance'],
                    ['class' => 'dropdown-item']
                ) ?>
            </li>
        </ul>
    </div>

    <div class="dropdown">
        <button type="button"
                class="btn text-nowrap dropdown-toggle <?= $moreActive ? 'btn-primary' : 'btn-outline-secondary' ?>"
                data-bs-toggle="dropdown"
                aria-expanded="false">
            <i class="bi bi-three-dots" aria-hidden="true"></i>
            เพิ่มเติม
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <?= Html::a(
                    '<i class="bi bi-building me-2" aria-hidden="true"></i>ทรัพย์สิน',
                    ['/booking/asset'],
                    ['class' => 'dropdown-item' . ($active === 'asset' ? ' active' : '')]
                ) ?>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <?= Html::a(
                    '<i class="bi bi-gear me-2" aria-hidden="true"></i>ตั้งค่าสถานะการจอง',
                    ['/booking/vehicle-status'],
                    ['class' => 'dropdown-item' . ($active === 'setting' ? ' active' : '')]
                ) ?>
            </li>
        </ul>
    </div>
</nav>
