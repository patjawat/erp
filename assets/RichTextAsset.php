<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * แถบเครื่องมือจัดรูปแบบข้อความสำหรับ textarea[data-richtext]
 * ลงทะเบียนในหน้าที่มีฟิลด์ข้อความยาวซึ่งต้องจัดเป็นข้อหรือหัวข้อย่อยได้
 */
class RichTextAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/richtext';
    public $css = ['richtext.css'];
    public $js = ['richtext.js'];
    public $publishOptions = ['forceCopy' => YII_DEBUG];
    public $depends = [
        'yii\web\YiiAsset',
        'app\assets\BootstapIconAsset',
    ];
}
