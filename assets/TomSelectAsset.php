<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Tom-Select (tom-select.js) CSS + JS จาก CDN
 * ใช้ร่วมกับ TomSelectWidget หรือ register เองเมื่อต้องการ init แบบ custom
 */
class TomSelectAsset extends AssetBundle
{
    public $css = [
        'https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css',
    ];

    public $js = [
        'https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js',
    ];

    public $jsOptions = [
        'position' => View::POS_END,
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
