<?php

use yii\db\Migration;

/** Payroll permissions kept separate from schema DDL for safer recovery. */
class m260816_141000_create_payroll_rbac extends Migration
{
    private array $permissions = [
        'payrollView' => 'ดูข้อมูลความพร้อมเงินเดือน',
        'payrollPrepare' => 'จัดเตรียมรอบเงินเดือน',
        'payrollApprove' => 'อนุมัติรอบเงินเดือน',
        'payrollBankManage' => 'จัดการข้อมูลบัญชีรับเงิน',
    ];

    private array $assignments = [
        ['financeViewer', 'payrollView'], ['financeOfficer', 'payrollPrepare'],
        ['financeApprover', 'payrollApprove'], ['financeAdmin', 'payrollBankManage'],
    ];

    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        foreach ($this->permissions as $name => $description) {
            if ($auth->getPermission($name) === null) {
                $permission = $auth->createPermission($name);
                $permission->description = $description;
                $auth->add($permission);
            }
        }
        foreach ($this->assignments as [$roleName, $permissionName]) {
            $role = $auth->getRole($roleName);
            $permission = $auth->getPermission($permissionName);
            if ($role !== null && $permission !== null && !$auth->hasChild($role, $permission)) {
                $auth->addChild($role, $permission);
            }
        }
        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        foreach (array_reverse($this->assignments) as [$roleName, $permissionName]) {
            $role = $auth->getRole($roleName);
            $permission = $auth->getPermission($permissionName);
            if ($role !== null && $permission !== null && $auth->hasChild($role, $permission)) {
                $auth->removeChild($role, $permission);
            }
        }
        foreach (array_reverse(array_keys($this->permissions)) as $name) {
            if (!$this->legacyCoreApplied() && ($permission = $auth->getPermission($name))) $auth->remove($permission);
        }
        $auth->invalidateCache();
    }

    private function legacyCoreApplied(): bool
    {
        return (new \yii\db\Query())->from('{{%migration}}')->where(['version' => 'm260816_140000_create_payroll_core'])->exists($this->db);
    }
}
