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
class LoginAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'login-design/style.css',
        'aos/aos.css', // self-hosted (เดิมดึงจาก unpkg CDN)
    ];
    public $js = [
        'login-design/js.js',
        'aos/aos.js', // self-hosted (เดิมดึงจาก unpkg CDN)
        'libs/typed/typed.min.js', // self-hosted (เดิมดึงจาก jsdelivr CDN)
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
    ];
}
