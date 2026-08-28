<?php

use yii\db\Migration;

/**
 * เติมค่า ref ให้พนักงานเก่าที่ยังไม่มีค่า (NULL หรือว่าง)
 * ref ถูกใช้เป็น key เชื่อมกับตาราง uploads (avatar, signature, เอกสาร) หากว่างจะอัพโหลดไฟล์ไม่ได้
 */
class m260715_120000_backfill_employees_ref extends Migration
{
    public function safeUp()
    {
        $rows = (new \yii\db\Query())
            ->select(['id'])
            ->from('{{%employees}}')
            ->where(['or', ['ref' => null], ['ref' => '']])
            ->all($this->db);

        foreach ($rows as $row) {
            do {
                $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            } while ($this->db->createCommand(
                'SELECT COUNT(*) FROM {{%employees}} WHERE [[ref]] = :ref',
                [':ref' => $ref]
            )->queryScalar() > 0);

            $this->update('{{%employees}}', ['ref' => $ref], ['id' => $row['id']]);
        }

        echo "    > backfilled ref for " . count($rows) . " employee(s)\n";
    }

    public function safeDown()
    {
        echo "    > m260715_120000_backfill_employees_ref cannot be reverted (data fix only).\n";
        return true;
    }
}
