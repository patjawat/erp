<?php

namespace app\widgets\datepicker;

use yii\web\AssetBundle;

class Assets extends AssetBundle
{
	public $sourcePath = '@app/widgets/datepicker';

    public $js = [
        'dist/thai.datepicker.js',
    ];

    public $css = [
        'dist/jquery.datetimepicker.css',
    ];

	public $depends = [
		'yii\web\YiiAsset',
	];
}
