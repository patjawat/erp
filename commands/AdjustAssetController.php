<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

class AdjustAssetController extends Controller
{
    /**
     * รันอัปเดตตาราง asset และ categorise
     */
    public function actionIndex()
    {
        // ขอ confirm ก่อน
        $confirm = $this->prompt("คุณต้องการรันการอัปเดตตาราง asset และ categorise ใช่หรือไม่? (y/n): ", [
            'required' => true,
            'pattern' => '/^(y|n)$/i',
            'error' => 'กรุณาพิมพ์ y หรือ n',
        ]);

        if (strtolower($confirm) !== 'y') {
            $this->stdout("ยกเลิกการทำงาน\n");
            return Controller::EXIT_CODE_NORMAL;
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $this->stdout("1. เปลี่ยนกลุ่มของวัสดุ...\n");
            $db->createCommand("
                UPDATE `categorise`
                SET group_id = 'MATER'
                WHERE `name` = 'asset_item' AND group_id = 4
            ")->execute();

            $this->stdout("2. สำรองข้อมูลเก่า...\n");
            $db->createCommand("
                UPDATE `categorise`
                SET name = 'asset_item_old'
                WHERE `group_id` = '3' AND `name` = 'asset_item'
                ORDER BY `group_id` DESC
            ")->execute();

            $this->stdout("3. แก้ไข group_id...\n");
            $db->createCommand("
                UPDATE `categorise`
                SET group_id = 'EQUIP'
                WHERE `name` LIKE 'asset_type' AND group_id = 4
                ORDER BY `group_id` DESC
            ")->execute();

            $this->stdout("4. ปรับประเภทวัสดุ...\n");
            $db->createCommand("
                UPDATE `categorise`
                SET group_id = 'MATER'
                WHERE `name` = 'asset_type' AND category_id = 4
            ")->execute();

            $db->createCommand("
                UPDATE `categorise`
                SET group_id = 'INTAN'
                WHERE `title` LIKE 'สินทรัพย์ไม่มีตัวตัน'
            ")->execute();

            $db->createCommand("
                UPDATE `categorise`
                SET group_id = 'BLDG'
                WHERE `title` LIKE 'อาคารถาวร'
            ")->execute();

            $this->stdout("5. สำรอง asset_items ลงใน data_json...\n");
            $db->createCommand("
                UPDATE asset_items
                SET data_json = JSON_MERGE_PRESERVE(
                    IF(data_json IS NULL, '{}', data_json),
                    JSON_OBJECT(
                        'asset_group_id', asset_group_id,
                        'asset_type_id', asset_type_id,
                        'asset_category_id', asset_category_id,
                        'fsn', fsn,
                        'price', price,
                        'depreciation', depreciation,
                        'service_life', service_life
                    )
                )
            ")->execute();

            $this->stdout("6. ย้ายข้อมูลจาก asset_items ลง categorise...\n");
            $db->createCommand("
                INSERT INTO categorise (group_id, name, title, code, category_id, data_json)
                SELECT
                    'EQUIP' AS group_id,
                    'asset_item' AS name,
                    ai.title,
                    ai.id AS code,
                    ai.asset_type_id,
                    JSON_MERGE_PRESERVE(
                        COALESCE(c.data_json, '{}'),
                        ai.data_json
                    ) AS data_json
                FROM asset_items ai
                LEFT JOIN categorise c
                    ON c.group_id = 'EQUIP'
                    AND c.name = 'asset_item'
                    AND c.code = ai.id
            ")->execute();

            $transaction->commit();
            $this->stdout("Update completed successfully.\n");

        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->stderr("Error: " . $e->getMessage() . "\n");
        }
    }
}
