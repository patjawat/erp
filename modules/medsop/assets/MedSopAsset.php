<?php

namespace app\modules\medsop\assets;

use yii\web\AssetBundle;

class MedSopAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/medsop/assets/dist';
    public $css = ['medsop.css?v=2026071813'];
    public $js = ['medsop.js?v=2026071812'];
    public $publishOptions = ['forceCopy' => YII_DEBUG];
    public $depends = ['yii\web\YiiAsset', 'yii\bootstrap5\BootstrapAsset'];
}
