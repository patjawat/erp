<?php

namespace app\modules\iacRisk\services;

use app\components\UserHelper;
use Yii;

class AccessService
{
    private const ENTRY_PERMISSIONS = [
        'iacRiskView', 'iacRiskAuthor', 'iacRiskUnitApprove', 'iacRiskCoordinate',
        'iacRiskCommittee', 'iacRiskDirector', 'iacRiskAdmin',
    ];

    public function canEnter(): bool
    {
        // บุคลากรทุกคนต้องเปิดดูข้อมูลความเสี่ยงของหน่วยงานตนเองได้
        // สิทธิ์ RBAC ด้านล่างใช้รองรับผู้ตรวจ/ผู้บริหารที่มีขอบเขตกว้างกว่า
        if (!Yii::$app->user->isGuest) return true;
        foreach (self::ENTRY_PERMISSIONS as $permission) {
            if (Yii::$app->user->can($permission)) return true;
        }
        return false;
    }

    public function canManageSettings(): bool { return Yii::$app->user->can('iacRiskAdmin'); }
    public function canScopeAllUnits(): bool { return Yii::$app->user->can('iacRiskAdmin') || Yii::$app->user->can('iacRiskCoordinate'); }
    public function canScopeAllHospitals(): bool { return Yii::$app->user->can('iacRiskAdmin'); }
    public function employee() { return UserHelper::GetEmployee(); }
}
