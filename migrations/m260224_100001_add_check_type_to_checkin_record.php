<?php

use yii\db\Migration;

/**
 * Adds check_type to checkin_record (in=บันทึกเข้า, out=บันทึกออก).
 */
class m260224_100001_add_check_type_to_checkin_record extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%checkin_record}}', 'check_type', $this->string(10)->notNull()->defaultValue('in')->comment('in=บันทึกเข้า, out=บันทึกออก')->after('method'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%checkin_record}}', 'check_type');
    }
}
