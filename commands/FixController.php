<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use app\modules\hr\models\Employees;
use yii\console\Controller;
use yii\helpers\BaseConsole;
use app\models\Categorise;

/**
 * แก้ไขรหัสตำแหน่งใหม่ v2
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FixController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex() {}
    public function actionEmployee()
    {
        if (BaseConsole::confirm("Are you sure?")) {
            $data = [];
            $employees = Employees::find()->all();
            foreach ($employees as $model) {
                if ($model->ref == '') {
                    $emp = Employees::findOne($model->id);
                    $emp->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
                    if ($emp->save(false)) {
                        echo $emp->fname . "\n";
                    } else {
                        echo 'ผิดพลาด' . "\n";
                    }
                }
            }
        }
        // echo  $data;
    }

    public function actionAssetItem()
    {
        if (BaseConsole::confirm("Are you sure?")) {
            $data = [];
            $employees = Categorise::find()->where(['name' => 'asset_item'])->all();
            foreach ($employees as $model) {
                if ($model->ref == '') {
                    $item = Categorise::findOne($model->id);
                    $item->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
                    if ($item->save(false)) {
                        echo $item->title . "\n";
                    } else {
                        echo 'ผิดพลาด' . "\n";
                    }
                }
            }
        }
        // echo  $data;
    }

    // ปรับปรุงเลเวลที่เขียนผิดจากเอไอ
    public function actionApprove()
    {
        if (\yii\helpers\BaseConsole::confirm("คุณแน่ใจหรือไม่ที่จะปรับปรุงข้อมูลสถานะการอนุมัติ?")) {

            // นำคำสั่ง SQL มาแยกเก็บใน Array
            $queries = [
                // แก้คำผิด 
                "UPDATE `approve_level_setting` SET `label` = 'เห็นชอบ' WHERE `system` = 'leave' AND `level` = 1",
                "UPDATE `approve_level_setting` SET `label` = 'เห็นชอบ' WHERE `system` = 'leave' AND `level` = 2",
                "UPDATE `approve_level_setting` SET `label` = 'ผาน' WHERE `system` = 'leave' AND `level` = 3",
                "UPDATE `approve_level_setting` SET `label` = 'อนุมัติ' WHERE `system` = 'leave' AND `level` = 4",

                // 1. เปลี่ยน เจ้าหน้าที่ตรวจสอบ -> ผ่าน
                "UPDATE `approve` SET `data_json` = JSON_SET(`data_json`, '$.label', 'ผ่าน') WHERE `data_json`->>'$.label' = 'เจ้าหน้าที่ตรวจสอบ'",

                // 2. เปลี่ยน หัวหน้าเห็นชอบ -> เห็นชอบ
                "UPDATE `approve` SET `data_json` = JSON_SET(`data_json`, '$.label', 'เห็นชอบ') WHERE `data_json`->>'$.label' = 'หัวหน้าเห็นชอบ'",

                // 3. เปลี่ยน หัวหน้ากลุ่มงานเห็นชอบ -> เห็นชอบ
                "UPDATE `approve` SET `data_json` = JSON_SET(`data_json`, '$.label', 'เห็นชอบ') WHERE `data_json`->>'$.label' = 'หัวหน้ากลุ่มงานเห็นชอบ'",

                // 4. เปลี่ยน ผอ.อนุมัติ -> อนุมัติ
                "UPDATE `approve` SET `data_json` = JSON_SET(`data_json`, '$.label', 'อนุมัติ') WHERE `data_json`->>'$.label' = 'ผอ.อนุมัติ'"
            ];

            // เริ่มต้น Transaction
            $transaction = \Yii::$app->db->beginTransaction();
            $totalAffected = 0;

            try {
                // วนลูปรันทีละคำสั่ง
                foreach ($queries as $index => $sql) {
                    $affectedRows = \Yii::$app->db->createCommand($sql)->execute();
                    $totalAffected += $affectedRows;

                    // แสดงผลความคืบหน้าทีละคำสั่ง
                    \yii\helpers\BaseConsole::output("ชุดที่ " . ($index + 1) . " ปรับปรุงสำเร็จ: $affectedRows รายการ");
                }

                // ยืนยันการบันทึกข้อมูล (Commit)
                $transaction->commit();

                \yii\helpers\BaseConsole::output("-------------------------------------");
                \yii\helpers\BaseConsole::output("เสร็จสมบูรณ์! ปรับปรุงข้อมูลรวมทั้งหมด: $totalAffected รายการ");
            } catch (\Exception $e) {
                // ยกเลิกการบันทึกหากเกิดข้อผิดพลาด (Rollback)
                $transaction->rollBack();
                \yii\helpers\BaseConsole::error("เกิดข้อผิดพลาด: " . $e->getMessage());
                \yii\helpers\BaseConsole::error("ได้ทำการยกเลิกคำสั่งทั้งหมดแล้ว (Rolled back)");
            }
        } else {
            \yii\helpers\BaseConsole::output("ยกเลิกการทำงาน");
        }
    }
}
