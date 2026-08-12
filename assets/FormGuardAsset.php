<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * กันการส่งฟอร์มซ้ำจากการกดปุ่มบันทึกหลายครั้ง
 * ลงทะเบียนในหน้าฟอร์มที่การบันทึกซ้ำจะสร้างข้อมูลซ้ำ
 */
class FormGuardAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/formguard';
    public $js = ['formguard.js'];
    public $publishOptions = ['forceCopy' => YII_DEBUG];
    public $depends = ['yii\web\YiiAsset'];
}
