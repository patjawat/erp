<?php
use yii\db\Migration;

class m260826_140000_add_signer_to_iac_pk4 extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%iac_pk4}}','signer_emp_id',$this->integer()->null()->after('summary'));
        $this->addColumn('{{%iac_pk4}}','signer_name',$this->string(255)->null()->after('signer_emp_id'));
        $this->addColumn('{{%iac_pk4}}','signer_position',$this->string(255)->null()->after('signer_name'));

        foreach (\app\modules\iacRisk\models\Pk4::find()->all() as $pk4) {
            $unit=\app\modules\settings\models\OrgUnit::findOne($pk4->org_unit_id);
            $signer=$unit?\app\modules\hr\models\Employees::findOne($unit->leader_emp_id):null;
            if (!$signer) continue;
            $pk4->signer_emp_id=$signer->id;
            $pk4->signer_name=$signer->fullname();
            $pk4->signer_position=$signer->positionName();
            $pk4->save(false,['signer_emp_id','signer_name','signer_position']);
        }
    }

    public function safeDown()
    {
        $this->dropColumn('{{%iac_pk4}}','signer_position');
        $this->dropColumn('{{%iac_pk4}}','signer_name');
        $this->dropColumn('{{%iac_pk4}}','signer_emp_id');
    }
}
