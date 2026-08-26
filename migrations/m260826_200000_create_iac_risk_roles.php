<?php
use yii\db\Migration;

class m260826_200000_create_iac_risk_roles extends Migration
{
    private array $roles=[
        'iacRiskAuthorRole'=>['ผู้จัดทำ IAC&Risk',['iacRiskView','iacRiskAuthor']],
        'iacRiskUnitApproverRole'=>['หัวหน้าหน่วยงาน IAC&Risk',['iacRiskView','iacRiskUnitApprove']],
        'iacRiskCoordinatorRole'=>['ทีมประสาน IAC&Risk',['iacRiskView','iacRiskAuthor','iacRiskCoordinate']],
        'iacRiskCommitteeRole'=>['คณะกรรมการ IAC&Risk',['iacRiskView','iacRiskCommittee']],
        'iacRiskDirectorRole'=>['ผู้อำนวยการ IAC&Risk',['iacRiskView','iacRiskDirector']],
        'iacRiskAdministratorRole'=>['ผู้ดูแลระบบ IAC&Risk',['iacRiskView','iacRiskAuthor','iacRiskUnitApprove','iacRiskCoordinate','iacRiskCommittee','iacRiskDirector','iacRiskAdmin']],
    ];

    public function safeUp()
    {
        $auth=Yii::$app->authManager;
        foreach($this->roles as $name=>[$description,$permissions]){
            $role=$auth->getRole($name);if(!$role){$role=$auth->createRole($name);$role->description=$description;$auth->add($role);}
            foreach($permissions as $permissionName){$permission=$auth->getPermission($permissionName);if($permission&&!$auth->hasChild($role,$permission))$auth->addChild($role,$permission);}
        }
        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth=Yii::$app->authManager;foreach(array_reverse(array_keys($this->roles)) as $name)if($role=$auth->getRole($name))$auth->remove($role);$auth->invalidateCache();
    }
}
