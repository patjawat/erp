<?php

use yii\db\Migration;

class m260518_101626_insert_benefit_categories extends Migration
{
    public function safeUp()
    {
        $rows = [
            [
                'name' => 'benefit',
                'code' => 'house',
                'title' => 'สวัสดิการบ้านพัก',
            ],
            [
                'name' => 'benefit',
                'code' => 'medical',
                'title' => 'ค่ารักษาพยาบาล',
            ],
            [
                'name' => 'benefit',
                'code' => 'education_child',
                'title' => 'ค่าเล่าเรียนบุตร',
            ],
            [
                'name' => 'benefit',
                'code' => 'support',
                'title' => 'เงินช่วยเหลือ',
            ],
            [
                'name' => 'benefit',
                'code' => 'uniform',
                'title' => 'ชุดเครื่องแบบ',
            ],
            [
                'name' => 'benefit',
                'code' => 'accident_insurance',
                'title' => 'ประกันอุบัติเหตุ',
            ],
            [
                'name' => 'benefit',
                'code' => 'life_insurance',
                'title' => 'ประกันชีวิต',
            ],
            [
                'name' => 'benefit',
                'code' => 'travel',
                'title' => 'ค่าเดินทาง',
            ],
            [
                'name' => 'benefit',
                'code' => 'scholarship',
                'title' => 'ทุนการศึกษา',
            ],
            [
                'name' => 'benefit',
                'code' => 'other',
                'title' => 'อื่น ๆ',
            ],
        ];

        foreach ($rows as $row) {

            $exists = (new \yii\db\Query())
                ->from('categorise')
                ->where([
                    'name' => $row['name'],
                    'code' => $row['code'],
                ])
                ->exists();

            if (!$exists) {
                $this->insert('categorise', [
                    'name' => $row['name'],
                    'code' => $row['code'],
                    'title' => $row['title'],
                ]);
            }
        }
    }

    public function safeDown()
    {
        $this->delete('categorise', [
            'name' => 'benefit',
        ]);
    }
    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260518_101626_insert_benefit_categories cannot be reverted.\n";

        return false;
    }
    */
}
