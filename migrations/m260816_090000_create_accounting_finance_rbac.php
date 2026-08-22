<?php

use yii\db\Migration;

/** Roles and permissions for separated accounting and finance duties. */
class m260816_090000_create_accounting_finance_rbac extends Migration
{
    private array $permissions = [
        'accountingView' => 'ดูข้อมูลระบบบัญชี',
        'accountingInboxReceive' => 'ส่งเอกสารต้นทางเข้ากล่องรับงานบัญชี',
        'accountingPrepare' => 'ตรวจเอกสารและจัดทำทะเบียนเจ้าหนี้',
        'accountingReview' => 'ตรวจทานและส่งรายการบัญชีกลับแก้ไข',
        'accountingApprove' => 'อนุมัติรายการเข้าสู่ทะเบียนบัญชี',
        'financeView' => 'ดูข้อมูลระบบการเงิน',
        'financeOperate' => 'จัดทำฎีกา แผนจ่าย และรายการรับจ่าย',
        'financeApprove' => 'อนุมัติการจ่ายเงิน',
    ];

    private array $roles = [
        'accountingViewer' => 'บัญชี: ดูข้อมูล',
        'accountingMaker' => 'บัญชี: ผู้จัดทำรายการ',
        'accountingReviewer' => 'บัญชี: ผู้ตรวจทาน',
        'accountingApprover' => 'บัญชี: ผู้อนุมัติ',
        'accountingAdmin' => 'บัญชี: ผู้ดูแลระบบ',
        'financeViewer' => 'การเงิน: ดูข้อมูล',
        'financeOfficer' => 'การเงิน: ผู้จัดทำรายการ',
        'financeApprover' => 'การเงิน: ผู้อนุมัติจ่าย',
        'financeAdmin' => 'การเงิน: ผู้ดูแลระบบ',
    ];

    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        foreach ($this->permissions as $name => $description) {
            if ($auth->getPermission($name) === null) {
                $item = $auth->createPermission($name);
                $item->description = $description;
                $auth->add($item);
            }
        }
        foreach ($this->roles as $name => $description) {
            if ($auth->getRole($name) === null) {
                $item = $auth->createRole($name);
                $item->description = $description;
                $auth->add($item);
            }
        }

        foreach ([
            ['accountingViewer', 'accountingView'],
            ['accountingMaker', 'accountingViewer'],
            ['accountingMaker', 'accountingPrepare'],
            ['accountingReviewer', 'accountingViewer'],
            ['accountingReviewer', 'accountingReview'],
            ['accountingApprover', 'accountingReviewer'],
            ['accountingApprover', 'accountingApprove'],
            ['accountingAdmin', 'accountingMaker'],
            ['accountingAdmin', 'accountingApprover'],
            ['accountingAdmin', 'accountingInboxReceive'],
            ['financeViewer', 'financeView'],
            ['financeOfficer', 'financeViewer'],
            ['financeOfficer', 'financeOperate'],
            ['financeApprover', 'financeViewer'],
            ['financeApprover', 'financeApprove'],
            ['financeAdmin', 'financeOfficer'],
            ['financeAdmin', 'financeApprover'],
            ['admin', 'accountingAdmin'],
            ['admin', 'financeAdmin'],
            ['purchase', 'accountingInboxReceive'],
        ] as [$parent, $child]) {
            $this->ensureChild($parent, $child);
        }
        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        foreach (array_reverse(array_keys($this->roles)) as $name) {
            if ($role = $auth->getRole($name)) {
                $auth->remove($role);
            }
        }
        foreach (array_reverse(array_keys($this->permissions)) as $name) {
            if ($permission = $auth->getPermission($name)) {
                $auth->remove($permission);
            }
        }
        $auth->invalidateCache();
    }

    private function ensureChild(string $parentName, string $childName): void
    {
        $auth = Yii::$app->authManager;
        $parent = $auth->getRole($parentName) ?: $auth->getPermission($parentName);
        $child = $auth->getRole($childName) ?: $auth->getPermission($childName);
        if ($parent !== null && $child !== null && !$auth->hasChild($parent, $child)) {
            $auth->addChild($parent, $child);
        }
    }
}
