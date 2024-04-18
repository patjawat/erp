<?php
namespace app\assets;

use yii\web\AssetBundle;

class BootstapIconAsset extends \yii\web\AssetBundle
{

    public $sourcePath = '@vendor/twbs/bootstrap-icons/font/';
    public $css = [
        'bootstrap-icons.css',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];

}