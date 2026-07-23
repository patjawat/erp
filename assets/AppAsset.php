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
        'css/custom.css',
        'css/bootstrap-icons.min.css',
        'aos/aos.css',
        'sweetalert2/dist/sweetalert2.css',
        'apexcharts/apexcharts.css',
        'css/waves.min.css',
        'css/tour.min.css',
        'libs/datepicker/jquery.datetimepicker.css',
        'libs/animate/animate.min.css',
        'libs/magnific-popup/magnific-popup.css',
        // 'libs/font-awesome/all.min.css',
        'libs/font-awesome/fontawesome-free-7.1.0-web/css/all.min.css',
    ];

    public $js = [
        'js/erp.js',
        'js/waves.js',
        'libs/datepicker/jquery.datetimepicker.full.min.js',
        'js/thai.datepicker.js',
        'js/fullcalendar.min.js',
        'sweetalert2/dist/sweetalert2.all.min.js',
        'aos/aos.js',
        'apexcharts/apexcharts.min.js',
        'chart-assets/echart/echarts.min.js',
        'libs/lazysizes/lazysizes.min.js', // lazyload รูปภาพ
        'libs/fabric/fabric.min.js',
        'libs/jspdf/jspdf.umd.min.js',
        'libs/pdf/pdf.min.js',
        'libs/magnific-popup/jquery.magnific-popup.js',
        'libs/font-awesome/fontawesome-free-7.1.0-web/js/all.min.js',
        'libs/lucide/lucide.min.js', // self-hosted (เดิมดึงจาก unpkg CDN)
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapPluginAsset',
    ];
}
