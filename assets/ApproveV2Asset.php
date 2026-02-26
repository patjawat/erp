<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Asset สำหรับหน้า approve-v2 — สไตล์ soft modern
 */
class ApproveV2Asset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/approve-v2.css',
    ];

    public $depends = [
        'yii\bootstrap5\BootstrapAsset',
    ];
}
