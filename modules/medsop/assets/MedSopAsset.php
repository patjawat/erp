<?php

namespace app\modules\medsop\assets;

use yii\web\AssetBundle;

class MedSopAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/medsop/assets/dist';
    public $css = ['medsop.css'];
    public $js = ['medsop.js'];
    public $depends = ['yii\web\YiiAsset', 'yii\bootstrap5\BootstrapAsset'];
}
