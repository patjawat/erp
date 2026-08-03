<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * seed ประเภทหน่วยงาน (categorise name=org_unit_type)
 * แก้ไข/เพิ่มเองได้ภายหลังผ่านหน้าตั้งค่า
 */
class m260801_000002_seed_org_unit_type extends Migration
{
    private array $types = [
        ['OU_ORG', 'หน่วยงาน'],
        ['OU_TEAM', 'ทีมประสาน'],
        ['OU_SSJ', 'สสจ.'],
        ['OU_CUP', 'CUP'],
    ];

    public function safeUp()
    {
        $i = 1;
        foreach ($this->types as [$code, $title]) {
            $exists = (new Query())
                ->from('categorise')
                ->where(['name' => 'org_unit_type', 'code' => $code])
                ->exists();
            if (!$exists) {
                $this->insert('categorise', [
                    'name' => 'org_unit_type',
                    'code' => $code,
                    'title' => $title,
                    'sort' => (string) $i,
                    'active' => 1,
                ]);
            }
            $i++;
        }
    }

    public function safeDown()
    {
        $this->delete('categorise', ['name' => 'org_unit_type', 'code' => array_column($this->types, 0)]);
    }
}
