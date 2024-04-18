<?php

namespace app\themes\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    // public $basePath = '@webroot';
    public $sourcePath = '@app/themes/assets';
    // public $baseUrl = '@app/themes/assets';
    public $css = [
        'css/bootstrap.min.css',
        'plugins/fontawesome/css/fontawesome.min.css',
        'plugins/fontawesome/css/all.min.css',
        'css/material.css',
        'css/line-awesome.min.css',
        'css/font-awesome.min.css',
        'css/style.css',
        'css/dataTables.bootstrap4.min.css',
        'css/select2.min.css',
        'css/bootstrap-datetimepicker.min.css'
        // 'css/dashbroad.css',

    ];
    public $js = [
		'js/jquery.slimscroll.min.js',
		'js/layout.js',
		'js/greedynav.js',
		'js/theme-settings.js',
		'js/app.js',
        'js/jquery.dataTables.min.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        // 'app\assets\AppAsset',
    ];
}
