<?php

use yii\db\Migration;
use yii\db\Query;

class m260802_000002_grant_probation_appraisal_to_user extends Migration
{
    private const ROUTE = '/hr/probation-appraisal/*';

    public function safeUp()
    {
        if (!(new Query())->from('{{%auth_item}}')->where(['name' => self::ROUTE])->exists()) {
            $this->insert('{{%auth_item}}', [
                'name' => self::ROUTE,
                'type' => 2,
                'description' => 'เส้นทางประเมินช่วงทดลองงานสำหรับผู้ได้รับมอบหมาย',
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        }
        if ((new Query())->from('{{%auth_item}}')->where(['name' => 'user'])->exists()
            && !(new Query())->from('{{%auth_item_child}}')->where(['parent' => 'user', 'child' => self::ROUTE])->exists()) {
            $this->insert('{{%auth_item_child}}', ['parent' => 'user', 'child' => self::ROUTE]);
        }
    }

    public function safeDown()
    {
        $this->delete('{{%auth_item_child}}', ['parent' => 'user', 'child' => self::ROUTE]);
        $this->delete('{{%auth_item}}', ['name' => self::ROUTE]);
    }
}
