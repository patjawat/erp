<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

 namespace app\widgets\orgchart;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Asset bundle for the Twitter bootstrap javascript files.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Asset extends AssetBundle
{
    public $sourcePath = '@app/widgets/orgchart/assets';

    public $css = [
        'style.css',
    ];

    public $js = [
    ];


    public $jsOptions = [
        // 'position' => View::POS_HEAD, 
    ];

    /*
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    */
}
