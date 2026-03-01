<?php

use yii\db\Migration;

/**
 * เพิ่มคอลัมน์ org_node_level — กำหนดระดับโหนดในผังองค์กร (hr/organization/diagram) ที่ใช้หาผู้อนุมัติ
 * null/0 = ใช้แผนกผู้ขอโดยตรง, 1 = ประเภท, 2 = กลุ่มงาน ฯลฯ
 */
class m260228_120000_add_org_node_level_to_approve_level_setting extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%approve_level_setting}}', 'org_node_level', $this->tinyInteger()->null()->comment('ระดับโหนดในผังองค์กร: null=แผนกผู้ขอ, 1=ประเภท, 2=กลุ่มงาน'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%approve_level_setting}}', 'org_node_level');
    }
}
