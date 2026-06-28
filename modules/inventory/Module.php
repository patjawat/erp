<?php

namespace app\modules\inventory;

use Yii;

/**
 * warehouse module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\inventory\controllers';

    /**
     * โหมดอ่านอย่างเดียว (Big-bang migration → V2):
     * - true  = ปิดการสร้าง/แก้ไข/ยืนยัน/ยกเลิกใบเบิก-จ่าย-รับใน V1 (อ่านได้อย่างเดียว)
     * - false = ใช้งานปกติ
     * Default true หลังเสร็จ migration — แก้ใน config/main.php ถ้าต้องการเปิดชั่วคราว
     * @var bool
     */
    public $frozen = true;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
    }

    /**
     * เช็คว่าโมดูล V1 อยู่ในโหมด freeze (read-only) หรือไม่
     */
    public static function isFrozen()
    {
        $module = Yii::$app->getModule('inventory');
        return $module instanceof self ? (bool) $module->frozen : true;
    }
}
