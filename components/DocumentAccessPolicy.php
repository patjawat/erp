<?php

namespace app\components;

use app\models\Categorise;
use app\modules\dms\models\DocumentsDetail;
use app\modules\hr\models\Organization;

/** กฎการมองเห็นหนังสือราชการของผู้ใช้ฝั่ง /me */
final class DocumentAccessPolicy
{
    public const DEPARTMENT_ALL = 'all_members';
    public const DEPARTMENT_HEADS = 'heads_only';

    private const EMPLOYEE_DETAIL_NAMES = [
        'comment_emp',
        'tags',
        'employee_tag',
        'employee',
        'req_approve',
    ];

    private const DEPARTMENT_DETAIL_NAMES = [
        'comment_dept',
        'department',
    ];

    public static function departmentMode(): string
    {
        $site = Categorise::findOne(['name' => 'site']);
        $mode = $site && is_array($site->data_json)
            ? ($site->data_json['document_department_access_mode'] ?? null)
            : null;

        return $mode === self::DEPARTMENT_ALL ? self::DEPARTMENT_ALL : self::DEPARTMENT_HEADS;
    }

    public static function canUseDepartmentRoute(int $departmentId, int $employeeId): bool
    {
        if ($departmentId <= 0 || $employeeId <= 0) {
            return false;
        }
        if (self::departmentMode() === self::DEPARTMENT_ALL) {
            return true;
        }

        $organization = Organization::findOne($departmentId);
        $data = $organization ? $organization->data_json : [];
        if (is_string($data)) {
            $data = json_decode($data, true) ?: [];
        }

        return in_array($employeeId, [
            (int) ($data['leader1'] ?? 0),
            (int) ($data['leader_1'] ?? 0),
            (int) ($data['leader2'] ?? 0),
            (int) ($data['leader_2'] ?? 0),
        ], true);
    }

    public static function canRead(int $documentId, int $departmentId, int $employeeId): bool
    {
        if ($documentId <= 0 || $employeeId <= 0) {
            return false;
        }

        $employeeRoute = DocumentsDetail::find()
            ->where(['document_id' => $documentId, 'name' => self::EMPLOYEE_DETAIL_NAMES])
            ->andWhere(['to_id' => (string) $employeeId])
            ->exists();
        if ($employeeRoute) {
            return true;
        }

        return self::canUseDepartmentRoute($departmentId, $employeeId)
            && DocumentsDetail::find()
                ->where(['document_id' => $documentId, 'name' => self::DEPARTMENT_DETAIL_NAMES])
                ->andWhere(['to_id' => (string) $departmentId])
                ->exists();
    }
}
