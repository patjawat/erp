<?php

namespace app\widgets\datepicker;

use yii\web\AssetBundle;

/**
 * Asset bundle สำหรับ Thai Datepicker widget
 * ใช้ thaiDatepicker จาก AppAsset (web/js/thai.datepicker.js) เพื่อไม่โหลดซ้ำ
 */
class Assets extends AssetBundle
{
    public $js = [];
    public $css = [];

    public $depends = [
        'yii\web\YiiAsset',
        'app\assets\AppAsset',
    ];
}
