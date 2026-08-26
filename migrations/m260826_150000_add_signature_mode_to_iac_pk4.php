<?php
use yii\db\Migration;

class m260826_150000_add_signature_mode_to_iac_pk4 extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%iac_pk4}}','signature_type',$this->string(20)->notNull()->defaultValue('system')->after('signer_position'));
        $this->addColumn('{{%iac_pk4}}','signature_data',$this->text()->null()->after('signature_type'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%iac_pk4}}','signature_data');
        $this->dropColumn('{{%iac_pk4}}','signature_type');
    }
}
