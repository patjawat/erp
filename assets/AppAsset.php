<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        // "css/site.css",
        'css/custom.css',
        // "css/v2.css",
        // "css/docs.css",
        'css/fontawesome-free-6.6.0-web/css/all.css',
        'css/bootstrap-icons.min.css',
        'aos/aos.css',
        'sweetalert2/dist/sweetalert2.css',
        'apexcharts/apexcharts.css',
        // 'css/style.min.css',
        'css/waves.min.css',
        'https://unpkg.com/nprogress@0.2.0/nprogress.css',
        'css/tour.min.css',
        'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.css',
        // 'https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css'
    ];

    public $js = [
        // 'js/v2.js',
        'js/erp.js',
        'js/waves.js',
        'js/thai.datepicker.js',
        'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js',
        // 'js/erp-app.js',
        // 'js/vendor-app.min.js',
        'sweetalert2/dist/sweetalert2.all.min.js',
        'aos/aos.js',
        'apexcharts/apexcharts.min.js',
        'chart-assets/echart/echarts.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js',
        'https://code.highcharts.com/highcharts.js',
        'https://code.highcharts.com/modules/exporting.js',
        'https://unpkg.com/nprogress@0.2.0/nprogress.js',
        'js/tour.js'
        // 'https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js'
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}
